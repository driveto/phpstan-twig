<?php declare(strict_types = 1);

namespace Driveto\PhpstanTwig\Twig;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Type\Constant\ConstantArrayType;

class TwigLoadTemplateDataExtractor extends DataExtractor
{

	public function isNodeSupported(Node $node, Scope $scope): bool
	{
		if ($node instanceof MethodCall
			&& $node->name instanceof Node\Identifier
			&& $node->name->toString() === 'loadTemplate'
		) {
			return true;
		}

		return false;
	}

	/** @return array<int|string, string> */
	public function extract(Node $node, Scope $scope): array
	{
		$localContextTypes = [];
		$parentContextTypes = [];

		$variableType = $scope->getVariableType('context');
		if ($variableType instanceof ConstantArrayType) {
			foreach ($variableType->getKeyTypes() as $keyType) {
				$name = $keyType->getValue();
				$value = $variableType->getOffsetValueType($keyType);

				$parentContextTypes[$name] = $this->getTextValueType($value);
			}
		}

		$nextNode = $node->getAttributes()['next'];
		if ($nextNode instanceof Node\Identifier && $nextNode->toString() === 'display') {
			$nextNextNode = $nextNode->getAttributes()['next'];
			if ($nextNextNode instanceof Node\Arg) {
				$nextNextNodeValue = $nextNextNode->value;
				if ($nextNextNodeValue instanceof Node\Expr\Array_) {
					foreach ($nextNextNodeValue->items as $includeVariable) {
						if (!($includeVariable instanceof Node\Expr\ArrayItem)
							|| !($includeVariable->key instanceof Node\Scalar\String_)
						) {
							continue;
						}

						$newVariableName = $includeVariable->key->value;
						$newVariableType = $scope->getType($includeVariable->value);

						$localContextTypes[$newVariableName] = $this->getTextValueType($newVariableType);
					}
				} elseif ($nextNextNodeValue instanceof Node\Expr\FuncCall && $nextNextNodeValue->name instanceof Node\Name\FullyQualified) {
					$functionName = $nextNextNodeValue->name->toString();
					if ($functionName === 'twig_to_array') {
						if ($nextNextNodeValue->args[0] instanceof Node\Arg
							&& $nextNextNodeValue->args[0]->value instanceof Node\Expr\Array_
						) {
							foreach ($nextNextNodeValue->args[0]->value->items as $newContextVariable) {
								if (!($newContextVariable instanceof Node\Expr\ArrayItem)
									|| !($newContextVariable->key instanceof Node\Scalar\String_)
									|| !($newContextVariable->value instanceof Node\Expr\BinaryOp\Coalesce)
									|| !($newContextVariable->value->left instanceof Node\Expr\ArrayDimFetch)
									|| !($newContextVariable->value->left->var instanceof Node\Expr\Variable)
									|| $newContextVariable->value->left->var->name !== 'context'
									|| !($newContextVariable->value->left->dim instanceof Node\Scalar\String_)
								) {
									continue;
								}

								$variableType = $scope->getType($newContextVariable->value->left->var);
								$variableName = $newContextVariable->key->value;

								$localContextTypes[$variableName] = $this->getTextValueType($variableType);
							}
						}
					} elseif ($functionName === 'twig_array_merge') {
						$localContextTypes = $parentContextTypes;
						if (
							isset($nextNextNodeValue->args[0])
							&& $nextNextNodeValue->args[0] instanceof Node\Arg
							&& isset($nextNextNodeValue->args[1])
							&& $nextNextNodeValue->args[1] instanceof Node\Arg
							&& $nextNextNodeValue->args[0]->value instanceof Node\Expr\Variable
							&& $nextNextNodeValue->args[0]->value->name === 'context'
							&& $nextNextNodeValue->args[1]->value instanceof Node\Expr\Array_
						) {
							foreach ($nextNextNodeValue->args[1]->value->items as $newContextVariable) {
								if (!($newContextVariable instanceof Node\Expr\ArrayItem)
									|| !($newContextVariable->key instanceof Node\Scalar\String_)
								) {
									continue;
								}

								$variableType = $scope->getType($newContextVariable->value);
								$variableName = $newContextVariable->key->value;

								$localContextTypes[$variableName] = $this->getTextValueType($variableType);
							}
						}
					}
				} elseif ($nextNextNodeValue instanceof Node\Expr\Variable && $nextNextNodeValue->name === 'context') {
					$localContextTypes = $parentContextTypes;
				}
			}
		}

		return $localContextTypes;
	}

	public function extractTemplateName(Node $node, Scope $scope): ?string
	{
		if (isset($node->args[0])
			&& $node->args[0] instanceof Node\Arg
			&& $node->args[0]->value instanceof Node\Scalar\String_
		) {
			return $node->args[0]->value->value;
		}

		return null;
	}

}
