<?php

namespace Drupal\ai_feed\Service;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\node\NodeInterface;
use Psr\Log\LoggerInterface;

/**
 * The AI Feed Sources service.
 *
 * This service is used to query and prepare content for the JSON feed consumed
 * by a language model integration framework such as LangChain.
 */
class Sources {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Retrieves and array of content for the AI feed.
   *
   * This method delivers all published nodes that are accessible to anonymous
   * users. In the future, this query can grow to include new filters and entity
   * types. This method also processes the content to put it into a consistant
   * and expected format.
   *
   * @return array
   *   An array of content data for the AI feed.
   */
  public function getContent(): array {
    // Query to build a collection of content to be ingested.
    $query = $this->entityTypeManager
      ->getStorage('node')
      ->getQuery()
      ->condition('status', NodeInterface::PUBLISHED)
      ->accessCheck(TRUE);
    $ids = $query->execute();
    $entities = \Drupal\node\Entity\Node::loadMultiple($ids);

    // Process the collection to fit the shape of the API.
    $entityData = [];
    foreach ($entities as $entity) {
      $entityData[] = [
        'id' => $this->getSearchIndexId($entity),
        'source' => 'drupal',
        'documentType' => $this->getDocumentType($entity),
        'documentId' => $entity->id(),
        'documentUrl' => $this->getUrl($entity),
        'documentTitle' => $entity->getTitle(),
        'documentContent' => $this->processContentBody($entity),
        'metaTags' => '',
        'metaDescription' => '',
        'dateCreated' => $this->formatTimestamp($entity->getCreatedTime()),
        'dateModified' => $this->formatTimestamp($entity->getChangedTime()),
        'dateProcessed' => $this->formatTimestamp(time()),
      ];
    }
    return $entityData;
  }

  /**
   * Format timestamps to use the ISO-8601 standard.
   *
   * @param int $timestamp
   *   A UNIX timestamp.
   *
   * @return string
   *   The formatted value of the date.
   */
  protected function formatTimestamp(int $timestamp): string {
    $dateTime = DrupalDateTime::createFromTimestamp($timestamp);
    return $dateTime->format(\DateTime::ATOM);
  }

  /**
   * Processes the content body of a content entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   A content entity.
   *
   * @return \Drupal\Component\Render\MarkupInterface
   *   The rendered HTML.
   */
  protected function processContentBody(EntityInterface $entity) {
    $view_builder = $this->entityTypeManager->getViewBuilder($entity->getEntityTypeId());
    $renderArray = $view_builder->view($entity, 'default');
    return $this->renderer->render($renderArray);
  }


  /**
   * Get a unique ID to reference this item in the search index.
   *
   * Examples: "ask-yale-edu-node-14" or "hospitality-yale-edu-media-128".
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   A content entity.
   *
   * @return string
   *   A predictable and unique ID to reference this item in the search index.
   */
  public function getSearchIndexId(EntityInterface $entity) {
    $host = \Drupal::request()->getHttpHost();
    $host = preg_replace('/[^a-zA-Z0-9]+/', '-', $host);
    return $host . '-' . $entity->getEntityTypeId() . '-' . $entity->id();
  }

  /**
   * Gets a standardizaed document type.
   *
   * Document type is the name of the Drupal entity and possible bundle.
   * Examples: "node/post", "media/image", or "user".
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   A content entity.
   *
   * @return string
   *   A string representing the type of content.
   */
  protected function getDocumentType(EntityInterface $entity) {
    $type = $entity->getEntityTypeId();
    if (!empty($entity->bundle())) {
      $type .= '/' . $entity->bundle();
    }
    return $type;
  }

  /**
   * Retrieves the canonical URL for a content entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   A content entity.
   *
   * @return string
   *   The canonical URL as a string.
   */
  protected function getUrl(EntityInterface $entity) {
    return $entity->toUrl('canonical', ['absolute' => TRUE])->toString();
  }

  /**
   * Constructs a new Sources object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger service.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    LoggerInterface $logger,
    RendererInterface $renderer,
    RequestStack $requestStack
  ) {
    $this->entityTypeManager = $entityTypeManager;
    $this->logger = $logger;
    $this->renderer = $renderer;
    $this->requestStack = $requestStack;
  }

}
