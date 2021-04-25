<?php

use SebastianBergmann\CodeCoverage\Filter;
use SebastianBergmann\CodeCoverage\Driver\Selector;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Report\Clover;

$with_coverage = getenv( 'BEHAT_CODE_COVERAGE' );
var_dump( $with_coverage );

$root_folder = dirname( dirname( __DIR__ ) );

if ( ! class_exists( 'SebastianBergmann\CodeCoverage\Filter' ) ) {
	require "{$root_folder}/vendor/autoload.php";
}

$filter = new Filter;
$filter->includeDirectory( "{$root_folder}/includes" );
$filter->includeDirectory( "{$root_folder}/src" );

$coverage = new CodeCoverage(
	( new Selector )->forLineCoverage( $filter ),
	$filter
);

$feature  = getenv( 'BEHAT_FEATURE_TITLE' );
$scenario = getenv( 'BEHAT_SCENARIO_TITLE' );

$coverage->start( "{$feature} - {$scenario}" );

register_shutdown_function(
	static function () use ( $coverage, $root_folder, $feature, $scenario ) {
		$coverage->stop();

		$feature_suffix  = preg_replace( '/[^a-z0-9]+/', '-', strtolower( $feature ) );
		$scenario_suffix = preg_replace( '/[^a-z0-9]+/', '-', strtolower( $scenario ) );
		$filename        = "clover-behat/{$feature_suffix}-{$scenario_suffix}.xml";

		( new Clover() )->process( $coverage, "{$root_folder}/build/logs/{$filename}" );
	}
);



