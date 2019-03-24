/**
 * Gets whether the AMP story's featured image has the right minimum dimensions.
 *
 * The featured image populates teh AMP story poster image.
 * The 3 minimum dimensions for that are 696px x 928px, 928px x 696px, or 928px x 928px.
 *
 * @param {Object} media A media object with width and height values.
 * @return {boolean} Whether the media has the minimum dimensions.
 */
export default ( media ) => {
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
