<?php

declare(strict_types = 1);

namespace Drupal\wholetthelogsout\Entity;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Url;
use Drupal\link\LinkItemInterface;
use Drupal\wholetthelogsout\Enum\CustomEntityTypes;
use Drupal\wholetthelogsout\Enum\LogLevel;

/**
 * Defines the Event entity.
 *
 * @ContentEntityType(
 *   id = "event",
 *   label = @Translation("Event"),
 *   label_collection = @Translation("Events"),
 *   label_singular = @Translation("event"),
 *   label_plural = @Translation("events"),
 *   label_count = @PluralTranslation(
 *     singular = "@count event",
 *     plural = "@count events"
 *   ),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\wholetthelogsout\EntityListBuilder",
 *     "views_data" =
 *   "Drupal\wholetthelogsout\Entity\ViewsData\EventViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\wholetthelogsout\Form\EventForm",
 *       "add" = "Drupal\wholetthelogsout\Form\EventForm",
 *       "edit" = "Drupal\wholetthelogsout\Form\EventForm",
 *       "delete" = "Drupal\wholetthelogsout\Form\EventDeleteForm",
 *     },
 *     "access" =
 *   "Drupal\wholetthelogsout\Entity\Access\EventAccessControlHandler",
 *     "route_provider" = {
 *       "html" =
 *   "Drupal\wholetthelogsout\Entity\Routing\EventHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "event",
 *   admin_permission = "administer event entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *   },
 *   links = {
 *     "canonical" = "/event/{event}",
 *     "add-form" = "/event/add",
 *     "edit-form" = "/event/{event}/edit",
 *     "delete-form" = "/event/{event}/delete",
 *     "collection" = "/admin/structure/event",
 *   },
 *   field_ui_base_route = "entity.event.collection"
 * )
 */
class Event extends EntityBase implements EventInterface {

  use EntityChangedTrait;

  /**
   * The event message max length.
   */
  private const MESSAGE_MAX_LENGTH = 5_000;

  /**
   * {@inheritDoc}
   */
  public function getType(): ?string {
    $type = $this->get('type')->value;

    return is_string($type)
      ? $type
      : NULL;
  }

  /**
   * {@inheritDoc}
   */
  public function getSeverity(): ?string {
    $severity = $this->get('severity')->value;

    return is_string($severity)
      ? $severity
      : NULL;
  }

  /**
   * {@inheritDoc}
   */
  public function getMessage(): ?string {
    $message = $this->get('message')->value;

    return is_string($message)
      ? $message
      : NULL;
  }

  /**
   * {@inheritDoc}
   */
  public function getUser(): ?string {
    $user = $this->get('user')->value;

    return is_string($user)
      ? $user
      : NULL;
  }

  /**
   * {@inheritDoc}
   */
  public function getUrl(): ?Url {
    $url = $this->get('url')->first();

    return $url instanceof LinkItemInterface
      ? $url->getUrl()
      : NULL;
  }

  /**
   * {@inheritDoc}
   */
  public function getExpiration(): ?int {
    $expire = $this->get('expire')->value;

    return is_numeric($expire)
      ? (int) $expire
      : NULL;
  }

  /**
   * {@inheritDoc}
   *
   * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
   *   Extends core method.
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE): void {
    parent::postSave($storage, $update);

    // Dispatch alerts only for new events.
    if ($update) {
      return;
    }

    \Drupal::service('wholetthelogsout.dispatcher')->dispatch($this);
  }

  /**
   * {@inheritDoc}
   */
  public function getCacheTagsToInvalidate(): array {
    // Add the user tag.
    $tags = ['user.events:' . $this->getOwnerId()];

    // Extract the website.
    $website = $this->website->entity;

    // Check if the website still exists.
    if ($website instanceof Website) {
      // Add the website tag.
      $tags[] = 'website.events:' . $website->id();
    }

    // Merge and return.
    return Cache::mergeTags(parent::getCacheTagsToInvalidate(), $tags);
  }

  /**
   * {@inheritDoc}
   */
  public function label() {
    return t('Event %event', ['%event' => $this->uuid()]);
  }

