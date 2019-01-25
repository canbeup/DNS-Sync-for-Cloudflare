<?php

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

}
