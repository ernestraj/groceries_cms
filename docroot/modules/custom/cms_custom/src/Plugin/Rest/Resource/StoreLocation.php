<?php

namespace Drupal\cms_custom\Plugin\Rest\Resource;

use Drupal\rest\Plugin\ResourceBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\views\Views;
use Drupal\taxonomy\Entity\Term;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\HttpFoundation\Request;
use Psr\Log\LoggerInterface;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource (
 *   id = "cmsCustomAction",
 *   label = @Translation("CMS Custom Rest Resource"),
 *   uri_paths = {
 *     "canonical" = "item/{nid}"
 *   }
 * )
 */
class StoreLocation extends ResourceBase
{

  /**
   * Responds to delete requests.
   */

  public function get($nid)
  {
    $data = [];
    $view = Views::getView('stores');
    if (is_object($view)) {
      $view->setDisplay('rest_export_1');
      $view->setArguments([$nid]);
      $view->execute();
      foreach ($view->result as $rid => $row) {
        foreach ($view->field as $fid => $field) {
          if (in_array($fid, ['field_brand', 'field_grocery_aisle'])) {
            $term = Term::load($field->getValue($row));
            $name = $term->getName();
            $data[$rid][$fid] = $name;
          } else {
            $data[$rid][$fid] = $field->getValue($row);
          }
        }
      }
    }
    return new JsonResponse($data);
  }
}
