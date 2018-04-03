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

    $this->assertFalse(entity_is_public('node', $node, [], ['uri' => ['path' => '']]));
    $this->assertFalse(entity_is_public('node', $node, [], ['uri' => ['path' => 'admin/node']]));
    \Drupal::configFactory()->getEditable('system.site')->set('page.front', 'admin/node')->save();
    $this->assertTrue(entity_is_public('node', $node, [], ['uri' => ['path' => 'admin/node']]));

    // Test the 'alias required' option.
    $this->assertFalse(entity_is_public('node', $node, ['alias required' => TRUE], []));
    $node->path['alias'] = 'test-alias';
    $node->save();
    drupal_static_reset();
    $this->assertTrue(entity_is_public('node', $node, ['alias required' => TRUE], []));

    // Test unpublishing the node.
    $node->status = 0;
    $node->save();
    drupal_static_reset();
    $this->assertFalse(entity_is_public('node', $node));
  }

}
