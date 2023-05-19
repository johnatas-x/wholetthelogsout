<?php

namespace Drupal\wholetthelogsout\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\link\LinkItemInterface;
use Drupal\wholetthelogsout\Entity\WebsiteInterface;

/**
 * Class WebsiteUrlRedirectController.
 *
 * Redirect the user to the website's URL.
 */
class WebsiteUrlRedirectController extends ControllerBase {

  /**
   * Redirect the user to the website's URL.
   *
   * @param \Drupal\wholetthelogsout\Entity\WebsiteInterface $website
   *   The website entity.
   *
   * @return \Drupal\Core\Routing\TrustedRedirectResponse
   *   A redirect response.
   */
  public function redirectToWebsiteUrl(WebsiteInterface $website): TrustedRedirectResponse {
    $url = $website->url->first();

    if (!$url instanceof LinkItemInterface) {
      throw new \RuntimeException('An error has occurred.');
    }

    return new TrustedRedirectResponse($url->getUrl()->toString());
  }

  /**
   * Access callback for the redirection.
   *
   * @param \Drupal\wholetthelogsout\Entity\WebsiteInterface $website
   *   The website entity.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   An access result.
   */
  public function redirectAccess(WebsiteInterface $website): AccessResult {
    return AccessResult::allowedIf(!$website->url->isEmpty())
      ->andIf($website->access('view', NULL, TRUE))
      ->cachePerUser()
      ->addCacheableDependency($website);
  }

}
