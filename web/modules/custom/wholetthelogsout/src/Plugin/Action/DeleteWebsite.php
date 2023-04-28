<?php

namespace Drupal\wholetthelogsout\Plugin\Action;

/**
 * Provides an action for deleting website entities.
 *
 * @Action(
 *   id = "website_delete_action",
 *   label = @Translation("Delete website"),
 *   type = "website"
 * )
 */
class DeleteWebsite extends DeleteEntityBase {

}
