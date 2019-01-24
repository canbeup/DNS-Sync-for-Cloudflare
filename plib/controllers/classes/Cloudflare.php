<?php

use Cloudflare\API\Endpoints\Zones;

class Cloudflare
{
  private $adapter;

  public function __construct(string $email, string $apiKey)
  {
    $key = new Cloudflare\API\Auth\APIKey($email, $apiKey);
    $this->adapter = new Cloudflare\API\Adapter\Guzzle($key);
  }

  /**
   * @return Zones
   */
  public function getZones()
  {
    return new Zones($this->adapter);
  }

}