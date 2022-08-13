<?php declare(strict_types = 1);

namespace Driveto\PhpstanTwig\Twig\NodeVisitor;

use Exception;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;

class ReplaceTwigGetAttribute extends NodeVisitorAbstract
{

	/** @param array<int|string, Type> $contextVariables */
	public function __construct(private array $contextVariables)
	{
	}

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

						if ($this->isGetAttributeFuncCall($contextValue)) {
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

						if ($this->isGetAttributeFuncCall($contextValue)) {
							$newNode = $this->enterNode($contextValue);
							if ($newNode instanceof Node\Expr) {
								return new Node\Expr\ArrayDimFetch($newNode, $value);
							}

						} elseif ($contextValue instanceof Node\Expr\ArrayDimFetch) {
							if ($contextValue->dim instanceof Node\Scalar\String_) {
								$contextValueName = $contextValue->dim->value;
								$contextValueType = $this->contextVariables[$contextValueName] ?? null;
								if ($contextValueType instanceof ObjectType
									&& $value instanceof Node\Scalar\String_
								) {
									return new Node\Expr\PropertyFetch(
										$contextValue,
										$value->value,
									);
								}
							}
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


	private function isGetAttributeFuncCall(Node $node): bool
	{
		return $node instanceof Node\Expr\FuncCall
			&& $node->name instanceof Node\Name
			&& $node->name->toString() === 'twig_get_attribute';
	}

}
