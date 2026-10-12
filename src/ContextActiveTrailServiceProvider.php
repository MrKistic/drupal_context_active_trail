<?php

namespace Drupal\context_active_trail;

use Drupal\context\ContextMenuActiveTrail;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Overrides the class for the menu link tree.
 */
class ContextActiveTrailServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    $definition = $container->getDefinition('menu.active_trail');
    // Override the menu active trail only when the original class is ContextMenuActiveTrail.
    if ($definition->getClass() == ContextMenuActiveTrail::class) {
      $definition->setClass('Drupal\context_active_trail\ContextActiveTrail');
      $definition->addArgument(new Reference('logger.factory'));
    }
  }

}
