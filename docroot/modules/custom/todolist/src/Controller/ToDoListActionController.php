<?php

/**
 * @file
 * Contains \Drupal\todolist\Controller\ToDoListActionController.
 */

namespace Drupal\todolist\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ToDoListActionController extends ControllerBase
{
  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;


  /**
   * @param \Drupal\Core\Database\Connection $connection
   *   The Connection object.
   */
  public function __construct(Connection $connection)
  {
    $this->connection = $connection;
  }

  public static function create(ContainerInterface $container)
  {
    return new static(
      $container->get('database')
    );
  }

  public function complete(Request $request)
  {
    if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
      $data = json_decode($request->getContent(), TRUE);
      print_r($data);
      die;
    }
  }

  public function delete(Request $request)
  {
    if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
      $data = json_decode($request->getContent(), TRUE);
      print_r($data);
      die;
    }
  }
}
