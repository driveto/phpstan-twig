<?php declare(strict_types = 1);

namespace Driveto\PhpstanTwig\Twig\NodeVisitor;

use Exception;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class ReplaceTwigGetAttribute extends NodeVisitorAbstract
{

	public function enterNode(Node $node)
	{
		if ($node instanceof Node\Expr\FuncCall) {
			if ($node->name instanceof Node\Name
				&& $node->name->toString() === 'twig_get_attribute'
				&& isset($node->args[2])
				&& $node->args[2] instanceof Node\Arg
				&& isset($node->args[3])
				&& $node->args[3] instanceof Node\Arg
				&& isset($node->args[4])
				&& $node->args[4] instanceof Node\Arg
				&& isset($node->args[5])
				&& $node->args[5] instanceof Node\Arg
				&& $node->args[5]->value instanceof Node\Scalar\String_
			) {
				$contextValue = $node->args[2]->value;

				switch ($node->args[5]->value->value) {
					case 'method':
						if (($node->args[3]->value instanceof Node\Scalar\String_) === false) {
							return null;
						}

						$method = $node->args[3]->value->value;
						$args = [];

						if ($node->args[4]->value instanceof Node\Expr\Array_) {
							foreach ($node->args[4]->value->items as $item) {
								if ($item === null) {
									continue;
								}

								$args[] = new Node\Arg($item->value);
							}
						}

						if ($contextValue instanceof Node\Expr\FuncCall
							&& $contextValue->name instanceof Node\Name
							&& $contextValue->name->toString() === 'twig_get_attribute'
						) {
							$fooNode = $this->enterNode($contextValue);
							if ($fooNode instanceof Node\Expr) {
								return new Node\Expr\MethodCall($fooNode, $method, $args);
							}
						} elseif (
							$contextValue instanceof Node\Expr\MethodCall
							|| $contextValue instanceof Node\Expr\ArrayDimFetch
						) {
							return new Node\Expr\MethodCall($contextValue, $method, $args);
						}

						throw new Exception();
					case 'any':
						$value = $node->args[3]->value;

						if (
							$contextValue instanceof Node\Expr\FuncCall
							&& $contextValue->name instanceof Node\Name
							&& $contextValue->name->toString() === 'twig_get_attribute'
						) {
							$newNode = $this->enterNode($contextValue);
							if ($newNode instanceof Node\Expr) {
								return new Node\Expr\ArrayDimFetch($newNode, $value);
							}

						} elseif ($contextValue instanceof Node\Expr\ArrayDimFetch) {
							return new Node\Expr\ArrayDimFetch($contextValue, $value);
						}
						throw new Exception();
					default:
						throw new Exception();
				}
			}

			return null;
		}
		return null;
	}

}
