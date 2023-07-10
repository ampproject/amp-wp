/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { createInterpolateElement } from '@wordpress/element';

const GOOGLE_ANALYTICS_VENDOR = 'googleanalytics';

const GOOGLE_ANALYTICS_NOTICE = createInterpolateElement(
	__(
		'For Google Analytics please consider using <GoogleSiteKitLink>Site Kit by Google</GoogleSiteKitLink>. This plugin configures analytics for both non-AMP and AMP pages alike, avoiding the need to manually provide a separate AMP configuration here. Nevertheless, for documentation on manual configuration see <GoogleAnalyticsDevGuideLink>Adding Analytics to your AMP pages</GoogleAnalyticsDevGuideLink>.',
		'amp'
	),
	{
		/* eslint-disable jsx-a11y/anchor-has-content -- Anchor has content defined in the translated string. */
		GoogleSiteKitLink: (
			<a
				href="https://wordpress.org/plugins/google-site-kit/"
				target="_blank"
				rel="noreferrer"
			/>
		),
		GoogleAnalyticsDevGuideLink: (
			<a
				href="https://developers.google.com/analytics/devguides/collection/amp-analytics/"
				target="_blank"
				rel="noreferrer"
			/>
		),
		/* eslint-enable jsx-a11y/anchor-has-content */
	}
);

const GOOGLE_ANALYTICS_DEPRECATION_NOTICE = createInterpolateElement(
	__(
		'The <Code>googleanalytics</Code> type is obsolete as Google Analytics has switched to use gtag in the transition from Universal Analytics (UA) to GA4. Please use gtag instead. Learn more about <GA4AMP>GA4 in AMP</GA4AMP>.',
		'amp'
	),
	{
		/* eslint-disable jsx-a11y/anchor-has-content -- Anchor has content defined in the translated string. */
		GA4AMP: (
			<a
				href="https://support.google.com/analytics/topic/13706307?hl=en&ref_topic=9303319&sjid=7478006548081699185-NA"
				target="_blank"
				rel="noreferrer"
			/>
		),
		Code: <code />,
		/* eslint-enable jsx-a11y/anchor-has-content */
	}
);

