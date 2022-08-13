<?php declare(strict_types = 1);

namespace Driveto\PhpstanTwig\Twig\NodeVisitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class RenameTwigTemplateClass extends NodeVisitorAbstract
{

	public function __construct(private string $templateHash)
	{
	}

	public function enterNode(Node $node)
	{
		if ($node instanceof Node\Stmt\Class_ && $node->name instanceof Node\Identifier) {
			$node->name->name = '__TwigTemplate_' . $this->templateHash;
			return $node;
		}

		return null;
	}

}
