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

}