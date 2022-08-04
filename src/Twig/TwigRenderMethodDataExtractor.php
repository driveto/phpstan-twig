<?php declare(strict_types = 1);

namespace Driveto\PhpstanTwig\Twig;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Twig\Environment;
use function assert;
use function is_string;

class TwigRenderMethodDataExtractor extends DataExtractor
{

	public function isNodeSupported(Node $node, Scope $scope): bool
	{
		if ($node instanceof MethodCall
			&& $node->name instanceof Node\Identifier
			&& $node->name->toString() === 'render'
		) {
			if ($node->var instanceof Node\Expr\Variable
				&& is_string($node->var->name)
				&& $node->var->name === 'this'
				&& $scope->getClassReflection() !== null
				&& $scope->getClassReflection()->isSubclassOf(AbstractController::class)
			) {
				return true;
			} elseif ($node->var instanceof Node\Expr\PropertyFetch) {
				$propertyType = $scope->getType($node->var);
				if ($propertyType instanceof ObjectType) {
					$propertyTypeReflection = $propertyType->getClassReflection();
					assert($propertyTypeReflection !== null);

					return $propertyTypeReflection->is(Environment::class)
						|| $propertyTypeReflection->isSubclassOf(Environment::class);
				}
			}
		}

		return false;
	}

	/** @return array<int|string, string> */
	public function extract(Node $node, Scope $scope): array
	{
		$templateVariablesArgument = $node->args[1]?->value ?? null;
		if ($templateVariablesArgument === null) {
			return [];
		}

		$localContextTypes = [];
		if ($templateVariablesArgument instanceof Node\Expr\Variable
			&& is_string($templateVariablesArgument->name)
		) {
			$variableType = $scope->getVariableType($templateVariablesArgument->name);
			if ($variableType instanceof ConstantArrayType) {
				foreach ($variableType->getKeyTypes() as $keyType) {
					$name = $keyType->getValue();
					$value = $variableType->getOffsetValueType($keyType);

					$localContextTypes[$name] = $this->getTextValueType($value);
				}
			}
		} elseif ($templateVariablesArgument instanceof Node\Expr\Array_) {
			foreach ($templateVariablesArgument->items as $arrayItem) {
				if (!($arrayItem instanceof Node\Expr\ArrayItem)
					|| !($arrayItem->key instanceof Node\Scalar\String_)
				) {
					continue;
				}

				$newVariableName = $arrayItem->key->value;
				$newVariableType = $scope->getType($arrayItem->value);

				$localContextTypes[$newVariableName] = $this->getTextValueType($newVariableType);
			}
		}

		return $localContextTypes;
	}

	public function extractTemplateName(Node $node, Scope $scope): ?string
	{
		if ($node instanceof MethodCall
			&& isset($node->args[0])
			&& $node->args[0] instanceof Node\Arg
		) {
			$arg = $node->args[0]->value;
			if ($arg instanceof Node\Scalar\String_) {
				return $arg->value;
			} elseif ($arg instanceof Node\Expr\Variable && is_string($arg->name)) {
				$templateVariableType = $scope->getVariableType($arg->name);
				if ($templateVariableType instanceof ConstantStringType) {
					return $templateVariableType->getValue();
				} elseif ($templateVariableType instanceof StringType) {
					return null;
				}
			}
		}

		return null;
	}

}
