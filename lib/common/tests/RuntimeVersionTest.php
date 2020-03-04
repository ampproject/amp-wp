<?php

use Amp\RemoteRequest\StubbedRemoteGetRequest;
use Amp\RuntimeVersion;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Amp\RuntimeVersion.
 *
 * @covers  RuntimeVersion
 * @package amp/common
 */
class RuntimeVersionTest extends TestCase
{

    /**
     * RuntimeVersion object to test against.
     *
     * @var RuntimeVersion
     */
    protected $runtimeVersion;

    /**
     * Associative array of mapping data for stubbing remote requests.
     *
     * @var array
     */
    const STUBBED_REMOTE_REQUESTS = [
        'https://cdn.ampproject.org/rtv/metadata'               => '{"ampRuntimeVersion":"012345678900000","ampCssUrl":"https://cdn.ampproject.org/rtv/012345678900000/v0.css","canaryPercentage":"0.1","diversions":["023456789000000","034567890100000","045678901200000"]}',
        'https://cdn.ampproject.org/v0.css'                     => '/* v0.css */',
        'https://cdn.ampproject.org/rtv/001515617716922/v0.css' => '/* v0.css */',
    ];

    public function __construct(...$args)
    {
        $this->runtimeVersion = new RuntimeVersion(new StubbedRemoteGetRequest(self::STUBBED_REMOTE_REQUESTS));
        parent::__construct(...$args);
    }

    /**
     * Test whether the release version is returned by default.
     *
     * @covers RuntimeVersion::currentVersion()
     */
    public function testItReturnsReleaseVersionByDefault()
    {
        $version = $this->runtimeVersion->currentVersion();
        $this->assertEquals('012345678900000', $version);
    }

    /**
     * Test whether the canary version can be requested via an option.
     *
     * @covers RuntimeVersion::currentVersion()
     */
    public function testItReturnsCanaryVersionViaOption()
    {
        $version = $this->runtimeVersion->currentVersion(['canary' => true]);
        $this->assertEquals('023456789000000', $version);
    }
}
