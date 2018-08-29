const { __ } = wp.i18n;
const {
	TextControl,
	SelectControl,
	Notice,
	RangeControl,
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
	const { ampLayout, height, width } = attributes;
	const showHeightNotice = ! height && ( 'fixed' === ampLayout || 'fixed-height' === ampLayout );
	const showWidthNotice = ! width && 'fixed' === ampLayout;

	return [
		<SelectControl
			key="ampLayout"
			label={ __( 'Layout', 'amp' ) }
			value={ ampLayout }
			options={ ampLayoutOptions }
			onChange={ value => ( setAttributes( { ampLayout: value } ) ) }
		/>,
		showWidthNotice && (
			<Notice key="showWidthNotice" status="error" isDismissible={ false }>
				{
					wp.i18n.sprintf(
						/* translators: %s is the layout name */
						__( 'Width is required for %s layout', 'amp' ),
						ampLayout
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
						/* translators: %s is the layout name */
						__( 'Height is required for %s layout', 'amp' ),
						ampLayout
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

/**
 * Animation controls for AMP Story layout blocks'.
 *
 * @param {Function} setAttributes Set Attributes.
 * @param {Object} attributes Props.
 * @return {[XML,*,XML,*,XML]} Controls.
 */
export function getAmpStoryAnimationControls( setAttributes, attributes ) {
	return [
		<SelectControl
			key="animation"
			label={ __( 'Animation Type', 'amp' ) }
			value={ attributes.animationType }
			options={ [
				{
					value: '',
					label: __( 'None', 'amp' )
				},
				{
					value: 'drop',
					label: __( 'Drop', 'amp' )
				},
				{
					value: 'fade-in',
					label: __( 'Fade In', 'amp' )
				},
				{
					value: 'fly-in-bottom',
					label: __( 'Fly In Bottom', 'amp' )
				},
				{
					value: 'fly-in-left',
					label: __( 'Fly In Left', 'amp' )
				},
				{
					value: 'fly-in-right',
					label: __( 'Fly In Right', 'amp' )
				},
				{
					value: 'fly-in-top',
					label: __( 'Fly In Top', 'amp' )
				},
				{
					value: 'pulse',
					label: __( 'Pulse', 'amp' )
				},
				{
					value: 'rotate-in-left',
					label: __( 'Rotate In Left', 'amp' )
				},
				{
					value: 'rotate-in-right',
					label: __( 'Rotate In Right', 'amp' )
				},
				{
					value: 'twirl-in',
					label: __( 'Twirl In', 'amp' )
				},
				{
					value: 'whoosh-in-left',
					label: __( 'Whoosh In Left', 'amp' )
				},
				{
					value: 'whoosh-in-right',
					label: __( 'Whoosh In Right', 'amp' )
				},
				{
					value: 'pan-left',
					label: __( 'Pan Left', 'amp' )
				},
				{
					value: 'pan-right',
					label: __( 'Pan Right', 'amp' )
				},
				{
					value: 'pan-down',
					label: __( 'Pan Down', 'amp' )
				},
				{
					value: 'pan-up',
					label: __( 'Pan Up', 'amp' )
				},
				{
					value: 'zoom-in',
					label: __( 'Zoom In', 'amp' )
				},
				{
					value: 'zoom-out',
					label: __( 'Zoom Out', 'amp' )
				}
			] }
			onChange={ value => ( setAttributes( { animationType: value } ) ) }
		/>,
		<RangeControl
			key='duration'
			label={ __( 'Duration (ms)' ) }
			value={ attributes.animationDuration }
			onChange={ ( value ) => setAttributes( { animationDuration: value } ) }
			min='0'
			max='5000'
		/>,
		<RangeControl
			key='delay'
			label={ __( 'Delay (ms)' ) }
			value={ attributes.animationDelay }
			onChange={ ( value ) => setAttributes( { animationDelay: value } ) }
			min='0'
			max='5000'
		/>
	];
}
