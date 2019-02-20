<?php

class Modules_CloudflareDnsSync_Permissions extends pm_Hook_Permissions
{
  public function getPermissions()
  {
    return [
        'manage_cloudflare' => [
            'default' => true,
            'place' => self::PLACE_MAIN,
            'name' => pm_Locale::lmsg('permission.cloudflare.title'),
            'description' => pm_Locale::lmsg('permission.cloudflare.description'),
        ],
        'manage_cloudflare_settings' => [
            'default' => true,
            'place' => self::PLACE_MAIN,
            'name' => pm_Locale::lmsg('permission.cloudflareSettings.title'),
            'description' => pm_Locale::lmsg('permission.cloudflareSettings.description'),
            'master' => 'manage_cloudflare',
        ]
    ];
  }
}