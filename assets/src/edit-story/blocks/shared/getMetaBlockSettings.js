/**
 * Internal dependencies
 */
import withMetaBlockSave from './with-meta-block-save';
import withMetaBlockEdit from './with-meta-block-edit';

/**
 * Returns the settings object for the AMP story meta blocks (post title, author, date).
 *
 * @param {Object}  args               Function arguments.
 * @param {string}  args.attribute     The post attribute this meta block reads from.
 * @param {string}  args.placeholder   Optional. Placeholder text in case the attribute is empty.
 * @param {string}  [args.tagName]     Optional. The HTML tag name to use for the content. Default '<p>'.
 * @param {boolean} [args.isEditable]  Optional. Whether the meta block is editable by the user or not. Default false.
 *
 * @return {Object} The meta block's settings object.
 */
const getMetaBlockSettings = ( { attribute, placeholder, tagName = 'p', isEditable = false } ) => {
	const supports = {
		anchor: true,
		reusable: true,
	};

	const schema = {
		align: {
			type: 'string',
		},
	};

	return {
		supports,
		attributes: schema,
		save: withMetaBlockSave( { tagName } ),
		edit: withMetaBlockEdit( { attribute, placeholder, tagName, isEditable } ),
	};
};

export default getMetaBlockSettings;
