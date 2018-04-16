<?php

namespace Drupal\Tests\entity_is_public\Functional;

use Drupal\rabbit_hole\Entity\BehaviorSettings;
use Drupal\Tests\BrowserTestBase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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

  /**
   * @var \Drupal\node\Entity\NodeType
   *
   * A node type instance.
   */
  protected $nodeType;

  /**
   * @var \Drupal\node\Entity\Node
   *
   * A node instance.
   */
  protected $node;

  /**
   * @inheritDoc
   */
  protected function setUp() {
    parent::setUp();

    $this->nodeType = $this->drupalCreateContentType();
    $this->node = $this->drupalCreateNode([
      'type' => $this->nodeType->getEntityTypeId(),
      'status' => 1,
    ]);
  }

  public function testDefaultSettings() {
    $this->assertTrue(entity_is_public('node', $this->node));
  }

  public function testContentTypeAllow() {
    $rabbit_hole_behavior = BehaviorSettings::create([
      'status' => TRUE,
      'id' => 'node_type_' . $this->nodeType->getEntityTypeId(),
      'action' => 'display_page',
    ]);
    $rabbit_hole_behavior->save();
    $this->assertTrue(entity_is_public('node', $this->node));
  }

  public function testAccessDenied() {
    $rabbit_hole_behavior = BehaviorSettings::create([
      'status' => TRUE,
      'id' => 'node_type_' . $this->nodeType->getEntityTypeId(),
      'action' => 'access_denied',
      'redirect_code' => Response::HTTP_MOVED_PERMANENTLY,
    ]);
    $rabbit_hole_behavior->save();
    $this->expectException(AccessDeniedHttpException::class);
    entity_is_public('node', $this->node);
  }

  public function testPageNotFound() {
    $rabbit_hole_behavior = BehaviorSettings::create([
      'status' => TRUE,
      'id' => 'node_type_' . $this->nodeType->getEntityTypeId(),
      'action' => 'page_not_found',
      'redirect_code' => Response::HTTP_MOVED_PERMANENTLY,
    ]);
    $rabbit_hole_behavior->save();
    $this->setExpectedException(NotFoundHttpException::class);
    entity_is_public('node', $this->node);
  }

  public function testPageRedirect() {
    $rabbit_hole_behavior = BehaviorSettings::create([
      'status' => TRUE,
      'id' => 'node_type_' . $this->nodeType->getEntityTypeId(),
      'action' => 'page_redirect',
      'redirect_code' => Response::HTTP_MOVED_PERMANENTLY,
      'redirect' => '<front>'
    ]);
    $rabbit_hole_behavior->save();
    $this->assertTrue(entity_is_public('node', $this->node));
  }

  public function testOverrideDefault() {
    $rabbit_hole_behavior = BehaviorSettings::create([
      'status' => TRUE,
      'id' => 'node_type_' . $this->nodeType->getEntityTypeId(),
      'action' => 'display_page',
      'allow_override' => TRUE,
    ]);
    $rabbit_hole_behavior->save();

    $this->node->rh_action->setValue('bundle_default');
    $this->node->save();
    $this->assertTrue(entity_is_public('node', $this->node));
  }

  public function testOverrideAccessDenied() {
    $rabbit_hole_behavior = BehaviorSettings::create([
      'status' => TRUE,
      'id' => 'node_type_' . $this->nodeType->getEntityTypeId(),
      'action' => 'display_page',
      'allow_override' => TRUE,
    ]);
    $rabbit_hole_behavior->save();

    $this->node->rh_action->setValue('access_denied');
    $this->node->save();
    $this->expectException(AccessDeniedHttpException::class);
    entity_is_public('node', $this->node);
  }

  public function testOverridePageNotFound() {
    $rabbit_hole_behavior = BehaviorSettings::create([
      'status' => TRUE,
      'id' => 'node_type_' . $this->nodeType->getEntityTypeId(),
      'action' => 'display_page',
      'allow_override' => TRUE,
    ]);
    $rabbit_hole_behavior->save();

    $this->node->rh_action->setValue('page_not_found');
    $this->node->save();
    $this->expectException(NotFoundHttpException::class);
    $this->assertFalse(entity_is_public('node', $this->node));
  }

  public function testOverridePageRedirect() {
    $rabbit_hole_behavior = BehaviorSettings::create([
      'status' => TRUE,
      'id' => 'node_type_' . $this->nodeType->getEntityTypeId(),
      'action' => 'access_denied',
      'allow_override' => TRUE,
    ]);
    $rabbit_hole_behavior->save();

    $this->node->rh_action->setValue('page_redirect');
    $this->node->rh_redirect->setValue('<front>');
    $this->node->rh_redirect_response->setValue(Response::HTTP_MOVED_PERMANENTLY);
    $this->node->save();
    $this->assertTrue(entity_is_public('node', $this->node));
  }

  public function testOverrideDisplayPage() {
    $rabbit_hole_behavior = BehaviorSettings::create([
      'status' => TRUE,
      'id' => 'node_type_' . $this->nodeType->getEntityTypeId(),
      'action' => 'access_denied',
      'allow_override' => TRUE,
    ]);
    $rabbit_hole_behavior->save();

    $this->node->rh_action->setValue('display_page');
    $this->node->save();
    $this->assertTrue(entity_is_public('node', $this->node));
  }

}
