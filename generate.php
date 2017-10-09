#!/usr/bin/env php
<?php
	// application.php

	require __DIR__ . '/vendor/autoload.php';

	error_reporting(E_ALL&~E_NOTICE);

	use Symfony\Component\Console\Application;

	$application = new Application();

	$application->add(new RkuCreator\GenerateProject());
	$application->add(new RkuCreator\GenerateData());
	$application->add(new RkuCreator\GenerateQuellcode());

	$application->run();