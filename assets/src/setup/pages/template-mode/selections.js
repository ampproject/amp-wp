/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { PanelBody } from '@wordpress/components';
import { useMemo } from '@wordpress/element';

/**
 * External dependencies
 */

import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import './style.css';
import { Selectable } from '../../components/selectable';
import { Standard } from './svg-standard';
import { Transitional } from './svg-transitional';
import { Reader } from './svg-reader';

export function Selection( { compatibility, id, illustration, moreDetails, onChange, recommendationLevel, selected, shortDescription, title } ) {
	const { recommendationLevelClass, recommendationLevelText } = useMemo( () => {
		switch ( recommendationLevel ) {
			case 0:
				return {
					recommendationLevelText: __( 'The best option for your site.', 'amp' ),
					recommendationLevelClass: '',
				};

			case 1:
				return {
					recommendationLevelText: __( 'A good option for your site.', 'amp' ),
					recommendationLevelClass: '',
				};

			default:
				return {
					recommendationLevelText: __( 'Not recommended for your site', 'amp' ),
					recommendationLevelClass: '',
				};
		}
	}, [ recommendationLevel ] );

	return (
		<Selectable className="template-mode-selection" selected={ selected }>
			<label htmlFor={ id }>
				<div className="template-mode-selection__input-container">
					<input
						type="radio"
						id={ id }
						checked={ selected }
						onChange={ onChange }
					/>
				</div>
				<div className="template-mode-selection__illustration">
					{ illustration }
				</div>
				<div className="template-mode-selection__description">
					<h2>
						{ title }
					</h2>
					<p>
						{ shortDescription }
					</p>
					<p className={ recommendationLevelClass }>
						{ recommendationLevelText }
					</p>
				</div>
			</label>
			<PanelBody title={ __( 'Details', 'amp' ) }>
				<p>
					{ moreDetails }
				</p>
				<h4>
					{ __( 'Compatibility', 'amp' ) }
				</h4>
				<p>
					{ compatibility }
				</p>
			</PanelBody>
		</Selectable>
	);
}

Selection.propTypes = {
	compatibility: PropTypes.node.isRequired,
	id: PropTypes.string.isRequired,
	illustration: PropTypes.node.isRequired,
	moreDetails: PropTypes.string.isRequired,
	onChange: PropTypes.func.isRequired,
	recommendationLevel: PropTypes.number.isRequired,
	selected: PropTypes.bool.isRequired,
	shortDescription: PropTypes.string.isRequired,
	title: PropTypes.string.isRequired,
};

export function Selections( { recommendedModes, currentMode, setCurrentMode } ) {
	const standardId = 'standard-mode';
	const transitionalId = 'transitional-mode';
	const readerId = 'reader-mode';

	return (
		<form>
			<Selection
				compatibility={ __( 'Compatibility details' ) }
				id={ standardId }
				illustration={ <Standard /> }
				moreDetails={ __( 'More details', 'amp' ) }
				onChange={ () => {
					setCurrentMode( 'standard' );
				} }
				recommendationLevel={ recommendedModes.indexOf( 'standard' ) }
				selected={ currentMode === 'standard' }
				shortDescription={ 'Lorem ipsum' }
				title={ __( 'Standard', 'amp' ) }
			/>

			<Selection
				compatibility={ __( 'Compatibility details' ) }
				id={ transitionalId }
				illustration={ <Transitional /> }
				moreDetails={ __( 'More details', 'amp' ) }
				onChange={ () => {
					setCurrentMode( 'transitional' );
				} }
				recommendationLevel={ recommendedModes.indexOf( 'transitional' ) }
				selected={ currentMode === 'transitional' }
				shortDescription={ 'Lorem ipsum' }
				title={ __( 'Transitional', 'amp' ) }
			/>

			<Selection
				compatibility={ __( 'Compatibility details' ) }
				id={ readerId }
				illustration={ <Reader /> }
				moreDetails={ __( 'More details', 'amp' ) }
				onChange={ () => {
					setCurrentMode( 'reader' );
				} }
				recommendationLevel={ recommendedModes.indexOf( 'reader' ) }
				selected={ currentMode === 'reader' }
				shortDescription={ 'Lorem ipsum' }
				title={ __( 'Reader', 'amp' ) }
			/>
		</form>
	);
}

Selections.propTypes = {
	currentMode: PropTypes.string.isRequired,
	setCurrentMode: PropTypes.func.isRequired,
	recommendedModes: PropTypes.arrayOf( PropTypes.string ).isRequired,
};
