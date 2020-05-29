<?php

namespace Drupal\cms_custom\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\views\Views;
use Drupal\taxonomy\Entity\Term;
use GuzzleHttp\Exception\RequestException;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Component\Utility\Html;
use Drupal\Core\Site\Settings;
use \Drupal\node\Entity\Node;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource (
 *   id = "cms_custom_action",
 *   label = @Translation("CMS Custom Rest Resource"),
 *   uri_paths = {
 *     "canonical" = "item/{nid}",
 *     "https://www.drupal.org/link-relations/create" = "item/create",
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
    $key = Settings::get('cms_custom.google-api-key');
    if (is_object($view)) {
      $view->setDisplay('rest_export_1');
      $view->setArguments([$nid]);
      $view->execute();
      foreach ($view->result as $rid => $row) {
        foreach ($view->field as $fid => $field) {
          $field_output = $view->style_plugin->getFieldValue($rid, $fid);

          if ($fid == 'field_brand') {
            $term = Term::load($field_output);
            $name = $term->getName();
            $data[$rid][$fid] = $name;
          } elseif ($fid == 'field_grocery_aisle') {
            $term = Term::load($field_output[0]);
            $name = $term->getName();
            $data[$rid][$fid] = $name;
          } elseif ($fid == 'name') {
            try {
              $data[$rid][$fid] = $field_output;
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

  public function post($data)
  {
    if (empty($data["brand"]) || empty($data["address"]) || empty($data["grocery"]) || empty($data["grocery"])) {
      return new JsonResponse(['message' => "Payload is not correct"], 400);
    }
    if (!empty($data["brand_id"])) {
      $brand = \Drupal::entityManager()->getStorage('taxonomy_term')->load($data["brand_id"]);
    } else if (empty($brand)) {
      $brand = Term::create([
        'name' => Html::escape($data["brand"]),
        'vid' => 'brand',
      ])->save();
      $brand_term_name = \Drupal::entityManager()->getStorage('taxonomy_term')->loadByProperties(["name" => $data["brand"]]);
      $brand = $brand_term_name[key($brand_term_name)];
    }

    if (!empty($data["grocery_brand_id"])) {
      $grocery_brand =  \Drupal::entityManager()->getStorage('taxonomy_term')->load($data["grocery_brand_id"]);
    }
    else if(empty($grocery_brand)) {
      $grocery_brand = Term::create([
        'name' => Html::escape($data["grocery_brand"]),
        'vid' => 'grocery_brand',
        'field_available' => 1
      ])->save();
      $grocery_brand_term_name = \Drupal::entityManager()->getStorage('taxonomy_term')->loadByProperties(["name" => $data["grocery_brand"]]);
      $grocery_brand = $grocery_brand_term_name[key($grocery_brand_term_name)];
    }

    if (!empty($data["grocery_id"])) {
      $grocery = \Drupal::entityManager()->getStorage('node')->load($data["grocery_id"]);
    } else if (empty($grocery)) {
      $grocery = Node::create([
        'type' => 'groceries',
        'title' => Html::escape($data["grocery"]),
        'field_grocery_description' => Html::escape($data["description"]),
        'field_grocery_brand' => [$grocery_brand->id()]
      ]);
      $grocery->save();
    }

    if (!empty($data["aisle_id"])) {
      $aisle = \Drupal::entityManager()->getStorage('taxonomy_term')->load($data["aisle_id"]);
    } else if (empty($aisle)) {
      $aisle = Term::create([
        'name' => Html::escape($data["aisle"]),
        'vid' => 'grocery_aisle',
        'field_grocery_item' => [$grocery->id()]
      ])->save();
      $aisle_term_name = \Drupal::entityManager()->getStorage('taxonomy_term')->loadByProperties(["name" => $data["aisle"]]);
      $aisle = $aisle_term_name[key($aisle_term_name)];
    }

    if (!empty($data["address"])) {
      $address = \Drupal::entityManager()->getStorage('taxonomy_term')->loadByProperties(["name" => $data["address"]]);
      if (empty($address)) {
        $address = Term::create([
          'name' => Html::escape($data["address"]),
          'vid' => 'store_location',
          'field_brand' => $brand->id(),
          'field_grocery_aisle' => [$aisle->id()],
          'field_grocery_brand' => [$grocery_brand->id()]
        ])->save();
        $address = \Drupal::entityManager()->getStorage('taxonomy_term')->loadByProperties(["name" => $data["address"]]);
      }
    }

    $groceries = !empty($aisle->field_grocery_item) ? $aisle->field_grocery_item->getValue() : [];
    if (!empty($groceries)) {
      $groceries = call_user_func_array('array_merge', $groceries);
      $groceries = array_values($groceries);
    }
    if (!in_array($grocery->id(), $groceries)) {
      $aisle->field_grocery_item->appendItem($grocery->id());
      $aisle->save();
    }

    $aisles = !empty($address[key($address)]->field_grocery_aisle) ? $address[key($address)]->field_grocery_aisle->getValue() : [];
    if (!empty($aisles)) {
      $aisles = call_user_func_array('array_merge', $aisles);
      $aisles = array_values($aisles);
    }
    if (!in_array($aisle->id(), $aisles)) {
      $address[key($address)]->field_grocery_aisle->appendItem($aisle->id());
      $address[key($address)]->save();
    }

    return new JsonResponse(["message" => "Grocery Item has been created."], 200);
  }
}
