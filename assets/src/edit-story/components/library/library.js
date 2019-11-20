/**
 * Internal dependencies
 */
import LibraryProvider from './libraryProvider';
import LibraryLayout from './libraryLayout';

function Library() {
	return (
		<LibraryProvider>
			<LibraryLayout />
		</LibraryProvider>
	);
}

export default Library;
