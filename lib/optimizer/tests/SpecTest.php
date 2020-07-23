<?php

namespace AmpProject\Optimizer;

use AmpProject\Dom\Document;
use AmpProject\Optimizer\Configuration\AmpRuntimeCssConfiguration;
use AmpProject\Optimizer\Configuration\RewriteAmpUrlsConfiguration;
use AmpProject\Optimizer\Tests\MarkupComparison;
use AmpProject\Optimizer\Tests\TestMarkup;
use AmpProject\Optimizer\Transformer\AmpRuntimeCss;
use AmpProject\Optimizer\Transformer\ReorderHead;
use AmpProject\Optimizer\Transformer\RewriteAmpUrls;
use AmpProject\Optimizer\Transformer\ServerSideRendering;
use AmpProject\RemoteRequest\StubbedRemoteGetRequest;
use DirectoryIterator;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;

/**
 * Test the individual transformers against the NodeJS spec test suite.
 *
 * @package ampproject/optimizer
 */
final class SpecTest extends TestCase
{
    use MarkupComparison;

    const TRANSFORMER_SPEC_PATH = __DIR__ . '/spec/transformers';

    const TESTS_TO_SKIP = [
        'ReorderHead - reorders_head_a4a'                => 'see https://github.com/ampproject/amp-toolbox/issues/583',
        'ReorderHead - reorders_head_amphtml'            => 'see https://github.com/ampproject/amp-toolbox/issues/583',
        'ReorderHead - preserves_amp_custom_style_order' => 'see https://github.com/ampproject/amp-toolbox/issues/604',

        'ServerSideRendering - converts_sizes_attribute_to_css' => 'see https://github.com/ampproject/amp-toolbox/issues/819',
    ];

    const CLASS_SKIP_TEST = '__SKIP__';

    /**
     * Regular expression to match the leading HTML comment that provides the configuration for a spec test.
     *
     * @see https://regex101.com/r/ImDtxI/2
     *
     * @var string
     */
    const LEADING_HTML_COMMENT_REGEX_PATTERN = '/^\s*<!--\s*(?<json>{(?>[^}]*})*)\s*-->/';

    /**
     * Provide the data for running the spec tests.
     *
     * @return array Scenarios to test.
     */
    public function dataTransformerSpecFiles()
    {
        $scenarios = [];
        $suites    = [
            'ReorderHead'         => [ReorderHead::class,         self::TRANSFORMER_SPEC_PATH . '/valid/ReorderHeadTransformer'],
            'ServerSideRendering' => [ServerSideRendering::class, self::TRANSFORMER_SPEC_PATH . '/valid/ServerSideRendering'],
            'AmpRuntimeCss'       => [AmpRuntimeCss::class,       self::TRANSFORMER_SPEC_PATH . '/valid/AmpBoilerplateTransformer'],
            'RewriteAmpUrls'      => [RewriteAmpUrls::class,      self::TRANSFORMER_SPEC_PATH . '/experimental/RewriteAmpUrls']
        ];

        foreach ($suites as $key => list($transformerClass, $specFileFolder)) {
            $suiteConfig = [];
            if (file_exists("{$specFileFolder}/config.json")) {
                $suiteConfigJson = file_get_contents("{$specFileFolder}/config.json");
                $suiteConfig = (array)json_decode($suiteConfigJson, true);
                if (empty($suiteConfig) || json_last_error() !== JSON_ERROR_NONE) {
                    $suiteConfig = [];
                }
            }

            foreach (new DirectoryIterator($specFileFolder) as $subFolder) {
                if ($subFolder->isFile() || $subFolder->isDot()) {
                    continue;
                }

                $scenario = "{$key} - {$subFolder}";

                if (array_key_exists($scenario, self::TESTS_TO_SKIP)) {
                    $scenarios[$scenario] = [
                        $scenario,
                        self::CLASS_SKIP_TEST,
                        [],
                        $scenario,
                        self::TESTS_TO_SKIP[$scenario],
                    ];

                    continue;
                }

                $scenarios[$scenario] = [
                    $scenario,
                    $transformerClass,
                    $suiteConfig,
                    file_get_contents("{$subFolder->getPathname()}/input.html"),
                    file_get_contents("{$subFolder->getPathname()}/expected_output.html"),
                ];
            }
        }

        return $scenarios;
    }

