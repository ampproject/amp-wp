/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useCallback, useMemo, useState } from '@wordpress/element';

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

/**
 * Internal dependencies
 */
import './style.scss';

export function BuildSelector( { buildOptions, onOptionSelect } ) {
	const [ term, setTerm ] = useState( '' );

	const handleInputChange = useCallback( ( event ) => {
		const newTerm = event.target.value.trim().toLowerCase();
		setTerm( newTerm );
	}, [] );

	const handleOptionSelect = useCallback(
		( buildLabel ) => {
			const newOption = buildOptions.find(
				( option ) => option.label === buildLabel
			);
			onOptionSelect( newOption );
		},
		[ buildOptions, onOptionSelect ]
	);

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
		</Combobox>
	);
}
