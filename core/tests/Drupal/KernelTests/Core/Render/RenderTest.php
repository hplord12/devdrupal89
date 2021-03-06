<?php

namespace Drupal\KernelTests\Core\Render;

use Drupal\KernelTests\KernelTestBase;

/**
 * Performs functional tests on drupal_render().
 *
 * @group Common
 */
class RenderTest extends KernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['system', 'common_test', 'theme_test'];

  /**
   * Tests theme preprocess functions being able to attach assets.
   */
  public function testDrupalRenderThemePreprocessAttached() {
    \Drupal::state()->set('theme_preprocess_attached_test', TRUE);

    $test_element = [
      '#theme' => 'common_test_render_element',
      'foo' => [
        '#markup' => 'Kittens!',
      ],
    ];
    \Drupal::service('renderer')->renderRoot($test_element);

    $expected_attached = [
      'library' => [
        'test/generic_preprocess',
        'test/specific_preprocess',
      ],
    ];
    $this->assertEqual($expected_attached, $test_element['#attached'], 'All expected assets from theme preprocess hooks attached.');

    \Drupal::state()->set('theme_preprocess_attached_test', FALSE);
  }

  /**
   * Ensures that render array children are processed correctly.
   */
  public function testRenderChildren() {
    // Ensure that #prefix and #suffix is only being printed once since that is
    // the behavior the caller code expects.
    $build = [
      '#type' => 'container',
      '#theme' => 'theme_test_render_element_children',
      '#prefix' => 'kangaroo',
      '#suffix' => 'kitten',
    ];
    $this->render($build);
    $this->removeWhiteSpace();
    $this->assertNoRaw('<div>kangarookitten</div>');
  }

  /**
   * Tests that we get an exception when we try to attach an illegal type.
   */
  public function testProcessAttached() {
    // Specify invalid attachments in a render array.
    $build['#attached']['library'][] = 'core/drupal.states';
    $build['#attached']['drupal_process_states'][] = [];
    $renderer = $this->container->get('bare_html_page_renderer');
    try {
      $renderer->renderBarePage($build, '', 'maintenance_page');
      $this->fail("Invalid #attachment 'drupal_process_states' allowed");
    }
    catch (\LogicException $e) {
      $this->pass("Invalid #attachment 'drupal_process_states' not allowed");
    }
  }

  /**
   * Tests the drupal_render_root() deprecation.
   *
   * @group legacy
   * @expectedDeprecation drupal_render_root() is deprecated in drupal:8.0.0 and is removed from drupal:9.0.0. Use \Drupal\Core\Render\RendererInterface::renderRoot() instead. See https://www.drupal.org/node/2912696
   */
  public function testRenderRootDeprecation() {
    \Drupal::state()->set('theme_preprocess_attached_test', TRUE);

    $test_element = [
      '#theme' => 'common_test_render_element',
      'foo' => [
        '#markup' => 'Kittens!',
      ],
    ];
    drupal_render_root($test_element);

    $expected_attached = [
      'library' => [
        'test/generic_preprocess',
        'test/specific_preprocess',
      ],
    ];
    $this->assertEqual($expected_attached, $test_element['#attached'], 'All expected assets from theme preprocess hooks attached.');
  }

}
