<?php

namespace Drupal\Tests\entity_is_public\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests for the FieldHelper class.
 *
 * @group entity_is_public
 */
class EntityIsPublicTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['entity_is_public', 'node', 'path'];

  public function testNodeIsPublic() {
    $node_type = $this->drupalCreateContentType();

    $node = $this->drupalCreateNode([
      'type' => $node_type->getEntityTypeId(),
      'status' => 1,
    ]);
    $this->assertTrue(entity_is_public('node', $node));

    // @TODO not sure what the following assertion is trying to prove.
    //$this->assertFalse(entity_is_public('node', $node, [], ['uri' => ['path' => '']]));
    $this->assertFalse(entity_is_public('node', $node, [], ['uri' => ['path' => 'admin/node']]));
    \Drupal::configFactory()->getEditable('system.site')->set('page.front', 'admin/node')->save();
    $this->assertTrue(entity_is_public('node', $node, [], ['uri' => ['path' => 'admin/node']]));

    // Test the 'alias required' option.
    $this->assertFalse(entity_is_public('node', $node, ['alias required' => TRUE], []));
    $node = $this->drupalCreateNode([
      'type' => $node_type->getEntityTypeId(),
      'status' => 1,
      'path' =>  ['alias' => '/test-alias'],
    ]);
    drupal_static_reset();
    $this->assertTrue(entity_is_public('node', $node, ['alias required' => TRUE], []));

    // Test with an unpublished node.
    $node = $this->drupalCreateNode([
      'type' => $node_type->getEntityTypeId(),
      'status' => 0,
    ]);
    drupal_static_reset();
    $this->assertFalse(entity_is_public('node', $node));
  }

}
