/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import classnames from 'classnames';

/**
 * Internal dependencies
 */
import './style.scss';
import { Selectable } from '../selectable';

/**
 * Nav menu.
 *
 * @param {Object}   props         Component props.
 * @param {Array}    props.links   List of links.
 * @param {Function} props.onClick Click handler that receives the original event and a clicked link object.
 */
export function NavMenu( { links = [], onClick } ) {
	return (
		<Selectable
			ElementName="nav"
			className="nav-menu"
		>
			<ul className="nav-menu__list">
				{ links.map( ( link ) => (
					<li key={ link.url } className="nav-menu__item">
						<a
							className={ classnames( 'nav-menu__link', {
								'nav-menu__link--active': link.isActive,
							} ) }
							href={ link.url }
							onClick={ ( event ) => onClick( event, link ) }
						>
							{ link.label }
						</a>
					</li>
				) ) }
			</ul>
		</Selectable>
	);
}

NavMenu.propTypes = {
	links: PropTypes.arrayOf(
		PropTypes.shape( {
			url: PropTypes.string,
			label: PropTypes.string,
			isActive: PropTypes.bool,
		} ),
	),
	onClick: PropTypes.func,
};
