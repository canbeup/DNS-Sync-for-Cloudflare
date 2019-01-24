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

    $this->cloudflare = new Cloudflare(pm_Settings::getDecrypted('cloudflareEmail'), pm_Settings::getDecrypted('cloudflareApiKey'));
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

  public function apiAction()
  {
    //Create a new Form
    $form = new pm_Form_Simple();
    $form->addElement('Text', 'cloudflareEmail', array(
        'label' => 'Cloudflare Email',
        'value' => pm_Settings::getDecrypted('cloudflareEmail'),
        'required' => true,
        'validator' => array(
            array('EmailAddress', true)
        )
    ));
    $form->addElement('Text', 'cloudflareApiKey', array(
        'label' => 'Cloudflare API Key',
        'value' => pm_Settings::getDecrypted('cloudflareApiKey'),
        'required' => true,
        'validator' => array(
            array('NotEmpty', true)
        )
    ));

    $form->addControlButtons(array(
        'cancelLink' => pm_Context::getModulesListUrl(),
    ));

    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
      pm_Settings::setEncrypted('cloudflareEmail', $form->getValue('cloudflareEmail'));
      pm_Settings::setEncrypted('cloudflareApiKey', $form->getValue('cloudflareApiKey'));

      $this->_status->addMessage('info', 'Data was successfully saved.');
      $this->_helper->json(array('redirect' => pm_Context::getBaseUrl()));
    }

    $this->view->form = $form;
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
