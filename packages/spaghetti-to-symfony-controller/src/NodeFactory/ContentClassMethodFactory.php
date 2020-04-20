<?php

declare(strict_types=1);

namespace Migrify\MigrationArtefact\SpaghettiToSymfonyController\NodeFactory;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Cast\String_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;

final class ContentClassMethodFactory extends AbstractControllerActionClassMethodFactory
{
    /**
     * @param Node[] $nodes
     */
    public function create(array $nodes): ClassMethod
    {
        $nodes = $this->wrapNodesToObContents($nodes);

        return $this->createPrivateClassMethodWithStmts('createContent', $nodes, 'string');
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
        $return = new Return_($contentVariable);

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
