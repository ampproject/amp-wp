/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Button, Icon } from '@wordpress/components';
import { useCallback } from '@wordpress/element';

/**
 * External dependencies
 */
import PropTypes from 'prop-types';

export function Nav( { activePageIndex, exitLink, pages, setActivePageIndex } ) {
	const moveForward = useCallback( () => {
		setActivePageIndex( activePageIndex + 1 );
	}, [ activePageIndex, setActivePageIndex ] );

	const moveBack = useCallback( () => {
		setActivePageIndex( activePageIndex - 1 );
	}, [ activePageIndex, setActivePageIndex ] );

	return (
		<div className="amp-setup-nav">
			<div className="amp-setup-nav__close">
				<Button isLink href={ exitLink }>
					{ __( 'Close', 'amp' ) }
				</Button>
			</div>
			<div className="amp-setup-nav__prev-next">
				{ 0 === activePageIndex
					? (
						<span className="amp-setup-nav__placeholder">
							{ ' ' }
						</span>
					)
					: (
						<Button
							className="amp-setup-nav__prev"
							onClick={ moveBack }
						>
							<Icon className="amp-mobile-hide" icon="arrow-left-alt2" size={ 18 } />
							<span className="amp-mobile-hide">
								{ __( 'Previous Step', 'amp' ) }
							</span>
							<span className="amp-mobile-show">
								{ __( 'Previous', 'amp' ) }
							</span>
						</Button>
					)
				}

				<Button
					className="amp-setup-nav__next"
					disabled={ pages.length - 1 === activePageIndex }
					onClick={ moveForward }
				>
					<span className="amp-mobile-hide">
						{ __( 'Next Step', 'amp' ) }
					</span>
					<span className="amp-mobile-show">
						{ __( 'Next', 'amp' ) }
					</span>
					<Icon className="amp-mobile-hide" icon="arrow-right-alt2" size={ 18 } />
				</Button>

			</div>
		</div>
	);
}

Nav.propTypes = {
	activePageIndex: PropTypes.number.isRequired,
	exitLink: PropTypes.string.isRequired,
	pages: PropTypes.arrayOf(
		PropTypes.shape( {
			title: PropTypes.string.isRequired,
		} ),
	).isRequired,
	setActivePageIndex: PropTypes.func.isRequired,
};
