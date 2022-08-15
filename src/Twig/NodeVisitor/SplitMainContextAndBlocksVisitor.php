<?php declare(strict_types = 1);

namespace Driveto\PhpstanTwig\Twig\NodeVisitor;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use function str_starts_with;

class SplitMainContextAndBlocksVisitor extends NodeVisitorAbstract
{

	public function __construct(
		private bool $renderMainContent,
		private string $templateName,
	)
	{
	}

	public function leaveNode(Node $node)
	{
		if ($this->renderMainContent) {
			if ($this->isRenderBlockMethod($node)) {
				return NodeTraverser::REMOVE_NODE;
			} elseif ($this->isRenderMainContentMethod($node)) {
				return $this->appendLoadTemplateBlockMethodCall($node);
			}
		} elseif ($this->isRenderMainContentMethod($node)) {
			return NodeTraverser::REMOVE_NODE;
		}

		return null;
	}

	private function isRenderBlockMethod(Node $node): bool
	{
		return $node instanceof Node\Stmt\ClassMethod
			&& str_starts_with($node->name->name, 'block_');
	}

	private function isRenderMainContentMethod(Node $node): bool
	{
		return $node instanceof Node\Stmt\ClassMethod
			&& $node->name->name === 'doDisplay';
	}

	private function appendLoadTemplateBlockMethodCall(Node $node): Node
	{
		if ($node instanceof Node\Stmt\ClassMethod) {
			$node->stmts[] = new Node\Stmt\Expression(
				new Node\Expr\MethodCall(
					new Node\Expr\Variable('this'),
					name: 'loadTemplateBlock',
					args: [
						new Node\Arg(new Node\Scalar\String_($this->templateName)),
					],
				),
			);
		}

		return $node;
	}

}
