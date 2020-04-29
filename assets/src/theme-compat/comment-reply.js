/**
 * Handles the addition of the comment form.
 *
 * @type {Object}
 */
// Avoid scope lookups on commonly used variables.
const document = window.document;

// Settings.
const config = {
	commentReplyClass : 'comment-reply-link',
	cancelReplyId     : 'cancel-comment-reply-link',
	commentFormId     : 'commentform',
	temporaryFormId   : 'wp-temp-form-div',
	parentIdFieldId   : 'comment_parent',
	postIdFieldId     : 'comment_post_ID'
};

// For holding the cancel element.
let cancelElement;

// For holding the comment form element.
let commentFormElement;

// The respond element.
let respondElement;

// The mutation observer.
let observer;

/**
 * Sets up object variables after the DOM is ready.
 */
function ready() {
	// Initialise the events.
	init();

	// Set up a MutationObserver to check for comments loaded late.
	observeChanges();
}

/**
 * Add events to links classed .comment-reply-link.
 *
 * Searches the context for reply links and adds the JavaScript events
 * required to move the comment form. To allow for lazy loading of
 * comments this method is exposed as window.commentReply.init().
 *
 * @param {HTMLElement} context The parent DOM element to search for links.
 */
function init( context ) {
	// Get required elements.
	cancelElement = document.getElementById( config.cancelReplyId );
	commentFormElement = document.getElementById( config.commentFormId );

	// No cancel element, no replies.
	if ( ! cancelElement ) {
		return;
	}

	cancelElement.addEventListener( 'touchstart', cancelEvent );
	cancelElement.addEventListener( 'click',      cancelEvent );

	// Submit the comment form when the user types [Ctrl] or [Cmd] + [Enter].
	const submitFormHandler = function( e ) {
		if ( ( e.metaKey || e.ctrlKey ) && e.keyCode === 13 ) {
			commentFormElement.removeEventListener( 'keydown', submitFormHandler );
			e.preventDefault();
			// The submit button ID is 'submit' so we can't call commentFormElement.submit(). Click it instead.
			commentFormElement.submit.click();
			return false;
		}
	};

	if ( commentFormElement ) {
		commentFormElement.addEventListener( 'keydown', submitFormHandler );
	}

	const links = replyLinks( context );
	let element;

	for ( const i = 0, l = links.length; i < l; i++ ) {
		element = links[i];

		element.addEventListener( 'touchstart', clickEvent );
		element.addEventListener( 'click',      clickEvent );
	}
}

/**
 * Return all links classed .comment-reply-link.
 *
 * @param {HTMLElement} context The parent DOM element to search for links.
 *
 * @return {HTMLCollection|NodeList|Array}
 */
function replyLinks( context ) {
	const selectorClass = config.commentReplyClass;

	// childNodes is a handy check to ensure the context is a HTMLElement.
	if ( ! context || ! context.childNodes ) {
		context = document;
	}

	return context.getElementsByClassName( selectorClass );
}

/**
 * Cancel event handler.
 *
 * @param {Event} event The calling event.
 */
function cancelEvent( event ) {
	const cancelLink = this;
	const temporaryFormId  = config.temporaryFormId;
	const temporaryElement = document.getElementById( temporaryFormId );

	if ( ! temporaryElement || ! respondElement ) {
		// Conditions for cancel link fail.
		return;
	}

	document.getElementById( config.parentIdFieldId ).value = '0';

	// Move the respond form back in place of the temporary element.
	temporaryElement.parentNode.replaceChild( respondElement ,temporaryElement );
	// cancelLink.style.display = 'none'; @TODO Not supported, use amp-bind.
	event.preventDefault();
}

/**
 * Click event handler.
 *
 * @param {Event} event The calling event.
 */
