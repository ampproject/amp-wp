<?php
/**
 * Trait HasCodeLinks.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Documentation\Model;

use SplFileObject;

trait HasCodeLinks {

	/**
	 * Get the path to the source file.
	 *
	 * @return string Path to the source file.
	 */
	public function get_file_path() {
		$file = $this;

		do {
			$file = $file->get_parent();

			if ( $file instanceof File ) {
				return $file->path;
			}
		} while ( ! $file instanceof Root );

		return '<unknown>';
	}

	/**
	 * Get the direct code link into the GitHub repository.
	 *
	 * @return string GitHub link.
	 */
	public function get_github_link() {
		// TODO: Adapt based on which tag to use for documentation.
		return sprintf(
			'/%s%s',
			$this->get_file_path(),
			$this->end_line === $this->line
				? "#L{$this->line}"
				: "#L{$this->line}-L{$this->end_line}"
		);
	}

	/**
	 * Get the code snippet.
	 *
	 * @return string Code snippet.
	 */
	public function get_code() {
		$file_path = $this->get_file_path();

		if ( '<unknown>' === $file_path ) {
			return "\n";
		}

		$php_file = new SplFileObject( AMP__DIR__ . "/{$file_path}" );

		$line = $this->line;
		$code = [];
		do {
			$php_file->seek( $line - 1 );
			$code[] = $php_file->current();
		} while ( ++$line <= $this->end_line );

		return implode( '', $this->strip_extra_indentation( $code ) );
	}

	/**
	 * Strip the extra indentation for an array of code lines.
	 *
	 * @param string[] $lines Array of code lines.
	 * @return string[] Array of code lines with indentation stripped.
	 */
	protected function strip_extra_indentation( $lines ) {
		// Only a single line, so just trim.
		if ( count( $lines ) < 2 ) {
			return [ ltrim( $lines[0] ) ];
		}

		// We assume that the first line contains code and is representative for
		// the indentation to strip across the whole snippet.
		$indent = strspn( $lines[0], "\t" );

		foreach ( $lines as &$line ) {
			// Skip blank lines without indentation.
			if ( '' === $line ) {
				continue;
			}

			$line = substr( $line, $indent );
		}

		return $lines;
	}
}
