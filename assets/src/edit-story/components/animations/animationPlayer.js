
/**
 * Rouhgly follows `Animation` API.
 * See https://developer.mozilla.org/en-US/docs/Web/API/Animation.
 */
class AnimationPlayer {
	constructor( animations ) {
		this.animations = animations;
		this.playState = 'pending';
		this.pendingCount = 0;
		this.onPlayStateChange = null;

		this.players = animations.map( ( { target, keyframes, timing } ) => {
			const player = target.animate( keyframes, timing );
			player.pause();
			return player;
		} );
	}

	updatePlayState( playState ) {
		if ( this.playState !== playState ) {
			this.playState = playState;
			if ( this.onPlayStateChange ) {
				this.onPlayStateChange();
			}
		}
	}

	play() {
		if ( this.playState === 'pending' ) {
			this.pendingCount = this.players.length;
			this.players.forEach( ( player ) => {
				player.onfinish = () => {
					this.pendingCount--;
					if ( this.pendingCount <= 0 ) {
						// When all finished, finish the combined animation.
						this.finish();
					}
				};
				player.oncancel = () => {
					// If one fails: cancel all others.
					this.cancel();
				};
				player.play();
			} );
			this.updatePlayState( 'running' );
		} else if ( this.playState === 'paused' ) {
			this.players.forEach( ( player ) => player.play() );
			this.updatePlayState( 'running' );
		}
	}

	pause() {
		if ( this.playState !== 'running' ) {
			return;
		}
		this.players.forEach( ( player ) => {
			player.pause();
		} );
		this.updatePlayState( 'paused' );
	}

	finish() {
		if ( this.playState !== 'running' && this.playState !== 'paused' ) {
			return;
		}
		this.players.forEach( ( player ) => {
			player.finish();
		} );
		this.updatePlayState( 'finished' );
	}

	cancel() {
		if ( this.playState !== 'running' && this.playState !== 'paused' ) {
			return;
		}
		this.players.forEach( ( player ) => {
			player.cancel();
		} );
		this.updatePlayState( 'canceled' );
	}
}

export default AnimationPlayer;
