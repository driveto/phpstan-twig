<?php declare(strict_types = 1);

namespace Driveto\PhpstanTwig\Tests\Rule\TwigCheckRuleTestCase\Case10;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class Controller extends AbstractController
{

	public function __invoke(): void
	{
		$this->render(
			'@Tests/Case10/template.html.twig',
			[
				'foo' => new Foo(),
				'values' => $this->getValues(),
				'value' => 'foobar',
				'stringValue' => 'foobar',
			],
		);
	}

	/** @return int[] */
	private function getValues(): array
	{
		return [1, 2, 3, 4, 5];
	}

}
