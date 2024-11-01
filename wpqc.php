<?php
/*
* Plugin Name: WP Query Creator
* Plugin URI: http://nioc.co.in/wp-query-creator/
* Description: WP Query Creator provides an interface for creating WP queries as shortcodes.
* Version: 1.0
* Requires at least: 5.0
* Requires PHP: 7.1
* Author: Tarak Patel
* Author URI: http://nioc.co.in/
* License: GPLv2 or later
* License URI: https://www.gnu.org/licenses/gpl-2.0.html
* Text Domain: wp-query-creator
*/

// Exit if accessed directly
   if ( !defined( 'ABSPATH' ) ) exit;

   class wpqc_Cls {

    public function __construct() {
      /*Admin functionality*/
      require_once(dirname(__FILE__) . '/includes/admin.php');

      /*Css & Js*/
      require_once(dirname(__FILE__) . '/includes/css_js.php');

      /*Shortcode*/
      require_once(dirname(__FILE__) . '/includes/shortcode.php');
    }
    
    /*General Functions*/
    /*Convert string to variable*/
    public function get_inbetween_strings($start, $end, $str) {
     $matches = array();
     $regex = "/$start([a-zA-Z0-9_|-]*)$end/";
     preg_match_all($regex, $str, $matches);
     return $matches[1];
   }

   /*Custom get_the_excerpt func*/
   public function wpqc_the_excerpt($charlength) {
    $excerpt = get_the_excerpt();
    $charlength++;
    if ( mb_strlen( $excerpt ) > $charlength ) {
     $subex = mb_substr( $excerpt, 0, $charlength - 5 );
     $exwords = explode( ' ', $subex );
     $excut = - ( mb_strlen( $exwords[ count( $exwords ) - 1 ] ) );
     if ( $excut < 0 ) {
      $return_val = mb_substr( $subex, 0, $excut );
    } else {
      $return_val = $subex;
    }
    $return_val .= '...';
  } else {
   $return_val = $excerpt;
 }
 return $return_val;
}

}

$wpqc_Cls = New wpqc_Cls;