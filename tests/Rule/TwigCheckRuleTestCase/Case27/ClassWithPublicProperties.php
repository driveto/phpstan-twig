<?php declare(strict_types = 1);

namespace Driveto\PhpstanTwig\Tests\Rule\TwigCheckRuleTestCase\Case27;

class ClassWithPublicProperties
{

	public function __construct(
		public string $name,
	)
	{
	}

	public function returnInt(int $number): int
	{
		return $number;
	}

}
