/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * Wrapper component to render <details/>.
 *
 * @param {Object}      props             Component props.
 * @param {Object}      props.open        Flag to whether open summary or not.
 * @param {string}      props.title       Title of details.
 * @param {JSX.Element} props.description Description message..
 * @return {JSX.Element|null} HTML markup.
 */
export function Details( { open = false, title, description } ) {
	open = Boolean( open );
	return ( title && description && (
		<details open={ open }>
			<summary>
				{ `${ title }` }
			</summary>
			<div className="detail-body">
				<p className="small-italic">
					{ description }
				</p>
			</div>
		</details>
	) );
}

Details.propTypes = {
	open: PropTypes.bool,
	title: PropTypes.string.isRequired,
	description: PropTypes.string.isRequired,
};
