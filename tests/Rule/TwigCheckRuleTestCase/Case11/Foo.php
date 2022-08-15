<?php declare(strict_types = 1);

namespace Driveto\PhpstanTwig\Tests\Rule\TwigCheckRuleTestCase\Case11;

class Foo
{

	public function getIntValue(int $value): int
	{
		return $value;
	}

	public function getStringValue(string $value): string
	{
		return $value;
	}

}
