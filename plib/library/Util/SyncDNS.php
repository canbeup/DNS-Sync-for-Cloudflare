<?php

use Cloudflare\API\Endpoints\DNS;
use PleskX\Api\Struct\Dns\Info;

class Modules_CloudflareDnsSync_Util_SyncDNS extends Modules_CloudflareDnsSync_Util_BaseDNS
{
  /**
   * DNSSyncUtil constructor.
   * 
   * @param $siteID
   * @param Modules_CloudflareDnsSync_Cloudflare $cloudflare
   * @param Modules_CloudflareDnsSync_PleskDNS $pleskDNS
   * @throws pm_Exception
   */
  public function __construct($siteID, Modules_CloudflareDnsSync_Cloudflare $cloudflare, Modules_CloudflareDnsSync_PleskDNS $pleskDNS)
  {
    parent::__construct($siteID, $cloudflare, $pleskDNS);
  }

  /**
   * Sync all the records in Plesk
   *
   * @param pm_View_Status $view_Status
   */
  public function syncAll(pm_View_Status $view_Status) {
    $recordsUpdated = 0;
    $recordsCreated = 0;

    //Get the Cloudflare DNS
    $cloudflareDNS = $this->cloudflare->getDNS();

    foreach ($this->getPleskRecords() as $pleskRecord) {

      if (Modules_CloudflareDnsSync_Helper_DomainSettings::syncRecordType($pleskRecord->type, $this->siteID)) {

        $this->sync($pleskRecord, $cloudflareDNS, $recordsUpdated, $recordsCreated);

      }

    }

    $this->addMessage($view_Status, $recordsCreated, $recordsUpdated);
  }

  /**
   * Sync a single record
   *
   * @param pm_View_Status $view_Status
   * @param $recordID
   */
  public function syncRecord(pm_View_Status $view_Status, $recordID) {
    $recordsUpdated = 0;
    $recordsCreated = 0;

    //Get the Cloudflare DNS
    $cloudflareDNS = $this->cloudflare->getDNS();

    $pleskRecord = $this->pleskDNS->getRecord($recordID);

    if ($pleskRecord !== false && Modules_CloudflareDnsSync_Helper_DomainSettings::syncRecordType($pleskRecord->type, $this->siteID)) {

      $this->sync($pleskRecord, $cloudflareDNS, $recordsUpdated, $recordsCreated);

    }

    $this->addMessage($view_Status, $recordsCreated, $recordsUpdated);
  }

  /**
   * @param Info $pleskRecord
   * @param DNS $cloudflareDNS
   * @param $recordsUpdated
   * @param $recordsCreated
   */
  private function sync(Info $pleskRecord, DNS $cloudflareDNS, &$recordsUpdated, &$recordsCreated) {

    //Get the cooresponding cloudflare record
    $cloudflareRecord = $this->getCloudflareRecord($pleskRecord);

    //Check if the record exists
    if ($cloudflareRecord !== false) {
      //If so, then check if the records need to be updated
      if (!$this->doRecordsMatch($pleskRecord, $cloudflareRecord)) {
        //If so, then update the record in Cloudflare
        $cloudflareDNS->updateRecordDetails($this->zoneID, $cloudflareRecord->id, array(
            'type' => $pleskRecord->type,
            'name' => $pleskRecord->host,
            'content' => $pleskRecord->value,
            'proxied' => Modules_CloudflareDnsSync_Helper_DomainSettings::useCloudflareProxy($pleskRecord->siteId)
        ));

        $recordsUpdated++;
      }

    } else {
      //If not, then create a new record
      $proxied = false;
      $priority = '';

      if ($pleskRecord->type == 'A' || $pleskRecord->type == 'AAAA' || $pleskRecord->type == 'CNAME') {
        $proxied = Modules_CloudflareDnsSync_Helper_DomainSettings::useCloudflareProxy($this->siteID);
      }

      if ($pleskRecord->type == 'MX') {
        $priority = $pleskRecord->opt;
      }

      //Create a new record in cloudflare
      if ($cloudflareDNS->addRecord($this->zoneID, $pleskRecord->type, $pleskRecord->host, $pleskRecord->value, 0, $proxied, $priority) === true) {
        $recordsCreated++;
      }
    }
  }

  /**
   * @param pm_View_Status $view_Status
   * @param $recordsCreated
   * @param $recordsUpdated
   */
  private function addMessage(pm_View_Status $view_Status, $recordsCreated, $recordsUpdated) {
    if ($recordsUpdated == 0 && $recordsCreated == 0) {
      $view_Status->addMessage('warning', 'No records created of updated.');
      return;
    }

    if ($recordsCreated > 0) {
      $view_Status->addMessage('info', $recordsCreated.' record'.($recordsCreated == 1 ? '' : 's').' created.');
    }

    if ($recordsUpdated > 0) {
      $view_Status->addMessage('info', $recordsUpdated.' record'.($recordsUpdated == 1 ? '' : 's').' updated.');
    }
  }
}