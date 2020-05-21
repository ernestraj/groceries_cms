<?php

namespace Drupal\cms_custom\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class EntityTypeSubscriber.
 *
 * @package Drupal\custom_events\EventSubscriber
 */
class UserRegisterationSubscriber implements EventSubscriberInterface {
  /**
   * {@inheritdoc}
   *
   * @return array
   *   The event names to listen for, and the methods that should be executed.
   */
  public static function getSubscribedEvents() {
    return [
      EntityTypeEvents::CREATE => 'userCreate',
    ];
  }
}

