/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { BlockControls, ColorPalette } from '@wordpress/block-editor';
import { Toolbar, Dropdown, IconButton } from '@wordpress/components';
import { getActiveFormat, applyFormat, removeFormat } from '@wordpress/rich-text';

/**
 * Internal dependencies
 */
import { name } from './';
import './edit.css';

const FormatEdit = ( { isActive, value, onChange } ) => {
	let activeColor;

	if ( isActive ) {
		const activeFormat = getActiveFormat( value, name );
		const style = activeFormat.attributes.style;
		activeColor = style.replace( new RegExp( `^color:\\s*` ), '' );
	}

	return (
		<BlockControls>
			<Toolbar>
				<Dropdown
					position="bottom right"
					renderToggle={ ( { isOpen, onToggle } ) => (
						<IconButton
							icon="editor-textcolor"
							tooltip={ __( 'Text Color', 'amp' ) }
							onClick={ onToggle }
							aria-expanded={ isOpen }
						>
							<span
								className="components-text-color-indicator"
								style={ {
									backgroundColor: activeColor,
								} }
							/>
						</IconButton>
					) }
					renderContent={ () => (
						<div className="components-text-color-popover-content">
							<ColorPalette
								value={ activeColor }
								onChange={ ( color ) => {
									if ( color ) {
										onChange( applyFormat( value, {
											type: name,
											attributes: {
												style: `color:${ color }`,
											},
										} ) );

										return;
									}

									onChange( removeFormat( value, name ) );
								} }
							/>
						</div>
					) }
				/>
			</Toolbar>
		</BlockControls>
	);
};

FormatEdit.propTypes = {
	isActive: PropTypes.bool.isRequired,
	value: PropTypes.shape( {
		activeFormats: PropTypes.array,
		formats: PropTypes.array,
		replacements: PropTypes.array,
		text: PropTypes.string,
		start: PropTypes.number,
		end: PropTypes.number,
	} ),
	onChange: PropTypes.func.isRequired,
};

export default FormatEdit;
