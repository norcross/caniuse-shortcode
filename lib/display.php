<?php
/**
 * Can I Use? Shortcode - Display Module
 *
 * Contains display template modules
 *
 * @package Can I Use Shortcode
 */

/**
 * Lets start the engine.
 */
class CIU_Shortcode_Display
{

	/**
	 * Get the header items for the display.
	 *
	 * @param  array $data  The data array used to build the display header.
	 *
	 * @return HTML
	 */
	public static function get_display_header( $data = array() ) {

		// Set an empty build.
		$build  = '';

		// Wrap the header in a div.
		$build .= '<div class="caniuse-header">';

			// Show the title if we have one.
			if ( ! empty( $data['title'] ) ) {
				$build .= '<h3 class="caniuse-header-title">' . esc_html( $data['title'] ) . '</h3>';
			}

			// Show the description if we have one.
			if ( ! empty( $data['description'] ) ) {
				$build .= wpautop( esc_html( $data['description'] ) );
			}

			// Show the status if we have one.
			if ( ! empty( $data['status'] ) && false !== $label = CIU_Shortcode_Helper::get_spec_status_label( $data['status'] ) ) {
				$build .= '<p class="status caniuse-header-status">' . esc_html( $label ) . '</p>';
			}

			// And the supported intro.
			$build .= '<p class="caniuse-header-supported">' . __( 'Supported from the following versions:', 'caniuse-shortcode' ) . '</p>';

		// Close the header div.
		$build .= '</div>';

		// Return it.
		return $build;
	}

	/**
	 * Get the items for the display row.
	 *
	 * @param  array  $data   The data array used to build the display row.
	 * @param  string $check  What item we're checking against.
	 * @param  string $title  The section row title.
	 *
	 * @return HTML
	 */
	public static function get_display_row( $data = array(), $check = '', $title = '' ) {

		// Bail with no check key.
		if ( empty( $check ) || false === $checks = CIU_Shortcode_Helper::get_support_checks( $check ) ) {
			return;
		}

		// Set an empty build.
		$build  = '';

		// Wrap the item in a div.
		$build .= '<div class="caniuse-section">';

			// Show the title if we have one.
			if ( ! empty( $title ) ) {
				$build .= '<h4>' . esc_html( $title ) . '</h4>';
			}

			// Now the output of lists.
			$build .= '<ul class="agents caniuse-agents-list">';

			// Loop them.
			foreach ( $checks as $key => $browser ) {

				// Check to make sure we have data for the specific browser.
				if ( empty( $data[ $browser ] ) ) {
					continue;
				}

				// Set my browser data array and skip if none came back.
				if ( false === $result = CIU_Shortcode_Helper::get_support_result( $data[ $browser ], $browser ) ) {
					continue;
				}

				// Start the markup.
				$build .= '<li class="caniuse-agents-item icon-' . esc_attr( $browser ). ' ' . esc_attr( $result['flag'] ). '" title="' . esc_attr( $result['title'] ) . '">';

				// The version label.
				$build .= '<span class="caniuse-agents-version version">' . esc_html( $result['label'] ). '</span>';

				// Close the markup.
				$build .= '</li>';
			}

			// Close the list output.
			$build .= '</ul>';

		// Close the section div.
		$build .= '</div>';

		// Return it.
		return $build;
	}

	/**
	 * Get the legend for the display item.
	 *
	 * @param  string $feature  The individual feature we're checking.
	 *
	 * @return HTML
	 */
	public static function get_display_legend( $feature = '' ) {

		// Set an empty build.
		$build  = '';

		// Wrap the legend in a div.
		$build .= '<div class="caniuse-section caniuse-section-legend">';

			// The message regarding prefix.
			$build .= '<p class="caniuse-section-text caniuse-section-subtext	">' . __( '* denotes prefix required.', 'caniuse-shortcode' ) . '</p>';

			// Now the output of lists.
			$build .= '<ul class="legend caniuse-legend-list">';

				// List each thing.
				$build .= '<li class="caniuse-legend-item caniuse-legend-label">' . __( 'Supported:', 'caniuse-shortcode' ) . '</li>';
				$build .= '<li class="caniuse-legend-item y">' . __( 'Yes', 'caniuse-shortcode' ) . '</li>';
				$build .= '<li class="caniuse-legend-item n">' . __( 'No', 'caniuse-shortcode' ) . '</li>';
				$build .= '<li class="caniuse-legend-item a">' . __( 'Partially', 'caniuse-shortcode' ) . '</li>';
				$build .= '<li class="caniuse-legend-item p">' . __( 'Polyfill', 'caniuse-shortcode' ) . '</li>';

			// Close the list output.
			$build .= '</ul>';

			// Show the source.
			$build .= '<p class="stats caniuse-section-text caniuse-section-stats">' . sprintf( __( 'Stats from <a target="_blank" href="%s">caniuse.com</a>', 'caniuse-shortcode' ), esc_url( 'http://caniuse.com/#feat=' . esc_attr( $feature ) ) ) . '</p>';

		// Close the section div.
		$build .= '</div>';

		// Return it.
		return $build;
	}

	// End class.
}

// Instantiate our class.
new CIU_Shortcode_Display();
