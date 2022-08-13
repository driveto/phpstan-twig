<?php declare(strict_types = 1);

use Driveto\PhpstanTwig\Tests\Inc\SampleExtension;
use Twig\Environment as Twig;
use Twig\Loader\FilesystemLoader;

$templateLoader = new FilesystemLoader();
$templateLoader->addPath(__DIR__ . '/Rule/TwigCheckRuleTestCase/', 'Tests');

$twig = new Twig($templateLoader);
$twig->addExtension(new SampleExtension());

return $twig;
