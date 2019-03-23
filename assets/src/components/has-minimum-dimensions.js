/**
 * Gets whether the media item has the minimum dimensions.
 *
 * The 3 minimum dimensions for the AMP story poster image are:
 * 696px x 928px, 928px x 696px, or 928px x 928px.
 *
 * @param {Object} media A media object with width and height values.
 * @param {Object} dimensions The dimensions to check, including a number value for largeDimension and smallDimension.
 * @return {boolean} Whether the media has the minimum dimensions.
 */
export default function( media, dimensions ) {
	const { largeDimension, smallDimension } = dimensions;
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