export default {
	'': {
		sample: '{}',
	},
	adobeanalytics: {
		notice: createInterpolateElement(
			__('<a>Learn more</a> about Adobe Analytics in AMP.'),
			{
				/* eslint-disable jsx-a11y/anchor-has-content -- Anchor has content defined in the translated string. */
				a: (
					<a
						href="https://experienceleague.adobe.com/docs/analytics/implementation/other/amp.html"
						target="_blank"
						rel="noreferrer"
					/>
				),
				/* eslint-enable jsx-a11y/anchor-has-content */
			}
		),
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
						destinationDomains: ['localhost'],
					},
				},
			},
			null,
			'\t'
		),
	},
	alexametrics: {
		notice: createInterpolateElement(
			__('<a>Learn more</a> about Alexa metrics in AMP.'),
			{
				/* eslint-disable jsx-a11y/anchor-has-content -- Anchor has content defined in the translated string. */
				a: (
					<a
						href="https://support.alexa.com/hc/en-us/articles/115004090654-Does-Alexa-have-a-Certify-Code-for-AMP"
						target="_blank"
						rel="noreferrer"
					/>
				),
				/* eslint-enable jsx-a11y/anchor-has-content */
			}
		),
		sample: JSON.stringify(
			{
				vars: {
					atrk_acct: '<YOURACCOUNT>',
					domain: '<YOURDOMAIN>',
				},
			},
			null,
			'\t'
		),
	},
	baiduanalytics: {
		notice: createInterpolateElement(
			__('<a>Learn more</a> about Baidu Analytics in AMP.'),
			{
				/* eslint-disable jsx-a11y/anchor-has-content -- Anchor has content defined in the translated string. */
				a: (
					<a
						href="https://tongji.baidu.com/web/help/article?id=268&castk=LTE%3D"
						target="_blank"
						rel="noreferrer"
					/>
				),
				/* eslint-enable jsx-a11y/anchor-has-content */
			}
		),
		sample: JSON.stringify(
			{
				vars: {
					token: '👉 ' + __('Provide Token.', 'amp') + ' 👈',
				},
				triggers: {
					pageview: {
						on: 'visible',
						request: 'pageview',
					},
				},
			},
			null,
			'\t'
		),
	},
	facebookpixel: {
		notice: createInterpolateElement(
			__('<a>Learn more</a> about Facebook Pixel in AMP.'),
			{
				/* eslint-disable jsx-a11y/anchor-has-content -- Anchor has content defined in the translated string. */
				a: (
					<a
						href="https://developers.facebook.com/docs/meta-pixel"
						target="_blank"
						rel="noreferrer"
					/>
				),
				/* eslint-enable jsx-a11y/anchor-has-content */
			}
		),
		sample: JSON.stringify(
			{
				vars: {
					pixelId:
						'👉 ' +
						__('Provide Facebook Pixel ID here.', 'amp') +
						' 👈',
				},
				triggers: {
					trackPageview: {
						on: 'visible',
						request: 'pageview',
					},
				},
			},
			null,
			'\t'
		),
	},
	[GOOGLE_ANALYTICS_VENDOR]: {
		notice: GOOGLE_ANALYTICS_NOTICE,
		warning: GOOGLE_ANALYTICS_DEPRECATION_NOTICE,
		sample: JSON.stringify(
			{
				vars: {
					account:
						'👉 ' +
						__(
							'Provide site tracking ID here (e.g. UA-XXXXX-Y)',
							'amp'
						) +
						' 👈',
				},
				triggers: {
					trackPageview: {
						on: 'visible',
						request: 'pageview',
					},
				},
			},
			null,
			'\t'
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
			'\t'
		),
	},
	googletagmanager: {
		// Throw notice to if user enters googletagmanager as vendor.
		notice: GOOGLE_ANALYTICS_NOTICE,
		sample: '{}',
	},
	newrelic: {
		notice: createInterpolateElement(
			__('<a>Learn more</a> about New Relic in AMP.'),
			{
				/* eslint-disable jsx-a11y/anchor-has-content -- Anchor has content defined in the translated string. */
				a: (
					<a
						href="https://docs.newrelic.com/docs/browser/browser-monitoring/installation/install-browser-monitoring-agent/"
						target="_blank"
						rel="noreferrer"
					/>
				),
				/* eslint-enable jsx-a11y/anchor-has-content */
			}
		),
		sample: JSON.stringify(
			{
				vars: {
					appId: '👉 ' + __('Provide App ID here.', 'amp') + ' 👈',
					licenseKey: '<LICENSE_KEY>',
				},
			},
			null,
			'\t'
		),
	},
	nielsen: {
		notice: createInterpolateElement(
			__('<a>Learn more</a> about Nielsen in AMP.'),
			{
				/* eslint-disable jsx-a11y/anchor-has-content -- Anchor has content defined in the translated string. */
				a: (
					<a
						href="https://engineeringportal.nielsen.com/docs/DCR_Static_Google_AMP_Cloud_API"
						target="_blank"
						rel="noreferrer"
					/>
				),
				/* eslint-enable jsx-a11y/anchor-has-content */
			}
		),
		sample: JSON.stringify(
			{
				vars: {
					apid: '👉 ' + __('Provide App ID here.', 'amp') + ' 👈',
					apv: '1.0',
					section: 'Entertainment',
					segA: 'Music',
				},
			},
			null,
			'\t'
		),
	},
	// Yandex Metrika
	metrika: {
		notice: createInterpolateElement(
			__('<a>Learn more</a> about Yandex Metrika in AMP.'),
			{
				/* eslint-disable jsx-a11y/anchor-has-content -- Anchor has content defined in the translated string. */
				a: (
					<a
						href="https://yandex.com/support/metrica/code/install-counter-amp.html"
						target="_blank"
						rel="noreferrer"
					/>
				),
				/* eslint-enable jsx-a11y/anchor-has-content */
			}
		),
		sample: JSON.stringify(
			{
				vars: {
					counterId: '<COUNTER_ID>',
					yaParams: "{'key': 'value'}",
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
							yaParams: "{'inner-key': 'inner-value'}",
						},
					},
					halfScroll: {
						on: 'scroll',
						scrollSpec: {
							verticalBoundaries: [50],
						},
						request: 'reachGoal',
						vars: {
							goalId: 'halfScrollGoal',
						},
					},
				},
			},
			null,
			'\t'
		),
	},
};
