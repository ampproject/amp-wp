/**
 * WordPress dependencies
 */
import { PluginDocumentSettingPanel } from '@wordpress/edit-post';
import { __ } from '@wordpress/i18n';
import { useCallback } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import { SelectControl, RangeControl } from '@wordpress/components';

/**
 * Internal dependencies
 */
import { metaToAttributeNames } from '../helpers';

const StorySettings = () => {
	const {
		meta,
		autoAdvanceAfterOptions,
	} = useSelect( ( select ) => {
		const { getEditedPostAttribute } = select( 'core/editor' );
		const { getSettings } = select( 'amp/story' );
		const { storySettings } = getSettings();

		return {
			meta: getEditedPostAttribute( 'meta' ),
			autoAdvanceAfterOptions: storySettings.autoAdvanceAfterOptions || {},
		};
	}, [] );

	const { editPost } = useDispatch( 'core/editor' );

	const updateMeta = useCallback( ( newMeta ) => {
		editPost( {
			meta: {
				...meta,
				...newMeta,
			},
		} );
	}, [ meta, editPost ] );

	const {
		autoAdvanceAfter,
		autoAdvanceAfterDuration,
	} = metaToAttributeNames( meta );

	const currentOption = autoAdvanceAfterOptions.find( ( i ) => i.value === autoAdvanceAfter ) || {};

	return (
		<>
			<p>
				{ __( 'These settings are applied to new pages.', 'amp' ) }
			</p>
			<SelectControl
				label={ __( 'Advance to next page', 'amp' ) }
				help={ currentOption.description || '' }
				value={ autoAdvanceAfter }
				options={ autoAdvanceAfterOptions }
				onChange={ ( value ) => updateMeta( { amp_story_auto_advance_after: value } ) }
				className="amp-story-settings-advance-after"
			/>
			{ 'time' === autoAdvanceAfter && (
				<RangeControl
					label={ __( 'Time in seconds', 'amp' ) }
					value={ autoAdvanceAfterDuration ? parseInt( autoAdvanceAfterDuration ) : 0 }
					onChange={ ( value ) => updateMeta( { amp_story_auto_advance_after_duration: value } ) }
					min={ 0 }
					max={ 100 }
					className="amp-story-settings-advance-after-duration"
				/>
			) }
		</>
	);
};

export const name = 'amp-story-settings-panel';

export const icon = 'book';

export const render = () => {
	return (
		<PluginDocumentSettingPanel
			name={ name }
			className={ name }
			title={ __( 'Story Settings', 'amp' ) }
		>
			<StorySettings />
		</PluginDocumentSettingPanel>
	);
};
