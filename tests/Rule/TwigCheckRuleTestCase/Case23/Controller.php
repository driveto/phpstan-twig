<?php declare(strict_types = 1);

namespace Driveto\PhpstanTwig\Tests\Rule\TwigCheckRuleTestCase\Case23;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Twig\Environment;

class Controller extends AbstractController
{

	public function __construct(private Environment $environment)
	{
	}

	public function __invoke(): void
	{
		$this->environment->render('@Tests/Case23/template.html.twig');
	}

}
