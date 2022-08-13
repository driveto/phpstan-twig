<?php declare(strict_types = 1);

namespace Driveto\PhpstanTwig\Tests\Rule\TwigCheckRuleTestCase\Case14;

class Foo
{

	public function printNumber(int $number): int
	{
		return $number;
	}

}
