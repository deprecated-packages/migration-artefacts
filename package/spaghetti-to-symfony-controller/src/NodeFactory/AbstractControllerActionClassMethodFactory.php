<?php

declare(strict_types=1);

namespace Migrify\MigrationArtefact\SpaghettiToSymfonyController\Rector\NodeFactory;

use Migrify\MigrationArtefact\SpaghettiToSymfonyController\Rector\ValueObject\SymfonyClass;
use PhpParser\BuilderFactory;
use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocNode\Symfony\SymfonyRouteTagValueNode;
use Rector\FrameworkMigration\Symfony\ImplicitToExplicitRoutingAnnotationDecorator;

abstract class AbstractControllerActionClassMethodFactory
{
    /**
     * @var BuilderFactory
     */
    private $builderFactory;

    /**
     * @var PhpDocInfoFactory
     */
    private $phpDocInfoFactory;

    /**
     * @var ImplicitToExplicitRoutingAnnotationDecorator
     */
    private $implicitToExplicitRoutingAnnotationDecorator;

    /**
     * @required
     */
    public function autowireAbstractClassMethodFactory(
        BuilderFactory $builderFactory,
        PhpDocInfoFactory $phpDocInfoFactory,
        ImplicitToExplicitRoutingAnnotationDecorator $implicitToExplicitRoutingAnnotationDecorator
    ): void {
        $this->builderFactory = $builderFactory;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->implicitToExplicitRoutingAnnotationDecorator = $implicitToExplicitRoutingAnnotationDecorator;
    }

    /**
     * @param Node[] $nodes
     */
    protected function createActionClassMethodWithStmts(
        string $name,
        array $nodes,
        string $routePath
    ): ClassMethod {
        $methodBuilder = $this->builderFactory->method($name);
        $methodBuilder->makePublic();
        $methodBuilder->setReturnType(new FullyQualified(SymfonyClass::RESPONSE_CLASS, ));
        $methodBuilder->addStmts($nodes);

        $classMethod = $methodBuilder->getNode();

        $this->addSymfonyRouteAnnotation($classMethod, $routePath);

        return $classMethod;
    }

    private function addSymfonyRouteAnnotation(ClassMethod $invokeMethod, string $routePath): void
    {
        $this->phpDocInfoFactory->createFromNode($invokeMethod);

        $symfonyRouteTagValueNode = new SymfonyRouteTagValueNode($routePath, [], $routePath);
        $this->implicitToExplicitRoutingAnnotationDecorator->decorateClassMethodWithRouteAnnotation(
            $invokeMethod,
            $symfonyRouteTagValueNode
        );
    }
}
