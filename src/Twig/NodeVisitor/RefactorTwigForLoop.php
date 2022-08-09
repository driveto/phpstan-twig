<?php declare(strict_types = 1);

namespace Driveto\PhpstanTwig\Twig\NodeVisitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use function sprintf;

class RefactorTwigForLoop extends NodeVisitorAbstract
{

	private int $depth = 0;

	/** @var array<int, string> */
	private array $loopValuesByDepth = [];

	public function enterNode(Node $node)
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
			$this->depth++;

			return new Node\Stmt\Expression(
				new Node\Expr\Assign(
					new Node\Expr\Variable($this->getCurrentContextName($this->depth)),
					new Node\Expr\Array_([], ['kind' => Node\Expr\Array_::KIND_SHORT]),
				)
			);
		}

		if (
			$node instanceof Node\Stmt\Expression
			&& $node->expr instanceof Node\Expr\Assign
			&& $node->expr->var instanceof Node\Expr\ArrayDimFetch
			&& $node->expr->var->var instanceof Node\Expr\Variable
			&& $node->expr->var->var->name === 'context'
			&& $node->expr->var->dim instanceof Node\Scalar\String_
			&& $node->expr->var->dim->value === '_seq'
		) {
			$node->expr->var->var->name = $this->getCurrentContextName($this->depth);

			return $node;
		}

		if ($node instanceof Node\Stmt\Foreach_) {
			if ($node->expr instanceof Node\Expr\ArrayDimFetch
				&& $node->expr->var instanceof Node\Expr\Variable
				&& $node->expr->var->name === 'context'
				&& $node->expr->dim instanceof Node\Scalar\String_
				&& $node->expr->dim->value === '_seq'
			) {
				$node->expr->var->name = $this->getCurrentContextName($this->depth);
			}

			if ($node->keyVar instanceof Node\Expr\ArrayDimFetch
			) {
				$node->keyVar = new Node\Expr\Variable($this->getCurrentContextKeyName());
			}

			if ($node->valueVar instanceof Node\Expr\ArrayDimFetch
				&& $node->valueVar->var instanceof Node\Expr\Variable
				&& $node->valueVar->var->name === 'context'
				&& $node->valueVar->dim instanceof Node\Scalar\String_
			) {
				$this->loopValuesByDepth[$this->depth] = $node->valueVar->dim->value;

				$node->valueVar->var->name = $this->getCurrentContextName($this->depth);
			}

			return $node;
		}

		if ($this->depth > 0
			&& $node instanceof Node\Expr\ArrayDimFetch
			&& $node->var instanceof Node\Expr\Variable
			&& $node->var->name === 'context'
			&& $node->dim instanceof Node\Scalar\String_
		) {
			$variableKey = $node->dim->value;
			if ($variableKey === '_key') {
				$node->var->name = $this->getCurrentContextKeyName();
			} else {
				foreach ($this->loopValuesByDepth as $depth => $value) {
					if ($node->dim->value !== $value) {
						continue;
					}

					$node->var->name = $this->getCurrentContextName($depth);
				}
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
			$node = new Node\Stmt\Echo_([new Node\Scalar\String_('not important')]);
			return $node;
		}

		if ($node instanceof Node\Stmt\Unset_) {
			$node = new Node\Stmt\Echo_([new Node\Scalar\String_('not important')]);
			return $node;
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
			$node->expr = new Node\Expr\FuncCall(
				new Node\Name('unset'),
				[
					new Node\Arg(new Node\Expr\Variable($this->getCurrentContextName($this->depth))),
					new Node\Arg(new Node\Expr\Variable($this->getCurrentContextKeyName())),
				],
			);

			$this->depth--;
			return $node;
		}

		return null;
	}

	private function getCurrentContextName(int $depth): string
	{
		return sprintf('contextForeach%d', $depth);
	}

	private function getCurrentContextKeyName(): string
	{
		return sprintf('contextForeach%sKey', $this->depth);
	}

}
