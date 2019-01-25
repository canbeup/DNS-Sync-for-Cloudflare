<?php

use GuzzleHttp\Exception\ClientException;

require 'classes/Cloudflare.php';
require 'classes/PleskDNS.php';
require 'classes/SettingsUtil.php';
require 'helpers/CloudflareRecord.php';
require 'utils/DNSListUtil.php';
require 'utils/DNSSyncUtil.php';
require 'utils/Functions.php';

class SyncController extends pm_Controller_Action
{
  /**
   * @var $cloudflare Cloudflare|bool
   */
  private $cloudflare;

  public function init()
  {
    parent::init();

    // Init title for all actions
    $this->view->pageTitle = 'Cloudflare DNS Sync';

    $this->cloudflare = Cloudflare::login(
        pm_Settings::getDecrypted(SettingsUtil::getUserKey(SettingsUtil::CLOUDFLARE_EMAIL)),
        pm_Settings::getDecrypted(SettingsUtil::getUserKey(SettingsUtil::CLOUDFLARE_API_KEY))
    );
  }

  public function indexAction()
  {
    $this->forward('domain');
  }

  public function domainAction()
  {
    if ($this->getRequest()->getParam("site_id") != null) {

      $siteID = $this->getRequest()->getParam("site_id");

      try {

        $zone = $this->cloudflare->getZone($siteID);

        if ($zone !== false) {

          $this->view->pageTitle = 'Cloudflare DNS Sync for <b>'.$zone->name.'</b>';

          $this->view->syncTools = [
              [
                  'title' => 'Sync DNS',
                  'description' => 'Sync the Plesk DNS to Cloudflare DNS',
                  'class' => 'sb-button1',
                  'action' => 'sync-dns?site_id='.$siteID,
              ]
          ];

          $this->view->list = $this->_getRecordsList($siteID);

        } else {
          $this->_status->addMessage('error', 'Could not find a Cloudflare zone for this domain.');
        }
      } catch (ClientException $exception) {
        $this->_status->addMessage('error', 'Could not find a Cloudflare zone for this domain.');
      }
    } else {
      $this->_status->addMessage('error', 'Could not find a Cloudflare zone for this domain.');
    }
  }

  public function syncDnsAction() {
    if ($this->getRequest()->getParam("site_id") != null) {

      $siteID = $this->getRequest()->getParam("site_id");

      try {

        $zone = $this->cloudflare->getZone($siteID);

        if ($zone !== false) {

          $this->view->pageTitle = 'Cloudflare DNS Sync for <b>'.$zone->name.'</b>';

          $dnsSyncUtil = new DNSSyncUtil($siteID, $this->cloudflare, new PleskDNS());

          $dnsSyncUtil->syncAll($this->_status);

        } else {
          $this->_status->addMessage('error', 'Could not find a Cloudflare zone for this domain.');
        }
      } catch (ClientException $exception) {
        $this->_status->addMessage('error', 'Could not find a Cloudflare zone for this domain.');
      }
    } else {
      $this->_status->addMessage('error', 'Could not find a Cloudflare zone for this domain.');
    }
  }

  public function domainDataAction()
  {
    if ($this->getRequest()->getParam("site_id") != null) {
      $siteID = $this->getRequest()->getParam("site_id");

      $list = $this->_getRecordsList($siteID);
      // Json data from pm_View_List_Simple
      $this->_helper->json($list->fetchData());
    }
  }

  private function _getRecordsList($siteID)
  {
    $data = (new DNSListUtil($siteID, $this->cloudflare, new PleskDNS()))->getList();

    $list = new pm_View_List_Simple($this->view, $this->_request);
    $list->setData($data);
    $list->setColumns(array(
        'col-host' => array(
            'title' => 'Host',
            'noEscape' => true,
        ),
        'col-type' => array(
            'title' => 'Record type',
            'noEscape' => true,
        ),
        'col-status' => array(
            'title' => 'Status',
            'noEscape' => true,
        ),
        'col-cloudflare' => array(
            'title' => 'Cloudflare Value',
            'noEscape' => true,
        ),
        'col-plesk' => array(
            'title' => 'Plesk Value',
            'noEscape' => true,
        )
    ));
    $list->setDataUrl(array('action' => 'domain-data?site_id='.$siteID));
    return $list;
  }
}
