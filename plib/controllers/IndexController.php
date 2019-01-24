<?php

class IndexController extends pm_Controller_Action
{
    public function indexAction()
    {
  public function init()
  {
    parent::init();

    // Init title for all actions
    $this->view->pageTitle = 'Cloudflare DNS Sync';
  }

  public function indexAction()
  {
    $this->forward('domains');
  }

  public function domainsAction()
  {

  }
    }
}
