<?php declare(strict_types = 1);

namespace Driveto\PhpstanTwig\Tests\Rule\TwigCheckRuleTestCase\Case16;

class Foo
{

	public function returnString(string $value): string
	{
		return $value;
	}

}
