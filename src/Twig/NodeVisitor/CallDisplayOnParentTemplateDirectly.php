<?php declare(strict_types = 1);

namespace Driveto\PhpstanTwig\Twig\NodeVisitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class CallDisplayOnParentTemplateDirectly extends NodeVisitorAbstract
{

	public function enterNode(Node $node)
	{
		if ($node instanceof Node\Expr\Assign
			&& $node->var instanceof Node\Expr\PropertyFetch
			&& $node->var->var instanceof Node\Expr\Variable
			&& $node->var->var->name === 'this'
			&& $node->var->name instanceof Node\Identifier
			&& $node->var->name->name === 'parent'
			&& $node->expr instanceof Node\Expr\MethodCall
			&& $node->expr->var instanceof Node\Expr\Variable
			&& $node->expr->var->name === 'this'
			&& $node->expr->name instanceof Node\Identifier
			&& $node->expr->name->name === 'loadTemplate'
		) {
			return new Node\Expr\MethodCall(
				$node->expr,
				'display',
				[
					new Node\Arg(new Node\Expr\Variable('context')),
				]
			);
		}

		return null;
	}

}
