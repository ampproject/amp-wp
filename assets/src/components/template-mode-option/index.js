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
import './style.css';
import { READER, STANDARD, TRANSITIONAL } from '../../common/constants';
import { AMPDrawer, HANDLE_TYPE_RIGHT } from '../amp-drawer';
import { AMPInfo } from '../amp-info';
import { Standard } from '../svg/standard';
import { Transitional } from '../svg/transitional';
import { Reader } from '../svg/reader';
import { Options } from '../options-context-provider';

/**
 * Mode-specific illustration.
 *
 * @param {Object} props      Component props.
 * @param {string} props.mode The template mode.
 */
function Illustration( { mode } ) {
	switch ( mode ) {
		case STANDARD:
			return <Standard />;

		case TRANSITIONAL:
			return <Transitional />;

		case READER:
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
		case STANDARD:
			return __( 'Standard', 'amp' );

		case TRANSITIONAL:
			return __( 'Transitional', 'amp' );

		case READER:
			return __( 'Reader', 'amp' );

		default:
			return null;
	}
}

/**
 * Returns the ID for an input corresponding to a mode option.
 *
 * @param {string} mode A template mode.
 */
export function getId( mode ) {
	return `template-mode-${ mode }`;
}

/**
 * An individual mode selection component.
 *
 * @param {Object}        props                    Component props.
 * @param {string|Object} props.children           Section content.
 * @param {string|Array}  props.details            The template mode details.
 * @param {string}        props.detailsUrl         Mode details URL.
 * @param {string}        props.mode               The template mode.
 * @param {boolean}       props.previouslySelected Optional. Whether the option was selected previously.
 * @param {Object}        props.labelExtra         Optional. Extra content to display on the right side of the option label.
 * @param {boolean}       props.initialOpen        Whether the panel should be open when the component renders.
 */
export function TemplateModeOption( { children, details, detailsUrl, initialOpen, labelExtra = null, mode, previouslySelected = false } ) {
	const { editedOptions, updateOptions } = useContext( Options );
	const { theme_support: themeSupport } = editedOptions;

	const id = getId( mode );

	return (
		<AMPDrawer
			className="template-mode-option"
			handleType={ HANDLE_TYPE_RIGHT }
			heading={ (
				<label className="template-mode-option__label" htmlFor={ id }>
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
						<h3>
							{ getTitle( mode ) }
						</h3>
						{ previouslySelected && (
							<AMPInfo>
								{ __( 'Previously selected', 'amp' ) }
							</AMPInfo>
						) }
						{ labelExtra && (
							<div className="template-mode-selection__label-extra">
								{ labelExtra }
							</div>
						) }
					</div>
				</label>
			) }
			hiddenTitle={ getTitle( mode ) }
			id={ `${ id }-container` }
			initialOpen={ 'boolean' === typeof initialOpen ? initialOpen : ( mode && themeSupport && mode === themeSupport ) }
			selected={ mode === themeSupport }
		>
			<div className="template-mode-selection__details">
				{ children }
				{ Array.isArray( details ) && (
					<ul className="template-mode-selection__details-list">
						{ details.map( ( detail, index ) => (
							<li
								key={ index }
								className="template-mode-selection__details-list-item"
								/* dangerouslySetInnerHTML reason: `detail` may contain `strong` elements. */
								dangerouslySetInnerHTML={ { __html: detail } }
							/>
						) ) }
					</ul>
				) }
				{ details && ! Array.isArray( details ) && (
					<p>
						<span dangerouslySetInnerHTML={ { __html: details } } />
						{ detailsUrl && (
							<>
								{ ' ' }
								<a href={ detailsUrl } target="_blank" rel="noreferrer noopener">
									{ __( 'Learn more.', 'amp' ) }
								</a>
							</>
						) }
					</p>
				) }
			</div>
		</AMPDrawer>
	);
}

TemplateModeOption.propTypes = {
	children: PropTypes.any,
	details: PropTypes.oneOfType( [
		PropTypes.string,
		PropTypes.arrayOf( PropTypes.string ),
	] ),
	detailsUrl: PropTypes.string,
	initialOpen: PropTypes.bool,
	labelExtra: PropTypes.node,
	mode: PropTypes.oneOf( [ READER, STANDARD, TRANSITIONAL ] ).isRequired,
	previouslySelected: PropTypes.bool,
};
