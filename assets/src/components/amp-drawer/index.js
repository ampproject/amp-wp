/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { PanelBody, VisuallyHidden } from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { Selectable } from '../selectable';
import './style.css';

export const HANDLE_TYPE_FULL_WIDTH = 'full-width';
export const HANDLE_TYPE_RIGHT = 'right';

/**
 * Wrapper for the core PanelBody component with styles applied.
 *
 * @param {Object}  props             Component props.
 * @param {any}     props.children    PanelBody content.
 * @param {string}  props.className   Extra CSS classes for the wrapper component.
 * @param {any}     props.heading     Content for the drawer heading.
 * @param {string}  props.id          A unique ID for the component.
 * @param {boolean} props.initialOpen Whether the drawer should be initially open.
 * @param {Object}  props.labelExtra  Optional. Extra content to display on the right side of the option label.
 * @param {boolean} props.selected    Whether to apply the selectable components selected CSS class.
 * @param {string}  props.hiddenTitle A title to go with the button that expands the drawer.
 * @param {string}  props.handleType  Display style for the drawer handle. Either 'full-width' or 'right'.
 */
export function AMPDrawer( {
	children = null,
	className,
	heading,
	handleType = HANDLE_TYPE_FULL_WIDTH,
	id,
	initialOpen = false,
	labelExtra = null,
	selected = false,
	hiddenTitle,
} ) {
	const [ opened, setOpened ] = useState( initialOpen );
	const [ resetStatus, setResetStatus ] = useState( null );

	/**
	 * Watch for changes to the panel body attributes and set opened state accordingly.
	 */
	useEffect( () => {
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

	// Force a rerender when initialOpen changes, only after the first render.
	useEffect( () => {
		if ( null === resetStatus ) {
			setResetStatus( 'waiting' );
			return;
		}

		setResetStatus( 'resetting' );
	// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [ initialOpen ] );

	/**
	 * After the `resetting` render, set status back to waiting.
	 */
	useEffect( () => {
		if ( 'resetting' === resetStatus ) {
			setResetStatus( 'waiting' );
		}
	}, [ resetStatus ] );

	return (
		<Selectable
			id={ id }
			className={
				classnames(
					'amp-drawer',
					`amp-drawer--handle-type-${ handleType }`,
					className,
					opened ? 'amp-drawer--opened' : '',
				)
			}
			selected={ selected }
		>
			{ handleType === HANDLE_TYPE_RIGHT && (
				<>
					<div className="amp-drawer__heading">
						{ heading }
					</div>
					{ labelExtra && (
						<div className="amp-drawer__label-extra">
							{ labelExtra }
						</div>
					) }
				</>
			) }
			{ 'resetting' !== resetStatus && (
				<PanelBody
					title={ handleType === HANDLE_TYPE_RIGHT ? (
						<VisuallyHidden as="span">
							{ hiddenTitle }
						</VisuallyHidden>
					) : (
						<>
							<div className="amp-drawer__heading">
								{ heading }
							</div>
							{ labelExtra && (
								<div className="amp-drawer__label-extra">
									{ labelExtra }
								</div>
							) }
						</>
					) }
					className="amp-drawer__panel-body"
					initialOpen={ initialOpen }
				>
					<div className="amp-drawer__panel-body-inner">
						{ children }
					</div>
				</PanelBody>
			) }
		</Selectable>
	);
}

AMPDrawer.propTypes = {
	children: PropTypes.any,
	className: PropTypes.string,
	handleType: PropTypes.oneOf( [ HANDLE_TYPE_FULL_WIDTH, HANDLE_TYPE_RIGHT ] ),
	heading: PropTypes.node.isRequired,
	hiddenTitle: PropTypes.node.isRequired,
	id: PropTypes.string.isRequired,
	initialOpen: PropTypes.bool,
	labelExtra: PropTypes.node,
	selected: PropTypes.bool,
};
