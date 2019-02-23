<?php

class IndexController extends pm_Controller_Action
{
  /**
   * @var $cloudflare Modules_CloudflareDnsSync_Cloudflare|bool
   */
  private $cloudflare;

  public function init()
  {
    parent::init();

    // Init title for all actions
    $this->view->pageTitle = pm_Locale::lmsg('title');

    // Init tabs for all actions
    $this->view->tabs = array(
        array(
            'title' => pm_Locale::lmsg('tab.domains'),
            'action' => 'domains',
        ),
        array(
            'title' => pm_Locale::lmsg('tab.api'),
            'action' => 'api',
        )
    );

    $this->cloudflare = Modules_CloudflareDnsSync_Cloudflare::login(
        pm_Settings::getDecrypted(Modules_CloudflareDnsSync_Util_Settings::getUserKey(Modules_CloudflareDnsSync_Util_Settings::CLOUDFLARE_EMAIL)),
        pm_Settings::getDecrypted(Modules_CloudflareDnsSync_Util_Settings::getUserKey(Modules_CloudflareDnsSync_Util_Settings::CLOUDFLARE_API_KEY))
    );

    if ($this->cloudflare == false) {
      $this->view->tabs = null;
    }
  }

  public function indexAction()
  {
    if ($this->cloudflare !== false) {
      $this->forward('domains');
    } else {
      $this->forward('api');
    }
  }

  public function domainsAction()
  {
    if ($this->cloudflare !== false) {
      try {
        $list = $this->_getDomainList();

        $this->view->list = $list;
      } catch (GuzzleHttp\Exception\ClientException $exception) {
        $this->view->error = pm_Locale::lmsg('message.noConnection');
      }
    } else {
      $this->forward('api');
    }
  }

  public function apiAction()
  {
    //Create a new Form
    $form = new pm_Form_Simple();
    $form->addElement('Text', Modules_CloudflareDnsSync_Util_Settings::CLOUDFLARE_EMAIL, array(
        'label' => pm_Locale::lmsg('form.cloudflareEmail'),
        'value' => pm_Settings::getDecrypted(Modules_CloudflareDnsSync_Util_Settings::getUserKey(Modules_CloudflareDnsSync_Util_Settings::CLOUDFLARE_EMAIL)),
        'required' => true,
        'validator' => array(
            array('EmailAddress', true)
        )
    ));
    $form->addElement('Text', Modules_CloudflareDnsSync_Util_Settings::CLOUDFLARE_API_KEY, array(
        'label' => pm_Locale::lmsg('form.cloudflareApiKey'),
        'value' => pm_Settings::getDecrypted(Modules_CloudflareDnsSync_Util_Settings::getUserKey(Modules_CloudflareDnsSync_Util_Settings::CLOUDFLARE_API_KEY)),
        'required' => true,
        'validator' => array(
            array('NotEmpty', true)
        )
    ));

    $form->addControlButtons(array(
        'cancelLink' => pm_Context::getModulesListUrl(),
    ));

    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
      pm_Settings::setEncrypted(Modules_CloudflareDnsSync_Util_Settings::getUserKey(Modules_CloudflareDnsSync_Util_Settings::CLOUDFLARE_EMAIL), $form->getValue(Modules_CloudflareDnsSync_Util_Settings::CLOUDFLARE_EMAIL));
      pm_Settings::setEncrypted(Modules_CloudflareDnsSync_Util_Settings::getUserKey(Modules_CloudflareDnsSync_Util_Settings::CLOUDFLARE_API_KEY), $form->getValue(Modules_CloudflareDnsSync_Util_Settings::CLOUDFLARE_API_KEY));

      $this->_status->addMessage('info', pm_Locale::lmsg('message.apiSaved'));
      $this->_helper->json(array('redirect' => pm_Context::getBaseUrl()));
    }

    $this->view->form = $form;
  }

  public function domainDataAction()
  {
    if ($this->cloudflare !== false) {
      $list = $this->_getDomainList();
      // Json data from pm_View_List_Simple
      $this->_helper->json($list->fetchData());
    }
  }

  private function _getDomainList()
  {
    $zones = $this->cloudflare->getZones();

    $data = array();
    /**
     * @var $domain pm_Domain
     */
    foreach (pm_Session::getCurrentDomains(true) as $domain) {
      $cloudflareID = pm_Locale::lmsg('text.zoneIdNotFound');
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
            'title' => pm_Locale::lmsg('table.domainName'),
            'noEscape' => true,
        ),
        'col-zone' => array(
            'title' => pm_Locale::lmsg('table.cloudflareZoneID'),
            'noEscape' => true,
        )
    ));
    $list->setDataUrl(array('action' => 'domain-data'));
    return $list;

  }
}
