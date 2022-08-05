<?php declare(strict_types = 1);

namespace Driveto\PhpstanTwig\Tests\Rule\TwigCheckRuleTestCase\Case3;

class Foo
{

	public function getBar(): Bar
	{
		return new Bar();
	}

}
