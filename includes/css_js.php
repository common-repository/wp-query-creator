<?php
class wpqc_CSS_JS extends wpqc_Cls {

	public function __construct() {
		/*Add style in wp-admin footer*/
		add_action('admin_footer', array( $this, 'wp_query_add_style_footer' ) );
	}

	/*Add style in wp-admin footer*/
	public function wp_query_add_style_footer() {
		?>
		<style>
			#wpqc_box pre{white-space:nowrap;margin-top:0}
			#wpqc_box .alain-right{float:right}
			#wpqc_box .rateme{color:green;text-align:center}
			#wpqc_box .content-box{background:white;padding:20px;margin:0 0 10px 0}
			#wpqc_box .content-box h3{margin:0 0 10px 0}
			#wpqc_box .psform code{margin-right:2px;margin-bottom:5px;display:inline-block}
			#wpqc_box .adf i{color:green;background:#fff;padding:0 5px;font-size:12px;text-transform:uppercase}
		</style>
		<?php
	}

}

$wpqc_CSS_JS = new wpqc_CSS_JS();