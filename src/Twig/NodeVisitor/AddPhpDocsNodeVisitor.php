<?php declare(strict_types = 1);

namespace Driveto\PhpstanTwig\Twig\NodeVisitor;

use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use PHPStan\Type\ConstantType;
use PHPStan\Type\GeneralizePrecision;
use PHPStan\Type\NeverType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;
use function count;
use function sprintf;
use function str_starts_with;

class AddPhpDocsNodeVisitor extends NodeVisitorAbstract
{

	/** @var array<int|string, Type> */
	private array $contextVariables;

	/** @param array<int|string, Type> $contextVariables */
	public function __construct(array $contextVariables)
	{
		$this->contextVariables = $contextVariables;
	}

	public function enterNode(Node $node)
	{
		if ($node instanceof Node\Stmt\ClassMethod) {
			if ($node->name->name === 'doDisplay' || str_starts_with($node->name->name, 'block_')) {
				if (count($this->contextVariables) === 0) {
					$node->setDocComment(new Doc('/** @param array{} $context */'));
				} else {
					$phpDocContent = "/**\n * @param array{";
					foreach ($this->contextVariables as $name => $value) {
						$phpDocContent .= sprintf("\n *     '%s': %s,", $name, $this->getTextValueType($value));
					}
					$phpDocContent .= "\n * } \$context\n */";

					$node->setDocComment(new Doc($phpDocContent));
				}

				return $node;
			}
		}
		return null;
	}


	private function getTextValueType(Type $value): string
	{
		if ($value instanceof NeverType) {
			return 'mixed';
		} elseif ($value instanceof ConstantType) {
			$generalType = $value->generalize(GeneralizePrecision::lessSpecific());
			if ($generalType->equals($value) === false) {
				return $this->getTextValueType($generalType);
			}
		}
		return $value->describe(VerbosityLevel::precise());
	}

}
