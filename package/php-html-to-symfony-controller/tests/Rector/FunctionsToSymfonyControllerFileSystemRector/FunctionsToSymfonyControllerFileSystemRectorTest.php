<?php

declare(strict_types=1);

namespace MigrationArtefact\PhpHtmlToSymfonyController\Rector\Tests\Rector\FunctionsToSymfonyControllerFileSystemRector;

use Iterator;
use MigrationArtefact\PhpHtmlToSymfonyController\Rector\Rector\FunctionsToSymfonyControllerFileSystemRector;
use Nette\Utils\FileSystem;
use Rector\Core\Testing\PHPUnit\AbstractFileSystemRectorTestCase;

final class FunctionsToSymfonyControllerFileSystemRectorTest extends AbstractFileSystemRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(
        string $originalFile,
        string $expectedFileControllerLocation,
        string $expectedFileControllerContent,
        string $expectedTwigLocation,
        string $expectedTwigContent
    ): void {
        $this->doTestFileWithoutAutoload($originalFile);

        $this->doAssertPhpFile($expectedFileControllerLocation, $expectedFileControllerContent);
        $this->doAssertTwigFile($expectedTwigLocation, $expectedTwigContent);
    }

    public function provideData(): Iterator
    {
        yield [
            __DIR__ . '/Source/ceniny_new.php',
            __DIR__ . '/Fixture/Symfony/Controller/CeninyNewController.php',
            __DIR__ . '/Expected/ExpectedCeninyNewController.php',
            __DIR__ . '/templates/controller/ceniny_new.twig',
            __DIR__ . '/Expected/expected_ceniny_new.twig',
        ];
    }

    protected function provideConfig(): string
    {
        // load services
        return __DIR__ . '/../../../config/config.yaml';
    }

    protected function getRectorClass(): string
    {
        return FunctionsToSymfonyControllerFileSystemRector::class;
    }

    private function doAssertPhpFile(
        string $expectedFileControllerLocation,
        string $expectedFileControllerContent
    ): void {
        $this->assertFileExists($expectedFileControllerLocation);
        $this->assertFileEquals($expectedFileControllerContent, $expectedFileControllerLocation);

        FileSystem::delete($expectedFileControllerLocation);
    }

    private function doAssertTwigFile(string $expectedTwigLocation, string $expectedTwigContent): void
    {
        $this->assertFileExists($expectedTwigLocation);
        $this->assertFileEquals($expectedTwigContent, $expectedTwigLocation);

        FileSystem::delete($expectedTwigLocation);
    }
}
