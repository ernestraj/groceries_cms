<?php

namespace Drupal\cms_custom\Plugin\Rest\Resource;

use Drupal\rest\Plugin\ResourceBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\views\Views;
use Drupal\taxonomy\Entity\Term;
use GuzzleHttp\Exception\RequestException;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Site\Settings;

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
    $latitude = \Drupal::request()->query->get('latitude');
    $longitude = \Drupal::request()->query->get('longitude');
    $client = \Drupal::httpClient();
    $data = [];
    $view = Views::getView('stores');
    $key = Settings::get('');
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
          } elseif ($fid == 'name') {
            try {
              $data[$rid][$fid] = $field->getValue($row);
              $url = 'https://maps.googleapis.com/maps/api/distancematrix/json?origins=' . $latitude . ',' . $longitude . '&destinations=' . UrlHelper::encodePath($field->getValue($row)) . '&key=' . $key;
              $response = $client->get($url);
              $json_response = json_decode($response->getBody(), TRUE);
              $distance = $json_response['rows'][0]['elements'][0]['distance'];
              $data[$rid]['distance'] = $distance['text'];
            } catch (RequestException $e) {
              watchdog_exception('cms_custom', $e->getMessage());
            }
          } else {
            $data[$rid][$fid] = $field->getValue($row);
          }
        }
      }
    }
    return new JsonResponse($data);
  }
}
