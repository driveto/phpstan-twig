<?php declare(strict_types = 1);

namespace Driveto\PhpstanTwig\Tests\Inc;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class GetIntExtension extends AbstractExtension
{

	public function getFunctions(): array
	{
		return [
			new TwigFunction('getIntFromExtension', [$this, 'getInt']),
		];
	}

	public function getInt(): int
	{
		return 42;
	}

}
