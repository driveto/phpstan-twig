<?php declare(strict_types = 1);

namespace Driveto\PhpstanTwig\Twig\NodeVisitor;

use PhpParser\Builder\Property;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use function array_unshift;
use function count;
use function sprintf;

class AddTypesToTwigExtensions extends NodeVisitorAbstract
{

	/** @var array<string, int> */
	private array $extensionClasses = [];

	public function enterNode(Node $node)
	{
		if ($node instanceof Node\Expr\ArrayDimFetch
			&& $node->var instanceof Node\Expr\PropertyFetch
			&& $node->var->var instanceof Node\Expr\Variable
			&& $node->var->var->name === 'this'
			&& $node->var->name instanceof Node\Identifier
			&& $node->var->name->name === 'extensions'
			&& $node->dim instanceof Node\Scalar\String_
		) {
			$className = $node->dim->value;
			$index = $this->extensionClasses[$className] ?? null;
			if ($index === null) {
				$index = $this->extensionClasses[$className] = count($this->extensionClasses);
			}

			return new Node\Expr\PropertyFetch(
				new Node\Expr\Variable('this'),
				new Node\Identifier(sprintf('extension%d', $index)),
			);
		}

		return null;
	}

	public function leaveNode(Node $node)
	{
		if ($node instanceof Node\Stmt\Class_) {
			if (count($this->extensionClasses) === 0) {
				return null;
			}
			$newProperties = [];
			foreach ($this->extensionClasses as $className => $index) {
				$newProperties[] = (new Property(sprintf('extension%d', $index)))
					->makePrivate()
					->setType($className)
					->getNode();
			}
			array_unshift($node->stmts, ...$newProperties);

			return $node;
		}

		return null;
	}

}
