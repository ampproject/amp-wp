const { domReady } = wp;

const OPEN_CLASS = 'is-open';

/**
 * Adds a listener toggling all details in the error type taxonomy details column.
 */
function addToggleListener() {
	let open = false;

	const details = [ ...document.querySelectorAll( '.column-details details' ) ];
	const toggleButtons = [ ...document.querySelectorAll( 'button.error-details-toggle' ) ];

	if ( 0 === details.length || 0 === toggleButtons.length ) {
		return;
	}

	const onButtonClick = () => {
		open = ! open;
		toggleButtons.forEach( btn => {
			btn.classList.toggle( OPEN_CLASS );
		} );
		details.forEach( detail => {
			if ( open ) {
				detail.setAttribute( 'open', true );
			} else {
				detail.removeAttribute( 'open' );
			}		
		} );
	};

	window.addEventListener( 'click', event => {
		if ( toggleButtons.includes( event.target ) ) {
			onButtonClick();
		}
	} );
}

domReady( addToggleListener );
