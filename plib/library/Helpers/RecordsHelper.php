<?php

class Modules_CloudflareDnsSync_RecordsHelper
{
  public static function getAvailableRecords() {
    return array(
        'A' => 'A',
        'AAAA' => 'AAAA',
        'CNAME' => 'CNAME',
        'TXT' => 'TXT',
        'NS' => 'NS',
        'MX' => 'MX',
    );
  }
}