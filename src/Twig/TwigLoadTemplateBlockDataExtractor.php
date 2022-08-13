<?php declare(strict_types = 1);

namespace Driveto\PhpstanTwig\Twig;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Type;

class TwigLoadTemplateBlockDataExtractor extends DataExtractor
{

	public function isNodeSupported(Node $node, Scope $scope): bool
	{
		if ($node instanceof MethodCall
			&& $node->name instanceof Node\Identifier
			&& $node->name->toString() === 'loadTemplateBlock'
		) {
			return true;
		}

		return false;
	}

	/** @return array<int|string, Type> */
	public function extract(Node $node, Scope $scope): array
	{
		$parentContextTypes = [];

		$variableType = $scope->getVariableType('context');
		if ($variableType instanceof ConstantArrayType) {
			foreach ($variableType->getKeyTypes() as $keyType) {
				$name = $keyType->getValue();
				$value = $variableType->getOffsetValueType($keyType);

				$parentContextTypes[$name] = $value;
			}
		}

		return $parentContextTypes;
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
