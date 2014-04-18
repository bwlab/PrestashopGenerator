#!/usr/bin/env php
<?php
include_once __DIR__. '/../vendor/autoload.php';
include_once __DIR__.'/../config/defines.inc.php';
use Bwlab\Commands\InitModuleCommand;
use Bwlab\Commands\ViewModuleCommand;
use Bwlab\Commands\ControllerModuleCommand;
use Bwlab\Commands\ModelModuleCommand;
use Symfony\Component\Console\Application;

$application = new Application();
$application->add(new InitModuleCommand);
$application->add(new ViewModuleCommand);
$application->add(new ControllerModuleCommand);
$application->add(new ModelModuleCommand);
$application->run();