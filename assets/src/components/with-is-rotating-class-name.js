/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { createHigherOrderComponent } from '@wordpress/compose';
import { withSelect } from '@wordpress/data';

const applyWithSelect = withSelect( ( select, props ) => {
	const { getRotatingBlock } = select( 'amp/story' );

	return {
		isRotating: getRotatingBlock() === props.clientId,
	};
} );

/**
 * Higher-order component that adds an is-rotating class to a block.
 *
 * @return {Function} Higher-order component.
 */
export default createHigherOrderComponent(
	( BlockListBlock ) => {
		return applyWithSelect( ( props ) => {
			const { isRotating } = props;

			const wrapperProps = {
				...props.wrapperProps,
				className: classnames(
					props.className,
					{ 'is-rotating': isRotating },
				),
			};

			return (
				<BlockListBlock { ...props } { ...wrapperProps } />
			);
		} );
	},
	'withIsRotatingClassName'
);
