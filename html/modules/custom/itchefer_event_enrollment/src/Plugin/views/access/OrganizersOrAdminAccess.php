<?php

namespace Drupal\itchefer_event_enrollment\Plugin\views\access;

use Drupal\Core\Session\AccountInterface;
use Drupal\views\Plugin\views\access\AccessPluginBase;
use Symfony\Component\Routing\Route;

/**
 * Organizers or admin access plugin.
 *
 * @ingroup views_access_plugins
 *
 * @ViewsAccess(
 *   id = "organizers_or_admin_access",
 *   title = @Translation("Organizers or admin access"),
 *   help = @Translation("Access to the event manage all enrollment page.")
 * )
 */
class OrganizersOrAdminAccess extends AccessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account) {
    return $account->isAuthenticated();
  }

  /**
   * {@inheritdoc}
   */
  public function alterRouteDefinition(Route $route) {
    $route->setRequirement('_custom_access', '\Drupal\itchefer_event_enrollment\Controller\OrganizersOrAdminAccessController::access');
  }

}
