<?php declare(strict_types = 1);

namespace Driveto\PhpstanTwig\Twig;

use Twig\Environment;
use function assert;

class TwigEnvironmentFactory
{

	private Environment $twig;

	public function __construct(string $twigLoaderPath)
	{
		$this->twig = require $twigLoaderPath;
		assert($this->twig instanceof Environment);

		$this->twig->disableDebug();
	}


	public function getTwig(): Environment
	{
		return $this->twig;
	}

}
