<?php declare(strict_types = 1);

namespace Driveto\PhpstanTwig\Twig\NodeVisitor;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use function array_unshift;
use function sprintf;

class RefactorTwigForLoop extends NodeVisitorAbstract
{

	private int $depth;

	public function __construct()
	{
		$this->depth = 0;
	}

	public function leaveNode(Node $node)
	{
		//remove $context['_parent'] = $context;
		if (
			$node instanceof Node\Stmt\Expression
			&& $node->expr instanceof Node\Expr\Assign
			&& $node->expr->var instanceof Node\Expr\ArrayDimFetch
			&& $node->expr->var->var instanceof Node\Expr\Variable
			&& $node->expr->var->var->name === 'context'
			&& $node->expr->var->dim instanceof Node\Scalar\String_
			&& $node->expr->var->dim->value === '_parent'
			&& $node->expr->expr instanceof Node\Expr\Variable
			&& $node->expr->expr->name === 'context'
		) {
			return NodeTraverser::REMOVE_NODE;
		}

		if ($node instanceof Node\Stmt\Foreach_) {
			if ($node->keyVar instanceof Node\Expr\ArrayDimFetch
				&& $node->keyVar->var instanceof Node\Expr\Variable
				&& $node->keyVar->var->name === 'context'
				&& $node->keyVar->dim instanceof Node\Scalar\String_
				&& $node->keyVar->dim->value === '_key'
			) {
				$this->depth++;

				$oldKeyVar = clone $node->keyVar;
				$node->keyVar = new Node\Expr\Variable($this->getCurrentContextName());
				array_unshift($node->stmts, new Node\Stmt\Expression(new Node\Expr\Assign(
					$oldKeyVar,
					clone $node->keyVar,
				)));
			}

			return $node;
		}

		//remove $_parent = $context['_parent'];
		if (
			$node instanceof Node\Stmt\Expression
			&& $node->expr instanceof Node\Expr\Assign
			&& $node->expr->var instanceof Node\Expr\Variable
			&& $node->expr->var->name === '_parent'
			&& $node->expr->expr instanceof Node\Expr\ArrayDimFetch
			&& $node->expr->expr->var instanceof Node\Expr\Variable
			&& $node->expr->expr->var->name === 'context'
			&& $node->expr->expr->dim instanceof Node\Scalar\String_
			&& $node->expr->expr->dim->value === '_parent'
		) {
			return NodeTraverser::REMOVE_NODE;
		}

		if ($node instanceof Node\Stmt\Unset_) {
			return NodeTraverser::REMOVE_NODE;
		}

		if ($node instanceof Node\Stmt\Expression
			&& $node->expr instanceof Node\Expr\Assign
			&& $node->expr->var instanceof Node\Expr\Variable
			&& $node->expr->var->name === 'context'
			&& $node->expr->expr instanceof Node\Expr\BinaryOp
			&& $node->expr->expr->left instanceof Node\Expr\FuncCall
			&& $node->expr->expr->left->name instanceof Node\Name
			&& $node->expr->expr->left->name->toString() === 'array_intersect_key'
		) {
			return NodeTraverser::REMOVE_NODE;
		}

		return null;
	}

	private function getCurrentContextName(): string
	{
		return sprintf('contextForeach%dKey', $this->depth);
	}

}
