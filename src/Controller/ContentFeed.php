<?php

namespace Drupal\ai_feed\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\ai_feed\Service\Sources;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ContentFeed.
 *
 * Returns a JSON feeds of website content for AI ingestion.
 *
 * @package Drupal\ai_feed\Controller
 */
class ContentFeed extends ControllerBase {

  /**
   * The AI Feed Sources service.
   *
   * @var \Drupal\ai_feed\Service\Sources
   */
  protected $sources;

   /**
    * Returns content and metadata in a JSON response.
    *
    * @var \Symfony\Component\HttpFoundation\JsonResponse
    *   A JSON response.
    */
  public function jsonResponse() {
    $content = $this->sources->getContent();
    $response = new JsonResponse($content);
    $response->headers->set('Content-Type', 'application/json');
    return $response;
  }

  /**
   * Constructs a new ContentFeed controller.
   *
   * @param \Drupal\ai_feed\Service\Sources $sources
   *   The AI Feed Sources service.
   */
  public function __construct(Sources $sources) {
    $this->sources = $sources;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('ai_feed.sources'));
  }

}
