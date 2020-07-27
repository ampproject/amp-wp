/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { PanelBody } from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { Selectable } from '../selectable';
import './style.css';

export function AMPDrawer( { children = null, className, heading, id, initialOpen, selected = false, hiddenTitle } ) {
	const [ opened, setOpened ] = useState( initialOpen );

	useEffect( () => {
		const mutationCallback = ( [ mutation ] ) => {
			if ( mutation.target.classList.contains( 'is-opened' ) ) {
				setOpened( true );
			} else {
				setOpened( false );
			}
		};

		const observer = new global.MutationObserver( mutationCallback );
		observer.observe( document.getElementById( id ).querySelector( '.components-panel__body' ), { attributes: true } );
	}, [ id ] );

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
