<?php declare(strict_types = 1);

namespace Driveto\PhpstanTwig\Tests\Rule;

use Driveto\PhpstanTwig\Rule\TwigCheckRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/** @extends RuleTestCase<TwigCheckRule> */
class TwigCheckRuleTest extends RuleTestCase
{

	protected function getRule(): Rule
	{
		return self::getContainer()->getByType(TwigCheckRule::class);
	}

	/**
	 * @dataProvider provideTwigCheckRuleData
	 *
	 * @param array<array{0: string, 1: int}> $expectedErrors
	 */
	public function testTwigCheckRule(string $fileName, array $expectedErrors): void
	{
		$this->analyse([$fileName], $expectedErrors);
	}

	/** @return mixed[] */
	public function provideTwigCheckRuleData(): iterable
	{
		yield 'simple string variable shows no error' => [
			__DIR__ . '/TwigCheckRuleTestCase/Case1/Controller.php',
			[],
		];

		yield 'unknown variable shows error' => [
			__DIR__ . '/TwigCheckRuleTestCase/Case2/Controller.php',
			[
				[
					'Offset \'simpleVariable\' does not exist on array{}.',
					12,
				],
			],
		];

		yield 'existing method on existing class shows no error' => [
			__DIR__ . '/TwigCheckRuleTestCase/Case3/Controller.php',
			[],
		];

		yield 'non existing method on nested object shows error' => [
			__DIR__ . '/TwigCheckRuleTestCase/Case4/Controller.php',
			[
				[
					'Call to an undefined method Driveto\PhpstanTwig\Tests\Rule\TwigCheckRuleTestCase\Case4\Bar::nonExistingMethod().',
					12,
				],
			],
		];

		yield 'non existing method on object shows error' => [
			__DIR__ . '/TwigCheckRuleTestCase/Case5/Controller.php',
			[
				[
					'Call to an undefined method Driveto\PhpstanTwig\Tests\Rule\TwigCheckRuleTestCase\Case5\Foo::nonExistingMethod().',
					12,
				],
			],
		];

		yield 'nested methods call shows no error' => [
			__DIR__ . '/TwigCheckRuleTestCase/Case6/Controller.php',
			[],
		];

		yield 'basic for loop shows no error' => [
			__DIR__ . '/TwigCheckRuleTestCase/Case7/Controller.php',
			[],
		];

		yield 'basic for loop with unknown variable shows error' => [
			__DIR__ . '/TwigCheckRuleTestCase/Case8/Controller.php',
			[
				[
					'Offset \'letter\' does not exist on array{numbers: array{int, int, int, int, int}, _seq: array{int, int, int, int, int}, number: int, _key: 0|1|2|3|4}.',
					12,
				],
			],
		];

		yield 'basic for loop with variable out of scope shows error' => [
			__DIR__ . '/TwigCheckRuleTestCase/Case9/Controller.php',
			[
				[
					'Offset \'number\' does not exist on array{numbers: array<int|string, int>, _seq: array<int|string, int>, number?: int, _key?: int|string}.',
					12,
				],
			],
		];

		yield 'basic for loop with variables shows no error' => [
			__DIR__ . '/TwigCheckRuleTestCase/Case10/Controller.php',
			[],
		];

		yield 'nested for loop resolved context correctly and shows no error' => [
			__DIR__ . '/TwigCheckRuleTestCase/Case11/Controller.php',
			[],
		];

		yield 'for loop with method call in argument shows no error' => [
			__DIR__ . '/TwigCheckRuleTestCase/Case12/Controller.php',
			[],
		];

		yield 'non existing twig function show error' => [
			__DIR__ . '/TwigCheckRuleTestCase/Case13/Controller.php',
			[
				[
					'Failed to compile template. Exception: Unknown "nonExistingFunction" function.',
					12,
				],
			],
		];

		yield 'wrong value type from extension function shows error' => [
			__DIR__ . '/TwigCheckRuleTestCase/Case14/Controller.php',
			[
				[
					'Parameter #1 $number of method Driveto\PhpstanTwig\Tests\Rule\TwigCheckRuleTestCase\Case14\Foo::printNumber() expects int, string given.',
					16,
				],
			],
		];

		yield 'using loop variable in for loop shows no error' => [
			__DIR__ . '/TwigCheckRuleTestCase/Case15/Controller.php',
			[],
		];
	}

	public static function getAdditionalConfigFiles(): array
	{
		return [
			__DIR__ . '/../../extension.neon',
			__DIR__ . '/../extension-test.neon',
		];
	}

}
