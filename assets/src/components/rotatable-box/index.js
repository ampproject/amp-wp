/**
 * WordPress dependencies
 */
import { Component, createRef } from '@wordpress/element';

class RotatableBox extends Component {
	constructor( props ) {
		super( props );

		this.state = {
			isRotating: false,
			angle: props.angle || 0,
		};

		this.elementRef = createRef();

		this.onRotateStart = this.onRotateStart.bind( this );
		this.onRotate = this.onRotate.bind( this );
		this.onRotateStop = this.onRotateStop.bind( this );
	}

	onRotateStart( e ) {
		if ( ( e.button && 2 === e.button ) || ( e.which && 3 === e.which ) ) {
			return;
		}

		e.preventDefault();

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

		const rad2deg = ( 180 / Math.PI );

		const centerX = left + ( width / 2 );
		const centerY = top + ( height / 2 );

		const x = e.clientX - centerX;
		const y = e.clientY - centerY;

		const angle = rad2deg * Math.atan2( y, x );

		const normalizedAngle = parseInt( angle > 0 ? angle : 360 + angle );

		this.elementRef.current.style.transform = `rotate(${ normalizedAngle }deg)`;

		this.setState(
			{
				angle: normalizedAngle,
			},
			() => this.props.onRotate && this.props.onRotate( e, this.elementRef.current, normalizedAngle )
		);
	}

	onRotateStop( e ) {
		if ( ! this.state.isRotating ) {
			return;
		}

		e.preventDefault();

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
				onMouseMove={ this.onRotate }
				onMouseUp={ this.onRotateStop }
			>
				<div className="rotatable-box-wrap">
					<div
						onMouseDown={ this.onRotateStart }
						className="rotatable-box-wrap__handle"
					/>
				</div>
				{ this.props.children }
			</div>
		);
	}
}

export default RotatableBox;
