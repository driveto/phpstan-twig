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
	}

	public static function getAdditionalConfigFiles(): array
	{
		return [
			__DIR__ . '/../../extension.neon',
			__DIR__ . '/../extension-test.neon',
		];
	}

}
