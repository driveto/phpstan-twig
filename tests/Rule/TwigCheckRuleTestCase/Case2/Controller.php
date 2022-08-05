<?php declare(strict_types = 1);

namespace Driveto\PhpstanTwig\Tests\Rule\TwigCheckRuleTestCase\Case2;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class Controller extends AbstractController
{

	public function __invoke(): void
	{
		$this->render('@Tests/Case2/template.html.twig');
	}

}
