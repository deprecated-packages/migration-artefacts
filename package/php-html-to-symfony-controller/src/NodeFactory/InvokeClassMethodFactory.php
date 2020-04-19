<?php

declare(strict_types=1);

namespace MigrationArtefact\PhpHtmlToSymfonyController\Rector\NodeFactory;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
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
        $templateName = 'controller/' . $routePath . '.twig';
        $templateNameString = new String_($templateName);
        $thisVariable = new Variable('this');

        $renderMethodCall = new MethodCall($thisVariable, 'render', [new Arg($templateNameString)]);

        return new Return_($renderMethodCall);
    }
}
