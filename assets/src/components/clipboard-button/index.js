/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useRef, useEffect } from '@wordpress/element';
import { useCopyToClipboard } from '@wordpress/compose';
import { Button } from '@wordpress/components';

const TIMEOUT = 4000;

// Adapted from @wordpress/components: <https://github.com/WordPress/gutenberg/blob/3c00d85b12ee45365e3ab329301a07312d99ffdf/packages/components/src/clipboard-button/index.js#L18-L69>.
export default function ClipboardButton( {
	children,
	onCopy,
	onFinishCopy,
	text,
	...buttonProps
} ) {
	const timeoutId = useRef();
	const ref = useCopyToClipboard( text, () => {
		if ( onCopy ) {
			onCopy();
		}

		clearTimeout( timeoutId.current );

		if ( onFinishCopy ) {
			timeoutId.current = setTimeout( () => onFinishCopy(), TIMEOUT );
		}
	} );

	useEffect( () => {
		clearTimeout( timeoutId.current );
	}, [] );

	// Workaround for inconsistent behavior in Safari, where <textarea> is not
	// the document.activeElement at the moment when the copy event fires.
	// This causes documentHasSelection() in the copy-handler component to
	// mistakenly override the ClipboardButton, and copy a serialized string
	// of the current block instead.
	const focusOnCopyEventTarget = ( event ) => {
		event.target.focus();
	};

	return (
		<Button
			{ ...buttonProps }
			className="components-clipboard-button"
			ref={ ref }
			onCopy={ focusOnCopyEventTarget }
		>
			{ children }
		</Button>
	);
}

ClipboardButton.propTypes = {
	children: PropTypes.any,
	onCopy: PropTypes.func,
	onFinishCopy: PropTypes.func,
	text: PropTypes.string.isRequired,
};
