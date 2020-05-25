/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Button, Icon } from '@wordpress/components';
import { useContext } from '@wordpress/element';

/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import { Navigation } from '../navigation-context-provider';

export function Nav( { exitLink } ) {
	const { activePageIndex, canGoForward, goBack, goForward } = useContext( Navigation );

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
							onClick={ goBack }
						>
							<Icon className="amp-mobile-hide" icon="arrow-left-alt2" size={ 18 } />
							<span>
								{ __( 'Previous', 'amp' ) }
								{ ' ' }
								<span className="amp-mobile-hide">
									{ __( 'Step', 'amp' ) }
								</span>
							</span>
						</Button>
					)
				}

				<Button
					className="amp-setup-nav__next"
					disabled={ ! canGoForward }
					onClick={ goForward }
				>
					<span>
						{ __( 'Next', 'amp' ) }
						{ ' ' }
						<span className="amp-mobile-hide">
							{ __( 'Step', 'amp' ) }
						</span>
					</span>
					<Icon className="amp-mobile-hide" icon="arrow-right-alt2" size={ 18 } />
				</Button>

			</div>
		</div>
	);
}

Nav.propTypes = {
	exitLink: PropTypes.string.isRequired,
};
