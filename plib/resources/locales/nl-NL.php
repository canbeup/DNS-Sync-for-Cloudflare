<?php

/**
 * Created by Sander Jochems
 */

$messages = [
    'title' => 'DNS Sync voor Cloudflare',
    'description' => 'Synchroniseer de DNS van Plesk met Cloudflare DNS',

    'title.dnsSyncFor' => 'DNS Sync voor <b>%%domain%%</b>',

    'tab.domains' => 'Domeinen',
    'tab.api' => 'API',
    'tab.dns' => 'DNS',
    'tab.settings' => 'Instellingen',

    'form.cloudflareEmail' => 'Cloudflare Email',
    'form.cloudflareApiKey' => 'Cloudflare API Key',

    'form.trafficThruCloudflare' => 'Verkeer door Cloudflare',
    'form.selectRecord' => 'Selecteer het type records dat u wilt synchroniseren',

    'table.domainName' => 'Domein Naam',
    'table.cloudflareZoneID' => 'Cloudflare Zone ID',

    'table.host' => 'Cloudflare Zone ID',
    'table.recordType' => 'Record Type',
    'table.status' => 'Status',
    'table.cloudflareValue' => 'Cloudflare Waarde',
    'table.pleskValue' => 'Plesk Waarde',

    'button.syncDNS' => 'Synchroniseer DNS',

    'text.zoneIdNotFound' => 'Zone ID niet gevonden',
    'text.recordNotFound' => 'Record niet gevonden',

    'message.apiSaved' => 'API-instellingen zijn succesvol opgeslagen.',
    'message.settingsSaved' => 'Domeininstellingen zijn succesvol opgeslagen.',
    'message.noConnection' => 'Kon niet verbinden met Cloudflare.',
    'message.couldNotSync' => 'De Plesk DNS-zone kon niet worden gesynchroniseerd met Cloudflare.',
    'message.noCloudflareZoneFound' => 'Kan geen Cloudflare zone voor dit domein gevonden worden.',
    'message.noAccessToDomain' => 'U heeft geen toegang tot dit domein.',
    'message.noAccessExtension' => 'U heeft geen toegang tot DNS Sync voor Cloudflare.',
    'message.noAccessSettings' => 'U heeft geen toegang tot de domein instellingen.',
    'message.noDomainSelected' => 'Er was geen domein geselecteerd.',
    'message.noRecordsEdited' => 'Geen records gemaakt of geupdate.',
    'message.xRecordsCreated' => '%%count%% record(s) gemaakt.',
    'message.xRecordsUpdated' => '%%count%% record(s) geupdate.',
];