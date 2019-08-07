/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import { migrateV120 } from '../shared';

/**
 * WordPress dependencies
 */
import { RichText } from '@wordpress/block-editor';

const blockAttributes = {
	autoplay: {
		type: 'boolean',
		source: 'attribute',
		selector: 'video',
		attribute: 'autoplay',
	},
	caption: {
		type: 'string',
		source: 'html',
		selector: 'figcaption',
	},
	controls: {
		type: 'boolean',
		source: 'attribute',
		selector: 'video',
		attribute: 'controls',
		default: true,
	},
	id: {
		type: 'number',
	},
	loop: {
		type: 'boolean',
		source: 'attribute',
		selector: 'video',
		attribute: 'loop',
	},
	muted: {
		type: 'boolean',
		source: 'attribute',
		selector: 'video',
		attribute: 'muted',
	},
	poster: {
		type: 'string',
		source: 'attribute',
		selector: 'video',
		attribute: 'poster',
	},
	preload: {
		type: 'string',
		source: 'attribute',
		selector: 'video',
		attribute: 'preload',
		default: 'metadata',
	},
	src: {
		type: 'string',
		source: 'attribute',
		selector: 'video',
		attribute: 'src',
	},
	playsInline: {
		type: 'boolean',
		source: 'attribute',
		selector: 'video',
		attribute: 'playsinline',
	},
};

const saveV120 = ( { attributes } ) => {
	const { autoplay, caption, controls, loop, muted, poster, preload, src, playsInline } = attributes;
	return (
		<figure>
			{ src && (
				<video
					autoPlay={ autoplay }
					controls={ controls }
					loop={ loop }
					muted={ muted }
					poster={ poster }
					preload={ preload !== 'metadata' ? preload : undefined }
					src={ src }
					playsInline={ playsInline }
				/>
			) }
			{ ! RichText.isEmpty( caption ) && (
				<RichText.Content tagName="figcaption" value={ caption } />
			) }
		</figure>
	);
};

saveV120.propTypes = {
	attributes: PropTypes.shape( {
		caption: PropTypes.string,
		autoplay: PropTypes.bool,
		controls: PropTypes.bool,
		loop: PropTypes.bool,
		muted: PropTypes.bool,
		poster: PropTypes.string,
		preload: PropTypes.string,
		src: PropTypes.string,
		playsInline: PropTypes.bool,
	} ).isRequired,
};

const deprecated = [
	{
		attributes: {
			...blockAttributes,
			deprecated: {
				default: '1.2.0',
			},
		},
		save: saveV120,
		migrate: migrateV120,
	},
];

export default deprecated;
