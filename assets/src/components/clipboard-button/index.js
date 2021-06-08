/**
 * WordPress dependencies
 */
import { useRef, useEffect } from '@wordpress/element';
import { useCopyToClipboard } from '@wordpress/compose';
import { Button } from '@wordpress/components';

/**
 * External dependencies
 */
import PropTypes from 'prop-types';

const TIMEOUT = 4000;

// Migrated from @wordpress/components.
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
