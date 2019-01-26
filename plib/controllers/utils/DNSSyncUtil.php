<?php

require_once 'DNSUtilBase.php';

class DNSSyncUtil extends DNSUtilBase
{
  public function __construct($siteID, Cloudflare $cloudflare, PleskDNS $pleskDNS)
  {
    parent::__construct($siteID, $cloudflare, $pleskDNS);
  }

  public function syncAll(pm_View_Status $view_Status) {
    $recordsUpdated = 0;
    $recordsCreated = 0;

    //Get the Cloudflare DNS
    $cloudflareDNS = $this->cloudflare->getDNS();

    foreach ($this->getPleskRecords() as $pleskRecord) {

      if (DomainSettingsHelper::syncRecordType($pleskRecord->type, $this->siteID)) {

        $cloudflareRecord = $this->getCloudflareRecord($pleskRecord);

        if ($cloudflareRecord !== false) {

          if (!$this->doRecordsMatch($pleskRecord, $cloudflareRecord)) {

            //Update the record in Cloudflare
            $cloudflareDNS->updateRecordDetails($this->zoneID, $cloudflareRecord->id, array(
                'type' => $pleskRecord->type,
                'name' => $pleskRecord->host,
                'content' => $pleskRecord->value
            ));

            $recordsUpdated++;
          }

        } else {
          $proxied = false;

          if ($pleskRecord->type == 'A' || $pleskRecord->type == 'AAAA' || $pleskRecord->type == 'CNAME') {
            $proxied = DomainSettingsHelper::useCloudflareProxy($this->siteID);
          }

          //Create a new record in cloudflare
          if ($cloudflareDNS->addRecord($this->zoneID, $pleskRecord->type, $pleskRecord->host, $pleskRecord->value, 0, $proxied) === true) {
            $recordsCreated++;
          }
        }

      }

    }

    if ($recordsCreated > 0) {
      $view_Status->addMessage('info', $recordsCreated.' record'.($recordsCreated == 1 ? '' : 's').' created.');
    }

    if ($recordsUpdated > 0) {
      $view_Status->addMessage('info', $recordsUpdated.' record'.($recordsUpdated == 1 ? '' : 's').' updated.');
    }
  }
}