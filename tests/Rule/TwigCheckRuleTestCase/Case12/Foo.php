<?php declare(strict_types = 1);

namespace Driveto\PhpstanTwig\Tests\Rule\TwigCheckRuleTestCase\Case12;

class Foo
{

	/** @return int[] */
	public function getNumbers(): array
	{
		return [1, 2, 3, 4, 5];
	}

}
