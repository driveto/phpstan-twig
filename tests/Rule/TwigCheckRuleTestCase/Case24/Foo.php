<?php declare(strict_types = 1);

namespace Driveto\PhpstanTwig\Tests\Rule\TwigCheckRuleTestCase\Case24;

class Foo
{

	public function returnInt(int $value): int
	{
		return $value;
	}

}
