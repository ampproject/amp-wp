/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

/**
 * Toggles the contents of a details element as an additional table tr.
 */
class RowToggler {
	constructor( tr, index, activeTermId ) {
		this.tr = tr;
		this.index = index;
		this.activeTermId = activeTermId;

		// Since we're adding additional rows, we need to override default .striped tables styles.
		this.tr.classList.add( this.index % 2 ? 'odd' : 'even' );
	}

	/**
	 * Sets up the new tr and adds an event listener to toggle details.
	 */
	init() {
		this.details = this.tr.querySelector( '.column-details details' );
		if ( this.details ) {
			this.createNewTr();
			const togglers = [
				...this.tr.querySelectorAll( '.single-url-detail-toggle' ),
				this.details.querySelector( 'summary' ),
			];

			togglers.forEach( ( el ) => {
				el.addEventListener( 'click', () => {
					this.toggle( el );
				} );
			} );
		}

		this.maybeInitiallyOpenRow();
	}

	/**
	 * If the term ID retrieved from the URL query param matches this row's term ID, expand the row on load.
	 */
	maybeInitiallyOpenRow() {
		if ( ! this.activeTermId || this.tr.id !== `tag-${ this.activeTermId }` ) {
			return;
		}

		this.toggle( this.tr.querySelector( '.single-url-detail-toggle' ) );
	}

	/**
	 * Creates the details table row from the original row's <details> element content, minus the summary.
	 */
	createNewTr() {
		this.newTr = document.createElement( 'tr' );
		this.newTr.classList.add( 'details' );
		this.newTr.classList.add( this.index % 2 ? 'odd' : 'even' );

		const newCell = document.createElement( 'td' );
		newCell.setAttribute( 'colspan', this.getRowColspan() );

		for ( const childNode of this.details.childNodes ) {
			if ( 'SUMMARY' !== childNode.tagName ) {
				newCell.appendChild( childNode.cloneNode( true ) );
			}
		}

		this.newTr.appendChild( newCell );
	}

	/**
	 * Gets the number of cells within the original row.
	 *
	 * @return {number} The number of cells.
	 */
	getRowColspan() {
		return [ ...this.tr.childNodes ]
			.filter( ( childNode ) => [ 'TD', 'TH' ].includes( childNode.tagName ) )
			.length;
	}

	/**
	 * Toggles the additional row.
	 *
	 * @param {Object} target The click event target.
	 */
	toggle( target ) {
		if ( this.tr.classList.contains( 'expanded' ) ) {
			this.onClose( target );
		} else {
			this.onOpen( target );
		}
	}

	/**
	 * Adds the additional row.
	 *
	 * @param {Object} target The click event target.
	 */
	onOpen( target ) {
		this.tr.parentNode.insertBefore( this.newTr, this.tr.nextSibling );
		this.tr.classList.add( 'expanded' );

		if ( 'SUMMARY' !== target.tagName ) { // This browser will do this if the summary was clicked.
			this.details.setAttribute( 'open', true );
		}
	}

	/**
	 * Removes the additional row.
	 *
	 * @param {Object} target The click event target.
	 */
	onClose( target ) {
		this.tr.parentNode.removeChild( this.newTr );
		this.tr.classList.remove( 'expanded' );

		if ( 'SUMMARY' !== target.tagName ) {
			this.details.removeAttribute( 'open' );
		}
	}
}

/**
 * Sets up expandable details for errors when viewing a single URL error list.
 */
class ErrorRows {
	constructor( activeTermId ) {
		this.rows = [ ...document.querySelectorAll( '.wp-list-table tr[id^="tag-"]' ) ]
			.map( ( tr, index ) => {
				const rowHandler = new RowToggler( tr, index, activeTermId );
				rowHandler.init();
				return rowHandler;
			} )
			.filter( ( row ) => row.details );
	}

	init() {
		this.addToggleAllListener();
	}

	/**
	 * Handle 'toggle all' buttons on the page.
	 */
	addToggleAllListener() {
		let open = false;
		const toggleButtons = [ ...document.querySelectorAll( '.column-details button.error-details-toggle' ) ];

		const onButtonClick = ( target ) => {
			open = ! open;
			this.rows.forEach( ( row ) => {
				if ( open ) {
					row.onOpen( target );
				} else {
					row.onClose( target );
				}
			} );
		};

		window.addEventListener( 'click', ( event ) => {
			if ( toggleButtons.includes( event.target ) ) {
				onButtonClick( event.target );
			}
		} );
	}
}

domReady( () => {
	let activeTermId = null;

	const matches = window.location.hash.match( /^#tag-(\d+)/ );
	if ( matches ) {
		activeTermId = parseInt( matches[ 1 ] );
	}

	new ErrorRows( activeTermId ).init();
} );
