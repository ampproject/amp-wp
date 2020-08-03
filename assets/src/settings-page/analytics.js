/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { useContext, useEffect, useRef, useState, useCallback } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { Button, TextControl, PanelRow, BaseControl } from '@wordpress/components';
import { AMPDrawer } from '../components/amp-drawer';
import { Options } from '../components/options-context-provider';

const NEW_ENTRY_KEY_PREFIX = '__new__';

/**
 * Component for a single analytics entry.
 *
 * @param {Object} props Component props.
 * @param {string} props.entryId Unique ID for the entry.
 * @param {Function} props.onChange Callback to run when data changes.
 * @param {Function} props.onDelete Callback to run when the entry is to be DocumentAndElementEventHandlers.
 * @param {string} props.type The entry type.
 * @param {string} props.config The config JSON string.
 */
function AnalyticsEntry( { entryId = '', onChange, onDelete, type = '', config = '{}' } ) {
	let entrySlug, analyticsTitle;

	const isExistingEntry = 0 !== entryId.indexOf( NEW_ENTRY_KEY_PREFIX );
	if ( isExistingEntry ) {
		entrySlug = sprintf(
			'%1$s%2$s',
			type ? type + '-' : '',
			entryId.substr( entryId.length - 6 ),
		);
		/* translators: %s: the entry slug. */
		analyticsTitle = sprintf( __( 'Analytics: %s', 'amp' ), entrySlug );
	} else {
		analyticsTitle = __( 'Add new entry:', 'amp' );
	}

	const textAreaRef = useRef();
	/**
	 * Track the validity of the config JSON object. A nonempty custom validity string will block form submission.
	 */
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
				{ analyticsTitle }
			</h4>
			<div className="amp-analytics-entry__options">
				<div className="amp-analytics-entry__text-inputs">
					<TextControl
						className="option-input"
						label={ __( 'Type:', 'amp' ) }
						onChange={ ( newType ) => {
							onChange( { type: newType } );
						} }
						type="text"
						required
						placeholder={ __( 'e.g. googleanalytics', 'amp' ) }
						value={ type }
					/>

					<TextControl
						label={ __( 'ID:', 'amp' ) }
						type="text"
						value={ isExistingEntry ? entryId : '' }
						readOnly
					/>
				</div>

				<BaseControl
					id={ `analytics-textarea-control-${ entryId }` }
					label={ __( 'JSON Configuration:', 'amp' ) }
				>
					<textarea

						rows="10"
						cols="100"
						className="amp-analytics-input"
						id={ `analytics-textarea-control-${ entryId }` }
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
				onClick={ onDelete }
				className="amp-analytics__delete-button"
			>
				{ __( 'Delete', 'amp' ) }
			</Button>
		</PanelRow>
	);
}
AnalyticsEntry.propTypes = {
	config: PropTypes.string,
	entryId: PropTypes.string,
	onChange: PropTypes.func.isRequired,
	onDelete: PropTypes.func.isRequired,
	type: PropTypes.string,
};

/**
 * Provides a unique temporary key for new entries. Will be replaced with a key generated on the backend when settings are saved.
 *
 * @param {string} prefix The prefix for new keys.
 */
function useUniqueNewKey( prefix = NEW_ENTRY_KEY_PREFIX ) {
	const [ currentIndex, setCurrentIndex ] = useState( 0 );

	const getNewKey = useCallback( () => {
		setCurrentIndex( ( oldIndex ) => oldIndex + 1 );
		return `${ prefix }-${ currentIndex }`;
	}, [ currentIndex, prefix ] );

	return getNewKey;
}

/**
 * Component handling addition and deletion of analytics entries.
 */
function AnalyticsOptions() {
	const [ detailsInitialOpen, setDetailsInitialOpen ] = useState( null );

	const getNewKey = useUniqueNewKey();
	const { editedOptions, fetchingOptions, originalOptions, updateOptions } = useContext( Options );
	const { analytics } = editedOptions;

	/**
	 * Set the initial open state of the details component.
	 */
	useEffect( () => {
		if ( fetchingOptions ) {
			return;
		}

		if ( 'boolean' === typeof detailsInitialOpen ) {
			return;
		}

		setDetailsInitialOpen( ! Boolean( Object.keys( analytics ).length ) );
	}, [ analytics, fetchingOptions, detailsInitialOpen ] );

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
							__( 'https://www.ampproject.org/docs/analytics/analytics-vendors', 'amp' ),
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
			{ Object.entries( analytics || {} ).map( ( [ key, { type, config } ] ) => (
				<AnalyticsEntry
					key={ `analytics-entry-${ key }` }
					entryId={ key }
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
				isPrimary
				onClick={ () => {
					updateOptions( {
						analytics: {
							...analytics,
							[ getNewKey() ]: {
								type: '',
								config: '{}',
							},
						},
					} );
				} }
			>
				{ __( 'Add entry', 'amp' ) }
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
