<?php

include 'ClassLoader.php';

class IndexController extends pm_Controller_Action
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

    $this->cloudflare = Cloudflare::login(
        pm_Settings::getDecrypted(SettingsUtil::getUserKey(SettingsUtil::CLOUDFLARE_EMAIL)),
        pm_Settings::getDecrypted(SettingsUtil::getUserKey(SettingsUtil::CLOUDFLARE_API_KEY))
    );
  }

  public function indexAction()
  {
    $this->forward('domains');
  }

  public function domainsAction()
  {
    try {
      $list = $this->_getDomainList();

      $this->view->list = $list;
    } catch (GuzzleHttp\Exception\ClientException $exception) {
      $this->view->error = "Could not connect to Cloudflare";
    }
  }

  public function apiAction()
  {
    //Create a new Form
    $form = new pm_Form_Simple();
    $form->addElement('Text', SettingsUtil::CLOUDFLARE_EMAIL, array(
        'label' => 'Cloudflare Email',
        'value' => pm_Settings::getDecrypted(SettingsUtil::getUserKey(SettingsUtil::CLOUDFLARE_EMAIL)),
        'required' => true,
        'validator' => array(
            array('EmailAddress', true)
        )
    ));
    $form->addElement('Text', SettingsUtil::CLOUDFLARE_API_KEY, array(
        'label' => 'Cloudflare API Key',
        'value' => pm_Settings::getDecrypted(SettingsUtil::getUserKey(SettingsUtil::CLOUDFLARE_API_KEY)),
        'required' => true,
        'validator' => array(
            array('NotEmpty', true)
        )
    ));

    $form->addControlButtons(array(
        'cancelLink' => pm_Context::getModulesListUrl(),
    ));

    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
      pm_Settings::setEncrypted(SettingsUtil::getUserKey(SettingsUtil::CLOUDFLARE_EMAIL), $form->getValue(SettingsUtil::CLOUDFLARE_EMAIL));
      pm_Settings::setEncrypted(SettingsUtil::getUserKey(SettingsUtil::CLOUDFLARE_API_KEY), $form->getValue(SettingsUtil::CLOUDFLARE_API_KEY));

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
          'col-domain' => '<a href="'.pm_Context::getBaseUrl().'index.php/sync/domain?site_id='.$domain->getId().'">'.$domain->getName().'</a>',
          'col-zone' => $cloudflareID,
      );
    }

    $list = new pm_View_List_Simple($this->view, $this->_request);
    $list->setData($data);
    $list->setColumns(array(
        'col-domain' => array(
            'title' => 'Domain Name',
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
