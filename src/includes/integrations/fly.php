<?php
/**
 * Utilities for Fly Dynamic Image Resizer
 * 
 * @package Tom's Helper Functions
 * @since 1.0.0
 */

namespace TS\Fly;

/**
 * Get the URL of a dynamically generated image.
 * 
 * @param int $attachment_id Attachment ID to get the URL for.
 * @param int $width
 * @param int $height
 * @param boolean $crop Whether to crop to these dimensions or just resize to fit within them.
 */
function get_image_src( $attachment_id, $width, $height, $crop = false ) {
	return esc_url( fly_get_attachment_image_src( $attachment_id, [$width, $height], $crop )['src'] );
}

/**
 * Output the URL of a dynamically generated image.
 * 
 * @param int $attachment_id Attachment ID to get the URL for.
 * @param int $width
 * @param int $height
 * @param boolean $crop Whether to crop to these dimensions or just resize to fit within them.
 */
function the_image_src( $attachment_id, $width, $height, $crop = false ) {
	echo ffor_get_fly_image_src( $attachment_id, $width, $height, $crop );
}
