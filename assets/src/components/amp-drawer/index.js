/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { PanelBody } from '@wordpress/components';
import { useEffect, useState, useLayoutEffect } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { Selectable } from '../selectable';
import './style.css';

export function AMPDrawer( { children = null, className, heading, id, initialOpen = false, selected = false, hiddenTitle } ) {
	const [ opened, setOpened ] = useState( initialOpen );

	/**
	 * Watch for changes to the panel body attributes and set opened state accordingly.
	 */
	useLayoutEffect( () => {
		const mutationCallback = ( [ mutation ] ) => {
			if ( mutation.target.classList.contains( 'is-opened' ) && ! opened ) {
				setOpened( true );
			} else if ( opened ) {
				setOpened( false );
			}
		};

		const observer = new global.MutationObserver( mutationCallback );

		const panel = document.getElementById( id )?.querySelector( '.components-panel__body' );
		if ( panel ) {
			observer.observe( panel, { attributes: true } );
		}

		return () => {
			observer.disconnect();
		};
	}, [ id, opened ] );

	return (
		<Selectable
			id={ id }
			className={
				[
					'amp-drawer',
					className ? className : '',
					opened ? 'amp-drawer--opened' : '',
				]
					.filter( ( item ) => item )
					.join( ' ' )
			}
			selected={ selected }
		>
			<div className="amp-drawer__heading">
				{ heading }
			</div>
			<PanelBody
				title={ (
					<span className="components-visually-hidden">
						{ hiddenTitle }
					</span>
				) }
				className="amp-drawer__panel-body"
				initialOpen={ initialOpen }
			>
				<div className="amp-drawer__panel-body-inner">
					{ children }
				</div>
			</PanelBody>
		</Selectable>
	);
}

AMPDrawer.propTypes = {
	children: PropTypes.any,
	className: PropTypes.string,
	heading: PropTypes.node.isRequired,
	hiddenTitle: PropTypes.node.isRequired,
	id: PropTypes.string.isRequired,
	initialOpen: PropTypes.bool,
	selected: PropTypes.bool,
};
