/**
 * Gets whether the media item has the minimum dimensions.
 *
 * The 3 minimum dimensions for the AMP story poster image are:
 * 696px x 928px, 928px x 696px, or 928px x 928px.
 *
 * @param {Object} media A media object with width and height values.
 * @return {boolean} Whether the media has the minimum dimensions.
 */
export default function( media ) {
	const largeDimension = 928;
	const smallDimension = 696;
	return (
		( media.width && media.height )	&&
		( media.width >= smallDimension && media.height >= smallDimension )	&&
		(
			( media.width >= largeDimension && media.height >= largeDimension ) ||
			( media.width < largeDimension && media.height >= largeDimension ) ||
			( media.height < largeDimension && media.width >= largeDimension )
		)
	);
}
