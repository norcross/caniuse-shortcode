<?php
/**
 * Can I Use? Shortcode - Display Module
 *
 * Contains display template modules
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

if ( ! class_exists( 'CIU_Shortcode_Display' ) ) {

// Start up the engine
class CIU_Shortcode_Display
{

	/**
	 * get the header items for the display
	 *
	 * @param  array  $data [description]
	 * @return [type]       [description]
	 */
	public static function get_display_header( $data = array() ) {

		// set an empty build
		$build  = '';

		// the header
		$build .= '<div class="caniuse-header">';

			// show the title
			if ( ! empty( $data['title'] ) ) {
				$build .= '<h3 class="caniuse-header-title">' . esc_attr( $data['title'] ) . '</h3>';
			}

			// show the description
			if ( ! empty( $data['description'] ) ) {
				$build .= wpautop( esc_attr( $data['description'] ) );
			}

			// show the status
			if ( ! empty( $data['status'] ) && false !== $label = CIU_Shortcode_Helper::get_spec_status_label( $data['status'] ) ) {
				$build .= '<p class="status caniuse-header-status">' . esc_attr( $label ) . '</p>';
			}

			// and the supported intro
			$build .= '<p class="caniuse-header-supported">' . __( 'Supported from the following versions:', 'caniuse-shortcode' ) . '</p>';

		// close the header
		$build .= '</div>';

		// return it
		return $build;
	}

	/**
	 * get the items for the display
	 *
	 * @param  array  $data  [description]
	 * @param  string $check [description]
	 * @param  string $title [description]
	 * @return [type]        [description]
	 */
	public static function get_display_row( $data = array(), $check = '', $title = '' ) {

		// bail with no check key
		if ( empty( $check ) || false === $checks = CIU_Shortcode_Helper::get_support_checks( $check ) ) {
			return;
		}

		// set an empty build
		$build  = '';

		// the header
		$build .= '<div class="caniuse-section">';

			// show the title
			if ( ! empty( $title ) ) {
				$build .= '<h4>' . esc_attr( $title ) . '</h4>';
			}

			// now the output of lists
			$build .= '<ul class="agents caniuse-agents-list">';

			// loop them
			foreach ( $checks as $key => $browser ) {

				// check to make sure we have data for the specific browser
				if ( empty( $data[ $browser ] ) ) {
					continue;
				}

				// set my browser data array
				$sppt   = CIU_Shortcode_Helper::get_support_result( $data[ $browser ] );

				// get some variables
				$blabel = CIU_Shortcode_Helper::get_browser_label( $browser );
				$yorn   = CIU_Shortcode_Helper::get_spec_support_label( $sppt['flag'] );
				$vlabel = ! empty( $sppt['vers'] ) ? esc_attr( $sppt['vers'] ) : __( 'No', 'caniuse-shortcode' );
				$plabel = ! empty( $sppt['pfix'] ) ? '*' : '';

				// start the markup
				$build .= '<li class="caniuse-agents-item icon-' . esc_attr( $browser ). ' ' . esc_attr( $sppt['flag'] ). '" title="' . esc_attr( $blabel ) . ' - ' . esc_attr( $yorn ) . '">';

				// the version label
				$build .= '<span class="caniuse-agents-version version">' . esc_attr( $vlabel . $plabel ). '</span>';

				// close the markup
				$build .= '</li>';
			}

			// close the list output
			$build .= '</ul>';

		// close the header
		$build .= '</div>';

		// return it
		return $build;
	}

	/**
	 * get the legend for the display
	 *
	 * @return [type]        [description]
	 */
	public static function get_display_legend( $data = array(), $check = '', $title = '' ) {

		// set an empty build
		$build  = '';

		// the header
		$build .= '<div class="caniuse-section caniuse-section-legend">';

			// the message regarding prefix
			$build .= '<p class="caniuse-section-text caniuse-section-subtext	">' . __( '* denotes prefix required.', 'caniuse-shortcode' ) . '</p>';

			// now the output of lists
			$build .= '<ul class="legend caniuse-legend-list">';

				// list each thing
				$build .= '<li class="caniuse-legend-item caniuse-legend-label">' . __( 'Supported:', 'caniuse-shortcode' ) . '</li>';
				$build .= '<li class="caniuse-legend-item y">' . __( 'Yes', 'caniuse-shortcode' ) . '</li>';
				$build .= '<li class="caniuse-legend-item n">' . __( 'No', 'caniuse-shortcode' ) . '</li>';
				$build .= '<li class="caniuse-legend-item a">' . __( 'Partially', 'caniuse-shortcode' ) . '</li>';
				$build .= '<li class="caniuse-legend-item p">' . __( 'Polyfill', 'caniuse-shortcode' ) . '</li>';

			// close the list output
			$build .= '</ul>';

			// show the source
			$build .= '<p class="stats caniuse-section-text caniuse-section-stats">' . sprintf( __( 'Stats from <a target="_blank" href="%s">caniuse.com</a>', 'caniuse-shortcode' ), esc_url( 'http://caniuse.com/#feat=stream' ) ) . '</p>';

		// close the header
		$build .= '</div>';

		// return it
		return $build;
	}

// end class
}

// end exists check
}

// Instantiate our class
new CIU_Shortcode_Display();