function clickEvent( event ) {
	const commId    = this.dataset.belowelement;
	const parentId  = this.dataset.commentid;
	const respondId = this.dataset.respondelement;
	const postId    = this.dataset.postid;

	if ( ! commId || ! parentId || ! respondId || ! postId ) {
		/*
		 * Theme or plugin defines own link via custom `wp_list_comments()` callback
		 * and calls `moveForm()` either directly or via a custom event hook.
		 */
		return;
	}

	/*
	 * Third party comments systems can hook into this function via the global scope,
	 * therefore the click event needs to reference the global scope.
	 */
	const follow = window.addComment.moveForm(commId, parentId, respondId, postId); // @TODO window not supported.
	if ( false === follow ) {
		event.preventDefault();
	}
}

/**
 * Creates a mutation observer to check for newly inserted comments.
 */
function observeChanges() {
	const observerOptions = {
		childList: true,
		subtree: true
	};

	observer = new MutationObserver( handleChanges );
	observer.observe( document.body, observerOptions );
}

/**
 * Handles DOM changes, calling init() if any new nodes are added.
 *
 * @param {Array} mutationRecords Array of MutationRecord objects.
 */
function handleChanges( mutationRecords ) {
	let index = mutationRecords.length;

	while ( index-- ) {
		// Call init() once if any record in this set adds nodes.
		if ( mutationRecords[ index ].addedNodes.length ) {
			init();
			return;
		}
	}
}

/**
 * Moves the reply form from its current position to the reply location.
 *
 * @param {String} addBelowId HTML ID of element the form follows.
 * @param {String} commentId  Database ID of comment being replied to.
 * @param {String} respondId  HTML ID of 'respond' element.
 * @param {String} postId     Database ID of the post.
 */
function moveForm( addBelowId, commentId, respondId, postId ) {
	// Get elements based on their IDs.
	const addBelowElement = document.getElementById( addBelowId );
	respondElement  = document.getElementById( respondId );

	// Get the hidden fields.
	const parentIdField = document.getElementById( config.parentIdFieldId );

	let element, cssHidden, style;

	if ( ! addBelowElement || ! respondElement || ! parentIdField ) {
		// Missing key elements, fail.
		return;
	}

	const postIdField   = document.getElementById( config.postIdFieldId );

	addPlaceHolder( respondElement );

	// Set the value of the post.
	if ( postId && postIdField ) {
		postIdField.value = postId;
	}

	parentIdField.value = commentId;

	cancelElement.style.display = '';
	addBelowElement.parentNode.insertBefore( respondElement, addBelowElement.nextSibling );

	// Focus on the first field in the comment form.
	try {
		for ( let index = 0; index < commentFormElement.elements.length; index++ ) {
			element = commentFormElement.elements[index];
			cssHidden = false;
/*
			// Get elements computed style.
			if ( 'getComputedStyle' in window ) {
				// Modern browsers.
				style = window.getComputedStyle( element );
			} else if ( document.documentElement.currentStyle ) {
				// IE 8.
				style = element.currentStyle;
			}
*/
			/*
			 * For display none, do the same thing jQuery does. For visibility,
			 * check the element computed style since browsers are already doing
			 * the job for us. In fact, the visibility computed style is the actual
			 * computed value and already takes into account the element ancestors.
			 */
/*			if ( ( element.offsetWidth <= 0 && element.offsetHeight <= 0 ) || style.visibility === 'hidden' ) {
				cssHidden = true;
			}

			// Skip form elements that are hidden or disabled.
			if ( 'hidden' === element.type || element.disabled || cssHidden ) {
				continue;
			}

			element.focus();
*/			// Stop after the first focusable element.
			break;
		}
	}
	catch(e) {

	}

	/*
	 * false is returned for backward compatibility with third party commenting systems
	 * hooking into this function.
	 */
	return false;
}

/**
 * Add placeholder element.
 *
 * Places a place holder element above the #respond element for
 * the form to be returned to if needs be.
 *
 * @param {HTMLElement} respondElement The #respond element holding comment form.
 */
function addPlaceHolder( respondElement ) {
	const temporaryFormId  = config.temporaryFormId;

	if ( document.getElementById( temporaryFormId ) ) {
		// The element already exists, no need to recreate.
		return;
	}

	const temporaryElement = document.createElement( 'div' );
	temporaryElement.id = temporaryFormId;
	temporaryElement.style.display = 'none';
	respondElement.parentNode.insertBefore( temporaryElement, respondElement );
}
