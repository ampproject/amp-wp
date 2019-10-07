/**
 * This is an almost 1:1 copy of the Inserter component in @wordpress/block-editor.
 *
 * It has been included here in a slightly modified way, namely without the hasItems
 * limitation and with an additional restriction to hide the inserter while reordering is in progress.
 *
 * In addition, this component also contains prop types.
 */

/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import { ReactElement } from 'react';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Dropdown, IconButton } from '@wordpress/components';
import { useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import InserterMenu from './menu'; // eslint-disable-line import/no-named-as-default
import './edit.css';

const defaultRenderToggle = ( { onToggle, disabled, isOpen } ) => (
	<IconButton
		icon="insert"
		label={ __( 'Add element', 'amp' ) }
		labelPosition="bottom"
		onClick={ onToggle }
		className="editor-inserter__toggle block-editor-inserter__toggle"
		aria-haspopup="true"
		aria-expanded={ isOpen }
		disabled={ disabled }
	/>
);

defaultRenderToggle.propTypes = {
	onToggle: PropTypes.func,
	disabled: PropTypes.bool,
	isOpen: PropTypes.bool,
};

const Inserter = ( props ) => {
	const { position, rootClientId, clientId, isAppender } = props;

	const disabled = useSelect( ( select ) => {
		// As used in <HeaderToolbar> component
		const showInserter = select( 'core/edit-post' ).getEditorMode() === 'visual' && select( 'core/editor' ).getEditorSettings().richEditingEnabled;

		return ! showInserter;
	} );

	const isReordering = useSelect( ( select ) => select( 'amp/story' ).isReordering(), [] );

	if ( isReordering ) {
		return null;
	}

	const onToggle = ( isOpen ) => {
		// Surface toggle callback to parent component
		if ( props.onToggle ) {
			props.onToggle( isOpen );
		}
	};

	/**
	 * Render callback to display Dropdown toggle element.
	 *
	 * @param {Object} args Callback args.
	 * @param {Function} args.onToggle Callback to invoke when toggle is pressed.
	 * @param {boolean} args.isOpen Whether dropdown is currently open.
	 *
	 * @return {ReactElement} Dropdown toggle element.
	 */
	const renderToggle = ( { onToggle: toggle, isOpen } ) => {
		const render = props.renderToggle || defaultRenderToggle;
		return render( { onToggle: toggle, isOpen, disabled } );
	};

	/**
	 * Render callback to display Dropdown content element.
	 *
	 * @param {Function} onClose Callback to invoke when dropdown is closed.
	 *
	 * @return {ReactElement} Dropdown content element.
	 */
	const renderContent = ( { onClose: onSelect } ) => {
		return (
			<InserterMenu
				onSelect={ onSelect }
				rootClientId={ rootClientId }
				clientId={ clientId }
				isAppender={ isAppender }
			/>
		);
	};

	renderContent.propTypes = {
		onClose: PropTypes.func,
	};

	return (
		<Dropdown
			className="editor-inserter block-editor-inserter"
			contentClassName="editor-inserter__popover block-editor-inserter__popover"
			position={ position }
			onToggle={ onToggle }
			expandOnMobile
			headerTitle={ __( 'Add element', 'amp' ) }
			renderToggle={ renderToggle }
			renderContent={ renderContent }
		/>
	);
};

Inserter.propTypes = {
	onToggle: PropTypes.func,
	renderToggle: PropTypes.func,
	position: PropTypes.string,
	rootClientId: PropTypes.string,
	clientId: PropTypes.string,
	isAppender: PropTypes.bool,
};

export default Inserter;
