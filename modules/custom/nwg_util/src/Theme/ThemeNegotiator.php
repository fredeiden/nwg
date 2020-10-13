<?php
/**
 * @file
 * Contains \Drupal\nwg_util\Theme\ThemeNegotiator
 */
namespace Drupal\nwg_util\Theme;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Theme\ThemeNegotiatorInterface;

class ThemeNegotiator implements ThemeNegotiatorInterface {

    /**
     * @param RouteMatchInterface $route_match
     * @return bool
     */
    public function applies(RouteMatchInterface $route_match)
    {
        return $this->negotiateRoute($route_match) ? true : false;
    }

    /**
     * @param RouteMatchInterface $route_match
     * @return null|string
     */
    public function determineActiveTheme(RouteMatchInterface $route_match)
    {
        return $this->negotiateRoute($route_match) ?: null;
    }

    /**
     * Function that does all of the work in selecting a theme
     * @param RouteMatchInterface $route_match
     * @return bool|string
     */
    private function negotiateRoute(RouteMatchInterface $route_match)
    {
        $name = $route_match->getRouteName();
        //dsm($name);

        $use_admin = array(
            'entity.user.canonical',
            'view.portfolio.page_1',
            'view.artist_portfolio.page_1',
            'layout_builder.defaults.node.view',
        );

        if (in_array($name, $use_admin)) {
            return \Drupal::config('system.theme')->get('admin');
        }

        return false;
    }

}
