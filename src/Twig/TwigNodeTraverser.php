<?php declare(strict_types = 1);

namespace Driveto\PhpstanTwig\Twig;

use Driveto\PhpstanTwig\Twig\NodeVisitor\AddPhpDocsNodeVisitor;
use Driveto\PhpstanTwig\Twig\NodeVisitor\AddTypesToTwigExtensions;
use Driveto\PhpstanTwig\Twig\NodeVisitor\RefactorTwigForLoop;
use Driveto\PhpstanTwig\Twig\NodeVisitor\RemoveDefaultNullCoalesce;
use Driveto\PhpstanTwig\Twig\NodeVisitor\RemoveTwigEscapeFilter;
use Driveto\PhpstanTwig\Twig\NodeVisitor\ReplaceTwigArrayAccess;
use Driveto\PhpstanTwig\Twig\NodeVisitor\ReplaceTwigGetAttribute;
use Driveto\PhpstanTwig\Twig\NodeVisitor\SplitMainContextAndBlocksVisitor;
use PhpParser\NodeTraverser;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use function assert;

class TwigNodeTraverser
{

	private Parser $parser;

	public function __construct(ParserFactory $parserFactory)
	{
		$this->parser = $parserFactory->create(ParserFactory::PREFER_PHP7);
	}

	/** @param array<int|string, string> $contextTypes */
	public function traverse(
		string $templateName,
		bool $renderMainContent,
		string $compiledTemplate,
		array $contextTypes,
	): string
	{
		$ast = $this->parser->parse($compiledTemplate);
		assert($ast !== null);

		$nodeTraverser = new NodeTraverser();
		$nodeTraverser->addVisitor(new SplitMainContextAndBlocksVisitor($renderMainContent, $templateName));
		$cleanAst = $nodeTraverser->traverse($ast);

		$nodeTraverser = new NodeTraverser();
		$nodeTraverser->addVisitor(new ReplaceTwigArrayAccess());
		$cleanAst = $nodeTraverser->traverse($ast);

		$nodeTraverser = new NodeTraverser();
		$nodeTraverser->addVisitor(new RemoveDefaultNullCoalesce());
		$cleanAst = $nodeTraverser->traverse($cleanAst);

		$nodeTraverser = new NodeTraverser();
		$nodeTraverser->addVisitor(new AddPhpDocsNodeVisitor($contextTypes));
		$nodeTraverser->addVisitor(new RemoveTwigEscapeFilter());
		$nodeTraverser->addVisitor(new ReplaceTwigGetAttribute());
		$cleanAst = $nodeTraverser->traverse($cleanAst);

		$nodeTraverser = new NodeTraverser();
		$nodeTraverser->addVisitor(new RefactorTwigForLoop());
		$cleanAst = $nodeTraverser->traverse($cleanAst);

		$nodeTraverser = new NodeTraverser();
		$nodeTraverser->addVisitor(new AddTypesToTwigExtensions());
		$cleanAst = $nodeTraverser->traverse($cleanAst);

		$prettyPrinter = new Standard();
		return $prettyPrinter->prettyPrintFile($cleanAst) . "\n";
	}

}
