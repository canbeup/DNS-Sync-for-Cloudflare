<?php

class Modules_CloudflareDnsSync_Helper_DomainSettings
{
  /**
   * @param $siteID
   * @return bool
   */
  public static function useCloudflareProxy($siteID, $recordType = 'A') {
    switch ($recordType) {
      case 'A':
      case 'AAAA':
      case 'CNAME':
        return pm_Settings::get(Modules_CloudflareDnsSync_Util_Settings::getDomainKey(Modules_CloudflareDnsSync_Util_Settings::CLOUDFLARE_PROXY, $siteID), true);
    }
    return false;
  }

  public static function syncRecordType($recordType, $siteID = null) {
    //Check if the record can be synced
    if (in_array($recordType, Modules_CloudflareDnsSync_Helper_Records::getAvailableRecords())) {
      //Check for the setting type of this record
      if (pm_Settings::get(Modules_CloudflareDnsSync_Util_Settings::getDomainKey('record'.$recordType, $siteID), true) || $siteID === null) {
        return true;
      }
    }
    return false;
  }
}