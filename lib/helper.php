<?php
/**
 * Can I Use? Shortcode - Helper Module
 *
 * Contains data, query, and template modules
 *
 * @package Can I Use Shortcode
 */

/**
 * Lets start the engine.
 */
class CIU_Shortcode_Helper
{

	/**
	 * Return an array of the items you want to check support on.
	 *
	 * @param  string $key  Which item we're checking for.
	 *
	 * @return array
	 */
	public static function get_support_checks( $key = '' ) {

		// Bail if no key is present.
		if ( empty( $key ) ) {
			return false;
		}

		// Set the array of data checks.
		$checks = array(
			'desktop'   => array( 'chrome', 'firefox', 'ie', 'opera', 'safari' ),
			'mobile'    => array( 'ios_saf', 'android', 'op_mini', 'and_chr', 'and_ff' )
		);

		// Return a subset of the data if requested.
		if ( in_array( sanitize_key( $key ), array( 'desktop', 'mobile' ) ) ) {
			return apply_filters( 'ciu_checks_' . $key, $checks[ $key ] );
		}

		// Set a combined array and return them.
		return apply_filters( 'ciu_checks_grouped', $checks );
	}

	/**
	 * Get the support data for a single feature.
	 *
	 * @param  string $feature  The feature we want to get support data.
	 *
	 * @return array
	 */
	public static function get_support_data( $feature = '' ) {

		// Bail without a feature request.
		if ( empty( $feature ) ) {
			return;
		}

		// Make a clean key from our feature.
		$k  = sanitize_key( 'ciu_support_' . $feature );

		// Check for transient before going forward.
		if ( false === $data = get_transient( $k ) ) {

			// Construct the API URL and if for some reason we messed up the URL, bail.
			if ( false === $url = apply_filters( 'ciu_data_url', CIU_SHORTCODE_URL ) ) {
				return array(
					'error' => true,
					'cause' => 'NO_SOURCE_URL',
				);
			}

			// Set the default HTTP call args with an optional filter.
			$args   = apply_filters( 'ciu_data_args', array( 'timeout' => 30, 'sslverify' => false ) );

			// Make the request.
			$resp   = wp_remote_get( esc_url( $url ), $args );

			// Bail on empty return.
			if ( empty( $resp ) ) {
				return array(
					'error' => true,
					'cause' => 'EMPTY_RESPONSE',
				);
			}

			// Bail on wp_error.
			if ( is_wp_error( $resp ) ) {
				return array(
					'error' => true,
					'cause' => 'API_ERROR',
				);
			}

			// Get our response code.
			$code   = wp_remote_retrieve_response_code( $resp );

			// Bail on 404.
			if ( 200 !== $code ) {
				return array(
					'error' => true,
					'cause' => 'RESPONSE_CODE',
					'code'  => $code,
				);
			}

			// Get the body.
			$body   = wp_remote_retrieve_body( $resp );

			// Bail on empty body.
			if ( empty( $body ) ) {
				return array(
					'error' => true,
					'cause' => 'EMPTY_BODY',
				);
			}

			// Decode the JSON.
			$decode = json_decode( $body, true );

			// Bail on empty JSON.
			if ( empty( $decode ) || empty( $decode['data'] ) ) {
				return array(
					'error' => true,
					'cause' => 'EMPTY_JSON',
				);
			}

			// Bail if data portion not found.
			if ( ! array_key_exists( $feature, $decode['data'] ) ) {
				return array(
					'error' => true,
					'cause' => 'KEY_NOT_FOUND',
				);
			}

			// Set my data element.
			$data   = $decode['data'][ $feature ];

			// Bail if no stats exist.
			if ( ! array_key_exists( 'stats', $data ) ) {
				return array(
					'error' => true,
					'cause' => 'NO_STATS_FOUND',
				);
			}

			// Set the transient.
			set_transient( $k, $data, DAY_IN_SECONDS );
		}

		// Return the data.
		return $data;
	}

