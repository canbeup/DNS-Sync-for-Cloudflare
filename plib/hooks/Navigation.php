<?php

class Modules_CloudflareDnsSync_Navigation extends pm_Hook_Navigation
{
  public function getNavigation()
  {
    return [
        [
            'controller' => 'index',
            'action' => 'index',
            'label' => pm_Locale::lmsg('title'),
            'pages' => [
                [
                    'controller' => 'index',
                    'action' => 'domains',
                    'label' => pm_Locale::lmsg('tab.domains'),
                    'pages' => [
                        [
                            'controller' => 'sync',
                            'action' => 'domain',
                            'label' => pm_Locale::lmsg('tab.dns')
                        ],
                        [
                            'controller' => 'sync',
                            'action' => 'settings',
                            'label' => pm_Locale::lmsg('tab.settings')
                        ]
                    ]
                ],
              [
                  'controller' => 'index',
                  'action' => 'api',
                  'label' => pm_Locale::lmsg('tab.api')
              ]
            ]
        ]
    ];
  }
}