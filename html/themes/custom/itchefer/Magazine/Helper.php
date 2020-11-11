<?php

namespace Drupal\itchefer\Magazine;
/**
 * Magazine helper.
 */
class Helper {

  /**
   * Implements hook_form_alter().
   */
  public function preprocess(array &$variables) {
    $articles = array();
    if ($variables['node']->getType() === 'magazine') {
        $highlighted_articles = $variables['node']->get('field_highlighted_articles')->referencedEntities();
        foreach ($highlighted_articles as $article) {
            array_push($articles, $article->link());
        }
    }
    $variables['articles'] = $articles;
  }
}
