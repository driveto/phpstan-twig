<?php declare(strict_types = 1);

namespace Driveto\PhpstanTwig\Twig\NodeVisitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class RemoveTwigEscapeFilter extends NodeVisitorAbstract
{

	public function enterNode(Node $node)
	{
		if ($node instanceof Node\Expr\FuncCall
			&& $node->name instanceof Node\Name
		) {
			$funcName = $node->name->toString();
			if ($funcName === 'twig_escape_filter'
				&& isset($node->args[1])
				&& $node->args[1] instanceof Node\Arg
			) {
				return $node->args[1]->value;
			} elseif (
				$funcName === 'twig_ensure_traversable'
				&& isset($node->args[0])
				&& $node->args[0] instanceof Node\Arg
			) {
				return $node->args[0]->value;
			} elseif (
				$funcName === 'twig_to_array'
				&& isset($node->args[0])
				&& $node->args[0] instanceof Node\Arg
			) {
				return $node->args[0]->value;
			}

			return $node;
		}
		return $node;
	}

}
