<?php

namespace Drupal\itchefer_stream\Plugin\ActivityContext;

use Drupal\activity_creator\ActivityFactory;
use Drupal\activity_creator\Plugin\ActivityContextBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Query\Sql\QueryFactory;
use Drupal\group\Entity\GroupContent;
use Drupal\social_group\GroupMuteNotify;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'CommunityActivityWithRecipientsContext' activity context.
 *
 * @ActivityContext(
 *   id = "community_activity_with_recipients_context",
 *   label = @Translation("Community activity with recipients context"),
 * )
 */
class CommunityActivityWithRecipientsContext extends ActivityContextBase {


  /**
   * Constructs a MentionActivityContext object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\Query\Sql\QueryFactory $entity_query
   *   The query factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\activity_creator\ActivityFactory $activity_factory
   *   The activity factory service.
   * @param \Drupal\social_group\GroupMuteNotify $group_mute_notify
   *   The group mute notifications.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    QueryFactory $entity_query,
    EntityTypeManagerInterface $entity_type_manager,
    ActivityFactory $activity_factory,
    GroupMuteNotify $group_mute_notify
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_query, $entity_type_manager, $activity_factory);

    $this->groupMuteNotify = $group_mute_notify;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity.query.sql'),
      $container->get('entity_type.manager'),
      $container->get('activity_creator.activity_factory'),
      $container->get('social_group.group_mute_notify')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getRecipients(array $data, $last_uid, $limit): array
  {
    $recipients = [];

    // We only know the context if there is a related object.
    if (isset($data['related_object']) && !empty($data['related_object'])) {
      $related_entity = $this->activityFactory->getActivityRelatedEntity($data);
      $entity_storage = $this->entityTypeManager->getStorage($related_entity['target_type']);
      $entity = $entity_storage->load($related_entity['target_id']);

      // It could happen that a notification has been queued but the content
      // has since been deleted. In that case we can find no additional
      // recipients.
      if (!isset($entity)) {
        return $recipients;
      }

      $allUsers = $this->entityTypeManager->getStorage('user')->getQuery()->execute();
      foreach ($allUsers as $user) {
        $recipients[] = [
          'target_type' => 'user',
          'target_id' => $user,
        ];
      }
    }

    return $recipients;
  }

  /**
   * {@inheritdoc}
   */
  public function isValidEntity(EntityInterface $entity): bool {
    // Special cases for comments.
    if ($entity->getEntityTypeId() === 'comment') {
      // Returns the entity to which the comment is attached.
      $entity = $entity->getCommentedEntity();
    }

    if (!isset($entity)) {
      return FALSE;
    }

    // Check if the content is placed in a group (regardless of content type).
    if (GroupContent::loadByEntity($entity)) {
      return FALSE;
    }

    if ($entity->getEntityTypeId() === 'post') {
      if (!$entity->field_recipient_group->isEmpty()
        || !$entity->field_recipient_user->isEmpty()) {
        return FALSE;
      }
    }

    return TRUE;
  }

}
