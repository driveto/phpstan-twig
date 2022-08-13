<?php declare(strict_types = 1);

use Driveto\PhpstanTwig\Tests\Inc\GetIntExtension;
use Driveto\PhpstanTwig\Tests\Inc\GetStringExtension;
use Twig\Environment as Twig;
use Twig\Loader\FilesystemLoader;

$templateLoader = new FilesystemLoader();
$templateLoader->addPath(__DIR__ . '/Rule/TwigCheckRuleTestCase/', 'Tests');

$twig = new Twig($templateLoader);
$twig->addExtension(new GetStringExtension());
$twig->addExtension(new GetIntExtension());

return $twig;
