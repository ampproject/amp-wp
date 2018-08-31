const { __ } = wp.i18n;
const {
	SelectControl,
	RangeControl
} = wp.components;

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
			label={ __( 'Duration (ms)', 'amp' ) }
			value={ parseInt( attributes.animationDuration ) }
			onChange={ ( value ) => {
				value = value + 'ms';
				setAttributes( { animationDuration: value } );
			} }
			min='0'
			max='5000'
		/>,
		<RangeControl
			key='delay'
			label={ __( 'Delay (ms)', 'amp' ) }
			value={ parseInt( attributes.animationDelay ) }
			onChange={ ( value ) => {
				value = value + 'ms';
				setAttributes( { animationDelay: value } );
			} }
			min='0'
			max='5000'
		/>
	];
}
