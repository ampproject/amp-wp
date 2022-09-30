/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import {
	createInterpolateElement,
	useState,
	useEffect,
} from '@wordpress/element';
import { Button, ExternalLink } from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';
import { __, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import './style.scss';
import { Selectable } from '../selectable';
import {
	AMPNotice,
	NOTICE_SIZE_SMALL,
	NOTICE_TYPE_ERROR,
	NOTICE_TYPE_INFO,
} from '../amp-notice';
import ClipboardButton from '../clipboard-button';
import { Details } from './details';
import { Plugins } from './plugins';
import { RawData } from './raw-data';
import { SiteInfo } from './site-info';
import { Themes } from './themes';
import { ValidatedUrls } from './validated-urls';
import { ValidationResultsNotice } from './validation-results-notice';

/**
 * AMP Support component.
 *
 * @param {Object} props Props for component.
 * @return {JSX.Element} Markup for AMP support component
 */
export function AMPSupport(props) {
	const { data, restEndpoint, args, ampValidatedPostCount } = props;

	const [sending, setSending] = useState(false);
	const [uuid, setUuid] = useState(null);
	const [error, setError] = useState(null);
	const [hasCopied, setHasCopied] = useState(false);
	const [submitSupportRequest, setSubmitSupportRequest] = useState(false);

	/**
	 * Event callback for send button.
	 */
	useEffect(() => {
		(async () => {
			if (!submitSupportRequest || uuid || sending) {
				return;
			}

			setSending(true);
			setUuid(null);
			setError(null);

			try {
				const response = await apiFetch({
					url: restEndpoint,
					method: 'POST',
					data: {
						args,
					},
				});

				if (
					undefined !== response.success &&
					undefined !== response?.data?.uuid
				) {
					setUuid(response.data.uuid);
				} else {
					throw new Error(
						__(
							'Failed to send support request. Please try again later.',
							'amp'
						)
					);
				}
			} catch (exception) {
				setSubmitSupportRequest(false);
				setError(exception.message);
			} finally {
				setSending(false);
			}
		})();
	}, [submitSupportRequest, uuid, sending, restEndpoint, args]);

	return (
		<div className="amp-support">
			<Selectable>
				<h2 className="amp-support__heading">
					{__('AMP Support', 'amp')}
				</h2>
				<p>
					{createInterpolateElement(
						__(
							'In order to best assist you, please tap the Send Data button below to send the following site information to our private database. Once you have done so, copy the the resulting Support UUID in the blue box that appears and include the ID in a new <a>support forum topic</a>. You do not have to submit data to get support, but our team will be able to help you more effectively if you do so.',
							'amp'
						),
						{
							a: (
								// eslint-disable-next-line jsx-a11y/anchor-has-content -- Anchor has content defined in the translated string.
								<a
									href="https://wordpress.org/support/plugin/amp/#new-topic-0"
									rel="noreferrer"
									target="_blank"
								/>
							),
						}
					)}
				</p>

				<ValidationResultsNotice
					data={data}
					args={args}
					ampValidatedPostCount={ampValidatedPostCount}
				/>

				<div className="amp-support__body">
					{data.site_info && <SiteInfo siteInfo={data.site_info} />}

					{data.themes && <Themes themes={data.themes} />}

					{data.plugins && <Plugins plugins={data.plugins} />}

					{data?.errors?.length > 0 && (
						<Details
							title={sprintf(
								/* translators: Placeholder is the number of errors */
								__('Errors (%d)', 'amp'),
								data.errors.length
							)}
							description={__(
								'Please check "Raw Data" for all error information.',
								'amp'
							)}
						/>
					)}

					{data?.error_sources?.length > 0 && (
						<Details
							title={sprintf(
								/* translators: Placeholder is the number of error sources */
								__('Error Sources (%d)', 'amp'),
								data.error_sources.length
							)}
							description={__(
								'Please check "Raw Data" for all error source information.',
								'amp'
							)}
						/>
					)}

					{data?.urls?.length > 0 && (
						<ValidatedUrls validatedUrls={data.urls} />
					)}

					{data && <RawData data={data} />}
				</div>
				<div className="amp-support__footer">
					<Button
						disabled={Boolean(uuid) || sending}
						className="components-button--send-button"
						isPrimary={true}
						onClick={() => {
							setSubmitSupportRequest(true);
						}}
					>
						{uuid && __('Sent', 'amp')}
						{sending && __('Sending…', 'amp')}
						{!uuid && !sending && __('Send data', 'amp')}
					</Button>
					{uuid && (
						<ExternalLink href="https://wordpress.org/support/plugin/amp/#new-topic-0">
							{__('Create support topic', 'amp')}
						</ExternalLink>
					)}
					{error && (
						<AMPNotice
							type={NOTICE_TYPE_ERROR}
							size={NOTICE_SIZE_SMALL}
						>
							{error}
						</AMPNotice>
					)}
				</div>
				{uuid && (
					<AMPNotice
						type={NOTICE_TYPE_INFO}
						size={NOTICE_SIZE_SMALL}
						className={'amp-notice--uuid'}
					>
						{__('Support UUID: ', 'amp')}
						<code>{uuid}</code>
						<ClipboardButton
							isSmall={true}
							text={uuid}
							onCopy={() => setHasCopied(true)}
							onFinishCopy={() => setHasCopied(false)}
						>
							{hasCopied
								? __('Copied!', 'amp')
								: __('Copy UUID', 'amp')}
						</ClipboardButton>
					</AMPNotice>
				)}
			</Selectable>
		</div>
	);
}

AMPSupport.propTypes = {
	restEndpoint: PropTypes.string.isRequired,
	args: PropTypes.any,
	data: PropTypes.shape({
		error_sources: PropTypes.array.isRequired,
		errors: PropTypes.array.isRequired,
		plugins: PropTypes.array,
		site_info: PropTypes.object,
		themes: PropTypes.array,
		urls: PropTypes.array,
	}),
	ampValidatedPostCount: PropTypes.shape({
		all: PropTypes.number.isRequired,
		fresh: PropTypes.number.isRequired,
		stale: PropTypes.number.isRequired,
	}),
};
