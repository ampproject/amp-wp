/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import { v4 as uuidv4 } from 'uuid';

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { useContext, useEffect, useRef } from '@wordpress/element';
import { Icon, plus, trash } from '@wordpress/icons';
import { Button, TextControl, PanelRow, BaseControl } from '@wordpress/components';

/**
 * Internal dependencies
 */
import { AMPDrawer } from '../components/amp-drawer';
import { Options } from '../components/options-context-provider';

/**
 * Component for a single analytics entry.
 *
 * @param {Object} props Component props.
 * @param {number} props.entryIndex Index for the entry.
 * @param {Function} props.onChange Callback to run when data changes.
 * @param {Function} props.onDelete Callback to run when the entry is to be deleted.
 * @param {string} props.type The entry type.
 * @param {string} props.config The config JSON string.
 */
function AnalyticsEntry( { entryIndex, onChange, onDelete, type = '', config = '{}' } ) {
	/**
	 * Track the validity of the config JSON object. A nonempty custom validity string will block form submission.
	 */
	const textAreaRef = useRef();
	useEffect( () => {
		if ( ! textAreaRef?.current ) {
			return;
		}

		if ( ! config ) {
			textAreaRef.current.setCustomValidity( '' );
			return;
		}
		try {
			const parsedValue = JSON.parse( config );
			if ( null === parsedValue || typeof parsedValue !== 'object' || Array.isArray( parsedValue ) ) {
				textAreaRef.current.setCustomValidity( __( 'A JSON object is required, e.g. {…}', 'amp' ) );
			} else {
				textAreaRef.current.setCustomValidity( '' );
			}
		} catch ( e ) {
			textAreaRef.current.setCustomValidity( e.message );
		}
	}, [ config ] );

	return (
		<PanelRow className="amp-analytics-entry">
			<h4>
				{
					/* translators: placeholder is the entry index */
					sprintf( __( 'Analytics Configuration #%s', 'amp' ), entryIndex )
				}
			</h4>
			<div className="amp-analytics-entry__options" id={ `amp-analytics-entry-${ String( entryIndex ) }` }>
				<div className="amp-analytics-entry__text-inputs">
					<TextControl
						className="option-input"
						label={ __( 'Type:', 'amp' ) }
						onChange={ ( newType ) => {
							onChange( { type: newType } );
						} }
						type="text"
						placeholder={ __( 'e.g. googleanalytics', 'amp' ) }
						value={ type }
					/>
				</div>

				<BaseControl
					id={ `analytics-textarea-control-${ entryIndex }` }
					label={ __( 'JSON Configuration:', 'amp' ) }
				>
					<textarea
						rows="10"
						cols="100"
						className="amp-analytics-input"
						id={ `analytics-textarea-control-${ entryIndex }` }
						onChange={ ( event ) => {
							onChange( { config: event.target.value } );
						} }
						placeholder="{...}"
						ref={ textAreaRef }
						required
						value={ config }
					/>
				</BaseControl>
			</div>
			<Button
				isLink
				onClick={ () => {
					if ( '{}' === config || global.confirm( __( 'Are you sure you want to delete this entry?', 'amp' ) ) ) {
						onDelete();
					}
				} }
				className="amp-analytics__delete-button"
			>
				<Icon icon={ trash } />
				{ __( 'Remove entry', 'amp' ) }
			</Button>
		</PanelRow>
	);
}
AnalyticsEntry.propTypes = {
	config: PropTypes.string,
	entryIndex: PropTypes.number.isRequired,
	onChange: PropTypes.func.isRequired,
	onDelete: PropTypes.func.isRequired,
	type: PropTypes.string,
};

/**
 * Component handling addition and deletion of analytics entries.
 */
function AnalyticsOptions() {
	const { editedOptions, originalOptions, updateOptions } = useContext( Options );
	const { analytics } = editedOptions;

	return (
		<div>

			<details open={ ! Boolean( Object.keys( originalOptions.analytics ).length ) }>
				<summary>
					{ __( 'Learn about analytics for AMP.', 'amp' ) }
				</summary>
				<p dangerouslySetInnerHTML={
					{ __html:
						sprintf(
							/* translators: 1: AMP Analytics docs URL. 2: AMP for WordPress analytics docs URL. 3: AMP analytics code reference. 4: amp-analytics, 5: {. 6: }. 7: <script>, 8: googleanalytics. 9: AMP analytics vendor docs URL. 10: UA-XXXXX-Y. */
							__( 'For Google Analytics, please see <a href="%1$s" target="_blank">Adding Analytics to your AMP pages</a>; see also the <a href="%2$s" target="_blank">Analytics wiki page</a> and the AMP project\'s <a href="%3$s" target="_blank">%4$s documentation</a>. The analytics configuration supplied below must take the form of JSON objects, which begin with a %5$s and end with a %6$s. Do not include any HTML tags like %4$s or %7$s. A common entry would have the type %8$s (see <a href="%9$s" target="_blank">available vendors</a>) and a configuration that looks like the following (where %10$s is replaced with your own site\'s account number):', 'amp' ),
							__( 'https://developers.google.com/analytics/devguides/collection/amp-analytics/', 'amp' ),
							__( 'https://amp-wp.org/documentation/playbooks/analytics/', 'amp' ),
							__( 'https://www.ampproject.org/docs/reference/components/amp-analytics', 'amp' ),
							'<code>amp-analytics</code>',
							'<code>{</code>',
							'<code>}</code>',
							'<code>&lt;script&gt;</code>',
							'<code>googleanalytics</code>',
							__( 'https://amp.dev/documentation/guides-and-tutorials/optimize-and-measure/configure-analytics/analytics-vendors/', 'amp' ),
							'<code>UA-XXXXX-Y</code>',
						),
					} }
				/>
				<pre>
					{ `{
	"vars": {
		"account": "UA-XXXXX-Y"
	},
	"triggers": {
		"trackPageview": {
			"on": "visible",
			"request": "pageview"
		}
	}
}` }

				</pre>
			</details>
			{ Object.entries( analytics || {} ).map( ( [ key, { type, config } ], index ) => (
				<AnalyticsEntry
					key={ `analytics-entry-${ index }` }
					entryIndex={ index + 1 }
					isExistingEntry={ key in originalOptions.analytics }
					type={ type }
					config={ config }
					onDelete={ () => {
						const newAnalytics = { ...analytics };
						delete newAnalytics[ key ];
						updateOptions( { analytics: newAnalytics } );
					} }
					onChange={ ( changes ) => {
						updateOptions( {
							analytics: {
								...analytics,
								[ key ]: {
									...analytics[ key ],
									...changes,
								},
							},
						} );
					} }
				/>
			) ) }

			<Button
				id="amp-analytics-add-entry"
				className="amp-analytics__entry-appender"
				onClick={ () => {
					updateOptions( {
						analytics: {
							...analytics,
							[ uuidv4() ]: {
								type: '',
								config: '{}',
							},
						},
					} );
				} }
			>
				<span className="screen-reader-text">
					{ __( 'Add entry', 'amp' ) }
				</span>
				<Icon icon={ plus } />
			</Button>
		</div>
	);
}

/**
 * Analytics section of the settings screen. Displays as a closed drawer on initial load.
 */
export function Analytics() {
	return (
		<AMPDrawer
			className="amp-analytics"
			heading={ (
				<h3>
					{ __( 'Analytics Options', 'amp' ) }
				</h3>
			) }
			hiddenTitle={ __( 'Analytics Options', 'amp' ) }
			id="analytics-options-drawer"
			initialOpen={ false }
		>
			<AnalyticsOptions />
		</AMPDrawer>
	);
}
