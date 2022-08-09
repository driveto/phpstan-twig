<?php declare(strict_types = 1);

namespace Driveto\PhpstanTwig\Tests\Rule\TwigCheckRuleTestCase\Case8;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class Controller extends AbstractController
{

	public function __invoke(): void
	{
		$this->render(
			'@Tests/Case8/template.html.twig',
			['numbers' => [1, 2, 3, 4, 5]],
		);
	}

}
