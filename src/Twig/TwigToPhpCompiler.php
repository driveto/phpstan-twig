<?php declare(strict_types = 1);

namespace Driveto\PhpstanTwig\Twig;

use PHPStan\Type\ConstantTypeHelper;
use PHPStan\Type\Type;
use Twig\Environment;

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

	/** @return array<int|string, Type> */
	public function getGlobalTypes(): array
	{
		$globalArgs = [];
		foreach ($this->twigEnvironment->getGlobals() as $globalVarName => $value) {
			$globalArgs[$globalVarName] = ConstantTypeHelper::getTypeFromValue($value);
		}

		return $globalArgs;
	}

}
