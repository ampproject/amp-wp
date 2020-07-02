/* eslint-disable jest/valid-describe */

/**
 * Internal dependencies
 */
import { nav } from '../amp-onboarding/nav';
import { welcome } from '../amp-onboarding/welcome';
import { technicalBackground } from '../amp-onboarding/technical-background';
import { templateMode, templateModeRecommendations } from '../amp-onboarding/template-mode';
import { readerThemes } from '../amp-onboarding/reader-themes';
import { summary } from '../amp-onboarding/summary';
import { done } from '../amp-onboarding/done';

describe( 'Nav', nav );
describe( 'Welcome', welcome );
describe( 'Technical background', technicalBackground );
describe( 'Template mode', templateMode );
describe( 'Template mode recommendations', templateModeRecommendations );
describe( 'Reader themes', readerThemes );
describe( 'Summary', summary );
describe( 'Done', done );

/* eslint-enable jest/valid-describe */
