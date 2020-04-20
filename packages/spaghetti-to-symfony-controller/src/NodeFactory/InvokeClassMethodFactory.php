<?php

declare(strict_types=1);

namespace Migrify\MigrationArtefact\SpaghettiToSymfonyController\NodeFactory;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;

final class InvokeClassMethodFactory extends AbstractControllerActionClassMethodFactory
{
    public function create(string $routePath): ClassMethod
    {
        $return = $this->createReturnThisRenderMethodCall($routePath);

        return $this->createActionClassMethodWithStmts('__invoke', [$return], $routePath);
    }

    private function createReturnThisRenderMethodCall(string $routePath): Return_
    {
        $thisVariable = new Variable('this');
        $args = $this->createRenderMethodArgs($routePath);
        $renderMethodCall = new MethodCall($thisVariable, 'render', $args);

        return new Return_($renderMethodCall);
    }

    /**
     * @return Arg[]
     */
    private function createRenderMethodArgs(string $routePath): array
    {
        $templateNameString = new String_('controller/' . $routePath . '.twig');

        $thisVariable = new Variable('this');
        $createContentMethodCall = new MethodCall($thisVariable, new Identifier('createContent'));

        // ['content' => $this->createContent()]
        $arrayItem = new ArrayItem($createContentMethodCall, new String_('content'));
        $array = new Array_([$arrayItem]);

        return [new Arg($templateNameString), new Arg($array)];
    }
}
