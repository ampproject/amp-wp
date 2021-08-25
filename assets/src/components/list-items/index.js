/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import classnames from 'classnames';

/**
 * Internal dependencies
 */
import './style.scss';

export function ListItems( { className = '', isDisc = false, heading, items } ) {
	return (
		<ul className={ classnames( 'list-items', className, { 'list-items--list-style-disc': isDisc } ) }>
			{ heading && (
				<li className="list-items__item">
					<h4 className="list-items__heading">
						{ heading }
					</h4>
				</li>
			) }
			{ items.map( ( item, index ) => {
				return (
					<li key={ index } className="list-items__item">
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
	isDisc: PropTypes.bool,
	items: PropTypes.arrayOf( PropTypes.shape( {
		label: PropTypes.string,
		value: PropTypes.oneOfType( [
			PropTypes.string,
			PropTypes.number,
			PropTypes.node,
		] ).isRequired,
	} ) ).isRequired,
};
