/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';

/**
 * Internal dependencies
 */
import FormattedMemoryValue from '../../../components/formatted-memory-value';
import { ValidationStatusIcon } from '../../../components/icon';
import ShakenTokensDiff from '../../../components/shaken-tokens-diff';
import SourcesStack from '../../../components/sources-stack';
import SourcesSummary from '../../../components/sources-summary';

export default function StylesheetsTableRow( {
	finalSize,
	index,
	isExcessive,
	isExcluded,
	isIncluded,
	origin,
	originalSize,
	orignalTag,
	orignalTagAbbr,
	priority,
	shakenTokens,
	sources,
	totalFinalSize,
	validatedTheme,
} ) {
	const [ expanded, setExpanded ] = useState( false );
	const toggle = () => setExpanded( ( value ) => ! value );

	const minifiedRatio = originalSize === 0 ? 1 : finalSize / originalSize;
	const minifiedPercentage = `${ minifiedRatio <= 1 ? '-' : '+' }${ Math.abs( ( 1.0 - minifiedRatio ) * 100 ).toFixed( 1 ) }%`;

	return (
		<>
			<tr className={ `stylesheet ${ index % 2 ? 'even' : 'odd' } ${ expanded ? 'expanded' : '' }` }>
				<td className="column-stylesheet_expand">
					<Button
						icon={ expanded ? 'arrow-up-alt2' : 'arrow-down-alt2' }
						onClick={ toggle }
						showTooltip={ true }
						label={ __( 'Expand/collapse', 'amp' ) }
					/>
				</td>
				<td className="column-stylesheet_order">
					{ index + 1 }
				</td>
				<td className="column-original_size">
					<FormattedMemoryValue value={ originalSize } unit="B" />
				</td>
				<td className="column-minified">
					{ minifiedPercentage }
				</td>
				<td className="column-final_size">
					<FormattedMemoryValue value={ finalSize } unit="B" />
				</td>
				<td className="column-percentage">
					{ `${ ( finalSize / totalFinalSize * 100 ).toFixed( 1 ) }%` }
				</td>
				<td className="column-priority">
					{ priority }
				</td>
				<td className="column-stylesheet_included">
					{ isIncluded && (
						<ValidationStatusIcon
							isValid
							isBoxed
							title={ __( 'Stylesheet included', 'amp' ) }
						/>
					) }
					{ isExcluded && (
						<ValidationStatusIcon
							isError
							isBoxed
							title={ __( 'Stylesheet excluded due to exceeding CSS budget', 'amp' ) }
						/>
					) }
					{ isExcessive && (
						<ValidationStatusIcon
							isWarning
							isBoxed
							title={ __( 'Stylesheet overruns CSS budget yet it is still included on page', 'amp' ) }
						/>
					) }
				</td>
				<td className="column-markup">
					{ orignalTagAbbr !== orignalTag
						? (
							<abbr title={ orignalTag }>
								<code>
									{ orignalTagAbbr }
								</code>
							</abbr>
						) : (
							<code>
								{ orignalTag }
							</code>
						) }
				</td>
				<td className="column-sources_with_invalid_output">
					<SourcesSummary
						sources={ sources }
						validatedTheme={ validatedTheme }
					/>
				</td>
			</tr>
			{ expanded && (
				<tr className={ `stylesheet-details ${ index % 2 ? 'even' : 'odd' }` }>
					<td colSpan="10">
						<dl className="detailed">
							<dt>
								{ __( 'Original Markup', 'amp' ) }
							</dt>
							<dd>
								<code className="stylesheet-origin-markup">
									{ orignalTag }
								</code>
							</dd>
							<dt>
								{ __( 'Sources', 'amp' ) }
							</dt>
							<dd>
								<SourcesStack sources={ sources } />
							</dd>
							<dt>
								{ __( 'CSS Code', 'amp' ) }
							</dt>
							<dd>
								<ShakenTokensDiff
									tokens={ shakenTokens }
									origin={ origin }
								/>
							</dd>
						</dl>
					</td>
				</tr>
			) }
		</>
	);
}
StylesheetsTableRow.propTypes = {
	finalSize: PropTypes.number,
	index: PropTypes.number,
	isExcessive: PropTypes.bool,
	isExcluded: PropTypes.bool,
	isIncluded: PropTypes.bool,
	origin: PropTypes.string,
	originalSize: PropTypes.number,
	orignalTag: PropTypes.string,
	orignalTagAbbr: PropTypes.string,
	priority: PropTypes.number,
	shakenTokens: PropTypes.array,
	sources: PropTypes.array,
	totalFinalSize: PropTypes.number,
	validatedTheme: PropTypes.string,
};
