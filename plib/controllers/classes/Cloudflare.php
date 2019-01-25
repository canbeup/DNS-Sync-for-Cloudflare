<?php

use Cloudflare\API\Adapter\Guzzle;
use Cloudflare\API\Auth\APIKey;
use Cloudflare\API\Endpoints\DNS;
use Cloudflare\API\Endpoints\Zones;
use Cloudflare\API\Endpoints\User;
use GuzzleHttp\Exception\ClientException;

class Cloudflare
{
  private $adapter;

  private function __construct(Guzzle $adapter)
  {
    $this->adapter = $adapter;
  }

  /**
   * @return User
   */
  public function getUser() {
    return new User($this->adapter);
  }

  /**
   * @return Zones
   */
  public function getZones()
  {
    return new Zones($this->adapter);
  }

  /**
   * @return bool|Zones
   */
  public function getZone($siteID)
  {
    foreach (pm_Session::getCurrentDomains(true) as $domain) {
      if ($domain->getId() == $siteID) {
        foreach ($this->getZones()->listZones()->result as $zone) {
          if ($zone->name == $domain->getName()) {
            return $zone;
          }
        }
      }
    }
    return false;
  }

  /**
   * @return DNS
   */
  public function getDNS() {
    return new DNS($this->adapter);
  }

  /**
   * @param $email
   * @param $apiKey
   * @return bool|Cloudflare
   */
  public static function login($email, $apiKey)
  {
    try {
      if ($email != null && $apiKey != null) {
        $key = new APIKey($email, $apiKey);
        $adapter = new Guzzle($key);

        return new Cloudflare($adapter);
      }
    } catch (ClientException $exception) {

    }
    return false;
  }

}