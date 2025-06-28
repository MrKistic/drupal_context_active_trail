<?php

namespace Drupal\context_active_trail;

use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\context\ContextManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Lock\LockBackendInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Menu\MenuActiveTrail;
use Drupal\Core\Menu\MenuLinkManagerInterface;
use Drupal\Core\Path\PathMatcherInterface;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Allow the active trail to be set manually.
 */
class ContextActiveTrail extends MenuActiveTrail {
  /**
   * Cache tag for things that are dependent on the breadcrumbs configuration.
   *
   * Whenever something might change which breadcrumb builder is responsible for
   * a given breadcrumb, this tag needs to be invalidated.
   */
  const CACHE_TAG_BREADCRUMBS = "cache_tag_breadcrumbs";

  /**
   * The context manager.
   *
   * @var \Drupal\Context\ContextManager
   */
  protected $contextManager;

  /**
   * The logger service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Menu\MenuLinkManagerInterface $menu_link_manager
   *   The menu link plugin manager.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   A route match object for finding the active link.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The cache backend.
   * @param \Drupal\Core\Lock\LockBackendInterface $lock
   *   The lock backend.
   * @param \Drupal\Core\Path\PathMatcherInterface $pathMatcher
   *    The path.matcher service.
   * @param \Drupal\Context\ContextManager $context_manager
   *   The context manager.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger service.
   */
  public function __construct(
    MenuLinkManagerInterface $menu_link_manager,
    RouteMatchInterface $route_match,
    CacheBackendInterface $cache,
    LockBackendInterface $lock,
    PathMatcherInterface $pathMatcher,
    ContextManager $context_manager,
    LoggerChannelFactoryInterface $logger_factory
  ) {
    parent::__construct($menu_link_manager, $route_match, $cache, $lock, $pathMatcher);
    $this->contextManager = $context_manager;
    $this->logger = $logger_factory->get('context_active_trail');
    $this->tags[] = 'context_active_trail';
  }

  /**
   * {@inheritdoc}
   */
  public function getActiveLink($menu_name = NULL) {
    // Try to get the value from context.
    foreach ($this->contextManager->getActiveReactions('active_trail') as $reaction) {
      if ($link_id = $reaction->getLinkId()) {
        try {
          $instance = $this->menuLinkManager->getInstance(['id' => $link_id]);
        }
        catch (PluginNotFoundException $e) {
          $this->logger->error('Could not find the configured menu link to set active: @error', [
            '@error' => $e->getMessage(),
          ]);
          return FALSE;
        }

        // Check that the instance is in the menu.
        if (!empty($menu_name) && $instance->getMenuName() != $menu_name) {
          break;
        }

        return $instance;
      }
    }

    // Fall back to the default.
    return parent::getActiveLink($menu_name);
  }

}
