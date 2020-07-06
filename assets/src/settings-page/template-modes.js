
/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { TemplateModeOption } from '../components/template-mode-option';
import '../css/template-mode-selection.css';

export function TemplateModes() {
	return (
		<div className="template-mode-selection">
			<h2>
				{ __( 'Template mode', 'amp' ) }
			</h2>
			<p>
				{ __( 'For a list of themes and plugins that are known to be AMP compatible, please see the ecosystem page', 'amp' ) }
			</p>
			<TemplateModeOption
				details=""
				mode="standard"
			/>
			<TemplateModeOption
				details=""
				mode="transitional"
			/>
			<TemplateModeOption
				details=""
				mode="reader"
			/>
		</div>
	);
}
