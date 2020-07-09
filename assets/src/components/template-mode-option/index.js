/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useContext } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { Selectable } from '../selectable';
import { AMPInfo } from '../amp-info';
import { Standard } from '../svg/standard';
import { Transitional } from '../svg/transitional';
import { Reader } from '../svg/reader';
import { Options } from '../options-context-provider';

/**
 * Mode-specific illustration.
 *
 * @param {Object} props Component props.
 * @param {string} props.mode The template mode.
 */
function Illustration( { mode } ) {
	switch ( mode ) {
		case 'standard':
			return <Standard />;

		case 'transitional':
			return <Transitional />;

		case 'reader':
			return <Reader />;

		default:
			return null;
	}
}
Illustration.propTypes = {
	mode: PropTypes.string.isRequired,
};

/**
 * Gets the title for the mode.
 *
 * @param {string} mode The mode.
 */
function getTitle( mode ) {
	switch ( mode ) {
		case 'standard':
			return __( 'Standard', 'amp' );

		case 'transitional':
			return __( 'Transitional', 'amp' );

		case 'reader':
			return __( 'Reader', 'amp' );

		default:
			return null;
	}
}

/**
 * An individual mode selection component.
 *
 * @param {Object} props Component props.
 * @param {string|Object} props.children Section content.
 * @param {string} props.details Mode details.
 * @param {string} props.mode The template mode.
 * @param {boolean} props.previouslySelected Optional. Whether the option was selected previously.
 */
export function TemplateModeOption( { children, details, mode, previouslySelected = false } ) {
	const { editedOptions, updateOptions } = useContext( Options );
	const { theme_support: themeSupport } = editedOptions;

	const id = `template-mode-${ mode }`;

	return (
		<Selectable id={ `${ id }-container` } className="template-mode-selection" selected={ mode === themeSupport }>
			<label htmlFor={ id }>
				<div className="template-mode-selection__input-container">
					<input
						type="radio"
						id={ id }
						checked={ mode === themeSupport }
						onChange={ () => {
							updateOptions( { theme_support: mode } );
						} }
					/>
				</div>
				<div className="template-mode-selection__illustration">
					{ <Illustration mode={ mode } /> }
				</div>
				<div className="template-mode-selection__description">
					<h2>
						{ getTitle( mode ) }
					</h2>
					{ previouslySelected && (
						<AMPInfo>
							{ __( 'Previously selected', 'amp' ) }
						</AMPInfo>
					) }
				</div>
			</label>
			<div className="template-mode-selection__details">
				<p>
					<span dangerouslySetInnerHTML={ { __html: details } } />
					{ ' ' }
					{ /* @todo Temporary URL. */ }
					<a href="http://amp-wp.org" target="_blank" rel="noreferrer">
						{ __( 'Learn more.', 'amp' ) }
					</a>
				</p>
			</div>

			{ children }
		</Selectable>
	);
}

TemplateModeOption.propTypes = {
	children: PropTypes.any,
	details: PropTypes.string.isRequired,
	mode: PropTypes.string.isRequired,
	previouslySelected: PropTypes.bool,
};
