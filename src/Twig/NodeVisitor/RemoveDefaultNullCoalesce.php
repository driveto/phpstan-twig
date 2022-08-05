<?php declare(strict_types = 1);

namespace Driveto\PhpstanTwig\Twig\NodeVisitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class RemoveDefaultNullCoalesce extends NodeVisitorAbstract
{

	public function enterNode(Node $node)
	{
		if ($node instanceof Node\Expr\BinaryOp\Coalesce
			&& $node->right instanceof Node\Expr\ConstFetch
			&& $node->right->name->toString() === 'null'
		) {
			return $node->left;
		}
		return null;
	}

}
