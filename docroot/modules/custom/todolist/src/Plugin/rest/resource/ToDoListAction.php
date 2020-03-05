<?php

namespace Drupal\todolist\Plugin\rest\resource;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Psr\Log\LoggerInterface;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "todolistaction",
 *   label = @Translation("To Do List Rest Resource"),
 *   uri_paths = {
 *     "canonical" = "todolist/delete"
 *     "https://www.drupal.org/link-relations/create" = "todolist/completed"
 *   }
 * )
 */
class ToDoListAction extends ResourceBase
{

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition)
  {
    die('hello');
  }

  /**
   * Responds to GET requests.
   *
   * Returns a list of bundles for specified entity.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function get()
  {
    $response = 'hello world';
    return new ResourceResponse($response);
  }

  public function post(array $data = [])
  {
    $response = array(
      "hello_world" => $data,
    );
    return new ResourceResponse($response);
  }

  public function patch($arg)
  {
    return new ResourceResponse('hello patch');
  }
}
