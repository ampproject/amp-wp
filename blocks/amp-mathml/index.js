/**
 * This file is part of the AMP Plugin for WordPress.
 *
 * The AMP Plugin for WordPress is free software: you can redistribute
 * it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation, either version 2
 * of the License, or (at your option) any later version.
 *
 * The AMP Plugin for WordPress is distributed in the hope that it will
 * be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with the AMP Plugin for WordPress. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * Internal block libraries.
 */
const { __ } = wp.i18n;
const {
	registerBlockType
} = wp.blocks;
const {
	PlainText
} = wp.editor;

/**
 * Register block.
 */
export default registerBlockType(
	'amp/amp-mathml',
	{
		title: __( 'AMP MathML', 'amp' ),
		category: 'common',
		icon: 'welcome-learn-more',
		keywords: [
			__( 'Mathematical formula', 'amp' ),
			__( 'Scientific content ', 'amp' )
		],

		attributes: {
			dataFormula: {
				source: 'attribute',
				selector: 'amp-mathml',
				attribute: 'data-formula'
			}
		},

		edit( { attributes, setAttributes } ) {
			const { dataFormula } = attributes;

			return (
				<PlainText
					key='formula'
					value={ dataFormula }
					placeholder={ __( 'Insert formula', 'amp' ) }
					onChange={ ( value ) => setAttributes( { dataFormula: value } ) }
				/>
			);
		},

		save( { attributes } ) {
			let mathmlProps = {
				'data-formula': attributes.dataFormula,
				layout: 'container'
			};
			return (
				<amp-mathml { ...mathmlProps }></amp-mathml>
			);
		}
	}
);
