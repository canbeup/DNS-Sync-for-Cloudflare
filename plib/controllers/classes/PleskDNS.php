<?php

use PleskX\Api\Struct\Dns\Info;

class PleskDNS
{
  private $client;

  /**
   * PleskDNS constructor.
   */
  public function __construct()
  {
    $this->client = new \PleskX\Api\InternalClient();
  }

  /**
   * @param $siteID
   * @return Info[]
   */
  public function getRecords($siteID) {
    return $this->client->dns()->getAll("site-id", $siteID);
  }

}