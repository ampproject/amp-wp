/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useContext, useState, useEffect, useMemo } from '@wordpress/element';
import { CheckboxControl } from '@wordpress/components';

/**
 * Internal dependencies
 */
import { SupportedTemplatesToggle } from '../components/supported-templates-toggle';
import { Selectable } from '../components/selectable';
import { Options } from '../components/options-context-provider';

function PostTypeCheckbox( { postTypeObject, supportedPostTypes } ) {
	const { updateOptions } = useContext( Options );

	// Initialize state with supports_amp if true.
	const [ checked, setChecked ] = useState( postTypeObject.supports_amp || supportedPostTypes.includes( postTypeObject.name ) );

	const newSupportedPostTypes = useMemo( () => {
		const newValue = supportedPostTypes.filter( ( postType ) => postType !== postTypeObject.name );
		if ( ! checked ) {
			newValue.push( postTypeObject.name );
		}

		return newValue;
	}, [ checked, postTypeObject.name, supportedPostTypes ] );

	useEffect( () => {
		if ( supportedPostTypes.length !== newSupportedPostTypes.length ) {
			updateOptions( { supported_post_types: newSupportedPostTypes } );
		}
	}, [ newSupportedPostTypes, supportedPostTypes.length, updateOptions ] );

	return (
		<li key={ `supportable-post-type-${ postTypeObject.name }` }>
			<CheckboxControl
				checked={ checked }
				label={ postTypeObject.label }
				onChange={
					() => {
						setChecked( ! checked );
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
	supportedPostTypes: PropTypes.array.isRequired,
};

function SupportedPostTypesFieldset() {
	const { editedOptions, fetchingOptions } = useContext( Options );

	const { supportable_post_types: supportablePostTypes, supported_post_types: supportedPostTypes } = editedOptions || {};

	if ( fetchingOptions || ! supportablePostTypes ) {
		return null;
	}

	return (
		<fieldset id="supported_post_types_fieldset">
			<h4 className="title">
				{ __( 'Content Types', 'amp' ) }
			</h4>
			<p>
				{ __( 'The following content types will be available as AMP:', 'amp' ) }
			</p>
			<ul>
				{ supportablePostTypes.map( ( postTypeObject ) => {
					return (
						<PostTypeCheckbox
							key={ `supportable-post-type-${ postTypeObject.name }` }
							postTypeObject={ postTypeObject }
							supportedPostTypes={ supportedPostTypes }
						/>
					);
				} ) }
			</ul>
		</fieldset>

	);
}

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

							const templatesToSwitch = [ supportableTemplate.id, ...( supportableTemplate.children.map( ( { id } ) => id ) ) ];

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

export function SupportedTemplatesFieldset() {
	const { editedOptions, fetchingOptions } = useContext( Options );

	const { supportable_templates: supportableTemplates } = editedOptions || {};

	if ( fetchingOptions || ! supportableTemplates ) {
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

export function SupportedTemplates() {
	return (
		<section>
			<h2>
				{ __( 'Supported Templates', 'amp' ) }
			</h2>
			<Selectable className="supported-templates">
				<SupportedTemplatesToggle />
				<SupportedPostTypesFieldset />
				<SupportedTemplatesFieldset />

			</Selectable>
		</section>
	);
}
