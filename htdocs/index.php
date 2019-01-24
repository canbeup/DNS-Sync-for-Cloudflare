<?php

pm_Context::init("cloudflare_dns_sync");

$application = new pm_Application();
$application->run();
