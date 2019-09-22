<?php
/**
 * Load functions in includes/ folder.
 * 
 * @package Tom's Helper Functions
 * @since 1.0.0
 */

// Load all the includes
if( $includes = glob( __DIR__ . '/includes/**/*.php' ) ) {
	foreach( $includes as $file ) {
		require_once $file;
	}
}