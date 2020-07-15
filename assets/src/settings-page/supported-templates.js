/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useContext } from '@wordpress/element';
import { CheckboxControl } from '@wordpress/components';

/**
 * Internal dependencies
 */
import { SupportedTemplatesToggle } from '../components/supported-templates-toggle';
import { Selectable } from '../components/selectable';
import { Options } from '../components/options-context-provider';

/**
 * A checkbox for a supportable post type.
 *
 * @param {Object} props Component props.
 * @param {Object} props.postTypeObject A post type object.
 */
function PostTypeCheckbox( { postTypeObject } ) {
	const { editedOptions, updateOptions } = useContext( Options );

	const {
		supported_post_types: supportedPostTypes,
	} = editedOptions || {};

	return (
		<li key={ `supportable-post-type-${ postTypeObject.name }` }>
			<CheckboxControl
				checked={ supportedPostTypes.includes( postTypeObject.name ) }
				label={ postTypeObject.label }
				onChange={
					( newChecked ) => {
						const newSupportedPostTypes = supportedPostTypes.filter( ( postType ) => postType !== postTypeObject.name );

						if ( newChecked ) {
							newSupportedPostTypes.push( postTypeObject.name );
						}

						updateOptions( { supported_post_types: newSupportedPostTypes } );
					}
				}
			/>
		</li>
	);
}
PostTypeCheckbox.propTypes = {
	postTypeObject: PropTypes.shape( {
		label: PropTypes.string,
		name: PropTypes.string,
		supports_amp: PropTypes.bool,
	} ).isRequired,
};

/**
 * Container for the supported post type checkbox fieldset.
 */
function SupportedPostTypesFieldset() {
	const { editedOptions, fetchingOptions } = useContext( Options );

	const {
		theme_support: themeSupport,
		supportable_post_types: supportablePostTypes,
	} = editedOptions || {};

	if ( fetchingOptions || ! supportablePostTypes ) {
		return null;
	}

	return (
		<fieldset id="supported_post_types_fieldset">
			{ 'reader' !== themeSupport && (
				<h4 className="title">
					{ __( 'Content Types', 'amp' ) }
				</h4>
			) }
			<p>
				{ __( 'The following content types will be available as AMP:', 'amp' ) }
			</p>
			<ul>
				{ supportablePostTypes.map( ( postTypeObject ) => {
					return (
						<PostTypeCheckbox
							key={ `supportable-post-type-${ postTypeObject.name }` }
							postTypeObject={ postTypeObject }
						/>
					);
				} ) }
			</ul>
		</fieldset>

	);
}

/**
 * Get a list of the template IDs for the supported template and its descendants.
 *
 * @param {object} supportableTemplate Supportable templates.
 * @return {array} Descendant template IDs, including the ID of the passed template.
 */
function getInclusiveDescendantTemplatesIds( supportableTemplate ) {
	const templateIds = [ supportableTemplate.id ];
	for ( const childSupportableTemplate of supportableTemplate.children ) {
		templateIds.push( ...getInclusiveDescendantTemplatesIds( childSupportableTemplate ) );
	}
	return templateIds;
}

/**
 * List of checkboxes corresponding to supportable templates.
 *
 * @param {Object} props Component props.
 * @param {Array} props.supportableTemplates Array of supportableTemplate objects.
 */
export function SupportedTemplatesCheckboxes( { supportableTemplates } ) {
	const { editedOptions, updateOptions } = useContext( Options );

	const { supported_templates: supportedTemplates } = editedOptions || {};

	if ( ! supportableTemplates.length ) {
		return null;
	}

	return (
		<ul>
			{ supportableTemplates.map( ( supportableTemplate ) => (
				<li key={ supportableTemplate.id }>
					<CheckboxControl
						checked={ supportedTemplates.includes( supportableTemplate.id ) }
						help={ supportableTemplates.description }
						label={ supportableTemplate.label }
						onChange={ ( checked ) => {
							let newSupported = [ ...supportedTemplates ];

							// Toggle child checkboxes along with their parent.
							const templatesToSwitch = getInclusiveDescendantTemplatesIds( supportableTemplate );

							if ( checked ) {
								templatesToSwitch.forEach( ( template ) => {
									if ( ! newSupported.includes( template ) ) {
										newSupported.push( template );
									}
								} );
							} else {
								newSupported = newSupported.filter( ( template ) => ! templatesToSwitch.includes( template ) );
							}

							updateOptions( { supported_templates: newSupported } );
						} }
					/>
					<SupportedTemplatesCheckboxes supportableTemplates={ supportableTemplate.children } />
				</li>
			) ) }
		</ul>
	);
}
SupportedTemplatesCheckboxes.propTypes = {
	supportableTemplates: PropTypes.array.isRequired,
};

/**
 * Container for the supported templates fieldset.
 */
export function SupportedTemplatesFieldset() {
	const { editedOptions, fetchingOptions } = useContext( Options );

	const { theme_support: themeSupport, supportable_templates: supportableTemplates, reader_theme: readerTheme } = editedOptions || {};

	if ( ( 'reader' === themeSupport && 'legacy' === readerTheme ) || fetchingOptions || ! supportableTemplates ) {
		return null;
	}

	return (
		<fieldset id="supported_templates_fieldset">
			<h4 className="title">
				{ __( 'Templates', 'amp' ) }
			</h4>

			<SupportedTemplatesCheckboxes supportableTemplates={ supportableTemplates } />
		</fieldset>
	);
}

/**
 * Component rendering the supported templates section of the settings page, including the "Serve all templates as AMP" toggle.
 */
export function SupportedTemplates() {
	const { editedOptions, fetchingOptions } = useContext( Options );

	const { all_templates_supported: allTemplatesSupported, theme_support: themeSupport, reader_theme: readerTheme } = editedOptions || {};

	if ( fetchingOptions ) {
		return null;
	}

	const isLegacy = 'reader' === themeSupport && 'legacy' === readerTheme;

	return (
		<section>
			<h2>
				{ __( 'Supported Templates', 'amp' ) }
			</h2>
			<Selectable className="supported-templates">
				<SupportedTemplatesToggle />
				{ ( ! allTemplatesSupported || isLegacy ) && (
					<div className="supported-templates__fields">
						<SupportedPostTypesFieldset />
						<SupportedTemplatesFieldset />
					</div>
				) }

			</Selectable>
		</section>
	);
}
