/**
 * WordPress dependencies
 */
import { Notice } from '@wordpress/components';
import { __, _n, sprintf } from '@wordpress/i18n';
import { createHigherOrderComponent } from '@wordpress/compose';
import { withSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { ValidationErrorMessage } from '../../';
import './edit.css';

const applyWithSelect = withSelect( ( select, { clientId } ) => {
	const { getBlockValidationErrors } = select( 'amp/block-validation' );

	const blockValidationErrors = getBlockValidationErrors( clientId );

	return {
		blockValidationErrors: blockValidationErrors.length ? blockValidationErrors : undefined,
	};
} );

/**
 * Wraps the edit() method of a block, and conditionally adds a Notice.
 *
 * @param {Function} BlockEdit - The original edit() method of the block.
 * @return {Function} The edit() method, conditionally wrapped in a notice for AMP validation error(s).
 */
export default createHigherOrderComponent(
	( BlockEdit ) => {
		return applyWithSelect( ( props ) => {
			const { blockValidationErrors, onReplace } = props;

			if ( ! blockValidationErrors ) {
				return <BlockEdit { ...props } />;
			}

			const errorCount = blockValidationErrors.length;

			const actions = [
				{
					label: __( 'Remove Element', 'amp' ),
					onClick: () => onReplace( [] ),
				},
			];

			return (
				<>
					<Notice
						status="warning"
						isDismissible={ false }
						actions={ actions }
					>
						<details className="amp-block-validation-errors">
							<summary className="amp-block-validation-errors__summary">
								{ sprintf(
									_n(
										'There is %s issue from AMP validation.',
										'There are %s issues from AMP validation.',
										errorCount,
										'amp'
									),
									errorCount,
								) }
							</summary>
							<ul className="amp-block-validation-errors__list">
								{ blockValidationErrors.map( ( error, key ) => {
									return (
										<li key={ key }>
											<ValidationErrorMessage { ...error } />
										</li>
									);
								} ) }
							</ul>
						</details>
					</Notice>
					<BlockEdit { ...props } />
				</>
			);
		} );
	},
	'withValidationErrorNotice'
);
