<?php

namespace Drupal\wholetthelogsout\Entity;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines the Website entity.
 *
 * @ContentEntityType(
 *   id = "website",
 *   label = @Translation("Website"),
 *   label_collection = @Translation("Websites"),
 *   label_singular = @Translation("website"),
 *   label_plural = @Translation("websites"),
 *   label_count = @PluralTranslation(
 *     singular = "@count website",
 *     plural = "@count websites"
 *   ),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\wholetthelogsout\EntityListBuilder",
 *     "views_data" = "Drupal\wholetthelogsout\Entity\WebsiteViewsData",
 *     "form" = {
 *       "default" = "Drupal\wholetthelogsout\Form\WebsiteForm",
 *       "add" = "Drupal\wholetthelogsout\Form\WebsiteForm",
 *       "edit" = "Drupal\wholetthelogsout\Form\WebsiteForm",
 *       "delete" = "Drupal\wholetthelogsout\Form\WebsiteDeleteForm",
 *     },
 *     "access" = "Drupal\wholetthelogsout\WebsiteAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\wholetthelogsout\EntityHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "website",
 *   admin_permission = "administer website entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *   },
 *   links = {
 *     "canonical" = "/website/{website}",
 *     "add-form" = "/website/add",
 *     "edit-form" = "/website/{website}/edit",
 *     "delete-form" = "/website/{website}/delete",
 *     "collection" = "/admin/structure/website",
 *   },
 *   field_ui_base_route = "entity.website.collection"
 * )
 */
class Website extends EntityBase implements WebsiteInterface {

  use EntityChangedTrait;

  /**
   * Entity's name.
   *
   * @var string
   */
  protected string $name;

  /**
   * Entity's URL.
   *
   * @var mixed
   */
  protected mixed $url;

  /**
   * {@inheritdoc}
   */
  public function getCacheTagsToInvalidate(): array {
    return Cache::mergeTags(parent::getCacheTagsToInvalidate(), [
      'user.websites:' . $this->getOwnerId(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the website.'))
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

    $fields['url'] = BaseFieldDefinition::create('link')
      ->setLabel(t('URL'))
      ->setDescription(t('The URL of the website (like "https://mysite.docksal").'))
      ->setSettings([
        'link_type' => 16,
        'title' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('form', [
        'type' => 'link_default',
        'weight' => -3,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    return $fields;
  }

}
