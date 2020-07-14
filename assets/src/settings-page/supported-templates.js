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
		let newValue = supportedPostTypes.filter( ( postType ) => postType !== postTypeObject.name );
		if ( ! checked ) {
			newValue = newValue.concat( postTypeObject.name );
		}

		return newValue;
	}, [ checked, postTypeObject.name, supportedPostTypes ] );

	useEffect( () => {
		if ( supportedPostTypes.length !== newSupportedPostTypes.length ) {
			updateOptions( { supported_post_types: newSupportedPostTypes } );
		}
	}, [ newSupportedPostTypes, supportedPostTypes.length, updateOptions ] );

	return (
		<li key={ `eligible-post-type-${ postTypeObject.name }` }>
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

	const { eligible_post_types: eligiblePostTypes, supported_post_types: supportedPostTypes } = editedOptions || {};

	if ( fetchingOptions || ! eligiblePostTypes ) {
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
				{ eligiblePostTypes.map( ( postTypeObject ) => {
					return (
						<PostTypeCheckbox
							key={ `eligible-post-type-${ postTypeObject.name }` }
							postTypeObject={ postTypeObject }
							supportedPostTypes={ supportedPostTypes }
						/>
					);
				} ) }
			</ul>
		</fieldset>

	);
}

export function SupportedTemplatesFieldSet() {
	return (
		<fieldset id="supported_templates_fieldset" className="hidden">
			<h4 className="title">
				{ __( 'Templates', 'amp' ) }
			</h4>
			{ `<style>
			#supported_templates_fieldset ul ul {
				margin-left: 40px;
			}
		</style>
		<h4 class="title"><?php esc_html_e( 'Templates', 'amp' ); ?></h4>
		<ul>
			<?php foreach ( $options as $id => $option ) : ?>
				<?php
				$element_id = AMP_Options_Manager::OPTION_NAME . '-supported-templates-' . $id;
				if ( $parent ? empty( $option['parent'] ) || $parent !== $option['parent'] : ! empty( $option['parent'] ) ) {
					continue;
				}

				// Skip showing an option if it doesn't have a label.
				if ( empty( $option['label'] ) ) {
					continue;
				}

				?>
				<li>
					<?php if ( empty( $option['immutable'] ) ) : ?>
						<input
							type="checkbox"
							id="<?php echo esc_attr( $element_id ); ?>"
							name="<?php echo esc_attr( $element_name ); ?>"
							value="<?php echo esc_attr( $id ); ?>"
							<?php checked( ! empty( $option['user_supported'] ) ); ?>
						>
					<?php else : // Persist user selection even when checkbox disabled, when selection forced by theme/filter. ?>
						<input
							type="checkbox"
							id="<?php echo esc_attr( $element_id ); ?>"
							<?php checked( ! empty( $option['supported'] ) ); ?>
							<?php disabled( true ); ?>
						>
						<?php if ( ! empty( $option['user_supported'] ) ) : ?>
							<input type="hidden" name="<?php echo esc_attr( $element_name ); ?>" value="<?php echo esc_attr( $id ); ?>">
						<?php endif; ?>
					<?php endif; ?>
					<label for="<?php echo esc_attr( $element_id ); ?>">
						<?php echo esc_html( $option['label'] ); ?>
					</label>

					<?php if ( ! empty( $option['description'] ) ) : ?>
						<span class="description">
							&mdash; <?php echo wp_kses_post( $option['description'] ); ?>
						</span>
					<?php endif; ?>

					<?php $this->list_template_conditional_options( $options, $id ); ?>
				</li>
			<?php endforeach; ?>
		</ul>` }

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
				<SupportedTemplatesFieldSet />

			</Selectable>
		</section>
	);
}
