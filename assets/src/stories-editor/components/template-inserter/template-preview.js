/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { withSafeTimeout } from '@wordpress/compose';
import { Spinner } from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { BlockPreview } from '../';

const TemplatePreview = ( { item } ) => {
	const [ shouldLoad, setShouldLoad ] = useState( false );

	useEffect( () => {
		if ( ! shouldLoad ) {
			// @todo Look into React Concurrent mode to replace this once it gets available.
			// Set timeout to cause a small latency between loading the templates, otherwise they all try to load instantly and cause a lag.
			this.props.setTimeout( () => {
				setShouldLoad( true );
			}, 100 );
		}
	}, [ shouldLoad ] );

	if ( ! shouldLoad ) {
		return <Spinner />;
	}

	return (
		<BlockPreview
			name="core/block"
			attributes={ { ref: item.id } }
		/>
	);
};

TemplatePreview.propTypes = {
	item: PropTypes.shape( {
		id: PropTypes.number.isRequired,
	} ).isRequired,
	setTimeout: PropTypes.func.isRequired,
};

export default withSafeTimeout( TemplatePreview );
