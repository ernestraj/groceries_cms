<?php

namespace Drupal\cms_custom\Plugin\Rest\Resource;

use Drupal\rest\Plugin\ResourceBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use GuzzleHttp\Exception\RequestException;
use Drupal\Component\Utility\UrlHelper;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource (
 *   id = "cmsCustomUserData",
 *   label = @Translation("Resource to get user data."),
 *   uri_paths = {
 *     "canonical" = "user_data",
 *   }
 * )
 */
class GetUserData extends ResourceBase
{

  /**
   * Responds to delete requests.
   */

  public function get()
  {
    $user = $this->currentUser();
    print_r($user->id());
    return new JsonResponse(["message" => "Grocery Item has been created."], 200);
  }
}
