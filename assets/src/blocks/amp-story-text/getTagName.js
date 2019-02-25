export default function( attributes ) {
	const { type } = attributes;
	let tagName = 'p';

	switch ( type ) {
		case 'h1':
			tagName = 'h1';
			break;
		case 'h2':
			tagName = 'h2';
			break;
		case 'p':
			tagName = 'p';
			break;
		default:
		// Todo: Automatically determine semantic tag name based on attributes.
	}

	return tagName;
}
