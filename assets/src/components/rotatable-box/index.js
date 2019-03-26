/**
 * WordPress dependencies
 */
import { Component, createRef } from '@wordpress/element';
import { withSpokenMessages, Button } from '@wordpress/components';
import { compose, withGlobalEvents } from '@wordpress/compose';
import { ESCAPE, LEFT, RIGHT } from '@wordpress/keycodes';
import { __, sprintf } from '@wordpress/i18n';

class RotatableBox extends Component {
	constructor( props ) {
		super( props );

		this.state = {
			isRotating: false,
			initialAngle: props.angle || 0,
			angle: 0,
		};

		this.elementRef = createRef();

		this.onRotateStart = this.onRotateStart.bind( this );
		this.onRotate = this.onRotate.bind( this );
		this.onRotateStop = this.onRotateStop.bind( this );
		this.onKeyUp = this.onKeyUp.bind( this );
	}

	onKeyUp( e ) {
		if ( ! this.state.isRotating ) {
			return;
		}

		e.preventDefault();

		const { keyCode } = e;

		if ( ESCAPE === keyCode ) {
			this.elementRef.current.style.transform = 'rotate(0deg)';

			this.setState(
				{
					isRotating: false,
					angle: this.state.initialAngle,
				},
				() => this.props.onRotateStop && this.props.onRotateStop( e, this.elementRef.current, this.state.initialAngle )
			);
		} else if ( LEFT === keyCode || RIGHT === keyCode ) {
			const angle = LEFT === keyCode ? this.state.angle - 30 : this.state.angle + 30;

			this.elementRef.current.style.transform = `rotate(${ angle }deg)`;

			this.props.speak( sprintf( __( 'Rotating block by %s degrees', 'amp' ), angle ) );

			this.setState(
				{
					angle,
				},
				() => this.props.onRotate && this.props.onRotate( e, this.elementRef.current, angle )
			);
		}
	}

	onRotateStart( e ) {
		if ( ( e.button && 2 === e.button ) || ( e.which && 3 === e.which ) ) {
			return;
		}

		e.preventDefault();

		this.elementRef.current.style.transform = `rotate(${ this.state.angle }deg)`;

		this.setState(
			{
				isRotating: true,
			},
			() => this.props.onRotateStart && this.props.onRotateStart( e, this.elementRef.current )
		);
	}

	onRotate( e ) {
		if ( ! this.state.isRotating ) {
			return;
		}

		e.preventDefault();

		const { top, left, width, height } = this.elementRef.current.getBoundingClientRect();

		const centerX = left + ( width / 2 );
		const centerY = top + ( height / 2 );

		const x = e.clientX - centerX;
		const y = e.clientY - centerY;

		const rad2deg = ( 180 / Math.PI );
		const angle = -( rad2deg * Math.atan2( x, y ) );

		this.elementRef.current.style.transform = `rotate(${ angle }deg)`;

		this.setState(
			{
				angle,
			},
			() => this.props.onRotate && this.props.onRotate( e, this.elementRef.current, angle )
		);
	}

	onRotateStop( e ) {
		if ( ! this.state.isRotating ) {
			return;
		}

		e.preventDefault();

		this.elementRef.current.style.transform = 'rotate(0deg)';

		this.setState(
			{
				isRotating: false,
			},
			() => this.props.onRotateStop && this.props.onRotateStop( e, this.elementRef.current, this.state.angle )
		);
	}

	render() {
		return (
			<div
				className={ this.props.className }
				ref={ this.elementRef }
			>
				<div className="rotatable-box-wrap">
					<Button
						role="switch"
						aria-checked={ this.state.isRotating }
						onMouseDown={ this.onRotateStart }
						className="rotatable-box-wrap__handle"
					>
						<span className="screen-reader-text">
							{ __( 'Rotate Block', 'amp' ) }
						</span>
					</Button>
				</div>
				{ this.props.children }
			</div>
		);
	}
}

export default compose(
	withSpokenMessages,
	withGlobalEvents( {
		mousemove: 'onRotate',
		mouseup: 'onRotateStop',
		keyup: 'onKeyUp',
	} ),
)( RotatableBox );
