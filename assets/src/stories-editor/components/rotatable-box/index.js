/**
 * External dependencies
 */
import classnames from 'classnames';
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useState, useEffect, useRef } from '@wordpress/element';
import { withSpokenMessages, Button } from '@wordpress/components';
import { ESCAPE, LEFT, RIGHT, ENTER } from '@wordpress/keycodes';
import { __, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { findClosestSnap } from '../../helpers';
import './edit.css';

const RotatableBox = ( { angle, initialAngle, blockElementId, className, speak, onRotateStart, onRotate, onRotateStop, snap, snapGap, children } ) => {
	const [ isRotating, setIsRotating ] = useState( false );
	const [ currentAngle, setAngle ] = useState( angle );

	const elementRef = useRef( null );

	useEffect( () => {
		elementRef.current = document.getElementById( blockElementId );

		document.addEventListener( 'mousemove', onMouseMove );
		document.addEventListener( 'mouseup', onMouseUp );
		document.addEventListener( 'keyUp', onKeyUp );

		return () => {
			document.removeEventListener( 'mousemove', onMouseMove );
			document.removeEventListener( 'mouseup', onMouseUp );
			document.removeEventListener( 'keyUp', onKeyUp );
		};
	}, [ blockElementId ] );

	const onKeyUp = ( e ) => {
		if ( ! isRotating ) {
			return;
		}

		e.preventDefault();

		const { keyCode } = e;

		if ( ESCAPE === keyCode ) {
			elementRef.current.classList.remove( 'is-rotating' );
			elementRef.current.style.transform = `rotate(${ initialAngle }deg)`;

			setIsRotating( false );
			setAngle( initialAngle );

			if ( onRotateStop ) {
				onRotateStop( e, initialAngle );
			}
		} else if ( LEFT === keyCode || RIGHT === keyCode ) {
			let newAngle = LEFT === keyCode ? currentAngle - 30 : currentAngle + 30;
			if ( newAngle > 360 ) {
				newAngle -= 360;
			} else if ( newAngle <= -360 ) {
				newAngle += 360;
			}

			elementRef.current.style.transform = `rotate(${ newAngle }deg)`;

			/* translators: %s: degrees */
			speak( sprintf( __( 'Rotating block by %s degrees', 'amp' ), newAngle ) );

			setAngle( newAngle );

			if ( onRotate ) {
				onRotate( e, newAngle );
			}
		} else if ( ENTER === keyCode ) {
			/* translators: %s: degrees */
			speak( sprintf( __( 'Saving block rotation of %s degrees', 'amp' ), currentAngle ) );

			if ( onRotateStop ) {
				onRotateStop( e );
			}
		}
	};

	const onMouseDown = ( e ) => {
		if ( isRotating ) {
			return;
		}

		const isRightClick = ( e.button && 2 === e.button );

		if ( isRightClick ) {
			return;
		}

		e.preventDefault();

		elementRef.current.classList.add( 'is-rotating' );

		setIsRotating( true );

		if ( onRotateStart ) {
			onRotateStart( e );
		}
	};

	const onMouseMove = ( e ) => {
		if ( ! isRotating ) {
			return;
		}

		e.preventDefault();

		elementRef.current.classList.add( 'is-rotating' );

		const { top, left, width, height } = elementRef.current.getBoundingClientRect();

		const centerX = left + ( width / 2 );
		const centerY = top + ( height / 2 );

		const x = e.clientX - centerX;
		const y = e.clientY - centerY;

		const rad2deg = ( 180 / Math.PI );
		let newAngle = Math.ceil( -( rad2deg * Math.atan2( x, y ) ) );

		newAngle = findClosestSnap( newAngle, snap, snapGap );

		if ( currentAngle === newAngle ) {
			return;
		}

		elementRef.current.style.transform = `rotate(${ newAngle }deg)`;

		setAngle( newAngle );

		if ( onRotate ) {
			onRotate( e, newAngle );
		}
	};

	const onMouseUp = ( e ) => {
		if ( ! isRotating ) {
			return;
		}

		e.preventDefault();

		elementRef.current.classList.remove( 'is-rotating' );
		elementRef.current.style.transform = `rotate(${ currentAngle }deg)`;

		setIsRotating( false );

		if ( onRotateStop ) {
			onRotateStop( e, currentAngle );
		}
	};

	return (
		<div
			className={ classnames( className, { 'is-rotating': isRotating } ) }
		>
			<div className="rotatable-box-wrap">
				<Button
					role="switch"
					aria-checked={ isRotating }
					onMouseDown={ onMouseDown }
					className="rotatable-box-wrap__handle"
				>
					<span className="screen-reader-text">
						{ __( 'Rotate Block', 'amp' ) }
					</span>
				</Button>
			</div>
			{ children }
		</div>
	);
};

RotatableBox.defaultProps = {
	angle: 0,
	initialAngle: 0,
	snapGap: 0,
};

RotatableBox.propTypes = {
	blockElementId: PropTypes.string.isRequired,
	className: PropTypes.string,
	angle: PropTypes.number,
	initialAngle: PropTypes.number,
	speak: PropTypes.func.isRequired,
	onRotateStart: PropTypes.func,
	onRotate: PropTypes.func,
	onRotateStop: PropTypes.func,
	children: PropTypes.any.isRequired,
	snap: PropTypes.oneOfType( [ PropTypes.arrayOf( PropTypes.number ), PropTypes.func ] ),
	snapGap: PropTypes.number,
};

export default withSpokenMessages( RotatableBox );
