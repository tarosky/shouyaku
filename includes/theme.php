<?php
/**
 * Change locale on front screen.
 */

add_action( 'init', function() {
	if ( is_user_logged_in() ) {
		if ( shouyaku_should_change_locale() ) {
			switch_to_locale( shouyaku_user_locale() );
		}
	}
}, 1 );


