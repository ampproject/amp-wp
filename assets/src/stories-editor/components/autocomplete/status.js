
/**
 * External dependencies
 */
import PropTypes from 'prop-types';

const Status = ( {
	length,
	queryLength,
	minQueryLength,
	selectedOption,
	selectedOptionIndex,
	tQueryTooShort,
	tNoResults,
	tSelectedOption,
	tResults,
} ) => {
	const queryTooShort = queryLength < minQueryLength;
	const noResults = length === 0;

	const contentSelectedOption = selectedOption ?
		tSelectedOption( selectedOption, length, selectedOptionIndex ) :
		'';

	let content = null;
	if ( queryTooShort ) {
		content = tQueryTooShort( minQueryLength );
	} else if ( noResults ) {
		content = tNoResults();
	} else {
		content = tResults( length, contentSelectedOption );
	}

	return (
		<div
			aria-atomic="true"
			aria-live="polite"
			role="status"
			style={ {
				border: '0',
				clip: 'rect(0 0 0 0)',
				height: '1px',
				marginBottom: '-1px',
				marginRight: '-1px',
				overflow: 'hidden',
				padding: '0',
				position: 'absolute',
				whiteSpace: 'nowrap',
				width: '1px',
			} }
		>
			{ content }
			{ queryTooShort && ','.repeat( queryLength ) }
		</div>
	);
};

Status.propTypes = {
	length: PropTypes.number.isRequired,
	queryLength: PropTypes.number.isRequired,
	minQueryLength: PropTypes.number.isRequired,
	selectedOption: PropTypes.string,
	selectedOptionIndex: PropTypes.number,
	tQueryTooShort: PropTypes.func.isRequired,
	tNoResults: PropTypes.func.isRequired,
	tSelectedOption: PropTypes.func.isRequired,
	tResults: PropTypes.func.isRequired,
};

export default Status;
