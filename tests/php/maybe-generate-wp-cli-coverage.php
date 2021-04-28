<?php

use SebastianBergmann\CodeCoverage\Filter;
use SebastianBergmann\CodeCoverage\Driver\Selector;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Report\Clover;

$root_folder = realpath( dirname( dirname( __DIR__ ) ) );

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
	static function () use ( $coverage, $feature, $scenario, $name ) {
		$coverage->stop();

		$project_dir = getenv( 'BEHAT_PROJECT_DIR' );

		$feature_suffix  = preg_replace( '/[^a-z0-9]+/', '-', strtolower( $feature ) );
		$scenario_suffix = preg_replace( '/[^a-z0-9]+/', '-', strtolower( $scenario ) );
		$filename        = "clover-behat/{$feature_suffix}-{$scenario_suffix}.xml";
		$destination     = "{$project_dir}/build/logs/{$filename}";

		( new Clover() )->process( $coverage, $destination, $name );
	}
);



