<?php

class Modules_CloudflareDnsSync_EventListener implements EventListener
{
  public function filterActions()
  {
    return [
        'domain_dns_update',
    ];
  }

  public function handleEvent($objectType, $objectId, $action, $oldValues, $newValues)
  {
    switch ($action) {
      case 'domain_dns_update':
        //Get the Domain
        try {
          $domain = pm_Domain::getByName($oldValues['Domain Name']);

          if (pm_Settings::get(Modules_CloudflareDnsSync_Util_Settings::getDomainKey(Modules_CloudflareDnsSync_Util_Settings::CLOUDFLARE_AUTO_SYNC, $domain->getId()), true)) {

            //Get the User with Sync Access
            $userID = pm_Settings::get(Modules_CloudflareDnsSync_Util_Settings::getDomainKey(Modules_CloudflareDnsSync_Util_Settings::CLOUDFLARE_DOMAIN_USER, $domain->getId()));

            if ($userID !== null) {
              $cloudflare = Modules_CloudflareDnsSync_Cloudflare::login(
                  pm_Settings::getDecrypted(Modules_CloudflareDnsSync_Util_Settings::getUserKey(Modules_CloudflareDnsSync_Util_Settings::CLOUDFLARE_EMAIL, $userID)),
                  pm_Settings::getDecrypted(Modules_CloudflareDnsSync_Util_Settings::getUserKey(Modules_CloudflareDnsSync_Util_Settings::CLOUDFLARE_API_KEY, $userID))
              );

              //Try to Sync the DNS
              if ($cloudflare !== false) {
                //Sync the DNS Zone
                $syncDNS = new Modules_CloudflareDnsSync_Util_DNS($domain->getId(), $cloudflare, new Modules_CloudflareDnsSync_PleskDNS());
                $syncDNS->syncAll();
              }
            }
          }
        } catch (pm_Exception $e) {
        }

        break;
    }
  }
}

return new Modules_CloudflareDnsSync_EventListener();