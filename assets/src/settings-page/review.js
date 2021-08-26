/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { IconLaptopToggles } from '../components/svg/icon-laptop-toggles';

/**
 * Review component on the settings screen.
 */
export function Review() {
	return (
		<div className="settings-review">
			<p>
				{ __( 'Your site is ready to bring great experiences to your users! In Standard mode there is a single AMP version of your site. Browse your site and ensure the functionality and look-and-feel are as expected.', 'amp' ) }
			</p>
			<h3 className="settings-review__heading">
				<IconLaptopToggles />
				{ __( 'Need help?', 'amp' ) }
			</h3>
			<ul className="settings-review__list">
				{ /* dangerouslySetInnerHTML reason: Injection of a link. */ }
				<li dangerouslySetInnerHTML={ {
					__html: sprintf(
						/* translators: placeholder is a link to support forum. */
						__( 'Reach out in the <a href="%s" target="_blank" rel="noreferrer noopener">support forums</a>', 'amp' ),
						'https://wordpress.org/support/plugin/amp/#new-topic-0',
					),
				} } />
				{ /* dangerouslySetInnerHTML reason: Injection of a link. */ }
				<li dangerouslySetInnerHTML={ {
					__html: sprintf(
						/* translators: placeholder is the template mode section anchor. */
						__( 'Try a different setting in the <a href="%s">template mode section</a>', 'amp' ),
						'#template-modes',
					),
				} } />
				{ /* dangerouslySetInnerHTML reason: Injection of a link. */ }
				<li dangerouslySetInnerHTML={ {
					__html: sprintf(
						/* translators: placeholder is a link to the plugin site. */
						__( '<a href="%s" target="_blank" rel="noreferrer noopener">Learn more</a> how the PX plugin works', 'amp' ),
						'https://amp-wp.org/documentation/how-the-plugin-works/',
					),
				} } />
			</ul>
		</div>
	);
}
