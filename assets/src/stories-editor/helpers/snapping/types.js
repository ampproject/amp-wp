/**
 * Straight line between two coordinates.
 *
 * @typedef {Array.<Array.<number, number>, Array.<number, number>>} Line
 */

/**
 * Function that draws a line from A to B.
 *
 * @typedef {LineCreator} LineCreator
 * @param {number} offset Offset on the axis.
 * @param {number} [start] Starting point.
 * @param {number} [end] End point.
 * @return Line
 */

/**
 * Object position e.g. as returned by `getBoundingClientRect()`.
 *
 * @typedef {BlockPosition} BlockPosition
 * @property {number} top The block's relative top position.
 * @property {number} right The block's relative right position.
 * @property {number} bottom The block's relative bottom position.
 * @property {number} left The block's relative left position.
 */

/**
 * Function returning an enhanced list of snap targets based on the current element's siblings' dimensions.
 *
 * @typedef {SnapTargetsEnhancer} SnapTargetsEnhancer
 * @param {Array<BlockPosition>} blockPositions List of relative block dimensions.
 * @return {SnapTargetsProvider}
 */

/**
 * A map of snap targets and their respective snapping guidelines.
 *
 * @typedef {SnapLines} SnapLines
 * @type {Object.<string, Array.<Line>>} List of snap lines
 */

/**
 * A function creating a map of snap targets and their respective snapping guidelines from a single number.
 *
 * @typedef {SnapLinesCreator} SnapLinesCreator
 * @param {number} number The number to generate snap targets for
 * @return {SnapLines} The resulting list of snap lines.
 */

/**
 * Returns a list of snap targets based on the current element's dimensions.
 *
 * @typedef {SnapTargetsProvider} SnapTargetsProvider
 * @param {number} targetMin The current element's minimum value.
 * @param {number} targetMax The current element's maximum value.
 * @return {SnapLines} The resulting list of snap lines.
 */
