<?php
/**
 * Can I Use? Shortcode - Front Module
 *
 * Contains front-end functionality
 *
 * @package Can I Use Shortcode
 */
/*  Copyright 2015 Reaktiv Studios

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; version 2 of the License (GPL v2) only.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( ! class_exists( 'CIU_Shortcode_Front' ) ) {

// Start up the engine
class CIU_Shortcode_Front
{

	/**
	 * [__construct description]
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts',                   array( $this, 'front_styles'                ),  10      );
		add_shortcode( 'caniuse',                           array( $this, 'caniuse_display_setup'       )           );
	}

	/**
	 * load the front CSS and JS files for the shortcode
	 *
	 * @return [type]       [description]
	 */
	public function front_styles() {

		// bail if not singular
		if ( ! is_singular() ) {
			return;
		}

		// check for our CSS bypass
		if ( false !== $css = apply_filters( 'ciu_disable_css', false ) ) {
			return;
		}

		// call the global post object
		global $post;

		// check if we are on a post and using the shortcode
		if( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'caniuse') ) {

			// set a version for the files based on debug mode or not
			$vers   = defined( 'WP_DEBUG' ) && WP_DEBUG ? time() : CIU_SHORTCODE_VER;
			$sffx   = defined( 'WP_DEBUG' ) && WP_DEBUG ? '.css' : '.min.css';

			// load the file
			wp_enqueue_style( 'ciu-display', plugins_url( '/css/caniuse' . $sffx, __FILE__), array(), $vers, 'all' );
		}
	}

	/**
	 * display the "Can I Use" info
	 *
	 * @return [type]       [description]
	 */
	public function caniuse_display_setup( $atts, $content = null ) {

		// grab my args
		$args   = shortcode_atts( array(
			'feature'  => '',
		), $atts );

		// bail with no feature declared, or no return
		if ( empty( $args['feature'] ) ) {
			return;
		}

		// get my data
		$data   = CIU_Shortcode_Helper::get_support_data( esc_attr( $args['feature'] ) );

		// bail if an error return
		if ( ! empty( $data['error'] ) ) {
			return;
		}

		// set an empty build
		$build  = '';

		// do the div wrapper
		$build .= '<div class="caniuse">';

			// the header
			$build .= CIU_Shortcode_Display::get_display_header( $data );

			// desktop row
			$build .= CIU_Shortcode_Display::get_display_row( $data['stats'], 'desktop', __( 'Desktop', 'caniuse-shortcode' ) );

			// mobile row
			$build .= CIU_Shortcode_Display::get_display_row( $data['stats'], 'mobile', __( 'Mobile / Tablet', 'caniuse-shortcode' ) );

			// legend
			$build .= CIU_Shortcode_Display::get_display_legend();

		// close the wrapper
		$build .= '</div>';

		// return it
		return $build;
	}

// end class
}

// end exists check
}

// Instantiate our class
new CIU_Shortcode_Front();
