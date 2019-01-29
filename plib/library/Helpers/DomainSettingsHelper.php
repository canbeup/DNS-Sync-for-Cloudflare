<?php

class DomainSettingsHelper
{
  /**
   * @param $siteID
   * @return bool
   */
  public static function useCloudflareProxy($siteID) {
    return pm_Settings::get(SettingsUtil::getDomainKey(SettingsUtil::CLOUDFLARE_PROXY, $siteID), true);
  }

  public static function syncRecordType($recordType, $siteID = null) {
    //Check if the record can be synced
    if (in_array($recordType, RecordsHelper::getAvailableRecords())) {
      //Check for the setting type of this record
      if (pm_Settings::get(SettingsUtil::getDomainKey('record'.$recordType, $siteID), true) || $siteID === null) {
        return true;
      }
    }
    return false;
  }
}