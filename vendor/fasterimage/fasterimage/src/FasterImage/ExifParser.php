<?php namespace FasterImage;

use FasterImage\Exception\InvalidImageException;
use WillWashburn\Stream\StreamableInterface;

/**
 * Class ExifParser
 *
 * @package FasterImage
 */
class ExifParser
{
    /**
     * @var int
     */
    protected $width;
    /**
     * @var  int
     */
    protected $height;

    /**
     * @var
     */
    protected $short;

    /**
     * @var
     */
    protected $long;

    /**
     * @var  StreamableInterface
     */
    protected $stream;

    /**
     * @var int
     */
    protected $orientation;

    /**
     * ExifParser constructor.
     *
     * @param StreamableInterface $stream
     */
    public function __construct(StreamableInterface $stream)
    {
        $this->stream = $stream;
        $this->parseExifIfd();
    }

    /**
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @return bool
     */
    public function isRotated()
    {
        return (! empty($this->orientation) && $this->orientation >= 5 && $this->orientation <= 8);
    }

    /**
     * @return bool
     * @throws \FasterImage\Exception\InvalidImageException
     */
    protected function parseExifIfd()
    {
        $byte_order = $this->stream->read(2);

        switch ( $byte_order ) {
            case 'II':
                $this->short = 'v';
                $this->long  = 'V';
                break;
            case 'MM':
                $this->short = 'n';
                $this->long  = 'N';
                break;
            default:
                throw new InvalidImageException;
                break;
        }

        $this->stream->read(2);

        $offset = current(unpack($this->long, $this->stream->read(4)));

        $this->stream->read($offset - 8);

        $tag_count = current(unpack($this->short, $this->stream->read(2)));

        for ( $i = $tag_count; $i > 0; $i-- ) {

            $type = current(unpack($this->short, $this->stream->read(2)));
            $this->stream->read(6);
            $data = current(unpack($this->short, $this->stream->read(2)));

            switch ( $type ) {
                case 0x0100:
                    $this->width = $data;
                    break;
                case 0x0101:
                    $this->height = $data;
                    break;
                case 0x0112:
                    $this->orientation = $data;
                    break;
            }

            if ( isset($this->width) && isset($this->height) && isset($this->orientation) ) {
                return true;
            }

            $this->stream->read(2);
        }

        return false;
    }
}
