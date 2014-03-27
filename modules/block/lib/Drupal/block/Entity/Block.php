<?php

/**
 * @file
 * Contains \Drupal\block\Entity\Block.
 */

namespace Drupal\block\Entity;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\block\BlockPluginBag;
use Drupal\block\BlockInterface;
use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Config\Entity\EntityWithPluginBagInterface;
use Drupal\Core\Entity\EntityStorageInterface;

/**
 * Defines a Block configuration entity class.
 *
 * @ConfigEntityType(
 *   id = "block",
 *   label = @Translation("Block"),
 *   controllers = {
 *     "access" = "Drupal\block\BlockAccessController",
 *     "view_builder" = "Drupal\block\BlockViewBuilder",
 *     "list_builder" = "Drupal\block\BlockListBuilder",
 *     "form" = {
 *       "default" = "Drupal\block\BlockFormController",
 *       "delete" = "Drupal\block\Form\BlockDeleteForm"
 *     }
 *   },
 *   admin_permission = "administer blocks",
 *   fieldable = FALSE,
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   },
 *   links = {
 *     "delete-form" = "block.admin_block_delete",
 *     "edit-form" = "block.admin_edit"
 *   }
 * )
 */
class Block extends ConfigEntityBase implements BlockInterface, EntityWithPluginBagInterface {

  /**
   * The ID of the block.
   *
   * @var string
   */
  public $id;

  /**
   * The plugin instance settings.
   *
   * @var array
   */
  protected $settings = array();

  /**
   * The region this block is placed in.
   *
   * @var string
   */
  protected $region = self::BLOCK_REGION_NONE;

  /**
   * The block weight.
   *
   * @var int
   */
  public $weight;

  /**
   * The plugin instance ID.
   *
   * @var string
   */
  protected $plugin;

  /**
   * The plugin bag that holds the block plugin for this entity.
   *
   * @var \Drupal\block\BlockPluginBag
   */
  protected $pluginBag;

  /**
   * {@inheritdoc}
   */
  protected $pluginConfigKey = 'settings';

  /**
   * The visibility settings.
   *
   * @var array
   */
  protected $visibility;

  /**
   * {@inheritdoc}
   */
  public function getPlugin() {
    return $this->getPluginBag()->get($this->plugin);
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginBag() {
    if (!$this->pluginBag) {
      $this->pluginBag = new BlockPluginBag(\Drupal::service('plugin.manager.block'), $this->plugin, $this->get('settings'), $this->id());
    }
    return $this->pluginBag;
  }

  /**
   * Overrides \Drupal\Core\Entity\Entity::label();
   */
  public function label() {
    $settings = $this->get('settings');
    if ($settings['label']) {
      return $settings['label'];
    }
    else {
      $definition = $this->getPlugin()->getPluginDefinition();
      return $definition['admin_label'];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function toArray() {
    $properties = parent::toArray();
    $names = array(
      'theme',
      'region',
      'weight',
      'plugin',
      'settings',
      'visibility',
    );
    foreach ($names as $name) {
      $properties[$name] = $this->get($name);
    }
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);

    if ($update) {
      Cache::invalidateTags(array('block' => $this->id()));
    }
    // When placing a new block, invalidate all cache entries for this theme,
    // since any page that uses this theme might be affected.
    else {
      // @todo Replace with theme cache tag: https://drupal.org/node/2185617
      Cache::invalidateTags(array('content' => TRUE));
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function postDelete(EntityStorageInterface $storage, array $entities) {
    parent::postDelete($storage, $entities);

    Cache::invalidateTags(array('block' => array_keys($entities)));
  }

  /**
   * Sorts active blocks by weight; sorts inactive blocks by name.
   */
  public static function sort(ConfigEntityInterface $a, ConfigEntityInterface $b) {
    // Separate enabled from disabled.
    $status = $b->get('status') - $a->get('status');
    if ($status) {
      return $status;
    }
    // Sort by weight, unless disabled.
    if ($a->get('region') != static::BLOCK_REGION_NONE) {
      $weight = $a->get('weight') - $b->get('weight');
      if ($weight) {
        return $weight;
      }
    }
    // Sort by label.
    return strcmp($a->label(), $b->label());
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    parent::calculateDependencies();
    $this->addDependency('theme', $this->theme);
    return $this->dependencies;
  }

}
