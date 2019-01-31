<?php

use PleskX\Api\Struct\Dns\Info;

class Modules_CloudflareDnsSync_Helper_SyncRecord
{
  public $type;
  public $host;
  public $value;
  public $proxied;
  public $priority;

  public function __construct(Info $pleskRecord)
  {
    $this->type = $pleskRecord->type;
    $this->host = $pleskRecord->host;

    $this->proxied = Modules_CloudflareDnsSync_Helper_DomainSettings::useCloudflareProxy($pleskRecord->siteId, $pleskRecord->type);

    $this->value = $pleskRecord->value;
    $this->priority = '';

    //Check for SRV
    if ($pleskRecord->type == 'SRV') {
      $content = explode(' ',$pleskRecord->opt . ' ' . $pleskRecord->value);
      $this->value = $content[1].' '.$content[2].' '.$content[3];
      $this->priority = $content[0];
    }

    //Check for MX
    if ($pleskRecord->type == 'MX') {
      $this->priority = $pleskRecord->opt;
    }
  }

}