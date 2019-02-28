/* global ReactDOM */

/**
 * External dependencies
 */
import uuid from 'uuid/v4';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import {
	InnerBlocks,
	PanelColorSettings,
	InspectorControls,
} from '@wordpress/editor';
import { Fragment, Component } from '@wordpress/element';
import { select } from '@wordpress/data';

/**
 * Internal dependencies
 */
import BlockNavigation from './block-navigation';
import { ALLOWED_BLOCKS } from '../../constants';

const {
	hasSelectedInnerBlock,
	getSelectedBlockClientId,
} = select( 'core/editor' );

const TEMPLATE = [
	[ 'amp/amp-story-text' ],
];

export default class EditPage extends Component {
	constructor( props ) {
		// Call parent constructor.
		super( props );

		if ( ! props.attributes.id ) {
			this.props.setAttributes( { id: uuid() } );
		}

		this.onChangeBackgroundColor = this.onChangeBackgroundColor.bind( this );
	}

	maybeAddBlockNavigation() {
		// If no blocks are selected or if it's the current page, change the view.
		if ( ! getSelectedBlockClientId() || this.props.clientId === getSelectedBlockClientId() || hasSelectedInnerBlock( this.props.clientId, true ) ) {
			const editLayout = document.getElementsByClassName( 'edit-post-layout' );
			if ( editLayout.length ) {
				const blockNav = document.getElementById( 'amp-root-navigation' );
				if ( ! blockNav ) {
					const navWrapper = document.createElement( 'div' );
					navWrapper.id = 'amp-root-navigation';
					editLayout[ 0 ].appendChild( navWrapper );
				}
				ReactDOM.render(
					<div key="layerManager" className="editor-selectors">
						<BlockNavigation />
					</div>,
					document.getElementById( 'amp-root-navigation' )
				);
			}
		}
	}

	componentDidMount() {
		this.maybeAddBlockNavigation();
	}

	componentDidUpdate() {
		// @todo Check if there is a better way to do this without calling it on both componentDidMount and componentDidUpdate.
		this.maybeAddBlockNavigation();
	}

	onChangeBackgroundColor( newBackgroundColor ) {
		this.props.setAttributes( { backgroundColor: newBackgroundColor } );
	}

	render() {
		const props = this.props;
		const { attributes } = props;

		return (
			<Fragment>
				<InspectorControls key="controls">
					<PanelColorSettings
						title={ __( 'Background Color Settings', 'amp' ) }
						initialOpen={ false }
						colorSettings={ [
							{
								value: attributes.backgroundColor,
								onChange: this.onChangeBackgroundColor,
								label: __( 'Background Color', 'amp' ),
							},
						] }
					/>
				</InspectorControls>
				<div key="contents" style={ { backgroundColor: attributes.backgroundColor } }>
					<InnerBlocks template={ TEMPLATE } allowedBlocks={ ALLOWED_BLOCKS } />
				</div>
			</Fragment>
		);
	}
}
