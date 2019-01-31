<?php

use GuzzleHttp\Exception\ClientException;

class SyncController extends pm_Controller_Action
{
  /**
   * @var $cloudflare Modules_CloudflareDnsSync_Cloudflare|bool
   */
  private $cloudflare;

  public function init()
  {
    parent::init();

    // Init title for all actions
    $this->view->pageTitle = 'Cloudflare DNS Sync';

    if ($this->getRequest()->getParam("site_id") != null) {

      $siteID = $this->getRequest()->getParam("site_id");

      // Init tabs for all actions
      $this->view->tabs = array(
          array(
              'title' => 'DNS',
              'action' => 'domain',
              'link' => 'domain?site_id='.$siteID,
          ),
          array(
              'title' => 'Settings',
              'action' => 'settings',
              'link' => 'settings?site_id='.$siteID,
          )
      );

    }

    $this->cloudflare = Modules_CloudflareDnsSync_Cloudflare::login(
        pm_Settings::getDecrypted(Modules_CloudflareDnsSync_Util_Settings::getUserKey(Modules_CloudflareDnsSync_Util_Settings::CLOUDFLARE_EMAIL)),
        pm_Settings::getDecrypted(Modules_CloudflareDnsSync_Util_Settings::getUserKey(Modules_CloudflareDnsSync_Util_Settings::CLOUDFLARE_API_KEY))
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

      if (pm_Session::getClient()->hasAccessToDomain($siteID)) {

        $this->view->tabs[0]['active'] = true;

        try {

          $zone = $this->cloudflare->getZone($siteID);

          if ($zone !== false) {

            $this->view->pageTitle = 'Cloudflare DNS Sync for <b>' . $zone->name . '</b>';

            $this->view->syncTools = [
                [
                    'title' => 'Sync DNS',
                    'description' => 'Sync the Plesk DNS to Cloudflare DNS',
                    'class' => 'sb-button1',
                    'action' => 'domain?site_id=' . $siteID.'&sync=all',
                ]
            ];

            //Check if we need to sync
            if ($this->getRequest()->getParam('sync') != null) {
              try {
                //Create the Sync Util
                $dnsSyncUtil = new Modules_CloudflareDnsSync_Util_SyncDNS($siteID, $this->cloudflare, new Modules_CloudflareDnsSync_PleskDNS());

                //Check if the sync method is all
                if ($this->getRequest()->getParam('sync') == 'all') {
                  //Sync the Plesk DNS to Cloudflare
                  $dnsSyncUtil->syncAll($this->_status);
                  //Check if the sync method is a single record
                } elseif (is_numeric($this->getRequest()->getParam('sync'))) {
                  //Get the record ID
                  $recordID = $this->getRequest()->getParam('sync');

                  //Sync the Plesk Record to Cloudflare
                  $dnsSyncUtil->syncRecord($this->_status, $recordID);
                }
              } catch (ClientException $exception) {
                $this->_status->addMessage('error', 'Could not sync the Plesk DNS zone to Cloudflare.');
                $this->_status->addMessage('warning', $exception->getMessage());
                $this->_status->addMessage('warning', $exception->getTraceAsString());
              }
            }

            $this->view->list = $this->_getRecordsList($siteID);

          } else {
            $this->_status->addMessage('error', 'Could not find a Cloudflare zone for this domain.');
          }
        } catch (ClientException $exception) {
          $this->_status->addMessage('error', 'Could not find a Cloudflare zone for this domain.');
        }
      } else {
        $this->_status->addMessage('error', 'You do not have access to this domain.');
      }
    } else {
      $this->_status->addMessage('error', 'There was no domain selected.');
    }
  }

  public function settingsAction()
  {
    if ($this->getRequest()->getParam("site_id") != null) {

      $siteID = $this->getRequest()->getParam("site_id");

      if (pm_Session::getClient()->hasAccessToDomain($siteID)) {

        try {
          $this->view->pageTitle = 'Cloudflare DNS Sync for <b>' . pm_Domain::getByDomainId($siteID)->getName() . '</b>';
        } catch (pm_Exception $e) {
        }

        $this->view->tabs[1]['active'] = true;

        //List the Type of available records
        $recordOptions = Modules_CloudflareDnsSync_Helper_Records::getAvailableRecords();

        $selectedRecords = array();

        foreach ($recordOptions as $option) {
          if (Modules_CloudflareDnsSync_Helper_DomainSettings::syncRecordType($option, $siteID)) {
            array_push($selectedRecords, $option);
          }
        }

        //Create a new Form
        $form = new pm_Form_Simple();
        $form->addElement('checkbox', Modules_CloudflareDnsSync_Util_Settings::CLOUDFLARE_PROXY, array(
            'label' => 'Traffic thru Cloudflare',
            'value' => Modules_CloudflareDnsSync_Helper_DomainSettings::useCloudflareProxy($siteID),
        ));
        $form->addElement('multiCheckbox', Modules_CloudflareDnsSync_Util_Settings::CLOUDFLARE_SYNC_TYPES, array(
            'label' => 'Select the type of records you want to sync',
            'multiOptions' => $recordOptions,
            'value' => $selectedRecords
        ));

        $form->addControlButtons(array(
            'sendTitle' => 'Save',
            'cancelLink' => pm_Context::getActionUrl('sync', 'settings?site_id=' . $siteID),
        ));

        if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
          pm_Settings::set(Modules_CloudflareDnsSync_Util_Settings::getDomainKey(Modules_CloudflareDnsSync_Util_Settings::CLOUDFLARE_PROXY, $siteID), $form->getValue(Modules_CloudflareDnsSync_Util_Settings::CLOUDFLARE_PROXY));

          foreach ($recordOptions as $option) {
            try {
              pm_Settings::set(Modules_CloudflareDnsSync_Util_Settings::getDomainKey('record' . $option, $siteID), in_array($option, $form->getValue(Modules_CloudflareDnsSync_Util_Settings::CLOUDFLARE_SYNC_TYPES)));
            } catch (Exception $e) {
            }
          }

          $this->_status->addMessage('info', 'Settings were successfully saved.');
          $this->_helper->json(array('redirect' => pm_Context::getActionUrl('sync', 'domain?site_id=' . $siteID)));
        }

        $this->view->form = $form;

      } else {
        $this->_status->addMessage('error', 'You do not have access to this domain.');
      }
    } else {
      $this->_status->addMessage('error', 'There was no domain selected.');
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
    $data = (new Modules_CloudflareDnsSync_List_SyncDNS($siteID, $this->cloudflare, new Modules_CloudflareDnsSync_PleskDNS()))->getList();

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
