#!/usr/bin/env php
<?php
include_once __DIR__. '/../vendor/autoload.php';
include_once __DIR__.'/../config/defines.inc.php';
use Bwlab\Commands\SalutaCommand;
use Symfony\Component\Console\Application;

$application = new Application();
$application->add(new SalutaCommand);
$application->run();