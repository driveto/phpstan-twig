<?php declare(strict_types = 1);

namespace Driveto\PhpstanTwig\Twig;

use Exception;
use Twig\Environment;
use function get_class;
use function is_bool;
use function is_object;
use function is_string;

class TwigToPhpCompiler
{

	private Environment $twigEnvironment;

	public function __construct(TwigEnvironmentFactory $twigEnvironmentFactory)
	{
		$this->twigEnvironment = $twigEnvironmentFactory->getTwig();
	}

	public function compile(string $templateName): string
	{
		$loader = $this->twigEnvironment->getLoader();
		$source = $loader->getSourceContext($templateName);

		return $this->twigEnvironment->compileSource($source);
	}

	/** @return array<int|string, string> */
	public function getGlobalTypes(): array
	{
		$globalArgs = [];
		foreach ($this->twigEnvironment->getGlobals() as $globalVarName => $value) {
			if (is_object($value)) {
				$globalArgs[$globalVarName] = get_class($value);
			} elseif (is_string($value)) {
				$globalArgs[$globalVarName] = 'string';
			} elseif (is_bool($value)) {
				$globalArgs[$globalVarName] = 'bool';
			} else {
				throw new Exception();
			}
		}

		return $globalArgs;
	}

}
