/**
 * External dependencies
 */
import OriginalAutocomplete from 'accessible-autocomplete/react';
/**
 * WordPress dependencies
 */
import {
	Button,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import Status from './status';
import 'accessible-autocomplete/src/autocomplete.css';
import './edit.css';

class Autocomplete extends OriginalAutocomplete {
	/**
	 * Overrides default method to prevent an issue with
	 * scrollbars appearing inadvertently.
	 */
	handleInputBlur() {}

	handleClearClick() {
		this.state.query = '';
		this.forceUpdate();
		this.props.onConfirm( null );
	}

	/**
	 * Override render method, to add clear font button.
	 *
	 */
	render() { // eslint-disable-line complexity
		const {
			cssNamespace,
			displayMenu,
			id,
			minLength,
			name,
			placeholder,
			required,
			tNoResults,
			tStatusQueryTooShort,
			tStatusSelectedOption,
			tStatusResults,
			ariaLabelBy,
		} = this.props;
		const { focused, hovered, menuOpen, options, query, selected } = this.state;
		const autoselect = this.hasAutoselect();

		const inputFocused = focused === -1;
		const noOptionsAvailable = options.length === 0;
		const queryLength = ( query ) ? query.length : 0;
		const queryNotEmpty = queryLength !== 0;
		const queryLongEnough = queryLength >= minLength;
		const showNoOptionsFound = this.props.showNoOptionsFound &&
			inputFocused && noOptionsAvailable && queryNotEmpty && queryLongEnough;

		const wrapperClassName = `${ cssNamespace }__wrapper`;

		const inputClassName = `${ cssNamespace }__input`;
		const componentIsFocused = focused !== null;
		const inputModifierFocused = componentIsFocused ? ` ${ inputClassName }--focused` : '';
		const inputModifierType = this.props.showAllValues ? ` ${ inputClassName }--show-all-values` : ` ${ inputClassName }--default`;
		const optionFocused = focused !== -1 && focused !== null;

		const menuClassName = `${ cssNamespace }__menu`;
		const menuModifierDisplayMenu = `${ menuClassName }--${ displayMenu }`;
		const menuIsVisible = menuOpen || showNoOptionsFound;
		const menuModifierVisibility = `${ menuClassName }--${ ( menuIsVisible ) ? 'visible' : 'hidden' }`;

		const optionClassName = `${ cssNamespace }__option`;

		const hintClassName = `${ cssNamespace }__hint`;
		const selectedOptionText = this.templateInputValue( options[ selected ] );
		const optionBeginsWithQuery = selectedOptionText &&
			selectedOptionText.toLowerCase().indexOf( query.toLowerCase() ) === 0;
		const hintValue = ( optionBeginsWithQuery && autoselect ) ?
			query + selectedOptionText.substr( queryLength ) :
			'';

		return (
			<div className={ wrapperClassName } onKeyDown={ this.handleKeyDown } role="combobox" tabIndex="-1" aria-expanded={ menuOpen ? 'true' : 'false' }>
				<Status
					length={ options.length }
					queryLength={ queryLength }
					minQueryLength={ minLength }
					selectedOption={ this.templateInputValue( options[ selected ] ) }
					selectedOptionIndex={ selected }
					tQueryTooShort={ tStatusQueryTooShort }
					tNoResults={ tNoResults }
					tSelectedOption={ tStatusSelectedOption }
					tResults={ tStatusResults }
				/>
				{ hintValue && (
					<span>
						<input className={ hintClassName } readOnly tabIndex="-1" value={ hintValue } />
					</span>
				) }

				<input
					aria-activedescendant={ optionFocused ? `${ id }__option--${ focused }` : '' }
					aria-owns={ `${ id }__listbox` }
					autoComplete="off"
					className={ `${ inputClassName }${ inputModifierFocused }${ inputModifierType }` }
					id={ id }
					onClick={ ( event ) => this.handleInputClick( event ) }
					onBlur={ this.handleInputBlur }
					onChange={ this.handleInputChange }
					onFocus={ this.handleInputFocus }
					name={ name }
					placeholder={ placeholder }
					ref={ ( inputElement ) => {
						this.elementReferences[ -1 ] = inputElement;
					} }
					type="text"
					required={ required }
					value={ query }
				/>
				{ query && ! menuOpen && queryLongEnough && (

					<Button
						icon="no"
						label={ __( 'Clear Font', 'amp' ) }
						onClick={ ( event ) => this.handleClearClick( event ) }
						className="autocomplete__icon"
					/>
				) }

				<ul
					className={ `${ menuClassName } ${ menuModifierDisplayMenu } ${ menuModifierVisibility }` }
					onMouseLeave={ ( event ) => this.handleListMouseLeave( event ) }
					id={ `${ id }__listbox` }
					aria-labelledby={ ariaLabelBy }
					role="listbox"
				>
					{ options.map( ( option, index ) => {
						const showFocused = focused === -1 ? selected === index : focused === index;
						const optionModifierFocused = showFocused && hovered === null ? ` ${ optionClassName }--focused` : '';
						const optionModifierOdd = ( index % 2 ) ? ` ${ optionClassName }--odd` : '';

						return (
							<li
								aria-selected={ focused === index }
								className={ `${ optionClassName }${ optionModifierFocused }${ optionModifierOdd }` }
								dangerouslySetInnerHTML={ { __html: this.templateSuggestion( option ) } }
								id={ `${ id }__option--${ index }` }
								key={ index }
								onBlur={ ( event ) => this.handleOptionBlur( event, index ) }
								onClick={ ( event ) => this.handleOptionClick( event, index ) }
								onKeyDown={ ( event ) => this.handleOptionClick( event, index ) }
								onMouseEnter={ ( event ) => this.handleOptionMouseEnter( event, index ) }
								ref={ ( optionEl ) => {
									this.elementReferences[ index ] = optionEl;
								} }
								role="option"
								tabIndex="-1"
							/>
						);
					} ) }

					{ showNoOptionsFound && (
						<li
							className={ `${ optionClassName } ${ optionClassName }--no-results` }
							role="option"
							tabIndex="-1"
						>
							{ tNoResults() }
						</li>
					) }
				</ul>
			</div>
		);
	}
}

export default Autocomplete;
