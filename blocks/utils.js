const { __ } = wp.i18n;
const {
	TextControl,
	SelectControl,
	Notice,
	Placeholder
} = wp.components;

/**
 * Display media placeholder.
 *
 * @param {string} name Block's name.
 * @param {string|boolean} url URL.
 * @return {XML} Placeholder.
 */
export function getMediaPlaceholder( name, url ) {
	return (
		<Placeholder label={ name }>
			<p className="components-placeholder__error">{ url }</p>
			<p className="components-placeholder__error">{ __( 'Previews for this are unavailable in the editor, sorry!', 'amp' ) }</p>
		</Placeholder>
	);
}

/**
 * Layout controls for AMP blocks' attributes: layout, width, height.
 *
 * @param {Object} props Props.
 * @param {Array} ampLayoutOptions Layout options.
 * @return {[XML,*,XML,*,XML]} Controls.
 */
export function getLayoutControls( props, ampLayoutOptions ) {
	// @todo Move getting ampLayoutOptions to utils as well.
	const { attributes, setAttributes } = props;
	const { layout, height, width } = attributes;
	const showHeightNotice = ! height && ( 'fixed' === layout || 'fixed-height' === layout );
	const showWidthNotice = ! width && 'fixed' === layout;

	return [
		<SelectControl
			key="ampLayout"
			label={ __( 'Layout', 'amp' ) }
			value={ layout }
			options={ ampLayoutOptions }
			onChange={ value => ( setAttributes( { layout: value } ) ) }
		/>,
		showWidthNotice && (
			<Notice key="showWidthNotice" status="error" isDismissible={ false }>
				{
					wp.i18n.sprintf(
						__( 'Width is required for %s layout', 'amp' ),
						layout
					)
				}
			</Notice>
		),
		<TextControl
			key="width"
			type="number"
			label={ __( 'Width (px)', 'amp' ) }
			value={ width !== undefined ? width : '' }
			onChange={ value => ( setAttributes( { width: value } ) ) }
		/>,
		showHeightNotice && (
			<Notice key="showHeightNotice" status="error" isDismissible={ false }>
				{
					wp.i18n.sprintf(
						__( 'Height is required for %s layout', 'amp' ),
						layout
					)
				}
			</Notice>
		),
		<TextControl
			key="height"
			type="number"
			label={ __( 'Height (px)', 'amp' ) }
			value={ height }
			onChange={ value => ( setAttributes( { height: value } ) ) }
		/>
	];
}
