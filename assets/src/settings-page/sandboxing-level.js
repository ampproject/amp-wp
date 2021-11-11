/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useContext } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { Options } from '../components/options-context-provider';
import { AMPDrawer } from '../components/amp-drawer';
import { STANDARD } from '../common/constants';

/**
 * Component rendering the sandboxing level.
 *
 * @param {Object} props                Component props.
 * @param {string} props.focusedSection Focused section.
 */
export function SandboxingLevel( { focusedSection } ) {
	const { updateOptions, editedOptions: {
		sandboxing_level: sandboxingLevel,
		theme_support: themeSupport,
	} } = useContext( Options );

	if ( ! sandboxingLevel || STANDARD !== themeSupport ) {
		return null;
	}

	return (
		<AMPDrawer
			heading={ (
				<h3>
					{ __( 'Sandboxing Level (Experimental)', 'amp' ) }
				</h3>
			) }
			hiddenTitle={ __( 'Sandboxing Level (Experimental)', 'amp' ) }
			id="sandboxing-level"
			initialOpen={ 'sandboxing-level' === focusedSection }
		>
			<p>
				{ __( 'Try out a more flexible AMP by generating pages that use AMP components without requiring AMP validity! By selecting a sandboxing level, you are indicating the minimum degree of sanitization. For example, if you selected level 1 but have a page without any POST form and no custom scripts, it will still be served as valid AMP, the same as if you had selected level 3.', 'amp' ) }
			</p>
			<ol>
				<li>
					<input
						type="radio"
						id="sandboxing-level-1"
						checked={ 1 === sandboxingLevel }
						onChange={ () => {
							updateOptions( { sandboxing_level: 1 } );
						} }
					/>
					<label htmlFor="sandboxing-level-1">
						<strong>
							{ __( 'Loose:', 'amp' ) }
						</strong>
						{ ' ' + __( 'Do not remove any AMP-invalid markup by default, including custom scripts. CSS tree-shaking is disabled.', 'amp' ) }
					</label>
				</li>
				<li>
					<input
						type="radio"
						id="sandboxing-level-2"
						checked={ 2 === sandboxingLevel }
						onChange={ () => {
							updateOptions( { sandboxing_level: 2 } );
						} }
					/>
					<label htmlFor="sandboxing-level-2">
						<strong>
							{ __( 'Moderate:', 'amp' ) }
						</strong>
						{ ' ' + __( 'Remove non-AMP markup, but allow POST forms. CSS tree shaking is enabled.', 'amp' ) }
					</label>
				</li>
				<li>
					<input
						type="radio"
						id="sandboxing-level-3"
						checked={ 3 === sandboxingLevel }
						onChange={ () => {
							updateOptions( { sandboxing_level: 3 } );
						} }
					/>
					<label htmlFor="sandboxing-level-3">
						<strong>
							{ __( 'Strict:', 'amp' ) }
						</strong>
						{ ' ' + __( 'Require valid AMP.', 'amp' ) }
					</label>
				</li>

			</ol>
		</AMPDrawer>
	);
}
SandboxingLevel.propTypes = {
	focusedSection: PropTypes.string,
};
