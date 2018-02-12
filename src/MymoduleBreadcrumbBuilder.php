<?php

namespace Drupal\Mymodule;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Link;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;

/**
 * Provides breadcrumb build logic.
 */
class MymoduleBreadcrumbBuilder implements BreadcrumbBuilderInterface {
  use StringTranslationTrait;

  /**
   * Whether this breadcrumb builder should be used to build the breadcrumb.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   *
   * @return bool
   *   TRUE if this builder should be used or FALSE to let other builders
   *   decide.
   */
  public function applies(RouteMatchInterface $route_match) {
    $allowed_routes = ['entity.taxonomy_term.canonical'];
    if (in_array($route_match->getRouteName(), $allowed_routes)) {
      $term = $route_match->getParameter('taxonomy_term');
      if (empty($term) || !is_object($term)) {
        return FALSE;
      }
      if (in_array($term->bundle(), ['subsection'])) {
        if ($universe = _p8z_profile_extend_get_universe()) {
          return TRUE;
        }
      }
    }
    return FALSE;
  }

  /**
   * Builds the breadcrumb.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   *
   * @return \Drupal\Core\Breadcrumb\Breadcrumb
   *   A breadcrumb.
   */
  public function build(RouteMatchInterface $route_match) {
    $universe_term = $route_match->getParameter('taxonomy_term');

    $breadcrumb = new Breadcrumb();
    $breadcrumb->addCacheContexts(["url"]);
    $breadcrumb->addCacheTags(["taxonomy_term:{$universe_term->id()}"]);

    $taxonomy_storage = \Drupal::entityTypeManager()
      ->getStorage('taxonomy_term');
    $parents = $taxonomy_storage->loadAllParents($universe_term->id());
    while ($parent = array_pop($parents)) {
      $breadcrumb->addLink(new Link($parent->getName(), Url::fromRoute('entity.taxonomy_term.canonical', ['taxonomy_term' => $parent->id()])));
    }
    return $breadcrumb;
  }

}
