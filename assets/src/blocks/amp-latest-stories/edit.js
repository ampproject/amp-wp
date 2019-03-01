/**
 * AMP Latest Stories edit component, mainly forked from the Gutenberg 'Latest Posts' class LatestPostsEdit.
 */

/**
 * External dependencies
 */
import { isUndefined, pickBy } from 'lodash';
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import {
	Component,
	Fragment,
	RawHTML,
} from '@wordpress/element';
import {
	PanelBody,
	Placeholder,
	QueryControls,
	Spinner,
	Toolbar,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import {
	InspectorControls,
	BlockAlignmentToolbar,
	BlockControls,
} from '@wordpress/editor';
import { withSelect } from '@wordpress/data';

class LatestStoriesEdit extends Component {
	componentWillMount() {
		this.isStillMounted = true;
	}

	componentWillUnmount() {
		this.isStillMounted = false;
	}

	render() {
		const { attributes, setAttributes, latestStories } = this.props;
		const { align, storyLayout, order, orderBy, storiesToShow } = attributes;

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

		// Removing posts from display should be instant.
		const displayStories = latestStories.length > storiesToShow ?
			latestStories.slice( 0, storiesToShow ) :
			latestStories;

		const layoutControls = [
			{
				icon: 'list-view',
				title: __( 'List View', 'amp' ),
				onClick: () => setAttributes( { storyLayout: 'list' } ),
				isActive: storyLayout === 'list',
			},
			{
				icon: 'grid-view',
				title: __( 'Grid View', 'amp' ),
				onClick: () => setAttributes( { storyLayout: 'grid' } ),
				isActive: storyLayout === 'grid',
			},
		];

		return (
			<Fragment>
				{ inspectorControls }
				<BlockControls>
					<BlockAlignmentToolbar
						value={ align }
						onChange={ ( nextAlign ) => {
							setAttributes( { align: nextAlign } );
						} }
					/>
					<Toolbar controls={ layoutControls } />
				</BlockControls>
				<ul
					className={ classnames( this.props.className, {
						'is-grid': storyLayout === 'grid',
					} ) }
				>
					{ displayStories.map( ( post, i ) => {
						const titleTrimmed = post.title.rendered.trim();
						return (
							<li key={ i }>
								<a href={ post.link } target="_blank" rel="noopener noreferrer">
									{ titleTrimmed ? (
										<RawHTML>
											{ titleTrimmed }
										</RawHTML>
									) :
										__( '(Untitled)', 'amp' )
									}
								</a>
							</li>
						);
					} ) }
				</ul>
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
