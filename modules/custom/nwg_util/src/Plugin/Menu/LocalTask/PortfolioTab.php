<?php

namespace Drupal\nwg_util\Plugin\Menu\LocalTask;

use Drupal\Core\Menu\LocalTaskDefault;
use Drupal\Core\Routing\RouteMatchInterface;

class PortfolioTab extends LocalTaskDefault {

  /**
  * {@inheritdoc}
  */
  public function getRouteParameters(RouteMatchInterface $route_match) {
    dsm($route_match->getRouteObject());
    $user_id = $route_match->getParameter('user');
    return [
      'user' => isset($user_id) ? \Drupal::currentUser()->id() : $user_id,
    ];
  }

}
