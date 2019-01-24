<?php

class SettingsUtil
{
  const CLOUDFLARE_EMAIL = 'cloudflareEmail';
  const CLOUDFLARE_API_KEY = 'cloudflareApiKey';

  public static function getUserKey($key) {
    return 'u'.pm_Session::getClient()->getId().'_'.$key;
  }
}