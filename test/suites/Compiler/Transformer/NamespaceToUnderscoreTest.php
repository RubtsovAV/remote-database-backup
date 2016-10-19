<?php
/**
 * @license see LICENSE
 */
namespace RubtsovAV\RestDatabaseExporter\Test\Compiler\Transformer;

use RubtsovAV\RestDatabaseExporter\Compiler\Transformer\NamespaceToUnderscore;

/**
 * @covers RubtsovAV\RestDatabaseExporter\Compiler\Transformer\NamespaceToUnderscore
 */
class NamespaceToUnderscoreTest extends \PHPUnit_Framework_TestCase
{
    private $transformer;

    public function setUp()
    {
        $this->transformer = new NamespaceToUnderscore();
    }

    private function getResourceFileContent($filename)
    {
        $baseDir = 'test/resources/compiler/transformer/';
        return file_get_contents($baseDir . $filename);
    }

    /**
     * @dataProvider transformProvider
     */
    public function testTransform($resourceNameWithSourceCode, $resourceNameWithExpectCode)
    {
        $sourceCode = $this->getResourceFileContent($resourceNameWithSourceCode);
        $expectCode = $this->getResourceFileContent($resourceNameWithExpectCode);

        $transformCode = $this->transformer->transform($sourceCode);
        $this->assertTrue($expectCode == $transformCode, 'The transformer is broken');
    }

    public function transformProvider()
    {
        return [
            ['namespace_to_underscore_source.php', 'namespace_to_underscore_expect.php']
        ];
    }
}
