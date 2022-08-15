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
					'Offset \'simpleVariable\' does not exist on array{globalString: string, globalObject: Driveto\PhpstanTwig\Tests\Inc\GlobalObject}.',
					1,
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
					1,
				],
			],
		];

		yield 'non existing method on object shows error' => [
			__DIR__ . '/TwigCheckRuleTestCase/Case5/Controller.php',
			[
				[
					'Call to an undefined method Driveto\PhpstanTwig\Tests\Rule\TwigCheckRuleTestCase\Case5\Foo::nonExistingMethod().',
					1,
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
					'Offset \'letter\' does not exist on array{globalString: string, globalObject: Driveto\PhpstanTwig\Tests\Inc\GlobalObject, numbers: non-empty-array<int, int>}.',
					2,
				],
			],
		];

		yield 'basic for loop with variable out of scope shows error' => [
			__DIR__ . '/TwigCheckRuleTestCase/Case9/Controller.php',
			[
				[
					'Offset \'number\' does not exist on array{globalString: string, globalObject: Driveto\PhpstanTwig\Tests\Inc\GlobalObject, numbers: array<int>}.',
					4,
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
					1,
				],
			],
		];

		yield 'using loop variable in for loop shows no error' => [
			__DIR__ . '/TwigCheckRuleTestCase/Case15/Controller.php',
			[],
		];

		yield 'variable defined in main context and used in block shows no error' => [
			__DIR__ . '/TwigCheckRuleTestCase/Case16/Controller.php',
			[],
		];

		yield 'test error message show correct line number ' => [
			__DIR__ . '/TwigCheckRuleTestCase/Case17/Controller.php',
			[
				[
					'Offset \'foo\' does not exist on array{globalString: string, globalObject: Driveto\PhpstanTwig\Tests\Inc\GlobalObject}.',
					1,
				], [
					'Offset \'bar\' does not exist on array{globalString: string, globalObject: Driveto\PhpstanTwig\Tests\Inc\GlobalObject}.',
					3,
				], [
					'Offset \'hello\' does not exist on array{globalString: string, globalObject: Driveto\PhpstanTwig\Tests\Inc\GlobalObject}.',
					9,
				], [
					'Offset \'foobar\' does not exist on array{globalString: string, globalObject: Driveto\PhpstanTwig\Tests\Inc\GlobalObject}.',
					6,
				],
			],
		];

		yield 'test error message show correct line number with nested templates' => [
			__DIR__ . '/TwigCheckRuleTestCase/Case18/Controller.php',
			[
				[
					'Offset \'parent_var_1\' does not exist on array{globalString: string, globalObject: Driveto\PhpstanTwig\Tests\Inc\GlobalObject}.',
					1,
				],
				[
					'Offset \'parent_var_2\' does not exist on array{globalString: string, globalObject: Driveto\PhpstanTwig\Tests\Inc\GlobalObject}.',
					3,
				],
				[
					'Offset \'parent_var_4\' does not exist on array{globalString: string, globalObject: Driveto\PhpstanTwig\Tests\Inc\GlobalObject}.',
					9,
				],
				[
					'Offset \'parent_var_3\' does not exist on array{globalString: string, globalObject: Driveto\PhpstanTwig\Tests\Inc\GlobalObject}.',
					6,
				],
				[
					'Offset \'template_var_1\' does not exist on array{globalString: string, globalObject: Driveto\PhpstanTwig\Tests\Inc\GlobalObject}.',
					4,
				],
				[
					'Offset \'child_1_var_2\' does not exist on array{globalString: string, globalObject: Driveto\PhpstanTwig\Tests\Inc\GlobalObject}.',
					5,
				],
				[
					'Offset \'child_2_var_1\' does not exist on array{globalString: string, globalObject: Driveto\PhpstanTwig\Tests\Inc\GlobalObject}.',
					1,
				],
				[
					'Offset \'child_2_var_2\' does not exist on array{globalString: string, globalObject: Driveto\PhpstanTwig\Tests\Inc\GlobalObject}.',
					3,
				],
				[
					'Offset \'child_1_var_1\' does not exist on array{globalString: string, globalObject: Driveto\PhpstanTwig\Tests\Inc\GlobalObject}.',
					2,
				],
				[
					'Offset \'template_var_2\' does not exist on array{globalString: string, globalObject: Driveto\PhpstanTwig\Tests\Inc\GlobalObject}.',
					8,
				],
			],
		];

		yield 'set used in block pair shows no errors' => [
			__DIR__ . '/TwigCheckRuleTestCase/Case19/Controller.php',
			[],
		];

		yield 'using variable defined in template in extended template show no error' => [
			__DIR__ . '/TwigCheckRuleTestCase/Case20/Controller.php',
			[],
		];

		yield 'using variable defined in template in extended template with wrond type show error' => [
			__DIR__ . '/TwigCheckRuleTestCase/Case21/Controller.php',
			[
				[
					'Parameter #1 $value of method Driveto\PhpstanTwig\Tests\Rule\TwigCheckRuleTestCase\Case21\Foo::returnInt() expects int, string given.',
					1,
				],
			],
		];

		yield 'using extension function in main block and another in local block show no error' => [
			__DIR__ . '/TwigCheckRuleTestCase/Case22/Controller.php',
			[],
		];

		yield 'multiple errors on same line and file show only once in reported errors' => [
			__DIR__ . '/TwigCheckRuleTestCase/Case23/Controller.php',
			[
				[
					'Offset \'nonExistingVar\' does not exist on array{globalString: string, globalObject: Driveto\PhpstanTwig\Tests\Inc\GlobalObject}.',
					1,
				],
			],
		];

		yield 'types on for loop with slice resolved correctly and show no error' => [
			__DIR__ . '/TwigCheckRuleTestCase/Case24/Controller.php',
			[],
		];

		yield 'union types are resolved correctly and show errors' => [
			__DIR__ . '/TwigCheckRuleTestCase/Case25/Controller.php',
			[
				[
					'Argument of an invalid type array<int>|null supplied for foreach, only iterables are supported.',
					1,
				],
			],
		];

		yield 'constant types resolves to their generic form and prevents always true/false errors' => [
			__DIR__ . '/TwigCheckRuleTestCase/Case26/Controller.php',
			[],
		];

		yield 'public variables on class resolves correctly and show error is used wrong' => [
			__DIR__ . '/TwigCheckRuleTestCase/Case27/Controller.php',
			[
				[
					'Parameter #1 $number of method Driveto\PhpstanTwig\Tests\Rule\TwigCheckRuleTestCase\Case27\ClassWithPublicProperties::returnInt() expects int, string given.',
					1,
				],
			],
		];

		yield 'null type doesnt fail on recursion loop and ends with no errors' => [
			__DIR__ . '/TwigCheckRuleTestCase/Case28/Controller.php',
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
