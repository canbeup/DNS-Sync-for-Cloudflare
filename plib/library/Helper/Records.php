<?php

class Modules_CloudflareDnsSync_Helper_Records
{
  /**
   * @return array
   */
  public static function getAvailableRecords() {
    return array(
        'A' => 'A',
        'AAAA' => 'AAAA',
        'CNAME' => 'CNAME',
        'TXT' => 'TXT',
        'NS' => 'NS',
        'MX' => 'MX',
        'SRV' => 'SRV',
    );
  }

  /**
   * @param $type
   * @return bool
   */
  public static function canUseProxy($type) {
    switch ($type) {
      case 'A':
      case 'AAAA':
      case 'CNAME':
        return true;
    }
    return false;
  }
}