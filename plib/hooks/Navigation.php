<?php

class Modules_CloudflareDnsSync_Navigation extends pm_Hook_Navigation
{
  public function getNavigation()
  {
    return [
        [
            'controller' => 'index',
            'action' => 'index',
            'label' => 'Cloudflare DNS Sync',
            'pages' => [
                [
                    'controller' => 'index',
                    'action' => 'domains',
                    'label' => 'Domains',
                    'pages' => [
                        [
                            'controller' => 'sync',
                            'action' => 'domain',
                            'label' => 'DNS'
                        ],
                        [
                            'controller' => 'sync',
                            'action' => 'settings',
                            'label' => 'Settings'
                        ]
                    ]
                ],
              [
                  'controller' => 'index',
                  'action' => 'api',
                  'label' => 'API'
              ]
            ]
        ]
    ];
  }
}