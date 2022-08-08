<?php declare(strict_types = 1);

namespace Driveto\PhpstanTwig\Twig;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use function count;
use function implode;
use function sprintf;

abstract class DataExtractor
{

	abstract public function isNodeSupported(Node $node, Scope $scope): bool;

	abstract public function extractTemplateName(Node $node, Scope $scope): ?string;

	/** @return array<int|string, string> */
	abstract public function extract(Node $node, Scope $scope): array;

	public function getTextValueType(Type $value): string
	{
		if ($value instanceof ObjectType) {
			return $value->getClassName();
		} elseif ($value instanceof BooleanType) {
			return 'bool';
		} elseif ($value instanceof StringType) {
			return 'string';
		} elseif ($value instanceof IntegerType) {
			return 'int';
		} elseif ($value instanceof MixedType) {
			return 'mixed';
		} elseif ($value instanceof ConstantArrayType) {
			$arrayItemTypes = [];
			foreach ($value->getKeyTypes() as $arrayKeyType) {
				$arrayValueType = $value->getOffsetValueType($arrayKeyType);

				$arrayItemTypes[$arrayKeyType->getValue()] = $this->getTextValueType($arrayValueType);
			}
			if (count($arrayItemTypes) > 0) {
				$phpDocContent = 'array{';
				foreach ($arrayItemTypes as $arrayItemName => $arrayItemValue) {
					$phpDocContent .= sprintf("'%s': %s,", $arrayItemName, $arrayItemValue);
				}
				$phpDocContent .= '}';

				return $phpDocContent;
			}
			return 'array';
		} elseif ($value instanceof ArrayType) {
			return sprintf(
				'array<%s, %s>',
				$this->getTextValueType($value->getKeyType()),
				$this->getTextValueType($value->getItemType()),
			);
		} elseif ($value instanceof UnionType) {
			$types = [];
			foreach ($value->getTypes() as $type) {
				$types[] = $this->getTextValueType($type);
			}
			return implode('|', $types);
		} elseif ($value instanceof IntersectionType) {
			$types = [];
			foreach ($value->getTypes() as $type) {
				$types[] = $this->getTextValueType($type);
			}
			return implode('&', $types);
		}

		return 'mixed';
	}

}