    /**
     * Test the transformers against their spec files.
     *
     * @dataProvider dataTransformerSpecFiles
     *
     * @param string $scenario         Test scenario.
     * @param string $transformerClass Class of the transformer to test.
     * @param array  $suiteConfig      Suite-wide config file to use.
     * @param string $source           Source file to transform.
     * @param string $expected         Expected transformed result.
     */
    public function testTransformerSpecFiles($scenario, $transformerClass, $suiteConfig, $source, $expected)
    {
        if ($transformerClass === self::CLASS_SKIP_TEST) {
            // $source contains the scenario name, $expected the reason.
            $this->markTestSkipped("Skipping {$source}, {$expected}");
        }

        $configuration = $this->mapConfigurationData(
            array_merge(
                $suiteConfig,
                $this->extractConfigurationData($source)
            )
        );

        $document    = Document::fromHtmlFragment($source);
        $transformer = $this->getTransformer($scenario, $transformerClass, $configuration);
        $errors      = new ErrorCollection();

        $transformer->transform($document, $errors);

        $this->assertSimilarMarkup($expected, $document->saveHTMLFragment());
    }

    /**
     * Map spec test input file configuration data to configuration arguments as needed by the PHP transformers.
     *
     * @param array $configurationData Associative array of configuration data coming from the spec test input file.
     * @return Configuration Configuration object to use for the transformation engine.
     */
    public function mapConfigurationData($configurationData)
    {
        $mappedConfiguration = [];

        foreach ($configurationData as $key => $value) {
            switch ($key) {
                case 'ampRuntimeStyles':
                    $mappedConfiguration[AmpRuntimeCss::class][AmpRuntimeCssConfiguration::STYLES] = $value;
                    break;
                case 'ampRuntimeVersion':
                    $mappedConfiguration[AmpRuntimeCss::class][AmpRuntimeCssConfiguration::VERSION] = $value;
                    $mappedConfiguration[RewriteAmpUrls::class][RewriteAmpUrlsConfiguration::AMP_RUNTIME_VERSION] = $value;
                    break;
                case 'experimentEsm':
                    $mappedConfiguration[RewriteAmpUrls::class][RewriteAmpUrlsConfiguration::EXPERIMENT_ESM] = $value;
                    break;
                case 'ampUrlPrefix':
                    $mappedConfiguration[RewriteAmpUrls::class][RewriteAmpUrlsConfiguration::AMP_URL_PREFIX] = $value;
                    break;
                case 'geoApiUrl':
                    $mappedConfiguration[RewriteAmpUrls::class][RewriteAmpUrlsConfiguration::GEO_API_URL] = $value;
                    break;
                case 'lts':
                    $mappedConfiguration[RewriteAmpUrls::class][RewriteAmpUrlsConfiguration::LTS] = $value;
                    break;
                case 'rtv':
                    $mappedConfiguration[RewriteAmpUrls::class][RewriteAmpUrlsConfiguration::RTV] = $value;
                    break;

                // @TODO: Known configuration arguments used in spec tests that are not implemented yet.
                case 'ampUrl':
                case 'canonical':
                case 'experimentBindAttribute':
                case 'preloadHeroImage':
                default:
                    $this->fail("Configuration argument not yet implemented: {$key}.");
            }
        }

        return new Configuration($mappedConfiguration);
    }

    /**
     * Parse the input source file and extract the configuration data.
     *
     * Input HTML files can contain a leading HTML comment that provides configuration arguments in the form of a JSON
     * object.
     *
     * @param string $source Input source file to parse for a configuration snippet.
     * @return array Associative array of configuration data found in the input HTML file.
     */
    private function extractConfigurationData(&$source)
    {
        $matches = [];
        if (!preg_match(self::LEADING_HTML_COMMENT_REGEX_PATTERN, $source, $matches)) {
            return [];
        }

        $json   = trim($matches['json']);
        $source = substr($source, strlen($matches[0]));

        if (empty($json)) {
            return [];
        }

        $configurationData = (array)json_decode($json, true);
        if (empty($configurationData) || json_last_error() !== JSON_ERROR_NONE) {
            return [];
        }

        return $configurationData;
    }

    /**
     * Get the transformer to test.
     *
     * @param string        $scenario         Test scenario.
     * @param string        $transformerClass Class of the transformer to get.
     * @param Configuration $configuration    Configuration to use.
     * @return Transformer Instantiated transformer object.
     */
    private function getTransformer($scenario, $transformerClass, $configuration)
    {
        $stubbedRequests = TestMarkup::STUBBED_REMOTE_REQUESTS;

        $transformationEngine = new TransformationEngine(
            $configuration,
            new StubbedRemoteGetRequest($stubbedRequests)
        );

        return new $transformerClass(...$this->callPrivateMethod($transformationEngine, 'getTransformerDependencies', [$transformerClass]));
    }

    /**
     * Call a private method as if it was public.
     *
     * @param object|string $object     Object instance or class string to call the method of.
     * @param string        $methodName Name of the method to call.
     * @param array         $args       Optional. Array of arguments to pass to the method.
     * @return mixed Return value of the method call.
     * @throws ReflectionException If the object could not be reflected upon.
     */
    private function callPrivateMethod($object, $methodName, $args = [])
    {
        $method = (new ReflectionClass($object))->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $args);
    }
}
