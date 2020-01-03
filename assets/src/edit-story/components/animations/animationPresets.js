
// See https://github.com/ampproject/amphtml/blob/master/extensions/amp-story/1.0/animation-presets.js
// todo@: Try to repro/reuse the presets as much as possible.

// First keyframe will always be considered offset: 0 and will be applied to the
// element as the first frame before animation starts.
function getPresetDef( name ) {
	switch ( name ) {
		case 'fade-in':
			return {
				duration: 400,
				easing: 'cubic-bezier(0.0, 0.0, 0.2, 1)',
				keyframes: [
					{ opacity: 0 },
					{ opacity: 1 },
				],
			};
		case 'fly-in-top':
			return {
				duration: 400,
				easing: 'cubic-bezier(0.0, 0.0, 0.2, 1)',
				keyframes: [
					{ transform: 'translateY(-300px)' },
					{ transform: 'translateY(0px)' },
				],
			};
		default:
			return null;
	}
}

function getAnimation( { preset, timing } ) {
	const { keyframes, ...presetTiming } = getPresetDef( preset );
	return { keyframes, timing: { ...presetTiming, ...timing } };
}

export default getAnimation;
