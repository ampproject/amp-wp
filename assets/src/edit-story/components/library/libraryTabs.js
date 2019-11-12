/**
 * WordPress dependencies
 */
import { useContext } from '@wordpress/element';

/**
 * Internal dependencies
 */
import Context from './context';
import { Tabs, Media, Text, Shapes, Links } from './tabs';

function LibraryTabs() {
	const { tab, setTab, tabs: { MEDIA, TEXT, SHAPES, LINKS } } = useContext( Context );
	const tabs = [
		[ MEDIA, Media ],
		[ TEXT, Text ],
		[ SHAPES, Shapes ],
		[ LINKS, Links ],
	];
	return (
		<Tabs>
			{ tabs.map( ( [ id, Tab ] ) => (
				<Tab key={ id } isActive={ tab === id } onClick={ () => setTab( id ) } />
			) ) }
		</Tabs>
	);
}

export default LibraryTabs;
