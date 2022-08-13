<?php declare(strict_types = 1);

namespace Driveto\PhpstanTwig\Twig\NodeVisitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class RemoveAlwaysTrueConditionOnConstantString extends NodeVisitorAbstract
{

	public function enterNode(Node $node)
	{
		if ($node instanceof Node\Expr\Ternary
			&& $node->cond instanceof Node\Expr\BinaryOp\Identical
			&& $node->cond->left instanceof Node\Scalar\String_
			&& $node->cond->right instanceof Node\Expr\Assign
			&& $node->cond->right->var instanceof Node\Expr\Variable
			&& $node->cond->right->var->name === 'tmp'
			&& $node->cond->right->expr instanceof Node\Scalar\String_
			&& $node->if instanceof Node\Scalar\String_
			&& $node->if->value === ''
			&& $node->else instanceof Node\Expr\New_
			&& $node->else->class instanceof Node\Name
			&& $node->else->class->toString() === 'Markup'
			&& isset($node->else->args[0])
			&& $node->else->args[0] instanceof Node\Arg
		) {
			$node->else->args[0]->value = $node->cond->right->expr;
			return $node->else;
		}

		return null;
	}

}
