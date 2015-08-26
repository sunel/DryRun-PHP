#!/usr/bin/env php
<?php

require 'vendor/autoload.php';

define('DS', DIRECTORY_SEPARATOR);

$command = new Dryrun\Command\RunCommand();

$application = new Dryrun\Application('DryRun', '0.1 (DEV)');

$application->add($command);
$application->setDefaultCommand($command->getName());

$application->run();
