<?php declare(strict_types = 1);

namespace Driveto\PhpstanTwig\Rule;

use Driveto\PhpstanTwig\Twig\TwigAnalyzer;
use Driveto\PhpstanTwig\Twig\TwigLoadTemplateDataExtractor;
use Driveto\PhpstanTwig\Twig\TwigNodeTraverser;
use Driveto\PhpstanTwig\Twig\TwigRenderMethodDataExtractor;
use Driveto\PhpstanTwig\Twig\TwigToPhpCompiler;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\FileAnalyserResult;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use function array_merge;
use function str_contains;

/** @implements Rule<MethodCall> */
final class TwigCheckRule implements Rule
{

	private TwigAnalyzer $twigAnalyzer;

	private TwigRenderMethodDataExtractor $twigRenderMethodDataExtractor;

	private TwigLoadTemplateDataExtractor $twigLoadTemplateDataExtractor;

	private TwigToPhpCompiler $twigToPhpCompiler;

	private TwigNodeTraverser $twigNodeTraverser;

	public function __construct(
		TwigAnalyzer $twigAnalyzer,
		TwigRenderMethodDataExtractor $twigRenderMethodDataExtractor,
		TwigLoadTemplateDataExtractor $loadTemplateDataExtractor,
		TwigToPhpCompiler $twigToPhpCompiler,
		TwigNodeTraverser $twigNodeTraverser
	)
	{
		$this->twigAnalyzer = $twigAnalyzer;
		$this->twigRenderMethodDataExtractor = $twigRenderMethodDataExtractor;
		$this->twigLoadTemplateDataExtractor = $loadTemplateDataExtractor;
		$this->twigToPhpCompiler = $twigToPhpCompiler;
		$this->twigNodeTraverser = $twigNodeTraverser;
	}

	public function getNodeType(): string
	{
		return MethodCall::class;
	}

	public function processNode(Node $node, Scope $scope): array
	{
		if ($this->twigRenderMethodDataExtractor->isNodeSupported($node, $scope)) {
			$templateName = $this->twigRenderMethodDataExtractor->extractTemplateName($node, $scope);
			$localContextTypes = $this->twigRenderMethodDataExtractor->extract($node, $scope);
		} elseif ($this->twigLoadTemplateDataExtractor->isNodeSupported($node, $scope)) {
			$templateName = $this->twigLoadTemplateDataExtractor->extractTemplateName($node, $scope);
			$localContextTypes = $this->twigLoadTemplateDataExtractor->extract($node, $scope);
		} else {
			return [];
		}

		if ($templateName === null) {
			return [];
		}

		$compiledTemplate = $this->twigToPhpCompiler->compile($templateName);

		$templateWithTypes = $this->twigNodeTraverser->traverse(
			$compiledTemplate,
			array_merge($this->twigToPhpCompiler->getGlobalTypes(), $localContextTypes),
		);

		$analyserResult = $this->twigAnalyzer->analyze($templateWithTypes);
		return $this->processResult($analyserResult, $templateName);
	}

	/** @return RuleError[] */
	private function processResult(FileAnalyserResult $analyserResult, string $templateName): array
	{
		$errors = [];
		foreach ($analyserResult->getErrors() as $error) {
			if (str_contains($error->getMessage(), '__TwigTemplate_')
				|| str_contains($error->getMessage(), 'Cannot unset offset')
			) {
				continue;
			}
			$errors[] = RuleErrorBuilder::message($error->getMessage())
				->file($templateName)
				->build();
		}

		return $errors;
	}

}