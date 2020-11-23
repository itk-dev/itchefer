<?php

namespace Drupal\itchefer_event_enrollment\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Defines EventEnrollmentController class.
 */
class EventEnrollmentController extends ControllerBase {

  /**
   * Display the markup.
   *
   * @return array
   *   Return markup array.
   */
  public function content() {
    return [
      '#type' => 'markup',
      '#markup' => $this->t('Hello, World!'),
    ];
  }

}
