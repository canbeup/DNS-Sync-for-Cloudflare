<?php

pm_Context::init("cloudflare-dns-sync");

$application = new pm_Application();
$application->run();
