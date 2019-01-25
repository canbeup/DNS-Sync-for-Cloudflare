<?php

require_once 'DNSUtilBase.php';

use PleskX\Api\Struct\Dns\Info;

class DNSListUtil extends DNSUtilBase
{
  public function __construct($siteID, Cloudflare $cloudflare, PleskDNS $pleskDNS)
  {
    parent::__construct($siteID, $cloudflare, $pleskDNS);
  }

  public function getList() {
    $data = array();

    foreach ($this->getPleskRecords() as $pleskRecord) {

      $cloudflareRecord = $this->getCloudflareRecord($pleskRecord);

      $cloudflareValue = 'Record not found';
      $cloudflareStatus = pm_Context::getBaseUrl().'images/error.png';

      if ($cloudflareRecord !== false) {
        $cloudflareValue = $cloudflareRecord->content;

        if ($this->doRecordsMatch($pleskRecord, $cloudflareRecord)) {
          $cloudflareStatus = pm_Context::getBaseUrl().'images/success.png';
        } else {
          $cloudflareStatus = pm_Context::getBaseUrl().'images/warning.png';
        }
      }

      $data[] = array(
          'col-host' => $this->removeDotAfterTLD($pleskRecord->host),
          'col-type' => $pleskRecord->type,
          'col-status' => '<img src="'.$cloudflareStatus.'"/>',
          'col-plesk' => $pleskRecord->value,
          'col-cloudflare' => $cloudflareValue
      );

    }

    return $data;
  }

}