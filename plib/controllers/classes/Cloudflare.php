<?php

use Cloudflare\API\Adapter\Guzzle;
use Cloudflare\API\Endpoints\Zones;
use GuzzleHttp\Exception\ClientException;

class Cloudflare
{
  private $adapter;

  private function __construct(Guzzle $adapter)
  {
    $this->adapter = $adapter;
  }

  /**
   * @return bool|Zones
   */
  public function getZones()
  {
    try {
      return new Zones($this->adapter);
    } catch (ClientException $exception) {
      return false;
    }
  }

  /**
   * @param $email
   * @param $apiKey
   * @return bool|Cloudflare
   */
  public static function login($email, $apiKey)
  {
    if ($email != null && $apiKey != null) {
      $key = new Cloudflare\API\Auth\APIKey($email, $apiKey);
      $adapter = new Cloudflare\API\Adapter\Guzzle($key);

      return new Cloudflare($adapter);
    } else {
      return false;
    }
  }

}