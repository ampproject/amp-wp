
/**
 * External dependencies
 */
import { ThemeProvider } from 'styled-components';
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import {
	Popover,
	SlotFillProvider,
} from '@wordpress/components';

/**
 * Internal dependencies
 */
import theme, { GlobalStyle } from '../theme';
import { useHistory, HistoryProvider } from './history';
import { useAPI, APIProvider } from './api';
import { useConfig, ConfigProvider } from './config';
import { useFont, FontProvider } from './font';
import { useStory, StoryProvider } from './story';
import Layout from './layout';

function App( { config } ) {
	const { storyId } = config;
	return (
		<SlotFillProvider>
			<ThemeProvider theme={ theme }>
				<ConfigProvider config={ config }>
					<APIProvider>
						<HistoryProvider size={ 50 }>
							<StoryProvider storyId={ storyId }>
								<FontProvider>
									<GlobalStyle />
									<Layout />
									<Popover.Slot />
								</FontProvider>
							</StoryProvider>
						</HistoryProvider>
					</APIProvider>
				</ConfigProvider>
			</ThemeProvider>
		</SlotFillProvider>
	);
}

App.propTypes = {
	config: PropTypes.object.isRequired,
};

export default App;

export {
	useHistory,
	useAPI,
	useStory,
	useConfig,
	useFont,
};
