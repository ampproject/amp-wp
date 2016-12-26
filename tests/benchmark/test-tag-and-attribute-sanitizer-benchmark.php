<?php

require_once( AMP__DIR__ . '/includes/sanitizers/class-amp-allowed-tags-generated.php' );

class AMP_Tag_And_Attribute_Sanitizer_Benchmark_Test extends WP_UnitTestCase {

	public $files = array();
	public $num_tests = 3;
	public $data = array();
	public $results = array();

	/**
	 * @group allowed-tags-benchmark
	 */
	public function test_sanitizer_benchmark() {

		$benchmark_data_dir = AMP__DIR__ . '/tests/benchmark/';
		$this->files = glob( $benchmark_data_dir . '*.html' );
		$dom = new DOMDocument;

		foreach ( $this->files as $file ) {
			printf( PHP_EOL . 'loading file: %s' . PHP_EOL, $file );
			$libxml_previous_state = libxml_use_internal_errors( true );
			$result = $dom->loadHTMLFile( $file );
			libxml_clear_errors();
			libxml_use_internal_errors( $libxml_previous_state );

			print(PHP_EOL.'----------------'.PHP_EOL);
			var_dump($result);

			$sanitizer = new AMP_Tag_And_Attribute_Sanitizer( $dom );
			for ( $i = 0; $i < $this->num_tests; $i++ ) {
				$start = $this->start_timer();
				$sanitizer->sanitize();
				$stop = $this->stop_timer();
				$this->add_result( 'tag_and_attribute_sanitizer', $file, $this->get_elapsed_time( $start, $stop ) );
			}

			$sanitizer = new AMP_Blacklist_Sanitizer( $dom );
			for ( $i = 0; $i < $this->num_tests; $i++ ) {
				$start = $this->start_timer();
				$sanitizer->sanitize();
				$stop = $this->stop_timer();
				$this->add_result( 'blacklist_sanitizer', $file, $this->get_elapsed_time( $start, $stop ) );
			}
		}

		var_dump($this->data);

		// $results = $this->get_results();
		// foreach ( $results as $function => $data ) {
		// 	echo "$function: \n\n";
		// 	foreach ( $data as $length => $values ) {
		// 		echo "  $length\n";
		// 		echo "  --------\n";
		// 		echo "     Range: {$values['min']} - {$values['max']}\n";
		// 		echo "    Median: {$values['median']}\n";
		// 		echo "      Mean: {$values['mean']}\n";
		// 		echo "        SD: {$values['sd']}\n\n";
		// 	}
		// }

		$results = $this->get_results();
		var_dump($results);
		foreach ( $results as $file => $data ) {
			echo "$function: \n\n";
			foreach ( $data as $length => $values ) {
				echo "  $length\n";
				echo "  --------\n";
				echo "     Range: {$values['min']} - {$values['max']}\n";
				echo "    Median: {$values['median']}\n";
				echo "      Mean: {$values['mean']}\n";
				// echo "        SD: {$values['sd']}\n\n";
			}
		}
	}

	public function calculate_diffs() {
		$sanitizers = array( 'tag_and_attribute_sanitizer', 'blacklist_sanitizer' );
		$files = $this->files;
		$results = array();
		foreach ( $files as $file ) {
			$results[ $file ] = array(
				'mean'		=> $this->format( ( $this->get_mean( $this->data[ $sanitizers[1] ][ $file ] ) - $this->get_mean( $this->data[ $sanitizers[0] ][ $file ] ) ) / $this->get_mean( $this->data[ $sanitizers[1] ][ $file ] ) ),
				'median'	=> $this->format( ( $this->get_median( $this->data[ $sanitizers[1] ][ $file ] ) - $this->get_median( $this->data[ $sanitizers[0] ][ $file ] ) ) / $this->get_median( $this->data[ $sanitizers[1] ][ $file ] ) ),
				'min'		=> $this->format( ( $this->get_min( $this->data[ $sanitizers[1] ][ $file ] ) - $this->get_min( $this->data[ $sanitizers[0] ][ $file ] ) ) / $this->get_min( $this->data[ $sanitizers[1] ][ $file ] ) ),
				'max'		=> $this->format( ( $this->get_max( $this->data[ $sanitizers[1] ][ $file ] ) - $this->get_max( $this->data[ $sanitizers[0] ][ $file ] ) ) / $this->get_max( $this->data[ $sanitizers[1] ][ $file ] ) ),
			);
		}
	}

	public function calculate_results() {
		
		$sanitizers = array( 'tag_and_attribute_sanitizer', 'blacklist_sanitizer' );
		$files = $this->files;
		$results = array();
		foreach ( $sanitizers as $sanitizer ) {
			foreach ( $files as $file ) {
				$results[ $sanitizer ][ $file ] = array(
					'mean'   => $this->format( $this->get_mean( $this->data[ $sanitizer ][ $file ] ) ),
					'median' => $this->format( $this->get_median( $this->data[ $sanitizer ][ $file ] ) ),
					'min'    => $this->format( $this->get_min( $this->data[ $sanitizer ][ $file ] ) ),
					'max'    => $this->format( $this->get_max( $this->data[ $sanitizer ][ $file ] ) ),
					'sd'     => $this->format( $this->get_standard_deviation( $this->data[ $sanitizer ][ $file ] ) ),
				);
			}
		}
		return $results;
	}

	public function format( $num ) {
		return sprintf( '%.10F', $num );
	}

	public function get_results() {
		if ( empty( $this->results ) ) {
			// $this->results = $this->calculate_results();
			$this->results = $this->calculate_diffs();
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
		$this->data[ $sanitizer ][ $file ][] = $result;
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
