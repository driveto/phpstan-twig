<?php declare(strict_types = 1);

namespace Driveto\PhpstanTwig\Twig;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Type\Type;

abstract class DataExtractor
{

	abstract public function isNodeSupported(Node $node, Scope $scope): bool;

	abstract public function extractTemplateName(Node $node, Scope $scope): ?string;

	/** @return array<int|string, Type> */
	abstract public function extract(Node $node, Scope $scope): array;

}
