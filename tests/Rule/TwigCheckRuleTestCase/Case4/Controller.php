<?php declare(strict_types = 1);

namespace Driveto\PhpstanTwig\Tests\Rule\TwigCheckRuleTestCase\Case4;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class Controller extends AbstractController
{

	public function __invoke(): void
	{
		$this->render(
			'@Tests/Case4/template.html.twig',
			['foo' => new Foo()],
		);
	}

}
