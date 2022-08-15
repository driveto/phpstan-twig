<?php declare(strict_types = 1);

namespace Driveto\PhpstanTwig\Tests\Rule\TwigCheckRuleTestCase\Case25;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Twig\Environment;
use function random_int;

class Controller extends AbstractController
{

	public function __construct(private Environment $environment)
	{
	}

	public function __invoke(): void
	{
		$numbers = null;
		if (random_int(1, 3) === 2) {
			$numbers = $this->getNumbers();
		}

		$this->environment->render(
			'@Tests/Case25/template.html.twig',
			[
				'foo' => new Foo(),
				'numbers' => $numbers,
			]
		);
	}

	/** @return int[] */
	private function getNumbers(): array
	{
		return [1, 2, 3];
	}

}
