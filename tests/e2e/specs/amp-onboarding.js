/* eslint-disable jest/valid-describe */

/**
 * Internal dependencies
 */
import { welcome } from '../amp-onboarding/welcome';
import { technicalBackground } from '../amp-onboarding/technical-background';
import { templateMode, templateModeRecommendations } from '../amp-onboarding/template-mode';
import { readerThemes } from '../amp-onboarding/reader-themes';
import { summary } from '../amp-onboarding/summary';
import { done } from '../amp-onboarding/done';

// npm run test:e2e -- -t "Welcome screen"
describe( 'Welcome screen', welcome );

// npm run test:e2e -- -t "Technical background"
describe( 'Technical background', technicalBackground );

// npm run test:e2e -- -t "Template mode"
describe( 'Template mode', templateMode );

// npm run test:e2e -- -t "Template recommendations"
describe( 'Template recommendations', templateModeRecommendations );

// npm run test:e2e -- -t "Reader themes"
describe( 'Reader themes', readerThemes );

// npm run test:e2e -- -t "Summary"
describe( 'Summary', summary );

// npm run test:e2e -- -t "Done"
describe( 'Done', done ); // After running this `npm run env:reset-site` is required for the above tests to run again.

/* eslint-enable jest/valid-describe */
