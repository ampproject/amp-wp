/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { createInterpolateElement } from '@wordpress/element';

const GOOGLE_ANALYTICS_VENDOR = 'googleanalytics';

const GOOGLE_ANALYTICS_NOTICE = createInterpolateElement(
	__( 'For Google Analytics please consider using <GoogleSiteKitLink>Site Kit by Google</GoogleSiteKitLink>. This plugin configures analytics for both non-AMP and AMP pages alike, avoiding the need to manually provide a separate AMP configuration here. Nevertheless, for documentation on manual configuration see <GoogleAnalyticsDevGuideLink>Adding Analytics to your AMP pages</GoogleAnalyticsDevGuideLink>.', 'amp' ),
	{
		/* eslint-disable jsx-a11y/anchor-has-content -- Anchor has content defined in the translated string. */
		GoogleSiteKitLink: <a href="https://wordpress.org/plugins/google-site-kit/" target="_blank" rel="noreferrer" />,
		GoogleAnalyticsDevGuideLink: <a href="https://developers.google.com/analytics/devguides/collection/amp-analytics/" target="_blank" rel="noreferrer" />,
		/* eslint-enable jsx-a11y/anchor-has-content */
	},
);

export default {
	'': {
		sample: '{}',
	},
	adobeanalytics: {
		notice: '',
		sample: JSON.stringify(
			{
				requests: {
					myClick: '{click}&v1={eVar1}',
				},
				vars: {
					host: 'metrics.example.com',
					reportSuites: 'reportSuiteID',
				},
				triggers: {
					pageLoad: {
						on: 'visible',
						request: 'pageview',
					},
					click: {
						on: 'click',
						selector: '#test1',
						request: 'myClick',
						vars: {
							eVar1: 'button clicked',
						},
					},
					linkers: {
						enabled: true,
						destinationDomains: [ 'localhost' ],
					},
				},
			},
			null,
			'\t',
		),
	},
	alexametrics: {
		notice: '',
		sample: JSON.stringify(
			{
				vars: {
					atrk_acct: 'YOURACCOUNT',
					domain: 'YOURDOMAIN',
				},
			},
			null,
			'\t',
		),
	},
	baiduanalytics: {
		notice: '',
		sample: JSON.stringify(
			{
				vars: {
					token: 'your-token',
				},
				triggers: {
					pageview: {
						on: 'visible',
						request: 'pageview',
					},
				},
			},
			null,
			'\t',
		),
	},
	facebookpixel: {
		notice: '',
		sample: JSON.stringify(
			{
				vars: {
					pixelId: '1760516960931795',
				},
				triggers: {
					trackPageview: {
						on: 'visible',
						request: 'pageview',
					},
					trackEvent: {
						on: 'click',
						selector: '#test1',
						request: 'event',
						vars: {
							eventName: 'ClickedBtn-x',
						},
					},
					trackSearch: {
						on: 'visible',
						request: 'eventSearch',
						vars: {
							search_string: 'How to use Facebook Pixel in AMP Pages',
						},
					},
					trackAddToWishlist: {
						on: 'click',
						selector: '#test1',
						request: 'eventAddToWishlist',
						vars: {
							content_ids: [ '1234' ],
							content_name: 'The Avengers',
							content_category: 'Entertainment',
							value: 1.50,
							currency: 'USD',
						},
					},
				},
			},
			null,
			'\t',
		),
	},

	[ GOOGLE_ANALYTICS_VENDOR ]: {
		notice: GOOGLE_ANALYTICS_NOTICE,
		sample: JSON.stringify(
			{
				vars: {
					account: '👉 ' + __( 'Provide site tracking ID here (e.g. UA-XXXXX-Y)', 'amp' ) + ' 👈',
				},
				triggers: {
					trackPageview: {
						on: 'visible',
						request: 'pageview',
					},
				},
			},
			null,
			'\t',
		),
	},
	gtag: {
		notice: GOOGLE_ANALYTICS_NOTICE,
		sample: JSON.stringify(
			{
				vars: {
					gtag_id: '<GA_MEASUREMENT_ID>',
					config: {
						'<GA_MEASUREMENT_ID>': { groups: 'default' },
					},
				},
			},
			null,
			'\t',
		),
	},
	googletagmanager: { // Throw notice to if user enters googletagmanager as vendor.
		notice: GOOGLE_ANALYTICS_NOTICE,
		sample: '{}',
	},
	newrelic: {
		notice: '',
		sample: JSON.stringify(
			{
				vars: {
					appId: '555555',
					licenseKey: '2fffffffff',
				},
			},
			null,
			'\t',
		),
	},
	nielsen: {
		notice: '',
		sample: JSON.stringify(
			{
				vars: {
					apid: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',
					apv: '1.0',
					section: 'Entertainment',
					segA: 'Music',
					segB: 'News',
					segC: 'Branded_Content',
				},
			},
			null,
			'\t',
		),
	},
	metrika: {
		notice: '',
		sample: JSON.stringify(
			{
				vars: {
					counterId: '71129776',
					yaParams: '{\'key\': \'value\'}',
				},
				requests: {},
				triggers: {
					notBounce: {
						on: 'timer',
						timerSpec: {
							immediate: false,
							interval: 15,
							maxTimerLength: 14,
						},
						request: 'notBounce',
					},
					someGoalReach: {
						on: 'click',
						selector: '#test1',
						request: 'reachGoal',
						vars: {
							goalId: 'superGoalId',
							yaParams: '{\'inner-key\': \'inner-value\'}',
						},
					},
					halfScroll: {
						on: 'scroll',
						scrollSpec: {
							verticalBoundaries: [
								50,
							],
						},
						request: 'reachGoal',
						vars: {
							goalId: 'halfScrollGoal',
						},
					},
				},
			},
			null,
			'\t',
		),
	},
};
