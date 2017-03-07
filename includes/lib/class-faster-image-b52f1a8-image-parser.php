<?php

/**
 * Parses the stream of the image and determines the size and type of the image
 *
 * @package FasterImage
 */
class Faster_Image_B52f1a8_Image_Parser {

	/**
	 * The type of image we've determined this is
	 *
	 * @var string
	 */
	protected $type;
	/**
	 * @var StreamableInterface $stream
	 */
	private $stream;

	/**
	 * ImageParser constructor.
	 *
	 * @param StreamableInterface $stream
	 */
	public function __construct( Stream_17b32f3_Streamable_Interface & $stream ) {
		$this->stream = $stream;
	}

	/**
	 * @return array|bool|null
	 */
	public function parse_size() {
		$this->stream->reset_pointer();

		switch ( $this->type ) {
			case 'png':
				return $this->parse_size_for_png();
			case 'ico':
			case 'cur':
				return $this->parse_size_for_ico();
			case 'gif':
				return $this->parse_size_for_gif();
			case 'bmp':
				return $this->parse_size_for_bmp();
			case 'jpeg':
				return $this->parse_size_for_jpeg();
			case 'tiff':
				return $this->parse_size_for_tiff();
			case 'psd':
				return $this->parse_size_for_psd();
			case 'webp':
				return $this->parse_size_for_webp();
		}

		return null;
	}

	/**
	 * @return array
	 */
	public function parse_size_for_ico() {
		$this->stream->read( 6 );

		$b1 = $this->get_byte();
		$b2 = $this->get_byte();

		return [
			0 === $b1 ? 256 : $b1,
			0 === $b2 ? 256 : $b2,
		];
	}

	/**
	 * @return array
	 */
	protected function parse_size_for_psd() {

		$this->stream->read( 14 );
		$sizes = unpack( 'N*',$this->stream->read( 12 ) );

		return [
			$sizes[2],
			$sizes[1],
		];
	}

	/**
	 * Reads and returns the type of the image
	 *
	 * @return bool|string
	 */
	public function parse_type() {
		if ( ! $this->type ) {
			$this->stream->reset_pointer();

			switch ( $this->stream->read( 2 ) ) {
				case 'BM':
					return $this->type = 'bmp';
				case 'GI':
					return $this->type = 'gif';
				case chr( 0xFF ) . chr( 0xd8 ):
					return $this->type = 'jpeg';
				case "\0\0":
					switch ( $this->read_byte( $this->stream->peek( 1 ) ) ) {
						case 1:
							return $this->type = 'ico';
						case 2:
							return $this->type = 'cur';
					}

					return false;

				case chr( 0x89 ) . 'P':
					return $this->type = 'png';
				case 'RI':
					if ( substr( $this->stream->read( 10 ), 6, 4 ) === 'WEBP' ) {
						return $this->type = 'webp';
					}

					return false;
				case'8B':
					return $this->type = 'psd';
				case 'II':
				case 'MM':
					return $this->type = 'tiff';
				default:
					return false;
			}
		}

		return $this->type;
	}

	/**
	 * @return array
	 */
	protected function parse_size_for_bmp() {
		$chars = $this->stream->read( 29 );
		$chars = substr( $chars, 14, 14 );
		$type  = unpack( 'C', $chars );

		$size = (reset( $type ) === 40) ? unpack( 'l*', substr( $chars, 4 ) ) : unpack( 'l*', substr( $chars, 4, 8 ) );

		return [
			current( $size ),
			abs( next( $size ) ),
		];
	}

	/**
	 * @return array
	 */
	protected function parse_size_for_gif() {
		$chars = $this->stream->read( 11 );

		$size = unpack( 'S*', substr( $chars, 6, 4 ) );

		return [
			current( $size ),
			next( $size ),
		];
	}

