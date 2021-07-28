/**
 * External dependencies
 */
import classnames from 'classnames';
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { FormToggle } from '@wordpress/components';

/**
 * Internal dependencies
 */
import { Loading } from '../loading';
import Declaration from './declaration';

export default function ShakenTokensDiff( {
	origin,
	tokens,
} ) {
	const [ showRemovedStyles, setShowRemovedStyles ] = useState( false );
	const [ tokensTree, setTokensTree ] = useState();
	const [ insCount, setInsCount ] = useState( 0 );
	const [ delCount, setDelCount ] = useState( 0 );

	useEffect( () => {
		if ( ! tokens || ! Array.isArray( tokens ) || tokens.length === 0 ) {
			return;
		}

		let _insCount = 0;
		let _delCount = 0;

		const _tokensTree = tokens.reduce( ( acc, token, index, source ) => {
			let indentation = 0;

			if ( index > 0 ) {
				indentation = acc[ index - 1 ].props.indentation;

				const previousSelector = source[ index - 1 ][ 1 ];

				if ( typeof previousSelector === 'string' ) {
					const openingBracesCount = previousSelector.split( '{' ).length;
					const closingBracesCount = previousSelector.split( '}' ).length;

					if ( openingBracesCount > closingBracesCount ) {
						indentation++;
					} else if ( openingBracesCount < closingBracesCount ) {
						indentation--;
					}
				}
			}

			const [ isKept ] = token;
			if ( isKept ) {
				_insCount++;
			} else {
				_delCount++;
			}

			return [
				...acc,
				<Declaration
					key={ index }
					token={ token }
					indentation={ indentation }
					origin={ origin }
				/>,
			];
		}, [] );

		setTokensTree( _tokensTree );
		setInsCount( _insCount );
		setDelCount( _delCount );
	}, [ origin, tokens ] );

	if ( ! tokensTree ) {
		return <Loading />;
	}

	return (
		<>
			{ insCount === 0 && (
				<p>
					<em>
						{ delCount === 0
							? __( 'The stylesheet was empty after minification (removal of comments and whitespace).', 'amp' )
							: __( 'All of the stylesheet was removed during tree-shaking.', 'amp' )
						}
					</em>
				</p>
			) }
			{ ( insCount !== 0 || delCount !== 0 ) && (
				<>
					{ delCount > 0 && (
						<p>
							<FormToggle
								checked={ showRemovedStyles }
								onChange={ () => setShowRemovedStyles( ( value ) => ! value ) }
							/>
							{ ' ' }
							{ __( 'Show styles removed during tree-shaking', 'amp' ) }
						</p>
					) }
					<code
						className={ classnames( 'shaken-stylesheet', {
							'removed-styles-shown': showRemovedStyles,
						} ) }
					>
						{ tokensTree }
					</code>
				</>
			) }
		</>
	);
}
ShakenTokensDiff.propTypes = {
	origin: PropTypes.string,
	tokens: PropTypes.array,
};
