/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useMemo, useState } from '@wordpress/element';

/**
 * External dependencies
 */
import {
	Combobox,
	ComboboxInput,
	ComboboxPopover,
	ComboboxList,
	ComboboxOption,
} from '@reach/combobox';
import '@reach/combobox/styles.css';

/**
 * Internal dependencies
 */
import './style.css';

export function BuildSelector( { buildOptions, onOptionSelect } ) {
	const [ term, setTerm ] = useState( '' );

	const handleInputChange = ( event ) => {
		const newTerm = event.target.value.trim().toLowerCase();
		if ( newTerm !== term ) {
			setTerm( newTerm );
		}
	};

	const handleOptionSelect = ( buildLabel ) => {
		const newOption = buildOptions.find(
			( option ) => option.label === buildLabel
		);
		onOptionSelect( newOption );
	};

	const results = useMemo( () => {
		return term === ''
			? buildOptions.slice( 0, 5 ) // Show the first 5 options by default.
			: buildOptions.filter( ( option ) =>
					option.label.toLowerCase().includes( term )
			  );
	}, [ term, buildOptions ] );

	return (
		<Combobox openOnFocus onSelect={ handleOptionSelect }>
			<ComboboxInput
				onChange={ handleInputChange }
				placeholder={ __( 'Enter build name', 'amp-qa-tester' ) }
			/>

			{ results && (
				<ComboboxPopover portal={ false }>
					{ results.length > 0 ? (
						<ComboboxList persistSelection>
							{ results.slice( 0, 10 ).map( ( option, index ) => (
								<ComboboxOption
									key={ index }
									value={ option.label }
								/>
							) ) }
						</ComboboxList>
					) : (
						<span style={ { display: 'block', margin: 8 } }>
							{ __( 'No results found', 'amp-qa-tester' ) }
						</span>
					) }
				</ComboboxPopover>
			) }
		</Combobox>
	);
}
