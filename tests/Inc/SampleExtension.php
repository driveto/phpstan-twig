<?php declare(strict_types = 1);

namespace Driveto\PhpstanTwig\Tests\Inc;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SampleExtension extends AbstractExtension
{

	public function getFunctions(): array
	{
		return [
			new TwigFunction('someFunction', [$this, 'getString']),
		];
	}

	public function getString(): string
	{
		return '42';
	}

}
