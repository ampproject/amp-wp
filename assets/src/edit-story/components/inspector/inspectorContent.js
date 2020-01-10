/**
 * Internal dependencies
 */
import useInspector from './useInspector';
import DesignInspector from './designInspector';
import DocumentInspector from './documentInspector';
import PrepublishInspector from './prepublishInspector';

function Inspector() {
	const {
		state: { tab },
		data: { tabs: { DESIGN, DOCUMENT, PREPUBLISH } },
	} = useInspector();

	const ContentInspector = ( {
		[ DESIGN ]: DesignInspector,
		[ DOCUMENT ]: DocumentInspector,
		[ PREPUBLISH ]: PrepublishInspector,
	} )[ tab ];

	return (
		<div tabIndex="0" role="tabpanel" aria-labelledby={ tab } id={ `${ tab }-tab` }>
			<ContentInspector />
		</div>
	);
}

export default Inspector;
