
/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useContext } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { Selectable } from '../components/selectable';
import { Options } from '../components/options-context-provider';

export function Welcome() {
	const { wizard_completed: wizardCompleted } = useContext( Options );

	return (
		<div className="welcome">
			<Selectable selected={ false }>
				<div className="welcome__illustration">
					<svg width="154" height="135" viewBox="0 0 154 135" fill="none" xmlns="http://www.w3.org/2000/svg">
						<rect x="28.8652" y="35.8047" width="77.3354" height="77.3354" rx="19" fill="white" stroke="#2459E7" strokeWidth="2" />
						<rect x="42.542" y="49.75" width="5.96464" height="49.7702" rx="2.98232" fill="white" stroke="#2459E7" strokeWidth="2" />
						<circle cx="45.5244" cy="64.2988" r="6.09961" fill="white" stroke="#2459E7" strokeWidth="2" />
						<rect x="70.6704" y="99.5117" width="5.96464" height="49.7702" rx="2.98232" transform="rotate(-180 70.6704 99.5117)" fill="white" stroke="#2459E7" strokeWidth="2" />
						<rect x="92.8345" y="99.5117" width="5.96464" height="49.7702" rx="2.98232" transform="rotate(-180 92.8345 99.5117)" fill="white" stroke="#2459E7" strokeWidth="2" />
						<circle cx="89.8516" cy="68.4355" r="6.09961" transform="rotate(-180 89.8516 68.4355)" fill="white" stroke="#2459E7" strokeWidth="2" />
						<circle cx="67.5327" cy="81.5723" r="6.09961" transform="rotate(-180 67.5327 81.5723)" fill="white" stroke="#2459E7" strokeWidth="2" />
						<path d="M95.3496 28.9929C97.5189 23.8663 105.289 14.2044 119.015 16.5696" stroke="#2459E7" strokeWidth="2" strokeLinecap="round" />
						<path d="M80.2668 26.0743C82.2095 22.1887 84.3994 13.8877 77.6177 11.7695" stroke="#2459E7" strokeWidth="2" strokeLinecap="round" />
						<path d="M140.528 26.6495C140.835 26.9335 141.287 26.9961 141.66 26.8062C142.033 26.6164 142.248 26.2144 142.2 25.7987L141.337 18.4382L147.49 14.938C147.855 14.7307 148.051 14.3181 147.982 13.9046C147.914 13.491 147.594 13.1643 147.182 13.0863L140.054 11.7358L138.754 5.1671C138.673 4.76193 138.352 4.44775 137.946 4.37632C137.539 4.3049 137.13 4.49093 136.916 4.84456L133.333 10.7815L126.416 9.73864C125.999 9.67576 125.587 9.88135 125.387 10.2526C125.187 10.6238 125.241 11.0808 125.522 11.3949L130.464 16.9086L127.441 23.3886C127.261 23.7737 127.345 24.2302 127.649 24.527C127.953 24.8238 128.411 24.896 128.792 24.7072L135.059 21.5972L140.528 26.6495Z" stroke="#2459E7" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
						<path d="M113.025 45.2951C116.002 42.2624 124.049 37.8452 132.429 44.4385" stroke="#2459E7" strokeWidth="2" strokeLinecap="round" />
						<path d="M37.8788 118.656C36.2358 122.01 33.0868 129.691 33.6344 133.579" stroke="#2459E7" strokeWidth="2" strokeLinecap="round" />
						<path d="M26.6027 114.924C22.749 117.612 14.5433 123.427 12.5492 125.19" stroke="#2459E7" strokeWidth="2" strokeLinecap="round" />
						<path d="M21.5176 102.637C16.7026 104.37 6.37452 107.987 3.58154 108.59" stroke="#2459E7" strokeWidth="2" strokeLinecap="round" />
						<circle cx="3.58159" cy="131.11" r="3.01225" fill="#2459E7" />
					</svg>
				</div>

				<div className="welcome__body">
					<h2>
						{ wizardCompleted ? __( 'AMP Settings Configured', 'amp' ) : __( 'Configure AMP', 'amp' ) }
					</h2>
					<p>
						{ __( 'The AMP plugin can guide you through choosing the best settings accordint to your theme, plugins, and technical capabilities.', 'amp' ) }
					</p>
				</div>
			</Selectable>
		</div>
	);
}
