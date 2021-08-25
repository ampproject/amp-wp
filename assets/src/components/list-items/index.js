/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import classnames from 'classnames';

/**
 * Internal dependencies
 */
import './style.scss';

export function ListItems( { className = '', disc = false, heading, items } ) {
	const slugify = ( value ) => {
		if ( 'string' !== typeof value ) {
			return value;
		}

		return value
			.toString()
			.trim()
			.toLowerCase()
			.replace( /\s+/g, '-' )
			.replace( /[^\w\-]+/g, '-' )
			.replace( /\-\-+/g, '-' )
			.replace( /^-+/, '-' )
			.replace( /-+$/, '-' );
	};

	if ( disc ) {
		className = classnames( 'list-items--list-style-disc', className );
	}

	return (
		<ul className={ classnames( 'list-items', className ) }>
			{ heading && (
				<li className="list-items__item">
					<h4 className="list-items__heading">
						{ heading }
					</h4>
				</li>
			) }
			{ items.map( ( item, index ) => {
				const key = item.label ? slugify( item.label ) + `-${ index }` : index.toString();

				return (
					<li key={ key } className="list-items__item">
						{ item.label && (
							<strong className="list-items__item-key">
								{ item.label }
							</strong>
						) }
						{ item.value ? (
							<span className="list-items__item-value">
								{ item.value }
							</span>
						) : '-' }
					</li>
				);
			} ) }
		</ul>
	);
}

ListItems.propTypes = {
	className: PropTypes.string,
	heading: PropTypes.string,
	disc: PropTypes.bool,
	items: PropTypes.arrayOf( PropTypes.shape( {
		label: PropTypes.string,
		value: PropTypes.oneOfType( [
			PropTypes.string,
			PropTypes.number,
			PropTypes.node,
		] ).isRequired,
	} ) ).isRequired,
};
