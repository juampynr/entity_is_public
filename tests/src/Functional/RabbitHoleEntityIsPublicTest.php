<?php

namespace Drupal\Tests\entity_is_public\Functional;

use Drupal\rabbit_hole\Entity\BehaviorSettings;
use Drupal\Tests\BrowserTestBase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Test the Rabbit hole integration.
 *
 * @group entity_is_public
 */
class RabbitHoleEntityIsPublicTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['entity_is_public', 'node', 'rabbit_hole', 'rh_node'];

  public function testNodeIsPublic() {
    $node_type = $this->drupalCreateContentType();
    $node = $this->drupalCreateNode([
      'type' => $node_type->getEntityTypeId(),
      'status' => 1,
    ]);
    $this->assertTrue(entity_is_public('node', $node));

    $rabbit_hole_behavior = BehaviorSettings::create([
      'status' => TRUE,
      'id' => 'node_type_' . $node_type->getEntityTypeId(),
      'action' => 'access_denied',
      'redirect_code' => Response::HTTP_MOVED_PERMANENTLY,
    ]);
    $rabbit_hole_behavior->save();
    $this->assertFalse(entity_is_public('node', $node));

    $rabbit_hole_behavior->setAction('page_not_found');
    $rabbit_hole_behavior->save();
    $this->assertFalse(entity_is_public('node', $node));

    $rabbit_hole_behavior->setAction('page_redirect');
    $rabbit_hole_behavior->save();
    $this->assertFalse(entity_is_public('node', $node));

    $rabbit_hole_behavior->setAction('display_page');
    $rabbit_hole_behavior->save();
    $this->assertTrue(entity_is_public('node', $node));

    // Allow individual node overrides.
    $rabbit_hole_behavior->setAllowOverride(TRUE);
    $rabbit_hole_behavior->save();

    $node->rh_action->setValue('bundle_default');
    $node->save();
    $this->assertTrue(entity_is_public('node', $node));

    $node->rh_action->setValue('access_denied');
    $node->save();
    $this->assertFalse(entity_is_public('node', $node));

    $node->rh_action->setValue('page_not_found');
    $node->save();
    $this->assertFalse(entity_is_public('node', $node));

    $node->rh_action->setValue('page_redirect');
    $node->save();
    $this->assertFalse(entity_is_public('node', $node));

    $node->rh_action->setValue('display_page');
    $node->save();
    $this->assertTrue(entity_is_public('node', $node));
  }

}