	/**
	 * @return array|bool
	 */
	protected function parse_size_for_jpeg() {
		$state = null;

		while ( true ) {
			switch ( $state ) {
				default:
					$this->stream->read( 2 );
					$state = 'started';
					break;

				case 'started':
					$b = $this->get_byte();
					if ( false === $b ) { return false;
					}

					$state = 0xFF === $b ? 'sof' : 'started';
					break;

				case 'sof':
					$b = $this->get_byte();

					if ( 0xe1 === $b ) {
						$data = $this->stream->read( $this->read_int( $this->stream->read( 2 ) ) - 2 );

						$stream = new Stream_17b32f3_Stream;
						$stream->write( $data );

						if ( $stream->read( 4 ) === 'Exif' ) {
							$stream->read( 2 );
							$exif = new Faster_Image_B52f1a8_Exif_Parser( $stream );
						}

						break;
					}

					if ( in_array( $b, range( 0xe0, 0xef ), true ) ) {
						$state = 'skipframe';
						break;
					}

					if ( in_array( $b, array_merge( range( 0xC0, 0xC3 ), range( 0xC5, 0xC7 ), range( 0xC9, 0xCB ), range( 0xCD, 0xCF ) ), true ) ) {
						$state = 'readsize';
						break;
					}
					if ( 0xFF === $b ) {
						$state = 'sof';
						break;
					}

					$state = 'skipframe';
					break;

				case 'skipframe':
					$skip = $this->read_int( $this->stream->read( 2 ) ) - 2;
					$this->stream->read( $skip );
					$state = 'started';
					break;

				case 'readsize':
					$c = $this->stream->read( 7 );

					$size = array( $this->read_int( substr( $c, 5, 2 ) ), $this->read_int( substr( $c, 3, 2 ) ) );

					if ( isset( $exif ) && $exif->is_rotated() ) {
						return array_reverse( $size );
					}

					return $size;
			}
		}

		return false;
	}

	/**
	 * @return array
	 */
	protected function parse_size_for_png() {
		$chars = $this->stream->read( 25 );

		$size = unpack( 'N*', substr( $chars, 16, 8 ) );

		return [
			current( $size ),
			next( $size ),
		];

	}

	/**
	 * @return array|bool
	 * @throws \FasterImage\Exception\InvalidImageException
	 * @throws StreamBufferTooSmallException
	 */
	protected function parse_size_for_tiff() {
		$exif = new Faster_Image_B52f1a8_Exif_Parser( $this->stream );

		if ( $exif->is_rotated() ) {
			return [ $exif->get_height(), $exif->get_width() ];
		}

		return [ $exif->get_width(), $exif->get_height() ];
	}

	/**
	 * @return null
	 * @throws StreamBufferTooSmallException
	 */
	protected function parse_size_for_webp() {
		$vp8 = substr( $this->stream->read( 16 ), 12, 4 );
		$len = unpack( 'V', $this->stream->read( 4 ) );

		switch ( trim( $vp8 ) ) {

			case 'VP8':
				$this->stream->read( 6 );

				$width  = current( unpack( 'v', $this->stream->read( 2 ) ) );
				$height = current( unpack( 'v', $this->stream->read( 2 ) ) );

				return [
					$width & 0x3fff,
					$height & 0x3fff,
				];

			case 'VP8L':
				$this->stream->read( 1 );

				$b1 = $this->get_byte();
				$b2 = $this->get_byte();
				$b3 = $this->get_byte();
				$b4 = $this->get_byte();

				$width  = 1 + ((($b2 & 0x3f) << 8) | $b1);
				$height = 1 + ((($b4 & 0xf) << 10) | ($b3 << 2) | (($b2 & 0xc0) >> 6));

				return [ $width, $height ];

			case 'VP8X':

				$flags = current( unpack( 'C', $this->stream->read( 4 ) ) );

				$b1 = $this->get_byte();
				$b2 = $this->get_byte();
				$b3 = $this->get_byte();
				$b4 = $this->get_byte();
				$b5 = $this->get_byte();
				$b6 = $this->get_byte();

				$width = 1 + $b1 + ($b2 << 8) + ($b3 << 16);

				$height = 1 + $b4 + ($b5 << 8) + ($b6 << 16);

				return [ $width, $height ];
			default:
				return null;
		}

	}

	/**
	 * @return mixed
	 */
	private function get_byte() {
		return $this->read_byte( $this->stream->read( 1 ) );
	}

	/**
	 * @param $string
	 *
	 * @return mixed
	 */
	private function read_byte( $string ) {
		return current( unpack( 'C', $string ) );
	}

	/**
	 * @param $str
	 *
	 * @return int
	 */
	private function read_int( $str ) {
		$size = unpack( 'C*', $str );

		return ($size[1] << 8) + $size[2];
	}
}
