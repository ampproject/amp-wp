
/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { Component } from '@wordpress/element';

export default class Status extends Component {
	constructor( props ) {
		super( props );
		this.state = {
			bump: false,
		};
	}

	componentWillReceiveProps( { queryLength } ) {
		const hasChanged = queryLength !== this.props.queryLength;
		if ( hasChanged ) {
			this.setState( ( { bump } ) => ( { bump: ! bump } ) );
		}
	}

	render() {
		const {
			length,
			queryLength,
			minQueryLength,
			selectedOption,
			selectedOptionIndex,
			tQueryTooShort,
			tNoResults,
			tSelectedOption,
			tResults,
		} = this.props;
		const { bump } = this.state;

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

		return <div
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
			<span>{ bump ? ',' : ',,' }</span>
		</div>;
	}
}

Status.propTypes = {
	length: PropTypes.number.isRequired,
	queryLength: PropTypes.number.isRequired,
	minQueryLength: PropTypes.number.isRequired,
	selectedOption: PropTypes.func,
	selectedOptionIndex: PropTypes.number,
	tQueryTooShort: PropTypes.func.isRequired,
	tNoResults: PropTypes.func.isRequired,
	tSelectedOption: PropTypes.func.isRequired,
	tResults: PropTypes.func.isRequired,
};
