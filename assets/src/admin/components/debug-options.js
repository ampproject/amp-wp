/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Button, ToggleControl } from '@wordpress/components';
import { Component } from '@wordpress/element';

/* @todo: import the query vars for each of these */
const debugOptions = {
	'disable-post': __( 'Disable post processing', 'amp' ),
	'disable-cache': __( 'Disable response cache', 'amp' ),
	'prevent-redirect': __( 'Prevent redirect', 'amp' ),
	'reject-all-errors': __( 'Reject all errors', 'amp' ),
	'accept-excessive-css': __( 'Accept excessive CSS', 'amp' ),
	'disable-amp': __( 'Disable AMP', 'amp' ),
	'disable-tree-shaking': __( 'Disable tree shaking', 'amp' ),
};

/**
 * Adds a submenu of debug options to the admin bar's AMP submenu.
 *
 * @return {Function} The component.
 */
class DebugOptions extends Component {
	/**
	 * Constructs the class.
	 *
	 * @param {*} args The constructor arguments.
	 */
	constructor( ...args ) {
		super( ...args );

		this.state = {};
		for ( const [ debugOptionId ] of Object.entries( debugOptions ) ) {
			this.state[ debugOptionId ] = false;
		}
	}

	/**
	 * Renders the component.
	 *
	 * @return {Function} The rendered component.
	 */
	render() {
		const listItems = [];
		for ( const [ debugOptionId, title ] of Object.entries( debugOptions ) ) {
			listItems.push( (
				<li id={ `wp-admin-bar-amp-${ debugOptionId }` } data-ampdevmode>
					<div className="ab-item">
						<ToggleControl
							label={ title }
							checked={ this.state[ debugOptionId ] }
							onChange={ () => {
								const newState = {};
								newState[ debugOptionId ] = ! this.state[ debugOptionId ];
								this.setState( newState );
							} }
						/>
					</div>
				</li>
			) );
		}

		return (
			<>
				{ listItems }
				<li id="wp-admin-bar-amp-reload" data-ampdevmode>
					<div className="ab-item">
						<Button>
							{ __( 'Reload and apply', 'amp' ) }
						</Button>
					</div>
				</li>
			</>
		);
	}
}

export default DebugOptions;
