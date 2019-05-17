/* global ampEditorBlocks */
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
import { InspectorControls } from '@wordpress/block-editor';
import { withSelect } from '@wordpress/data';

const blockName = 'amp/amp-latest-stories';

class LatestStoriesEdit extends Component {
	/**
	 * If the stylesheet isn't present, this adds it to the <head>.
	 *
	 * This block uses <ServerSideRender>, so sometimes the stylesheet isn't present.
	 * This checks if it is, and conditionally adds it.
	 */
	componentWillMount() {
		this.isStillMounted = true;

		if ( 'undefined' === typeof ampEditorBlocks ) {
			return;
		}

		const stylesheetQuery = document.querySelector( `link[href="${ ampEditorBlocks.latestStoriesCssUrl }"]` );
		if ( ! stylesheetQuery ) {
			const stylesheet = document.createElement( 'link' );
			stylesheet.setAttribute( 'rel', 'stylesheet' );
			stylesheet.setAttribute( 'type', 'text/css' );
			stylesheet.setAttribute( 'href', ampEditorBlocks.latestStoriesCssUrl );
			document.head.appendChild( stylesheet );
		}
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

		const serverSideAttributes = Object.assign( {}, attributes, { useCarousel: false } );

		return (
			<Fragment>
				{ inspectorControls }
				<ServerSideRender
					block={ blockName }
					attributes={ serverSideAttributes }
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
