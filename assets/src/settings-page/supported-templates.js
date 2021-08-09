/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { useContext } from '@wordpress/element';
import { CheckboxControl } from '@wordpress/components';

/**
 * Internal dependencies
 */
import { SupportedTemplatesToggle } from '../components/supported-templates-toggle';
import { Options } from '../components/options-context-provider';

/**
 * Determine whether the supportable templates include the static front page.
 *
 * @param {Array} supportableTemplates Supportable templates.
 * @return {boolean} Has front page template.
 */
function hasFrontPageTemplate( supportableTemplates ) {
	return Boolean( supportableTemplates.find( ( supportableTemplate ) => {
		if ( supportableTemplate.children && hasFrontPageTemplate( supportableTemplate.children ) ) {
			return true;
		}
		return supportableTemplate.id === 'is_front_page';
	} ) );
}

/**
 * A checkbox for a supportable post type.
 *
 * @param {Object} props                Component props.
 * @param {Object} props.postTypeObject A post type object.
 */
function PostTypeCheckbox( { postTypeObject } ) {
	const { editedOptions, updateOptions } = useContext( Options );

	const {
		supported_post_types: supportedPostTypes,
		supportable_templates: supportableTemplates,
		supported_templates: supportedTemplates,
		all_templates_supported: allTemplatesSupported,
	} = editedOptions || {};

	const hasPageOnFront = hasFrontPageTemplate( supportableTemplates );
	const isBlogTemplateSupported = supportedTemplates.includes( 'is_home' );
	const isFrontPageTemplateSupported = supportedTemplates.includes( 'is_front_page' );

	return (
		<li key={ `supportable-post-type-${ postTypeObject.name }` }>
			<CheckboxControl
				checked={ supportedPostTypes.includes( postTypeObject.name ) }
				label={ postTypeObject.label }
				onChange={
					( newChecked ) => {
						if ( ! newChecked && hasPageOnFront && ! allTemplatesSupported && 'page' === postTypeObject.name ) {
							let warning = '';
							if ( isBlogTemplateSupported && isFrontPageTemplateSupported ) {
								warning = __( 'Note that disabling pages will prevent you from serving your homepage and posts page (blog index) as AMP.', 'amp' );
							} else if ( isBlogTemplateSupported ) {
								warning = __( 'Note that disabling pages will prevent you from serving your posts page (blog index) as AMP.', 'amp' );
							} else if ( isFrontPageTemplateSupported ) {
								warning = __( 'Note that disabling pages will prevent you from serving your homepage as AMP.', 'amp' );
							}
							// eslint-disable-next-line no-alert
							if ( warning && ! window.confirm( warning ) ) {
								return;
							}
						}

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
	const { editedOptions } = useContext( Options );

	const { supportable_post_types: supportablePostTypes } = editedOptions || {};

	if ( ! supportablePostTypes ) {
		return null;
	}

	return (
		<fieldset id="supported_post_types_fieldset">
			<h4 className="title">
				{ __( 'Content Types', 'amp' ) }
			</h4>
			<p>
				{ __( 'Content types enabled for AMP:', 'amp' ) }
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
 * @param {Object} supportableTemplate Supportable templates.
 * @return {Array} Descendant template IDs, including the ID of the passed template.
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
 * @param {Object} props                      Component props.
 * @param {Array}  props.supportableTemplates Array of supportableTemplate objects.
 */
export function SupportedTemplatesCheckboxes( { supportableTemplates } ) {
	const { editedOptions, updateOptions } = useContext( Options );

	const { supported_templates: supportedTemplates, supported_post_types: supportedPostTypes } = editedOptions || {};

	if ( ! supportableTemplates.length ) {
		return null;
	}

	const hasPageOnFront = hasFrontPageTemplate( supportableTemplates );
	const isPageSupported = supportedPostTypes.includes( 'page' );
	const relevantSupportableTemplates = ! hasPageOnFront ? supportableTemplates : supportableTemplates.filter( ( supportableTemplate ) => {
		return (
			! hasPageOnFront ||
			isPageSupported ||
			! [ 'is_home', 'is_front_page' ].includes( supportableTemplate.id )
		);
	} );

	return (
		<ul>
			{ relevantSupportableTemplates.map( ( supportableTemplate ) => (
				<li key={ supportableTemplate.id }>
					<CheckboxControl
						checked={ supportedTemplates.includes( supportableTemplate.id ) }
						help={ supportableTemplate.description }
						label={ supportableTemplate.label }
						onChange={ ( checked ) => {
							if (
								! checked &&
								'is_singular' === supportableTemplate.id &&
								// eslint-disable-next-line no-alert
								! window.confirm( __( 'Are you sure you want to disable the singular template? This template is needed to serve individual posts and pages as AMP.' ) )
							) {
								return;
							}

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
	supportableTemplates: PropTypes.arrayOf( PropTypes.shape( {
		id: PropTypes.string,
		description: PropTypes.string,
		label: PropTypes.string,
		children: PropTypes.array,
	} ) ),
};

/**
 * Container for the supported templates fieldset.
 */
export function SupportedTemplatesFieldset() {
	const { editedOptions } = useContext( Options );

	const {
		all_templates_supported: allTemplatesSupported,
		theme_support: themeSupport,
		supportable_templates: supportableTemplates,
		reader_theme: readerTheme,
	} = editedOptions || {};

	if ( ( 'reader' === themeSupport && 'legacy' === readerTheme ) || ! supportableTemplates ) {
		return null;
	}

	return (
		<fieldset id="supported_templates_fieldset">
			<h4 className="title">
				{ __( 'Templates', 'amp' ) }
			</h4>

			<SupportedTemplatesToggle />

			{ ! allTemplatesSupported ? (
				<>
					{ /* dangerouslySetInnerHTML reason: Link embedded in translation string. */ }
					<p
						dangerouslySetInnerHTML={ {
							__html: sprintf(
								/* translators: placeholder is link to WordPress handbook page about the template hierarchy. */
								__( 'Limit AMP on a subset of the WordPress <a href="%s" target="_blank" rel="noreferrer">Template Hierarchy</a>:', 'amp' ),
								'https://developer.wordpress.org/themes/basics/template-hierarchy/',
							),
						} }
					/>

					{ supportableTemplates
						? <SupportedTemplatesCheckboxes supportableTemplates={ supportableTemplates } />
						: (
							<p>
								{ __( 'Your site does not provide any templates to support.', 'amp' ) }
							</p>
						)
					}
				</>
			) : null }
		</fieldset>
	);
}

/**
 * Component rendering the supported templates section of the settings page, including the "Serve all templates as AMP" toggle.
 */
export function SupportedTemplates() {
	return (
		<div className="supported-templates">
			<div className="supported-templates__fields">
				<SupportedPostTypesFieldset />
				<SupportedTemplatesFieldset />
			</div>
		</div>
	);
}
