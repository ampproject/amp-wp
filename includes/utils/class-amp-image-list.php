<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName

// phpcs:disable Generic.Files.OneObjectStructurePerFile.MultipleFound
/**
 * Image collection classes.
 *
 * @package AMP
 */

/**
 * Interface Has_Caption
 *
 * @since 1.5.0
 */
interface Has_Caption {
	/**
	 * Gets the caption.
	 *
	 * @return string The caption text.
	 */
	public function get_caption();
}

/**
 * Class AMP_Image
 *
 * @since 1.5.0
 */
class AMP_Image {

	/**
	 * The image node.
	 *
	 * @var DOMElement
	 */
	protected $image_node;

	/**
	 * Constructs the class.
	 *
	 * @param DOMElement $image_node The image node.
	 */
	public function __construct( DOMElement $image_node ) {
		$this->image_node = $image_node;
	}

	/**
	 * Gets the image.
	 *
	 * @return DOMElement
	 */
	public function get_image_node() {
		return $this->image_node;
	}
}

/**
 * Class AMP_Captioned_Image
 *
 * @since 1.5.0
 */
final class AMP_Captioned_Image extends AMP_Image implements Has_Caption {

	/**
	 * The caption text.
	 *
	 * @var string
	 */
	private $caption;

	/**
	 * Constructs the class.
	 *
	 * @param DOMElement $image_node The image node.
	 * @param string     $caption    The caption text.
	 */
	public function __construct( DOMElement $image_node, $caption ) {
		parent::__construct( $image_node );
		$this->caption = $caption;
	}

	/**
	 * Gets the caption text.
	 *
	 * @return string The caption text.
	 */
	public function get_caption() {
		return $this->caption;
	}
}

/**
 * Class AMP_Image_List
 *
 * @since 1.5.0
 */
final class AMP_Image_List implements IteratorAggregate, Countable {

	/**
	 * The captioned images.
	 *
	 * @var AMP_Captioned_Image[]
	 */
	private $elements = [];

	/**
	 * Adds an image to the list.
	 *
	 * @param DOMElement $image_node The image to add.
	 * @param string     $caption    The caption to add.
	 * @return self
	 */
	public function add( DOMElement $image_node, $caption = '' ) {
		$this->elements[] = empty( $caption ) ? new AMP_Image( $image_node ) : new AMP_Captioned_Image( $image_node, $caption );
		return $this;
	}

	/**
	 * Gets an iterator with the elements.
	 *
	 * This together with the IteratorAggregate turns the object into a "Traversable",
	 * so you can just foreach over it and receive its elements in the correct type.
	 *
	 * @return ArrayIterator An iterator with the elements.
	 */
	public function getIterator() {
		return new ArrayIterator( $this->elements );
	}

	/**
	 * Gets the count of the images.
	 *
	 * @return int The number of images.
	 */
	public function count() {
		return count( $this->elements );
	}
}
