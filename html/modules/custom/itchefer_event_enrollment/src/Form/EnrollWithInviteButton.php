<?php

namespace Drupal\itchefer_event_enrollment\Form;

use Drupal\social_event\EventEnrollmentInterface;
use Drupal\social_event\Form\EnrollActionForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Url;

/**
 * Enroll button to open a product modal, for invited users.
 *
 * @package Drupal\social_event_invite\Form
 */
class EnrollWithInviteButton extends EnrollActionForm {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'enroll-with-invite-button';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Node $node = NULL) {
    $form = parent::buildForm($form, $form_state);
    $nid = $this->routeMatch->getRawParameter('node');
    $uid = \Drupal::currentUser()->id();
    $current_user = $this->userStorage->load($uid);

    if (!$current_user->isAnonymous()) {
      $conditions = [
        'field_account' => $uid,
        'field_event' => $nid,
      ];
      $enrollments = \Drupal::entityTypeManager()->getStorage('event_enrollment')->loadByProperties($conditions);
      //$enrollments = $this->entityStorage->loadByProperties($conditions);

      // If the event is invite only and you have not been invited, return.
      // Unless you are the node owner or organizer.
      if (empty($enrollments)) {
        if ((int) $node->field_enroll_method->value === EventEnrollmentInterface::ENROLL_METHOD_INVITE
          && social_event_manager_or_organizer() === FALSE) {
          return [];
        }
      }
      elseif ($enrollment = array_pop($enrollments)) {
        $enroll_request_status = $enrollment->field_request_or_invite_status->value;

        // If user got invited perform actions.
        if ($enroll_request_status == '4') {
          $submit_text = $this->t('Accept');
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
              'title' => t('Pick a product and accept'),
              'width' => 'auto',
            ]),
          ];

          $form['enroll_for_this_event'] = [
            '#type' => 'link',
            '#title' => $submit_text,
            '#url' => Url::fromRoute('itchefer_event_enrollment.product_modal_form', ['node' => $nid]),
            '#attributes' => $attributes,
          ];

          // We need a hidden element for later usage.
          $form['event_id'] = [
            '#type' => 'hidden',
            '#value' => $this->routeMatch->getRawParameter('node'),
          ];

          // Add a hidden operation we can fill with jquery when declining.
          $form['operation'] = [
            '#type' => 'hidden',
            '#default_value' => '',
          ];

          $form['#attached']['library'][] = 'social_event/form_submit';

        }
      }
    }

    // For AN users it can be rendered on a Public event with
    // invite only as option. Let's make it similar to a Group experience
    // where there is no button rendered.
    // We unset it here because in the parent form and this form
    // a lot of times this button get's overridden.
    if ($current_user->isAnonymous()) {
      unset($form['enroll_for_this_event']);
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {}

}
