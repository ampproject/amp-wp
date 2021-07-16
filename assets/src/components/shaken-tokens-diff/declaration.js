/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import Indentation from '../indentation';
import DiffLine from './diff-line';
import Selector from './selector';

export default function Declaration( {
	token,
	indentation = 0,
	isStyleAttribute,
} ) {
	const [ isKept, selectors, declaration ] = token;

	if ( ! declaration && typeof selectors === 'string' ) {
		return (
			<span className="declaration-block">
				<DiffLine
					className="selector"
					isIns={ isKept }
					isDel={ ! isKept }
				>
					<Indentation size={ indentation } isTab />
					{ selectors }
				</DiffLine>
			</span>
		);
	}

	const selectorsList = Object.keys( selectors );

	return (
		<span className="declaration-block">
			{ selectorsList.map( ( selector, index ) => (
				<DiffLine
					key={ selector }
					className="selector"
					isIns={ selectors[ selector ] }
					isDel={ ! selectors[ selector ] }
				>
					<Indentation size={ indentation } isTab />
					<Selector
						selector={ selector }
						isLast={ index === selectorsList.length - 1 }
						isStyleAttribute={ isStyleAttribute }
					/>
				</DiffLine>
			) ) }
			<DiffLine
				isIns={ isKept }
				isDel={ ! isKept }
			>
				<Indentation size={ indentation + 1 } isTab />
				{ `{ ${ declaration.join( '; ' ) } }` }
			</DiffLine>
		</span>
	);
}
Declaration.propTypes = {
	token: PropTypes.array,
	indentation: PropTypes.number,
	isStyleAttribute: PropTypes.bool,
};
