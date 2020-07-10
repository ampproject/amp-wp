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
	const supportedPostTypesFieldset = document.getElementById( 'supported_post_types_fieldset' )
	const supportedTemplatesFieldset = document.getElementById( 'supported_templates_fieldset' )
	const supportedPostTypesFieldsetRef = useRef( supportedPostTypesFieldset );
	const supportedTemplatesFieldsetRef = useRef( supportedTemplatesFieldset );
	const supportedTemplateInputs = useRef( [ ...supportedTemplatesFieldset.querySelectorAll( 'input[type=checkbox]' ) ] );
	const supportedPostTypeInputs = useRef( [ ...supportedPostTypesFieldset.querySelectorAll( 'input[type=checkbox]' ) ] );

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
		supportedPostTypesFieldsetRef.current.classList.toggle(
			'hidden',
			supportedPostTypesHidden,
		);

		supportedTemplatesFieldsetRef.current.classList.toggle(
			'hidden',
			allTemplatesSupported || ( 'reader' === themeSupport && 'legacy' === readerTheme ),
		);
	}, [ allTemplatesSupported, readerTheme, themeSupport ] );

	const handleChecked = ( checkboxInput ) => {
		const hiddenInput = checkboxInput.nextElementSibling;
		hiddenInput.value = JSON.stringify( checkboxInput.checked );
	};

	/**
	 * Check or uncheck all of a checkbox's child checkboxes when it is checked or unchecked.
	 */
	useEffect( () => {
		const listenerCallback = ( event ) => {
			const isSupportedTemplateCheckbox = supportedTemplateInputs.current.includes( event.target );
			const isPostTypeSupportCheckbox = supportedPostTypeInputs.current.includes( event.target );

			if ( ! isSupportedTemplateCheckbox && ! isPostTypeSupportCheckbox ) {
				return;
			}

			if ( isSupportedTemplateCheckbox ) {
				const checked = event.target.checked;
				[ ...event.target.parentElement.querySelectorAll( 'input[type=checkbox]' ) ].forEach( ( inputElement ) => {
					inputElement.checked = checked;
					handleChecked( inputElement );
				} );
			}

			handleChecked( event.target );
		};

		global.addEventListener( 'click', listenerCallback );

		return () => {
			global.removeEventListener( 'click', listenerCallback );
		};
	}, [] );

	return null;
}
