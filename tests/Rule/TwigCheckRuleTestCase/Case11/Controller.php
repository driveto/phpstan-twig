<?php declare(strict_types = 1);

namespace Driveto\PhpstanTwig\Tests\Rule\TwigCheckRuleTestCase\Case11;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class Controller extends AbstractController
{

	public function __invoke(): void
	{
		$this->render(
			'@Tests/Case11/template.html.twig',
			[
				'foo' => new Foo(),
				'numbers' => $this->getNumbers(),
				'letters' => $this->getLetters(),
			],
		);
	}

	/** @return int[] */
	private function getNumbers(): array
	{
		return [1, 2, 3, 4, 5];
	}

	/** @return string[] */
	private function getLetters(): array
	{
		return ['some', 'value'];
	}

}
