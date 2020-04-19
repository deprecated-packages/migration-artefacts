<?php

declare(strict_types=1);

namespace Migrify\MigrationArtefact\SpaghettiToSymfonyController\Rector\NodeFactory;

use Migrify\MigrationArtefact\SpaghettiToSymfonyController\Rector\ValueObject\SymfonyClass;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Cast\String_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;

final class ContentClassMethodFactory extends AbstractControllerActionClassMethodFactory
{
    public function create(array $nodes, string $routePath): ClassMethod
    {
        $nodes = $this->wrapNodesToObContents($nodes);
        $routePath .= '_content';

        return $this->createActionClassMethodWithStmts('content', $nodes, $routePath);
    }

    /**
     * @param Node[] $nodes
     * @return Node[]
     */
    private function wrapNodesToObContents(array $nodes): array
    {
        $contentVariable = new Variable('content');

        $stringCastedObGetContents = new String_($this->createFunCall('ob_get_contents'));
        $assign = new Assign($contentVariable, $stringCastedObGetContents);
        $return = new Return_(new New_(new FullyQualified(SymfonyClass::RESPONSE_CLASS), [new Arg($contentVariable)]));

        return array_merge(
            [$this->createFunCall('ob_start')],
            $nodes,
            [$assign],
            [$this->createFunCall('ob_end_clean')],
            [$return]
        );
    }

    private function createFunCall(string $name): FuncCall
    {
        return new FuncCall(new Name($name));
    }
}
