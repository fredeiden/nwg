<?php

namespace Drupal\nwg_util\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {

      //    if ($route = $collection->get('entity.taxonomy_term.canonical')) {
      //    }

  }

}
