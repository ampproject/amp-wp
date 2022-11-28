/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useEffect, useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { Phone } from '../../../components/phone';

/**
 * Page preview component.
 *
 * @param {Object} props     Component props.
 * @param {string} props.url URL of the page to be previewed.
 */
export function Preview( { url } ) {
	const iframeRef = useRef( null );
	const [ isLoading, setIsLoading ] = useState( true );

	useEffect( () => {
		if ( ! iframeRef.current ) {
			return null;
		}

		const iframe = iframeRef.current;
		const onLoad = () => setIsLoading( false );

		iframe.addEventListener( 'load', onLoad );

		return () => {
			iframe.removeEventListener( 'load', onLoad );
		};
	}, [] );

	useEffect( () => {
		if ( url ) {
			setIsLoading( true );
		}
	}, [ url ] );

	return (
		<Phone isLoading={ isLoading }>
			<iframe
				className="done__preview-iframe"
				src={ url }
				ref={ iframeRef }
				title={ __( 'Site preview', 'amp' ) }
				name="amp-wizard-completion-preview"
			/>
		</Phone>
	);
}

Preview.propTypes = {
	url: PropTypes.string,
};
