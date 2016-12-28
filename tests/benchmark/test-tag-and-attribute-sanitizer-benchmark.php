<?php

require_once( AMP__DIR__ . '/includes/sanitizers/class-amp-allowed-tags-generated.php' );

class AMP_Tag_And_Attribute_Sanitizer_Benchmark_Test extends WP_UnitTestCase {

	public $files = array();
	public $num_tests = 100;
	public $data = array();
	public $results = array();
	public $diffs = array();
	public $sanitizers_to_test = array(
		'AMP_Blacklist_Sanitizer', // all sanitizers below will be compared with the results of this one
		'AMP_Tag_And_Attribute_Sanitizer',
	);

	/**
	 * @group allowed-tags-benchmark
	 */
	public function test_sanitizer_benchmark() {

		$benchmark_data_dir = AMP__DIR__ . '/tests/benchmark/test-data/';
		$sanitizer_instances = array();
		$this->files = glob( $benchmark_data_dir . '*.html' );
		$dom = new DOMDocument;

		foreach ( $this->files as $file ) {
			$libxml_previous_state = libxml_use_internal_errors( true );
			$result = $dom->loadHTMLFile( $file );
			libxml_clear_errors();
			libxml_use_internal_errors( $libxml_previous_state );

			foreach ( $this->sanitizers_to_test as $sanitizer_name ) {
				$sanitizer_instances[ $sanitizer_name ] = new $sanitizer_name( $dom );
			}

			for ( $i = 0; $i < $this->num_tests; $i++ ) {
				foreach ( $this->sanitizers_to_test as $sanitizer_name ) {
					$start = $this->start_timer();
					$sanitizer_instances[ $sanitizer_name ]->sanitize();
					$stop = $this->stop_timer();
					$this->add_result( $sanitizer_name, $file, $this->get_elapsed_time( $start, $stop ) );
				}
			}
		}


		$results = $this->get_results();
		$diffs = $this->get_diffs();
		foreach ( $this->sanitizers_to_test as $sanitizer_name ) {
			echo "\n";
			echo "  $sanitizer_name stats:\n";
			echo "  ------------------\n";
			echo "     Range: {$results[ $sanitizer_name ]['min']} - {$results[ $sanitizer_name ]['max']}\n";
			echo "    Median: {$results[ $sanitizer_name ]['median']}\n";
			echo "      Mean: {$results[ $sanitizer_name ]['mean']}\n";
			echo "        SD: {$results[ $sanitizer_name ]['sd']}\n\n";

			if ( ! isset( $diffs[ $sanitizer_name ] ) ) {
				continue;
			}

			echo "  $sanitizer_name times as multiples of {$this->sanitizers_to_test[0]}: \n";
			echo "  ------------------\n";
			echo "     Range: {$diffs[ $sanitizer_name ]['min']} - {$diffs[ $sanitizer_name ]['max']}\n";
			echo "    Median: {$diffs[ $sanitizer_name ]['median']}\n";
			echo "      Mean: {$diffs[ $sanitizer_name ]['mean']}\n";
			echo "        SD: {$diffs[ $sanitizer_name ]['sd']}\n\n";
		}

	}

	public function calculate_diffs() {
		$results = $this->get_results();
		$sanitizers = array_slice( $this->sanitizers_to_test, 1);
		foreach ( $sanitizers as $sanitizer ) {
			$diffs[ $sanitizer ] = array(
				'mean'   => $this->format( $results[ $sanitizer ]['mean']   / $results[ $this->sanitizers_to_test[0] ]['mean'] ),
				'median' => $this->format( $results[ $sanitizer ]['median'] / $results[ $this->sanitizers_to_test[0] ]['median'] ),
				'min'    => $this->format( $results[ $sanitizer ]['min']    / $results[ $this->sanitizers_to_test[0] ]['min'] ),
				'max'    => $this->format( $results[ $sanitizer ]['max']    / $results[ $this->sanitizers_to_test[0] ]['max'] ),  
				'sd'     => $this->format( $results[ $sanitizer ]['sd']     / $results[ $this->sanitizers_to_test[0] ]['sd'] ),  
			);
		}
		return $diffs;
	}

	public function calculate_results() {
		$results = array();
		foreach ( $this->sanitizers_to_test as $sanitizer_name ) {
			$results[ $sanitizer_name ] = array(
				'mean'   => $this->format( $this->get_mean( $this->data[ $sanitizer_name ] ) ),
				'median' => $this->format( $this->get_median( $this->data[ $sanitizer_name ] ) ),
				'min'    => $this->format( $this->get_min( $this->data[ $sanitizer_name ] ) ),
				'max'    => $this->format( $this->get_max( $this->data[ $sanitizer_name ] ) ),
				'sd'     => $this->format( $this->get_standard_deviation( $this->data[ $sanitizer_name ] ) ),
			);
		}
		return $results;
	}

	public function format( $num ) {
		return sprintf( '%.10F', $num );
	}

	public function get_diffs() {
		if ( empty( $this->diffs ) ) {
			$this->diffs = $this->calculate_diffs();
		}
		return $this->diffs;
	}

	public function get_results() {
		if ( empty( $this->results ) ) {
			$this->results = $this->calculate_results();
			// $this->results = $this->calculate_diffs();
		}
		return $this->results;
	}

	public function start_timer() {
		return microtime( true );
	}
	
	public function stop_timer() {
		return microtime( true );
	}
	
	public function get_elapsed_time( $start, $stop ) {
		return $stop - $start;
	}
	
	public function add_result( $sanitizer, $file, $result ) {
		$this->data[ $sanitizer ][] = $result;
	}
	
	public function get_mean( $array ) {
		return array_sum( $array ) / count( $array );
	}
	
	public function get_median( $array ) {
		rsort( $array );
		$middle = (int) round( count( $array ) / 2 );
		return $array[ $middle - 1 ];
	}
	
	public function get_standard_deviation( $array ){
		return sqrt( array_sum( array_map( array( $this, 'get_sd_square' ), $array, array_fill( 0, count( $array ), ( array_sum( $array ) / count( $array ) ) ) ) ) / ( count( $array ) - 1 ) );
	}

	public function get_sd_square( $x, $mean ) {
		return pow( $x - $mean, 2 );
	}
	
	public function get_max( $array ) {
		return max( $array );
	}
	
	public function get_min( $array ) {
		return min( $array );
	}
}
