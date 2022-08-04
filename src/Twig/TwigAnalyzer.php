<?php declare(strict_types = 1);

namespace Driveto\PhpstanTwig\Twig;

use PHPStan\Analyser\FileAnalyser;
use PHPStan\Analyser\FileAnalyserResult;
use PHPStan\Collectors\Registry as CollectorsRegistry;
use PHPStan\Collectors\RegistryFactory as CollectorsRegistryFactory;
use PHPStan\DependencyInjection\Container;
use PHPStan\DependencyInjection\DerivativeContainerFactory;
use PHPStan\Rules\Registry as RulesRegistry;
use PHPStan\Rules\RegistryFactory as RulesRegistryFactory;
use function assert;
use function file_put_contents;
use function is_string;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;

class TwigAnalyzer
{

	private ?Container $container = null;

	private ?RulesRegistry $rulesRegistry = null;

	private ?CollectorsRegistry $collectorRegistry = null;

	private ?FileAnalyser $fileAnalyser = null;

	private DerivativeContainerFactory $derivativeContainerFactory;

	public function __construct(
		DerivativeContainerFactory $derivativeContainerFactory
	)
	{
		$this->derivativeContainerFactory = $derivativeContainerFactory;
	}

	public function analyze(string $templateContent): FileAnalyserResult
	{
		$fileName = tempnam(sys_get_temp_dir(), 'twig_template_');
		assert(is_string($fileName));

		file_put_contents($fileName, $templateContent);

		$analyzerResult = $this->getFileAnalyzer()->analyseFile(
			$fileName,
			[],
			$this->getRulesRegistry(),
			$this->getCollectorRegistry(),
			null,
		);

		unlink($fileName);
		return $analyzerResult;
	}

	private function getFileAnalyzer(): FileAnalyser
	{
		if ($this->fileAnalyser === null) {
			$this->fileAnalyser = $this->getContainer()->getByType(FileAnalyser::class);
		}

		return $this->fileAnalyser;
	}

	private function getRulesRegistry(): RulesRegistry
	{
		if ($this->rulesRegistry === null) {
			$rules = $this->getContainer()->getServicesByTag(RulesRegistryFactory::RULE_TAG);

			$this->rulesRegistry = new RulesRegistry($rules);
		}

		return $this->rulesRegistry;
	}

	private function getContainer(): Container
	{
		if ($this->container === null) {
			$this->container = $this->derivativeContainerFactory->create([
				__DIR__ . '/../../config/php-parser.neon',
			]);
		}

		return $this->container;
	}

	private function getCollectorRegistry(): CollectorsRegistry
	{
		if ($this->collectorRegistry === null) {
			$collectors = $this->getContainer()->getServicesByTag(CollectorsRegistryFactory::COLLECTOR_TAG);

			$this->collectorRegistry = new CollectorsRegistry($collectors);
		}

		return $this->collectorRegistry;
	}

}
