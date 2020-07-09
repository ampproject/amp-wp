/**
 * WordPress dependencies
 */
import { useRef, useEffect, useContext } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { Options } from '../components/options-context-provider';

/**
 * Side effect component hiding and showing theme support form input fields depending on the state of other selections on the page.
 *
 * @todo The external DOM components this manipulates should eventually be converted to React.
 */
export function SupportedTemplatesVisibility() {
	const { editedOptions } = useContext( Options );

	const { all_templates_supported: allTemplatesSupported, reader_theme: readerTheme, theme_support: themeSupport } = editedOptions || {};

	const supportedPostTypesTitle = useRef( document.querySelector( '#all_templates_supported_fieldset, #supported_post_types_fieldset > .title' ) );
	const supportedPostTypesFieldset = useRef( document.getElementById( 'supported_post_types_fieldset' ) );
	const supportedTemplatesFieldset = useRef( document.getElementById( 'supported_templates_fieldset' ) );
	const supportedTemplateInputs = useRef( [ ...document.querySelectorAll( '#supported_templates_fieldset input[type=checkbox]' ) ] );
	const supportedPostTypeInputs = useRef( [ ...document.querySelectorAll( '#supported_post_types_fieldset input[type=checkbox]' ) ] );

	/**
	 * Show/hide settings features depending on options on the page.
	 */
	useEffect( () => {
		supportedPostTypesTitle.current.classList.toggle(
			'hidden',
			'reader' === themeSupport,
		);

		let supportedPostTypesHidden = allTemplatesSupported;
		if ( 'reader' === themeSupport && 'legacy' === readerTheme ) {
			supportedPostTypesHidden = false;
		}
		supportedPostTypesFieldset.current.classList.toggle(
			'hidden',
			supportedPostTypesHidden,
		);

		supportedTemplatesFieldset.current.classList.toggle(
			'hidden',
			allTemplatesSupported || ( 'reader' === themeSupport && 'legacy' === readerTheme ),
		);
	}, [ allTemplatesSupported, readerTheme, themeSupport ] );

	/**
	 * Check or uncheck all of a checkbox's child checkboxes when it is checked or unchecked.
	 */
	useEffect( () => {
		const listenerCallback = ( event ) => {
			if ( supportedTemplateInputs.current.includes( event.target ) ) {
				const checked = event.target.checked;
				[ ...event.target.parentElement.querySelectorAll( 'input[type=checkbox]' ) ].forEach( ( inputElement ) => {
					inputElement.checked = checked;
				} );
			} else if ( supportedPostTypeInputs.current.includes( event.target ) ) {
				const hiddenInput = event.target.closest( 'li' ).querySelector( 'input[type=hidden][name]' );
				hiddenInput.value = event.target.checked ? 'true' : 'false';
			}
		};

		global.addEventListener( 'click', listenerCallback );

		return () => {
			global.removeEventListener( 'click', listenerCallback );
		};
	}, [] );

	return null;
}
