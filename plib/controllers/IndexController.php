<?php

require 'classes/Cloudflare.php';
require 'classes/PleskDNS.php';

class IndexController extends pm_Controller_Action
{
  /**
   * @var $cloudflare Cloudflare
   */
  private $cloudflare;

  public function init()
  {
    parent::init();

    // Init title for all actions
    $this->view->pageTitle = 'Cloudflare DNS Sync';

    // Init tabs for all actions
    $this->view->tabs = array(
        array(
            'title' => 'Domains',
            'action' => 'domains',
        ),
        array(
            'title' => 'API',
            'action' => 'api',
        )
    );

    $this->cloudflare = new Cloudflare('sanderjochems@hotmail.nl', '384c07aaa0b4b8827155d5de764bda2b3a9c5');
  }

  public function indexAction()
  {
    $this->forward('domains');
  }

  public function domainsAction()
  {
    $list = $this->_getDomainList();

    $this->view->list = $list;
  }

  public function domainDataAction()
  {
    $list = $this->_getDomainList();
    // Json data from pm_View_List_Simple
    $this->_helper->json($list->fetchData());
  }

  private function _getDomainList()
  {
    $zones = $this->cloudflare->getZones();

    $data = array();
    /**
     * @var $domain pm_Domain
     */
    foreach (pm_Session::getCurrentDomains(true) as $domain) {
      $cloudflareID = "Zone ID not found";
      foreach ($zones->listZones()->result as $zone) {
        if ($zone->name == $domain->getName()) {
          $cloudflareID = $zone->id;
          break;
        }
      }
      $data[] = array(
          'col-id' => $domain->getId(),
          'col-domain' => $domain->getName(),
          'col-zone' => $cloudflareID,
      );
    }

    $list = new pm_View_List_Simple($this->view, $this->_request);
    $list->setData($data);
    $list->setColumns(array(
        'col-id' => array(
            'title' => 'ID',
            'noEscape' => true,
        ),
        'col-domain' => array(
            'title' => 'Domain',
            'noEscape' => true,
        ),
        'col-zone' => array(
            'title' => 'Cloudflare Zone ID',
            'noEscape' => true,
        )
    ));
    $list->setDataUrl(array('action' => 'domain-data'));
    return $list;
  }
}
