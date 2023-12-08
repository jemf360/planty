<?php

/* @var $container \Dice\Dice */

$container->addRule( '\ShortPixel\CriticalCSS', [
	'shared' => true,
] );
$container->addRule( '\ShortPixel\CriticalCSS\Web\Check\Background\Process', [
	'shared' => true,
] );
$container->addRule( '\ShortPixel\CriticalCSS\API\Background\Process', [
	'shared' => true,
] );