	/**
	 * Get the visual label from a key for status.
	 *
	 * @param  string $key  Which label key we want.
	 *
	 * @return string
	 */
	public static function get_spec_status_label( $key = '' ) {

		// Bail with no key.
		if ( empty( $key ) ) {
			return false;
		}

		// Switch through my keys and return the appropriate.
		switch ( $key ) {

			case 'rec':
				return __( 'W3C Recommendation', 'caniuse-shortcode' );
				break;

			case 'pr':
				return __( 'W3C Proposed Recommendation', 'caniuse-shortcode' );
				break;

			case 'cr':
				return __( 'W3C Candidate Recommendation', 'caniuse-shortcode' );
				break;

			case 'wd':
				return __( 'W3C Working Draft', 'caniuse-shortcode' );
				break;

			case 'other':
				return __( 'Non-W3C, but Reputable', 'caniuse-shortcode' );
				break;

			case 'unoff':
				return __( 'Unofficial or W3C "Note"', 'caniuse-shortcode' );
				break;

			default :
				return false;

			// End all case breaks.
		}
	}

	/**
	 * Get the visual label from a key for support.
	 *
	 * @param  string $key  Which label key we want.
	 *
	 * @return string
	 */
	public static function get_spec_support_label( $key = '' ) {

		// Bail with no key.
		if ( empty( $key ) ) {
			return false;
		}

		// Switch through my keys and return the appropriate.
		switch ( $key ) {

			case 'y':
				return __( 'Yes', 'caniuse-shortcode' );
				break;

			case 'y x':
				return __( 'With Prefix', 'caniuse-shortcode' );
				break;

			case 'x':
				return __( 'With Prefix', 'caniuse-shortcode' );
				break;

			case 'n':
				return __( 'No', 'caniuse-shortcode' );
				break;

			case 'a':
				return __( 'Partial Support', 'caniuse-shortcode' );
				break;

			case 'p':
				return __( 'Polyfill', 'caniuse-shortcode' );
				break;

			case 'u':
				return __( 'Unknown', 'caniuse-shortcode' );
				break;

			default :
				return false;

			// End all case breaks.
		}
	}

	/**
	 * Get the proper title for a browser key.
	 *
	 * @param  string $key  Which browser key we want.
	 *
	 * @return string
	 */
	public static function get_browser_title( $key = '' ) {

		// Bail with no key.
		if ( empty( $key ) ) {
			return false;
		}

		// Switch through my keys and return the appropriate.
		switch ( $key ) {

			case 'android':
				return __( 'Android', 'caniuse-shortcode' );
				break;

			case 'and_ff':
				return __( 'Android Firefox', 'caniuse-shortcode' );
				break;

			case 'and_chr':
				return __( 'Android Chrome', 'caniuse-shortcode' );
				break;

			case 'bb':
				return __( 'Blackberry', 'caniuse-shortcode' );
				break;

			case 'chrome':
				return __( 'Google Chrome', 'caniuse-shortcode' );
				break;

			case 'firefox':
				return __( 'Mozilla Firefox', 'caniuse-shortcode' );
				break;

			case 'ie':
				return __( 'Internet Explorer', 'caniuse-shortcode' );
				break;

			case 'edge':
				return __( 'Microsoft Edge', 'caniuse-shortcode' );
				break;

			case 'ios_saf':
				return __( 'iOS Safari', 'caniuse-shortcode' );
				break;

			case 'opera':
				return __( 'Opera', 'caniuse-shortcode' );
				break;

			case 'op_mini':
				return __( 'Opera Mini', 'caniuse-shortcode' );
				break;

			case 'op_mob':
				return __( 'Opera Mobile', 'caniuse-shortcode' );
				break;

			case 'safari':
				return __( 'Apple Safari', 'caniuse-shortcode' );
				break;

			default :
				return false;

			// End all case breaks.
		}
	}

