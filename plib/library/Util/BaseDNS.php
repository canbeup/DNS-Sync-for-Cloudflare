<?php

use PleskX\Api\Struct\Dns\Info;

abstract class Modules_CloudflareDnsSync_Util_BaseDNS
{
  protected $cloudflare;
  protected $pleskDNS;
  protected $cloudflareRecords;
  protected $pleskRecords;

  public $siteID;
  public $domainName;

  public $zoneID;

  /**
   * DNSUtilBase constructor.
   * @param $siteID
   * @param Modules_CloudflareDnsSync_Cloudflare $cloudflare
   * @param Modules_CloudflareDnsSync_PleskDNS $pleskDNS
   * @throws pm_Exception
   */
  public function __construct($siteID, Modules_CloudflareDnsSync_Cloudflare $cloudflare, Modules_CloudflareDnsSync_PleskDNS $pleskDNS)
  {
    //Save the Site ID
    $this->siteID = $siteID;

    //Save Cloudflare and the Plesk NDS
    $this->cloudflare = $cloudflare;
    $this->pleskDNS = $pleskDNS;

    //Fetch the domain from the Site ID
    $this->domainName = pm_Domain::getByDomainId($siteID)->getName();

    $this->zoneID = $this->cloudflare->getZone($siteID)->id;

    $this->cloudflareRecords = $this->cloudflare->getDNS()->listRecords($this->zoneID, '', '', '', 1, 250)->result;
    $this->pleskRecords = $this->pleskDNS->getRecords($this->siteID);
  }

  /**
   * @return Modules_CloudflareDnsSync_Helper_CloudflareRecord[]
   */
  public function getCloudflareRecords(): array
  {
    return $this->cloudflareRecords;
  }

  /**
   * @return Info[]
   */
  public function getPleskRecords(): array
  {
    return $this->pleskRecords;
  }

  /**
   * @param Info $pleskRecord
   * @return bool|Modules_CloudflareDnsSync_Helper_CloudflareRecord
   */
  protected function getCloudflareRecord(Info $pleskRecord) {
    if ($pleskRecord->type == 'A' || $pleskRecord->type == 'AAAA' || $pleskRecord->type == 'CNAME') {
      foreach ($this->getCloudflareRecords() as $cloudflareRecord) {

        if ($pleskRecord->type == $cloudflareRecord->type && $this->removeDotAfterTLD($pleskRecord->host) == $cloudflareRecord->name) {
          return $cloudflareRecord;
        }
      }
    } elseif ($pleskRecord->type == 'TXT') {
      foreach ($this->getCloudflareRecords() as $cloudflareRecord) {
        if ($pleskRecord->type == $cloudflareRecord->type && $this->removeDotAfterTLD($pleskRecord->host) == $cloudflareRecord->name) {
          return $cloudflareRecord;
        }
      }
    } elseif ($pleskRecord->type == 'NS') {
      foreach ($this->getCloudflareRecords() as $cloudflareRecord) {
        if ($pleskRecord->type == $cloudflareRecord->type && $this->removeDotAfterTLD($pleskRecord->host) == $cloudflareRecord->name && $this->removeDotAfterTLD($pleskRecord->value) == $cloudflareRecord->content) {
          return $cloudflareRecord;
        }
      }
    } elseif ($pleskRecord->type == 'MX') {
      foreach ($this->getCloudflareRecords() as $cloudflareRecord) {
        if ($pleskRecord->type == $cloudflareRecord->type && $this->removeDotAfterTLD($pleskRecord->host) == $cloudflareRecord->name && $pleskRecord->opt == $cloudflareRecord->priority) {
          return $cloudflareRecord;
        }
      }
    }
    return false;
  }

  /**
   * @param Info $pleskRecord
   * @param Modules_CloudflareDnsSync_Helper_CloudflareRecord $cloudflareRecord
   * @return bool
   */
  protected function doRecordsMatch(Info $pleskRecord, $cloudflareRecord) {
    //Record Type (A, AAAA, CNAME, TEXT, Etc)
    if ($pleskRecord->type == $cloudflareRecord->type) {
      //The Domain name (sub.domain.tld)
      if ($this->removeDotAfterTLD($pleskRecord->host) == $cloudflareRecord->name) {
        //The value of the (sub)domain
        if ($this->removeDotAfterTLD($pleskRecord->value) == $cloudflareRecord->content) {
          //Check for the domain settings (Cloudflare Traffic)
          if (Modules_CloudflareDnsSync_Helper_DomainSettings::useCloudflareProxy($pleskRecord->siteId) == $cloudflareRecord->proxied) {
            //If all of this is true, then the domains match
            return true;
          }
        }
      }
    }
    return false;
  }

  /**
   * @param $domain
   * @return string
   */
  protected function removeDotAfterTLD($domain) {
    if ($this->endsWith($domain, '.')) {
      if (strlen($domain) > 1) {
        return substr($domain, 0, strlen($domain) - 1);
      }
    }
    return $domain;
  }

  private function endsWith($string, $endString)
  {
    $len = strlen($endString);
    if ($len == 0) {
      return true;
    }
    return (substr($string, -$len) === $endString);
  }
}