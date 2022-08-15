<?php declare(strict_types = 1);

namespace Driveto\PhpstanTwig\Tests\Rule\TwigCheckRuleTestCase\Case6;

class Foo
{

	public function echo(string $name): string
	{
		return $name;
	}

}
