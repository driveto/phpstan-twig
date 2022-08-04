<?php declare(strict_types = 1);

use Twig\Environment as Twig;
use Twig\Loader\FilesystemLoader;

$templateLoader = new FilesystemLoader();
$templateLoader->addPath(__DIR__ . '/Rule/TwigCheckRuleTestCase/', 'Tests');

return new Twig($templateLoader);
