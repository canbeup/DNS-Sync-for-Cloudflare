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

      if (in_array($pleskRecord->type, RecordsHelper::getAvailableRecords())) {

        $cloudflareRecord = $this->getCloudflareRecord($pleskRecord);

        $cloudflareValue = 'Record not found';
        $syncStatus = pm_Context::getBaseUrl() . 'images/error.png';

        if ($cloudflareRecord !== false) {
          $cloudflareValue = $cloudflareRecord->content;

          if ($this->doRecordsMatch($pleskRecord, $cloudflareRecord)) {
            $syncStatus = pm_Context::getBaseUrl() . 'images/success.png';
          } else {
            $syncStatus = pm_Context::getBaseUrl() . 'images/warning.png';
          }
        }

        $data[] = array(
            'col-host' => $this->removeDotAfterTLD($pleskRecord->host),
            'col-type' => $pleskRecord->type.($pleskRecord->type == 'MX' ? ' ('.$pleskRecord->opt.')' : ''),
            'col-status' => '<img src="' . $syncStatus . '"/>',
            'col-plesk' => $pleskRecord->value,
            'col-cloudflare' => $cloudflareValue
        );

      }

    }

    return $data;
  }

}