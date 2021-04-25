<?php

use SebastianBergmann\CodeCoverage\Filter;
use SebastianBergmann\CodeCoverage\Driver\Selector;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Report\Clover;

$root_folder = dirname( dirname( __DIR__ ) );

if ( ! class_exists( 'SebastianBergmann\CodeCoverage\Filter' ) ) {
	require "{$root_folder}/vendor/autoload.php";
}

$filter = new Filter();
$filter->includeDirectory( "{$root_folder}/includes" );
$filter->includeDirectory( "{$root_folder}/src" );

$coverage = new CodeCoverage(
	( new Selector() )->forLineCoverage( $filter ),
	$filter
);

$feature  = getenv( 'BEHAT_FEATURE_TITLE' );
$scenario = getenv( 'BEHAT_SCENARIO_TITLE' );
$name     = "{$feature} - {$scenario}";

$coverage->start( $name );

register_shutdown_function(
	static function () use ( $coverage, $root_folder, $feature, $scenario, $name ) {
		$coverage->stop();

		$feature_suffix  = preg_replace( '/[^a-z0-9]+/', '-', strtolower( $feature ) );
		$scenario_suffix = preg_replace( '/[^a-z0-9]+/', '-', strtolower( $scenario ) );
		$filename        = "clover-behat/{$feature_suffix}-{$scenario_suffix}.xml";
		$destination     = "{$root_folder}/build/logs/{$filename}";

		var_dump( getcwd() );
		var_dump( $destination );

		( new Clover() )->process( $coverage, $destination, $name );
	}
);



