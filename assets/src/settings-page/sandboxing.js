/**
 * WordPress dependencies
 */
import { createInterpolateElement, useContext } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { CheckboxControl } from '@wordpress/components';

/**
 * Internal dependencies
 */
import { Options } from '../components/options-context-provider';

/**
 * Component rendering the Sandboxing experiment.
 */
export function Sandboxing() {
	const {
		updateOptions,
		editedOptions: {
			sandboxing_enabled: sandboxingEnabled,
			sandboxing_level: sandboxingLevel,
		},
	} = useContext(Options);

	return (
		<>
			<p>
				{__(
					'Try out a more flexible AMP by generating pages that use AMP components without requiring AMP validity! By selecting a sandboxing level, you are indicating the minimum degree of sanitization. For example, if you selected the loose level but have a page without any POST form and no custom scripts, it will still be served as valid AMP—the same as if you had selected the strict level.',
					'amp'
				)}
			</p>

			<CheckboxControl
				className="sandboxing-enabled"
				checked={sandboxingEnabled}
				label={__('Enable sandboxing experiment', 'amp')}
				onChange={(newChecked) => {
					updateOptions({ sandboxing_enabled: newChecked });
				}}
			/>

			{sandboxingEnabled && (
				<ol>
					<li>
						<input
							type="radio"
							id="sandboxing-level-1"
							checked={1 === sandboxingLevel}
							onChange={() => {
								updateOptions({ sandboxing_level: 1 });
							}}
						/>
						<label htmlFor="sandboxing-level-1">
							{createInterpolateElement(
								__(
									'<b>Loose:</b> Do not remove any AMP-invalid markup by default, including custom scripts. CSS processing is disabled.',
									'amp'
								),
								{ b: <strong /> }
							)}
						</label>
					</li>
					<li>
						<input
							type="radio"
							id="sandboxing-level-2"
							checked={2 === sandboxingLevel}
							onChange={() => {
								updateOptions({ sandboxing_level: 2 });
							}}
						/>
						<label htmlFor="sandboxing-level-2">
							{createInterpolateElement(
								__(
									'<b>Moderate:</b> Remove anything invalid AMP except for POST forms, excessive CSS, and other PX-verified markup.',
									'amp'
								),
								{ b: <strong /> }
							)}
						</label>
					</li>
					<li>
						<input
							type="radio"
							id="sandboxing-level-3"
							checked={3 === sandboxingLevel}
							onChange={() => {
								updateOptions({ sandboxing_level: 3 });
							}}
						/>
						<label htmlFor="sandboxing-level-3">
							{createInterpolateElement(
								__(
									'<b>Strict:</b> Require valid AMP, removing all markup that causes validation errors (except for excessive CSS).',
									'amp'
								),
								{ b: <strong /> }
							)}
						</label>
					</li>
				</ol>
			)}
		</>
	);
}
