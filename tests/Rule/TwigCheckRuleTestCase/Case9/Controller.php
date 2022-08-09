<?php declare(strict_types = 1);

namespace Driveto\PhpstanTwig\Tests\Rule\TwigCheckRuleTestCase\Case9;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class Controller extends AbstractController
{

	public function __invoke(): void
	{
		$this->render(
			'@Tests/Case9/template.html.twig',
			['numbers' => $this->getValues()],
		);
	}

	/** @return int[] */
	private function getValues(): array
	{
		return [1, 2, 3, 4, 5];
	}

}
