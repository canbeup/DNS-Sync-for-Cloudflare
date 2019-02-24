<?php

class Modules_CloudflareDnsSync_List_DNS extends Modules_CloudflareDnsSync_Util_BaseDNS
{
  public function __construct($siteID, Modules_CloudflareDnsSync_Cloudflare $cloudflare, Modules_CloudflareDnsSync_PleskDNS $pleskDNS)
  {
    parent::__construct($siteID, $cloudflare, $pleskDNS);
  }

  public function getList() {
    $data = array();

    $cloudflareList = $this->getCloudflareRecords();

    foreach ($this->getPleskRecords() as $pleskRecord) {

      if (in_array($pleskRecord->type, Modules_CloudflareDnsSync_Helper_Records::getAvailableRecords())) {

        $cloudflareRecord = $this->getCloudflareRecord($pleskRecord);

        $cloudflareValue = pm_Locale::lmsg('text.recordNotFound');
        $pleskValue = $pleskRecord->value;
        $syncStatus = pm_Context::getBaseUrl() . 'images/error.png';

        if ($cloudflareRecord !== false) {
          $cloudflareValue = $cloudflareRecord->content;

          if ($pleskRecord->type == 'SRV') {
            $cloudflareValue = $cloudflareRecord->priority . ' ' . str_replace("\t",' ',$cloudflareRecord->content);
            $pleskValue = $pleskRecord->opt . ' ' . $pleskRecord->value;
          }

          if ($this->doRecordsMatch($pleskRecord, $cloudflareRecord)) {
            $syncStatus = pm_Context::getBaseUrl() . 'images/success.png';
          } else {
            $syncStatus = pm_Context::getBaseUrl() . 'images/warning.png';
          }

          foreach ($cloudflareList as $key => $value) {
            if ($value->id == $cloudflareRecord->id) {
              unset($cloudflareList[$key]);
              break;
            }
          }
        }

        $data[] = array(
            'col-host' => $this->removeDotAfterTLD($pleskRecord->host),
            'col-type' => $pleskRecord->type.($pleskRecord->type == 'MX' ? ' ('.$pleskRecord->opt.')' : ''),
            'col-status' => '<img src="' . $syncStatus . '"/>',
            'col-plesk' => $this->minifyValue($pleskValue),
            'col-cloudflare' => $this->minifyValue($cloudflareValue),
        );

      }

    }

    foreach ($cloudflareList as $cloudflareRecord) {
      $data[] = array(
          'col-host' => $this->removeDotAfterTLD($cloudflareRecord->name),
          'col-type' => $cloudflareRecord->type.($cloudflareRecord->type == 'MX' ? ' ('.$cloudflareRecord->priority.')' : ''),
          'col-status' => '<img src="' . pm_Context::getBaseUrl() . 'images/error2.png"/>',
          'col-plesk' => pm_Locale::lmsg('text.recordNotFound'),
          'col-cloudflare' => $this->minifyValue($cloudflareRecord->content)
      );
    }

    return $data;
  }

  private function minifyValue($value, $length = 60) {
    if (strlen($value) > $length) {
      return substr($value, 0, $length).'...';
    }
    return $value;
  }

}