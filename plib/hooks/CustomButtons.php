<?php

class Modules_CloudflareDnsSync_CustomButtons extends pm_Hook_CustomButtons
{

  public function getButtons()
  {
    if (!$this->isAvailable()) {
      return [];
    }

    $commonParmas = [
        'title' => 'Cloudflare DNS Sync',
        'description' => 'Sync your Plesk DNS zone to Cloudflare',
        'icon' => pm_Context::getBaseUrl() . '/images/logo.png',
        'link' => pm_Context::getActionUrl('index'),
    ];

    return [
        array_merge($commonParmas, [
            'place' => self::PLACE_DOMAIN_PROPERTIES,
            'contextParams' => true,
            'visibility' => [$this, 'isClientButtonVisible']
        ])
    ];
  }

  public function isAvailable()
  {
    return version_compare(\pm_ProductInfo::getVersion(), '17.0') >= 0;
  }

  public function isClientButtonVisible($options)
  {
    if (empty($options['site_id'])) {
      return false;
    }

    foreach (pm_Session::getCurrentDomains(true) as $domain) {
      if ($domain->getId() == $options['site_id']) {
        return true;
      }
    }
  }
}