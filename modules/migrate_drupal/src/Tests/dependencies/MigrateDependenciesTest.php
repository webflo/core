<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\dependencies\MigrateDependenciesTest.
 */

namespace Drupal\migrate_drupal\Tests\dependencies;

use Drupal\Component\Utility\SafeMarkup;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate_drupal\Tests\d6\MigrateDrupal6TestBase;

/**
 * Ensure the consistency among the dependencies for migrate.
 *
 * @group migrate_drupal
 */
class MigrateDependenciesTest extends MigrateDrupal6TestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['aggregator', 'comment'];

  /**
   * Tests that the order is correct when loading several migrations.
   */
  public function testMigrateDependenciesOrder() {
    $migration_items = array('d6_comment', 'd6_filter_format', 'd6_node:page');
    // @TODO https://www.drupal.org/node/2666640
    return;
    $migrations = $this->getMigration()->createInstances($migration_items);
    $expected_order = array('d6_filter_format', 'd6_node:page', 'd6_comment');
    $this->assertIdentical(array_keys($migrations), $expected_order);
    $expected_requirements = array(
      // d6_comment depends on d6_node:*, which the storage controller expands
      // into every variant of d6_node created by the MigrationBuilder.
      'd6_node:article',
      'd6_node:company',
      'd6_node:employee',
      'd6_node:event',
      'd6_node:page',
      'd6_node:sponsor',
      'd6_node:story',
      'd6_node:test_event',
      'd6_node:test_page',
      'd6_node:test_planet',
      'd6_node:test_story',
      'd6_node_type',
      'd6_node_settings',
      'd6_filter_format',
      'd6_user',
      'd6_comment_type',
      'd6_comment_entity_display',
      'd6_comment_entity_form_display',
    );
    // Migration dependencies for comment include dependencies for node
    // migration as well.
    $actual_requirements = $migrations['d6_comment']->get('requirements');
    $this->assertIdentical(count($actual_requirements), count($expected_requirements));
    foreach ($expected_requirements as $requirement) {
      $this->assertIdentical($actual_requirements[$requirement], $requirement);
    }
  }

  /**
   * Tests dependencies on the migration of aggregator feeds & items.
   */
  public function testAggregatorMigrateDependencies() {
    // @TODO: fix when dependencies are working again.
    return;
    /** @var \Drupal\migrate\entity\Migration $migration */
    $migration = $this->getMigration('d6_aggregator_item');
    $executable = new MigrateExecutable($migration, $this);
    $this->startCollectingMessages();
    $executable->import();
    $this->assertEqual($this->migrateMessages['error'], array(SafeMarkup::format('Migration @id did not meet the requirements. Missing migrations d6_aggregator_feed. requirements: d6_aggregator_feed.', array('@id' => $migration->id()))));
    $this->collectMessages = FALSE;
  }

}
