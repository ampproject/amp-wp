/**
 * Internal dependencies
 */
import useLibrary from './useLibrary';
import { Tabs, Media, Text, Shapes, Links } from './tabs';

function LibraryTabs() {
	const {
		state: { tab },
		actions: { setTab },
		data: { tabs: { MEDIA, TEXT, SHAPES, LINKS } },
	} = useLibrary();
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