	/**
	 * Check each item against the chosen browser and return the proper results.
	 *
	 * @param  array  $dataset  Our entire dataset that we're parsing.
	 * @param  string $browser  Which browser we're checking.
	 *
	 * @return array
	 */
	public static function get_support_result( $dataset = array(), $browser = '' ) {

		// Bail if no dataset was provided.
		if ( empty( $dataset ) || ! is_array( $dataset ) ) {
			return false;
		}

		// Get our browser title and label.
		$title  = self::get_browser_title( $browser );

		// Set my fallback support results.
		$base   = array(
			'title' => $title . ' - ' . self::get_spec_support_label( 'n' ),
			'label' => __( 'No', 'caniuse-shortcode' ),
			'flag'  => 'n',
			'vers'  => '',
			'pfix'  => false,
		);

		// First, get the most current support for this dataset.
		if ( false === $status = self::get_status_detail( $dataset ) ) {
			return $base;
		}

		// If we're in one of our good ones, grab that.
		if ( ! empty( $status['flag'] ) && in_array( $status['flag'], array( 'y', 'y x', 'a', 'p' ) ) ) {

			// Check for spec support and modify the title.
			$spec   = self::get_spec_support_label( $status['flag'] );
			$title  = ! empty( $spec ) ? $title . ' - ' . $spec : $title;

			// Get our support prefix.
			$prefix = self::check_support_prefix( $status['flag'] );

			// Build the version label.
			$label  = ! empty( $prefix ) ? $status['vers'] . '*' : $status['vers'];

			// Return our array.
			return array(
				'title' => $title,
				'label' => $label,
				'flag'  => $status['flag'],
				'vers'  => $status['vers'],
				'pfix'  => $prefix,
			);
		}

		// If we have the "unknown" flag, handle that.
		if ( ! empty( $status['flag'] ) && 'u' === esc_attr( $status['flag'] ) ) {

			// Check for spec support and modify the title.
			$spec   = self::get_spec_support_label( 'u' );
			$title  = ! empty( $spec ) ? $title . ' - ' . $spec : $title;

			// Get our support prefix.
			$prefix = self::check_support_prefix( 'u' );

			// Build the version label.
			$label  = ! empty( $prefix ) ? $status['vers'] . '*' : $status['vers'];

			// Return our array.
			return array(
				'title' => $title,
				'label' => $label,
				'flag'  => $status['flag'],
				'vers'  => $status['vers'],
				'pfix'  => false,
			);
		}

		// Set my fallback support results.
		return $base;
	}

	/**
	 * Take our dataset and find the current support status.
	 *
	 * @param  array  $dataset  Our entire dataset that we're parsing.
	 *
	 * @return string
	 */
	public static function get_status_detail( $dataset = array() ) {

		// Fetch the status from the end of the array.
		$status = array_values( array_slice( $dataset, -1 ) )[0];

		// And get the version we're on.
		$vers   = array_search( $status, $dataset, true );

		// Set the support status without any notes.
		$flag   = preg_replace( '/[^a-zA-Z\s]/', '', $status );

		// Return the support detail array.
		return array(
			'flag'  => trim( $flag ),
			'vers'  => self::trim_string( $vers, 3, true )
		);
	}

	/**
	 * Trim a string we have.
	 *
	 * @param  string  $string  The string to trim.
	 * @param  integer $count   How many characters to trim by.
	 * @param  boolean $period  Whether to also check for a period and trim that.
	 *
	 * @return string
	 */
	public static function trim_string( $string = '', $count = 1, $period = false ) {

		// Do the string cleanup.
		$string = substr( trim( $string ), 0, absint( $count ) );

		// Return it with our withour the period check.
		return ! empty( $period ) ? rtrim( $string, '.' ) : $string;
	}

	/**
	 * Check if a particular support item requires a prefix or not.
	 *
	 * @param  string $result  The result of the check.
	 *
	 * @return bool
	 */
	public static function check_support_prefix( $result = '' ) {
		return false !== stripos( $result, 'x' ) ? true : false;
	}

	// End class.
}

// Instantiate our class.
new CIU_Shortcode_Helper();
