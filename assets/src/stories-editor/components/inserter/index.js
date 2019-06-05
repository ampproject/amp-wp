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

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Dropdown, IconButton } from '@wordpress/components';
import { Component } from '@wordpress/element';
import { compose, ifCondition } from '@wordpress/compose';
import { withSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import InserterMenu from './menu';

const defaultRenderToggle = ( { onToggle, disabled, isOpen } ) => (
	<IconButton
		icon="insert"
		label={ __( 'Add block', 'amp' ) }
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

class Inserter extends Component {
	constructor() {
		super( ...arguments );

		this.onToggle = this.onToggle.bind( this );
		this.renderToggle = this.renderToggle.bind( this );
		this.renderContent = this.renderContent.bind( this );
	}

	onToggle( isOpen ) {
		const { onToggle } = this.props;

		// Surface toggle callback to parent component
		if ( onToggle ) {
			onToggle( isOpen );
		}
	}

	/**
	 * Render callback to display Dropdown toggle element.
	 *
	 * @param {Function} onToggle Callback to invoke when toggle is
	 *                                    pressed.
	 * @param {boolean}  isOpen   Whether dropdown is currently open.
	 *
	 * @return {WPElement} Dropdown toggle element.
	 */
	renderToggle( { onToggle, isOpen } ) {
		const {
			disabled,
			renderToggle = defaultRenderToggle,
		} = this.props;

		return renderToggle( { onToggle, isOpen, disabled } );
	}

	/**
	 * Render callback to display Dropdown content element.
	 *
	 * @param {Function} onClose Callback to invoke when dropdown is
	 *                                   closed.
	 *
	 * @return {WPElement} Dropdown content element.
	 */
	renderContent( { onClose } ) {
		const { rootClientId, clientId, isAppender } = this.props;

		return (
			<InserterMenu
				onSelect={ onClose }
				rootClientId={ rootClientId }
				clientId={ clientId }
				isAppender={ isAppender }
			/>
		);
	}

	render() {
		const { position } = this.props;

		return (
			<Dropdown
				className="editor-inserter block-editor-inserter"
				contentClassName="editor-inserter__popover block-editor-inserter__popover"
				position={ position }
				onToggle={ this.onToggle }
				expandOnMobile
				headerTitle={ __( 'Add a block', 'amp' ) }
				renderToggle={ this.renderToggle }
				renderContent={ this.renderContent }
			/>
		);
	}
}

Inserter.propTypes = {
	onToggle: PropTypes.func,
	disabled: PropTypes.bool,
	renderToggle: PropTypes.func,
	position: PropTypes.string,
	rootClientId: PropTypes.string,
	clientId: PropTypes.string,
	isAppender: PropTypes.bool,
};

const applyWithSelect = withSelect( ( select ) => {
	const { isReordering } = select( 'amp/story' );

	return {
		isReordering: isReordering(),
	};
} );

export default compose(
	applyWithSelect,
	ifCondition(
		( { isReordering } ) => ! isReordering
	),
)( Inserter );
