/**
 * AMP Latest Stories edit component, mainly forked from the Gutenberg 'Latest Posts' class LatestPostsEdit.
 */

/**
 * External dependencies
 */
import { isUndefined, pickBy } from 'lodash';

/**
 * WordPress dependencies
 */
import {
	Component,
	Fragment,
} from '@wordpress/element';
import {
	PanelBody,
	Placeholder,
	QueryControls,
	ServerSideRender,
	Spinner,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import {
	InspectorControls,
} from '@wordpress/editor';
import { withSelect } from '@wordpress/data';

const blockName = 'amp/amp-latest-stories';

class LatestStoriesEdit extends Component {
	componentWillMount() {
		this.isStillMounted = true;
	}

	componentWillUnmount() {
		this.isStillMounted = false;
	}

	render() {
		const { attributes, setAttributes, latestStories } = this.props;
		const { order, orderBy, storiesToShow } = attributes;

		const inspectorControls = (
			<InspectorControls>
				<PanelBody title={ __( 'Latest Stories Settings', 'amp' ) }>
					<QueryControls
						{ ...{ order, orderBy } }
						numberOfItems={ storiesToShow }
						onOrderChange={ ( value ) => setAttributes( { order: value } ) }
						onOrderByChange={ ( value ) => setAttributes( { orderBy: value } ) }
						onNumberOfItemsChange={ ( value ) => setAttributes( { storiesToShow: value } ) }
					/>
				</PanelBody>
			</InspectorControls>
		);

		const hasStories = Array.isArray( latestStories ) && latestStories.length;
		if ( ! hasStories ) {
			return (
				<Fragment>
					{ inspectorControls }
					<Placeholder
						icon="admin-post"
						label={ __( 'Latest Stories', 'amp' ) }
					>
						{ ! Array.isArray( latestStories ) ?
							<Spinner /> :
							__( 'No stories found.', 'amp' )
						}
					</Placeholder>
				</Fragment>
			);
		}

		return (
			<Fragment>
				{ inspectorControls }
				<ServerSideRender
					block={ blockName }
					attributes={ attributes }
				/>
			</Fragment>
		);
	}
}

export default withSelect( ( select, props ) => {
	const { storiesToShow, order, orderBy } = props.attributes;
	const { getEntityRecords } = select( 'core' );
	const latestStoriesQuery = pickBy( {
		order,
		orderby: orderBy,
		per_page: storiesToShow,
	}, ( value ) => ! isUndefined( value ) );
	return {
		latestStories: getEntityRecords( 'postType', 'amp_story', latestStoriesQuery ),
	};
} )( LatestStoriesEdit );
