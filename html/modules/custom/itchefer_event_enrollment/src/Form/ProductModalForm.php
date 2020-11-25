<?php

namespace Drupal\itchefer_event_enrollment\Form;

use Drupal\Core\Ajax\CloseDialogCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\social_event\Entity\EventEnrollment;
use Drupal\social_event\EventEnrollmentInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * ProductModalForm class.
 */
class ProductModalForm extends FormBase {
  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    AccountProxyInterface $currentUser
  ) {
    $this->currentUser = $currentUser;
  }

  /**
   * A STOLEN HACK!
   *
   * @see https://www.drupal.org/project/drupal/issues/2742859
   */
  private function initialize() {
    $container = \Drupal::getContainer();
    if (NULL === $this->currentUser) {
      $this->currentUser = $container->get('current_user');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'product_modal_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $options = NULL) {
    $node = \Drupal::routeMatch()->getParameter('node');
    $nid = $node->id();
    $uid = $this->currentUser->id();
    $field_products = \Drupal::entityTypeManager()->getStorage('node')->load($nid)->get('field_product')->referencedEntities();
    $to_enroll_status = '1';
    $submit_text = $this->t('Enroll');
    $enrollment_open = TRUE;
    $request_to_join = FALSE;
    $isNodeOwner = ($node->getOwnerId() === $uid);

    // Add request to join event.
    if ((int) $node->field_enroll_method->value === EventEnrollmentInterface::ENROLL_METHOD_REQUEST && !$isNodeOwner) {
      $submit_text = $this->t('Send request');
      $to_enroll_status = '2';

      if ($current_user->isAnonymous()) {
        $attributes = [
          'class' => [
            'use-ajax',
            'js-form-submit',
            'form-submit',
            'btn',
            'btn-accent',
            'btn-lg',
          ],
          'data-dialog-type' => 'modal',
          'data-dialog-options' => json_encode([
            'title' => t('Request to enroll'),
            'width' => 'auto',
          ]),
        ];

        $request_to_join = TRUE;
      }
    }
    $form['description'] = [
      '#type' => 'html_tag',
      '#tag' => 'p',
      '#value' => $this->t('Add the products you wish for the event.'),
    ];

    if ($request_to_join === '1') {
      $form['description'] = [
        '#type' => 'html_tag',
        '#tag' => 'p',
        '#value' => $this->t('You can leave a message in your request. Only when your request is approved, you will receive a notification via email and notification center. You also need to pick a product to request the event with.'),
      ];

      $form['message'] = [
        '#type' => 'textarea',
        '#title' => $this->t('Message'),
        '#maxlength' => 250,
      ];
    }
    $form['to_enroll_status'] = [
      '#type' => 'hidden',
      '#value' => $to_enroll_status,
    ];

    $form['#attributes']['name'] = 'product_modal_form';
    // Radio buttons without options.
    $form['radios'] = [
      '#type' => 'radios',
      '#title' => 'Tilvalg',
      '#options' => [],
    ];

    // Options being added from referenced entities.
    foreach ($field_products as $key => $value) {
      $form['radios']['#options'] += [
        $value->id() => $value->label(),
      ];
    }

    $form['#prefix'] = '<div id="product_selection">';
    $form['#suffix'] = '</div>';

    // The status messages that will contain any form errors.
    $form['status_messages'] = [
      '#type' => 'status_messages',
      '#weight' => -10,
    ];

    $form['event'] = [
      '#type' => 'hidden',
      '#value' => $nid,
    ];

    $form['enroll_for_this_event'] = [
      '#type' => 'submit',
      '#value' => $submit_text,
      '#disabled' => !$enrollment_open,
      '#attributes' => $attributes,
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $submit_text,
      '#disabled' => !$enrollment_open,
      '#attributes' => $attributes,
      '#button_type' => 'primary',
      '#ajax' => [
        'callback' => [$this, 'submitModalFormAjax'],
        'event' => 'click',
      ],
    ];

    $form['#attached']['library'] = [
      'core/drupal.dialog.ajax',
      'social_event/modal',
    ];
    $form['#attached']['drupalSettings']['eventProductSelection'] = [
      'closeDialog' => TRUE,
    ];

    return $form;
  }

  /**
   * AJAX callback handler that displays any errors or a success message.
   */
  public function submitModalFormAjax(array $form, FormStateInterface $form_state) {
    $response = new AjaxResponse();

    if ($form_state->getErrors()) {
      // If there are errors, we can show the form again with the errors in
      // the status_messages section.
      $form['status_messages'] = [
        '#type' => 'status_messages',
        '#weight' => -10,
      ];
      $form_state->setRebuild();

      return $response->addCommand(new OpenModalDialogCommand($this->t('Pick a product'), $form, static::getDataDialogOptions()));
    }

    // Refactor this into a service or helper.
    $message = $form_state->getValue('message');

    $current_user = \Drupal::currentUser();
    $uid = $current_user->id();

    $nid = $form_state->getValue('event');

    // Default event enrollment field set.
    $fields = [
      'user_id' => $uid,
      'field_event' => $nid,
      'field_enrollment_status' => '0',
      'field_account' => $uid,
      'field_request_or_invite_status' => EventEnrollmentInterface::REQUEST_PENDING,
      'field_request_message' => $message,
    ];

    // Create a new enrollment for the event.
    $enrollment = EventEnrollment::create($fields);
    $enrollment->save();

    // On success leave a message and reload the page.
    \Drupal::messenger()->addStatus(t('Your request has been sent successfully'));
    return $response->addCommand(new CloseDialogCommand());
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {}

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {}

  /**
   * Gets the configuration names that will be editable.
   *
   * @return array
   *   An array of configuration object names that are editable if called in
   *   conjunction with the trait's config() method.
   */
  protected function getEditableConfigNames() {
    return ['config.request_enrollment_modal_form'];
  }

  /**
   * Helper method so we can have consistent dialog options.
   *
   * @return string[]
   *   An array of jQuery UI elements to pass on to our dialog form.
   */
  protected static function getDataDialogOptions() {
    return [
      'dialogClass' => 'form--default social_event-popup',
      'closeOnEscape' => TRUE,
      'width' => '582',
    ];
  }

}