  /**
   * {@inheritDoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields[self::getParentReferenceFieldName()] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t(CustomEntityTypes::Website->capital()))
      ->setDescription(t('The website this event belongs to.'))
      ->setRequired(TRUE)
      ->setSetting('target_type', self::getParentReferenceFieldName())
      ->setSetting('handler', 'default')
      ->setSetting('handler', 'default')->setDisplayOptions('form', [
        'type' => 'options_select',
        'weight' => -5,
      ]);

    $fields['type'] = self::typeBaseFieldDefinition();
    $fields['severity'] = self::severityBaseFieldDefinition();
    $fields['user'] = self::userBaseFieldDefinition();
    $fields['url'] = self::urlBaseFieldDefinition();
    $fields['message'] = self::messageBaseFieldDefinition();
    $fields['expire'] = self::expireBaseFieldDefinition();

    return $fields;
  }

  /**
   * {@inheritDoc}
   */
  public static function getParentReferenceFieldName(): ?string {
    return CustomEntityTypes::Website->value;
  }

  /**
   * Default value callback for 'expire' base field definition.
   *
   * @return array
   *   An array of default values.
   *
   * @see ::baseFieldDefinitions()
   */
  public static function getDefaultExpireTimestamp(): array {
    return [strtotime('+2 weeks')];
  }

  /**
   * Type base field definition.
   *
   * @return \Drupal\Core\Field\BaseFieldDefinition
   *   The base field definition.
   */
  private static function typeBaseFieldDefinition(): BaseFieldDefinition {
    return BaseFieldDefinition::create('string')
      ->setLabel(t('Type'))
      ->setDescription(t('The event type.'))
      ->setSettings([
        'max_length' => 64,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);
  }

  /**
   * Severity base field definition.
   *
   * @return \Drupal\Core\Field\BaseFieldDefinition
   *   The base field definition.
   */
  private static function severityBaseFieldDefinition(): BaseFieldDefinition {
    return BaseFieldDefinition::create('list_string')
      ->setLabel(t('Severity'))
      ->setDescription(t('The event severity.'))
      ->setDefaultValue(LogLevel::Notice->value)
      ->setRequired(TRUE)
      ->setSettings(['allowed_values' => LogLevel::array()])
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'options_select',
        'weight' => -3,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
  }

  /**
   * User base field definition.
   *
   * @return \Drupal\Core\Field\BaseFieldDefinition
   *   The base field definition.
   */
  private static function userBaseFieldDefinition(): BaseFieldDefinition {
    return BaseFieldDefinition::create('string')
      ->setLabel(t('User'))
      ->setDescription(t('The user that triggered the event.'))
      ->setSettings([
        'max_length' => 64,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -2,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
  }

  /**
   * Url base field definition.
   *
   * @return \Drupal\Core\Field\BaseFieldDefinition
   *   The base field definition.
   */
  private static function urlBaseFieldDefinition(): BaseFieldDefinition {
    return BaseFieldDefinition::create('link')
      ->setLabel(t('URL'))
      ->setDescription(t('A URL that references the event.'))
      ->setSettings([
        'link_type' => 16,
        'title' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'settings' => [
          'rel' => '0',
          'target' => '_blank',
          'trim_length' => NULL,
          'url_only' => FALSE,
          'url_plain' => FALSE,
        ],
        'type' => 'link',
        'weight' => -3,
      ])
      ->setDisplayOptions('form', [
        'type' => 'link_default',
        'weight' => -1,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(FALSE);
  }

  /**
   * Message base field definition.
   *
   * @return \Drupal\Core\Field\BaseFieldDefinition
   *   The base field definition.
   */
  private static function messageBaseFieldDefinition(): BaseFieldDefinition {
    return BaseFieldDefinition::create('string_long')
      ->setLabel(t('Message'))
      ->setDescription(t('The event message.'))
      ->setDefaultValue('')
      ->addPropertyConstraints('value', [
        'Length' => [
          'max' => self::MESSAGE_MAX_LENGTH,
          'maxMessage' => 'This message is too long. It should have ' .
          self::MESSAGE_MAX_LENGTH . ' characters or less.',
        ],
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -1,
      ])
      ->setDisplayOptions('form', [
        'settings' => [
          'rows' => 10,
        ],
        'type' => 'string_textarea',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);
  }

  /**
   * Expire base field definition.
   *
   * @return \Drupal\Core\Field\BaseFieldDefinition
   *   The base field definition.
   */
  private static function expireBaseFieldDefinition(): BaseFieldDefinition {
    return BaseFieldDefinition::create('timestamp')
      ->setLabel(t('Expires on'))
      ->setDescription(t('The time when this event expires.'))
      ->setDefaultValueCallback(static::class . '::getDefaultExpireTimestamp')
      ->setSetting('admin_only', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'settings' => [
          'date_format' => 'medium',
        ],
        'type' => 'timestamp',
        'weight' => -3,
      ])
      ->setDisplayOptions('form', [
        'type' => 'datetime_timestamp',
        'weight' => 2,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
  }

}
