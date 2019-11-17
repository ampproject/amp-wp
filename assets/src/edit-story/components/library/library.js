/**
 * Internal dependencies
 */
import LibraryProvider from './libraryProvider';
import LibraryLayout from './libraryTabs';

function Library() {
	return (
		<LibraryProvider>
			<LibraryLayout />
		</LibraryProvider>
	);
}

export default Library;
