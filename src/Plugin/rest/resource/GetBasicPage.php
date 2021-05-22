<?php

namespace Drupal\rest_api_demo\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\Core\Session\AccountProxyInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\taxonomy\Entity\Term;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Provides a resource to post nodes.
 *
 * @RestResource(
 *   id = "rest_api_demo",
 *   label = @Translation("Rest Resource Post Example Demo"),
 *   uri_paths = {
 *     "https://www.drupal.org/link-relations/create" = "/rest/api/post/node-create/page"
 *   }
 * )
 */
class GetBasicPage extends ResourceBase {

  use StringTranslationTrait;

  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Constructs a Drupal\rest\Plugin\ResourceBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   A current user instance.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, array $serializer_formats, LoggerInterface $logger, AccountProxyInterface $current_user) {

    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);

    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('rest_api_demo'),
      $container->get('current_user')
    );
  }

  /**
   * Responds to POST requests.
   *
   * Creates a new node.
   *
   * @param mixed $data
   *   Data to create the node.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function post($data) {

    // You must to implement the logic of your REST Resource here.
    // Use current user after pass authentication to validate access.
    if (!$this->currentUser->hasPermission('access content')) 
    {
      throw new AccessDeniedHttpException();
    }
    $error = 200;
    if (!empty($data['type']['value'])) {
      if (!empty($data['title']['value'])) {
        try {
             if (!empty($data['field_tags']['name'])) { 
                $properties = [];
                $properties['name'] = $data['field_tags']['name'];
                $properties['vid'] = "tags";
                $terms = \Drupal::entityManager()->getStorage('taxonomy_term')->loadByProperties($properties);
                  $term = reset($terms);
                  //echo "<pre>"; print_r($properties); die();
                  if ($term) {
                    $term_id = $term->id();
                  } else {
                    $term = Term::create([
                      'vid' =>'tags',
                      'name' => $data['field_tags']['name'],
                    ]);
                    $term->save();
                    $term_id = $term->id();
                  }
                }

          $datetime = $data['field_publish_date']['datetime'];
          if (!empty($datetime)) {
            $date = new DrupalDateTime($datetime);
            $field_publish_date = $date->format('Y-m-d\TH:i:s');
          }
          $node = \Drupal::entityTypeManager()->getStorage('node')->create(
            [
              'type' => $data['type']['value'],
              'title' => $data['title']['value'],
              'body' => [
                'summary' => $data['body']['summary'],
                'value' => $data['body']['value'],
                'format' => $data['body']['format'],
              ],
              'field_publish_date' => $field_publish_date,
              'field_tags' => $term_id ? $term_id : '',
            ]
          );
          $node->save();
          $error = 200;
          $response['status'] = 'success';
          $response['message'] = 'Content with title ' ."'". $data['title']['value'] . "'".' has been created successfully.';
        } catch (EntityStorageException $e) {
            $response['status'] = 'failure';
            $error = 500;
            $response['error'] = 'Internal Server Error';
        }
      } else {
          $response['status'] = 'failure';
          $error = 400;
          $response['error'] = 'Title field is missing.';
      }
    } else {
        $response['status'] = 'failure';
        $error = 400;
        $response['error'] = 'Type field is missing.';
    }

    $response = new ModifiedResourceResponse($response, $error);
    return $response;
  }
}
