<?php declare(strict_types = 1);

namespace Driveto\PhpstanTwig\Rule;

use Driveto\PhpstanTwig\Twig\TwigAnalyzer;
use Driveto\PhpstanTwig\Twig\TwigLineNumberExtractor;
use Driveto\PhpstanTwig\Twig\TwigLoadTemplateBlockDataExtractor;
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
use Twig\Error\SyntaxError;
use function array_merge;
use function sprintf;
use function str_contains;
use function str_ends_with;

/** @implements Rule<MethodCall> */
final class TwigCheckRule implements Rule
{

	private TwigAnalyzer $twigAnalyzer;

	private TwigRenderMethodDataExtractor $twigRenderMethodDataExtractor;

	private TwigLoadTemplateDataExtractor $twigLoadTemplateDataExtractor;

	private TwigLoadTemplateBlockDataExtractor $twigLoadTemplateBlockDataExtractor;

	private TwigToPhpCompiler $twigToPhpCompiler;

	private TwigNodeTraverser $twigNodeTraverser;

	public function __construct(
		TwigAnalyzer $twigAnalyzer,
		TwigRenderMethodDataExtractor $twigRenderMethodDataExtractor,
		TwigLoadTemplateDataExtractor $loadTemplateDataExtractor,
		TwigLoadTemplateBlockDataExtractor $twigLoadTemplateBlockDataExtractor,
		TwigToPhpCompiler $twigToPhpCompiler,
		TwigNodeTraverser $twigNodeTraverser
	)
	{
		$this->twigAnalyzer = $twigAnalyzer;
		$this->twigRenderMethodDataExtractor = $twigRenderMethodDataExtractor;
		$this->twigLoadTemplateDataExtractor = $loadTemplateDataExtractor;
		$this->twigLoadTemplateBlockDataExtractor = $twigLoadTemplateBlockDataExtractor;
		$this->twigToPhpCompiler = $twigToPhpCompiler;
		$this->twigNodeTraverser = $twigNodeTraverser;
	}

	public function getNodeType(): string
	{
		return MethodCall::class;
	}

	public function processNode(Node $node, Scope $scope): array
	{
		$renderMainContent = true;
		$callerName = null;
		if ($this->twigRenderMethodDataExtractor->isNodeSupported($node, $scope)) {
			$templateName = $this->twigRenderMethodDataExtractor->extractTemplateName($node, $scope);
			$localContextTypes = $this->twigRenderMethodDataExtractor->extract($node, $scope);
			$callerName = $scope->getFile();
		} elseif ($this->twigLoadTemplateDataExtractor->isNodeSupported($node, $scope)) {
			$templateName = $this->twigLoadTemplateDataExtractor->extractTemplateName($node, $scope);
			$localContextTypes = $this->twigLoadTemplateDataExtractor->extract($node, $scope);
		} elseif ($this->twigLoadTemplateBlockDataExtractor->isNodeSupported($node, $scope)) {
			$templateName = $this->twigLoadTemplateBlockDataExtractor->extractTemplateName($node, $scope);
			$localContextTypes = $this->twigLoadTemplateBlockDataExtractor->extract($node, $scope);
			$renderMainContent = false;
		} else {
			return [];
		}

		if ($templateName === null) {
			return [];
		}

		try {
			$compiledTemplate = $this->twigToPhpCompiler->compile($templateName);
		} catch (SyntaxError $e) {
			return [
				RuleErrorBuilder::message(sprintf(
					'Failed to compile template. Exception: %s',
					$e->getMessage(),
				))
				->file($templateName)
				->build(),
			];
		}

		$templateWithTypes = $this->twigNodeTraverser->traverse(
			$templateName,
			$renderMainContent,
			$compiledTemplate,
			array_merge($this->twigToPhpCompiler->getGlobalTypes(), $localContextTypes),
		);

		$lineNumberExtractor = new TwigLineNumberExtractor($templateWithTypes);

		$analyserResult = $this->twigAnalyzer->analyze($templateWithTypes);
		return $this->processResult($analyserResult, $templateName, $lineNumberExtractor, $callerName);
	}

	/** @return RuleError[] */
	private function processResult(
		FileAnalyserResult $analyserResult,
		string $templateName,
		TwigLineNumberExtractor $lineNumberExtractor,
		?string $callerName,
	): array
	{
		$errors = [];
		foreach ($analyserResult->getErrors() as $error) {
			if (str_contains($error->getMessage(), '__TwigTemplate_')
				|| str_contains($error->getMessage(), 'Cannot unset offset')
			) {
				continue;
			}

			$newError = RuleErrorBuilder::message($error->getMessage());
			if ($error->getLine() !== null) {
				$newError->line($error->getLine());
			}

			$newErrorFile = $templateName;
			if (!str_ends_with($error->getFile(), '.twig')) {
				if ($error->getLine() !== null) {
					$newError->line($lineNumberExtractor->getTwigLineNumber($error->getLine()));
				}
			} elseif (!str_ends_with($error->getFile(), $templateName)) {
				$newErrorFile = sprintf('%s -> %s', $error->getFile(), $templateName);
			}

			if ($callerName !== null) {
				$newErrorFile = sprintf('%s -> %s', $newErrorFile, $callerName);
			}

			$newError->file($newErrorFile);

			$errors[] = $newError->build();
		}

		return $errors;
	}

}
