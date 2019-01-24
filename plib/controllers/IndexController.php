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

  }
    }
}
