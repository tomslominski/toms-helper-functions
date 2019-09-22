<?php
/**
 * Utilities for Advanced Custom Fields
 * 
 * @package Tom's Helper Functions
 * @since 1.0.0
 */

namespace TS\ACF;

/**
 * Get the post ID used in ACF's get_field for any object.
 * 
 * @return string
 */
function get_field_id() {
	if( is_front_page() ) {
		return get_option( 'page_on_front' );
	}
	
	$queried_object = get_queried_object();

	if( !$queried_object ) {
		return false;
	}

	switch( get_class( $queried_object ) ) {
		case 'WP_Term':
			return 'category_' . $queried_object->term_id;
		
		case 'WP_Post':
			return $queried_object->post_id;

		default:
			return false;
	}
}

/**
 * Render a block from a template part within the post content.
 * 
 * @param array $block Block settings.
 */
function render_block( $block ) {
	// Convert name ("acf/testimonial") into path friendly slug ("testimonial")
	$slug = str_replace( ['acf/'. '_'], ['', '-'], $block['name'] );
	
	// Find path and load template
	locate_template( [get_theme_file_path( "/template-parts/blocks/{$slug}.php" )], true, false );
}
