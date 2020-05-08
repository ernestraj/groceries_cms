<?php

namespace Drupal\todolist\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource (
 *   id = "todolistaction",
 *   label = @Translation("To Do List Rest Resource"),
 *   uri_paths = {
 *     "canonical" = "todolist/actions"
 *   }
 * )
 */
class ToDoListAction extends ResourceBase
{

  /**
   * Responds to delete requests.
   */

  public function delete($data)
  {
    $database = \Drupal::database();
    $result = $database->delete('todo')->condition('id', $data['delete'])->execute();
    if ($result) {
      return new JsonResponse(['operation' => 'completed']);
    } else {
      return new JsonResponse(['operation' => 'error']);
    }
  }

  /**
   *  Responds to put requests.
   */
  public function put($data)
  {
    $database = \Drupal::database();
    $id = $data['completed'];
    $status = 0;
    if ($data['operation'] == "complete") {
      $status = 1;
    }
    $result = $database->update('todo')->fields(['completed' => $status])->condition('id', $id)->execute();
    if ($result) {
      return new ResourceResponse(['operation' => 'completed']);
    } else {
      return new ResourceResponse(['operation' => 'error']);
    }
  }
}
