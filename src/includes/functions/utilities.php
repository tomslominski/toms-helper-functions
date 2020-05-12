<?php
/**
 * Generic utilities for WordPress
 *
 * @package Tom's Helper Functions
 * @since 1.0.0
 */

namespace TS;

/**
 * Get the context the package is being run in.
 *
 * @return string "plugin" or "theme"
 */
function get_context() {
    $path_parts = explode( DIRECTORY_SEPARATOR, __DIR__ );
    $key = array_search( 'wp-content', $path_parts );
    $directory = $path_parts[$key + 1] ?? '';

	return 'plugins' === $directory ? 'plugin' : 'theme';
}

/**
 * Get absolute path to plugin or theme.
 *
 * @return string Absolute path to package or theme.
 */
function get_package_path() {
	$package_path = explode( DIRECTORY_SEPARATOR, __DIR__ );
	$content_path = explode( DIRECTORY_SEPARATOR, WP_CONTENT_DIR );
	$key = count( $content_path ) + 2;

	array_splice( $package_path, $key );

	return implode( DIRECTORY_SEPARATOR, $package_path );
}

/**
 * Get version of plugin or theme.
 *
 * @return bool|string Plugin or theme version or false on failure.
 */
function get_package_version() {
	switch( get_context() ) {
		case 'plugin':
			$plugin = get_plugin_data();
			return isset( $plugin['Version'] ) && $plugin['Version'] ? $plugin['Version'] : false;

		case 'theme':
			$theme = wp_get_theme();
			return $theme->get( 'Version' ) ? $theme->get( 'Version' ) : false;

		default:
			return false;
	}
}

/**
 * Returns theme version from style.css.
 *
 * @return bool|string Theme version.
 */
function get_theme_version() {
	return get_package_version();
}

/**
 * Get plugin header data array.
 *
 * @return bool|array Array or false on failure.
 */
function get_plugin_data() {
	require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

	if( $plugin_path = get_package_path() ) {
		$plugins = get_plugins();
		$path_parts = explode( DIRECTORY_SEPARATOR, $plugin_path );
		$directory = $path_parts[count( $path_parts ) - 1];

		foreach( $plugins as $file => $plugin_data ) {
			if( false !== strstr( $file, $directory ) ) {
				return $plugin_data;
			}
		}
	}

	return false;
}

/**
 * Check if site is running on a dev server.
 *
 * @return bool True if running on a dev server.
 */
function is_dev() {
	return (bool) preg_match( '#(localhost|fatwordpress\.co\.uk|local)$#', $_SERVER['HTTP_HOST'] );
}

/**
 * Get parent term of current or provided term ID.
 *
 * @param int $term_id Term ID to get parent of.
 * @return bool|\WP_Term Parent or false on failure.
 */
function get_parent_term( $term_id = null ) {
	$term_id = $term_id ?? get_queried_object_id();
	$term = get_term( $term_id );

	if( !$term || is_wp_error( $term ) || !$term->parent ) {
		return false;
	}

	return get_term( $term->parent );
}

/**
 * Get the URL to the current queried object.
 *
 * @return string|bool URL to the current page or false on failure.
 */
function get_current_page_link() {
	$queried_object = get_queried_object();

	if( !$queried_object ) {
		return false;
	}

	switch( get_class( $queried_object ) ) {
		case 'WP_Post_Type':
			return get_post_type_archive_link( $queried_object->name );

		case 'WP_Term':
			return get_term_link( $queried_object );

		case 'WP_Post':
			return get_permalink( $queried_object );

		default:
			return false;
	}
}

/**
 * Get the Yoast primary category, or if none set, the first relevant post term.
 *
 * @param \WP_Post|int $post Post object or ID.
 * @param string $taxonomy Relevant taxonomy to get the term for.
 * @return \WP_Term|bool Term ID or false on failure.
 */
function get_primary_category( $post = null, $taxonomy = null ) {
	$post_id = $post->ID ?? $post ?? get_the_ID();
	$primary_category = get_post_meta( $post_id, '_yoast_wpseo_primary_product_cat', true );

	if( $primary_category && term_exists( $primary_category ) ) {
		return get_term( $primary_category, $taxonomy );
	} else {
		$terms = wp_get_post_terms( $post_id, $taxonomy );

		if( !is_wp_error( $terms ) && isset( $terms[0]->term_id ) ) {
			return get_term( $terms[0]->term_id, $taxonomy );
		}
	}

	return false;
}


/**
 * Include a template with variables.
 *
 * @param string $template Template path.
 * @param array $args Arguments to pass to the template.
 */
function get_template( $template, $args = [] ) {
	$path = trailingslashit( get_package_path() ) . $template;

	if( file_exists( $path ) ) {
		extract( $args );
		include( $path );
	}
}

/**
 * Include a template from the template part directory.
 *
 * @param string $template Template part name.
 * @param array $args Arguments to pass to the template.
 */
function get_template_part( $template, $args = [] ) {
	get_template( "template-parts/{$template}.php", $args );
}

/**
 * Get the taxonomy from the current query.
 *
 * @return string Taxonomy slug.
 */
function get_taxonomy_for_query() {
	// Always 'category' if on blog page
	if( is_home() ) {
		return 'category';
	}

	$queried_object = get_queried_object();

	if( !$queried_object ) {
		return 'category';
	}

	switch( get_class( $queried_object ) ) {
		case 'WP_Term':
			return $queried_object->taxonomy;

		case 'WP_Post_Type':
			return $queried_object->taxonomies[0] ?? false;

		default:
			return 'category';
	}
}

/**
 * Get the post_type from the current query.
 *
 * @return string Post type slug.
 */
function get_post_type_for_query() {
	if( is_home() ) {
		return 'post';
	}

	$queried_object = get_queried_object();

	if( !$queried_object ) {
		return 'post';
	}

	switch( get_class( $queried_object ) ) {
		case 'WP_Post':
			return $queried_object->post_type;

		case 'WP_Term':
			$taxonomy = get_taxonomy( $queried_object->taxonomy );
			return $taxonomy->object_type[0];

		case 'WP_Post_Type':
			return $queried_object->name;

		default:
			return 'post';
	}
}
