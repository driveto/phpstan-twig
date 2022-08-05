<?php declare(strict_types = 1);

namespace Driveto\PhpstanTwig\Twig\NodeVisitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class ReplaceTwigArrayAccess extends NodeVisitorAbstract
{

	public function enterNode(Node $node)
	{
		if ($node instanceof Node\Expr\Ternary) {
			if (
				$node->cond instanceof Node\Expr\BinaryOp\BooleanOr
				&& $node->cond->left instanceof Node\Expr\BinaryOp\BooleanAnd
				&& $node->cond->left->right instanceof Node\Expr\FuncCall
				&& $node->cond->left->right->name instanceof Node\Name
				&& $node->cond->left->right->name->toString() === 'is_array'
				&& $node->cond->left->left instanceof Node\Expr\Assign
				&& $node->cond->left->left->expr instanceof Node\Expr\BinaryOp\Coalesce
				&& $node->cond->left->left->expr->left instanceof Node\Expr\ArrayDimFetch
				&& $node->cond->left->left->expr->left->var instanceof Node\Expr\Variable
				&& $node->cond->left->left->expr->left->var->name === 'context'
				&& $node->if instanceof Node\Expr\BinaryOp\Coalesce
				&& $node->if->left instanceof Node\Expr\ArrayDimFetch
			) {
				return new Node\Expr\ArrayDimFetch(
					$node->cond->left->left->expr->left,
					$node->if->left->dim,
				);
			}
		}
		return null;
	}

}
