<?php declare(strict_types = 1);

namespace Driveto\PhpstanTwig\Twig;

use function explode;
use function preg_match;

class TwigLineNumberExtractor
{

	/** @var array<int, int> */
	private array $twigLineNumbersByPhpLineNumbers = [];

	public function __construct(string $compiledTemplate)
	{
		foreach (explode("\n", $compiledTemplate) as $index => $line) {
			if (preg_match('/\\s*\\/\\/ line (\\d+)$/', $line, $matches) !== 1) {
				continue;
			}

			$twigLineNumber = (int) ($matches[1] ?? 0);
			if ($twigLineNumber <= 0) {
				continue;
			}

			$this->twigLineNumbersByPhpLineNumbers[$index + 1] = $twigLineNumber;
		}
	}

	public function getTwigLineNumber(int $lineNumber): int
	{
		$result = 1;
		foreach ($this->twigLineNumbersByPhpLineNumbers as $phpLineNumber => $twigLineNumber) {
			if ($phpLineNumber > $lineNumber) {
				break;
			}

			$result = $twigLineNumber;
		}
		return $result;
	}

}
