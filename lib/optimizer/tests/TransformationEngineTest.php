<?php

namespace AmpProject\Optimizer;

use AmpProject\Dom\Document;
use AmpProject\Optimizer\Tests\MarkupComparison;
use AmpProject\Optimizer\Tests\TestMarkup;
use AmpProject\Optimizer\Tests\TestTransformer;
use AmpProject\RemoteRequest\StubbedRemoteGetRequest;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;

/**
 * Test the transformation engine as a whole.
 *
 * @package ampproject/optimizer
 */
final class TransformationEngineTest extends TestCase
{
    use MarkupComparison;

    const MINIMAL_HTML_MARKUP           = '<html></html>';
    const MINIMAL_OPTIMIZED_HTML_MARKUP = TestMarkup::DOCTYPE .
                                          '<html i-amphtml-layout="" i-amphtml-no-boilerplate="" transformed="self;v=1"><head>' .
                                          TestMarkup::META_CHARSET .
                                          '<style amp-runtime="" i-amphtml-version="012345678900000">/* v0.css */</style>' .
                                          '</head><body></body></html>';

    /**
     * Provide data to test optimizing a string of HTML.
     *
     * @return array[] Associative array of data arrays.
     */
    public function dataOptimizeHtml()
    {
        return [
            'base_html_conversion' => [
                self::MINIMAL_HTML_MARKUP,
                self::MINIMAL_OPTIMIZED_HTML_MARKUP,
            ],
        ];
    }

    /**
     * Test optimizing a string of HTML.
     *
     * @covers       \AmpProject\Optimizer\TransformationEngine::optimizeHtml()
     * @dataProvider dataOptimizeHtml
     *
     * @param string $source   Source HTML string to optimize.
     * @param string $expected Expected HTML output.
     */
    public function testOptimizeHtml($source, $expected)
    {
        $errors = new ErrorCollection();

        $output = $this->getTransformationEngine()->optimizeHtml($source, $errors);

        $this->assertEqualMarkup($expected, $output);
        $this->assertCount(0, $errors);
    }

    /**
     * Test optimizing a DOM object directly.
     *
     * We're only testing the flow once here, to make sure all typing and plumbing works.
     * All conversion details will be the same as with optimizeHtml, so there's no point
     * in testing everything twice.
     *
     * @covers TransformationEngine::optimizeDom()
     */
    public function testOptimizeDom()
    {
        $dom    = Document::fromHtml(self::MINIMAL_HTML_MARKUP);
        $errors = new ErrorCollection();

        $this->getTransformationEngine()->optimizeDom($dom, $errors);
        $output = $dom->saveHTML();

        $this->assertEqualMarkup(self::MINIMAL_OPTIMIZED_HTML_MARKUP, $output);
        $this->assertCount(0, $errors);
    }

    public function dataDependencyResolution()
    {
        return [
            'no_constructor' => [ TestTransformer\NoConstructor::class ],

            'no_dependencies' => [ TestTransformer\NoDependencies::class ],

            'remote_request_only' => [ TestTransformer\RemoteRequestOnly::class ],

            'configuration_only' => [ TestTransformer\ConfigurationOnly::class ],

            'configuration_then_remote_request' => [ TestTransformer\ConfigurationThenRemoteRequest::class ],

            'remote_request_then_configuration' => [ TestTransformer\RemoteRequestThenConfiguration::class ],
        ];
    }
    /**
     * Test dependency resolution.
     *
     * @covers \AmpProject\Optimizer\TransformationEngine::getTransformerDependencies()
     * @dataProvider dataDependencyResolution
     *
     * @param string $transformerClass Transformer class to use for testing.
     */
    public function testDependencyResolution($transformerClass)
    {
        $configurationData = [
            Configuration::KEY_TRANSFORMERS => [
                $transformerClass,
            ],
        ];

        $configuration = new Configuration($configurationData);
        $configuration->registerConfigurationClass($transformerClass, TestTransformer\DummyTransformerConfiguration::class);

        $transformationEngine = $this->getTransformationEngine($configuration);
        $this->assertInstanceof(TransformationEngine::class, $transformationEngine);
        $transformers = $this->getPrivateProperty($transformationEngine, 'transformers');
        $this->assertCount(1, $transformers);
        $this->assertInstanceof($transformerClass, array_pop($transformers));
    }

    /**
     * Get the transformation engine instance to test against.
     *
     * @param Configuration|null $configuration Optional. Configuration object to use.
     * @return TransformationEngine Transformation engine instance to test against.
     */
    private function getTransformationEngine(Configuration $configuration = null)
    {
        return new TransformationEngine(
            $configuration,
            new StubbedRemoteGetRequest(TestMarkup::STUBBED_REMOTE_REQUESTS)
        );
    }

    /**
     * Get a private property as if it was public.
     *
     * @param object|string $object       Object instance or class string to get the property of.
     * @param string        $propertyName Name of the property to get.
     * @return mixed Return value of the property.
     * @throws ReflectionException If the object could not be reflected upon.
     */
    private function getPrivateProperty($object, $propertyName)
    {
        $property = ( new ReflectionClass($object) )->getProperty($propertyName);
        $property->setAccessible(true);
        return $property->getValue($object);
    }
}
