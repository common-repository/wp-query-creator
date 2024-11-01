<?php
class wpqc_Admin extends wpqc_Cls {

	public function __construct() {
		/*Menu pages*/
		add_action('admin_menu', array( $this, 'wpqc_menu' ));

		/*Form options*/
		add_action( 'admin_init', array( $this, 'wpqc_register_option_settings' ));

		/*Setting link*/
		add_filter( 'plugin_action_links_' . plugin_basename( dirname( dirname( __FILE__ ) ) ) . '/wpqc.php', array( $this, 'wpqc_setting_link' ) );
	}

	/*Add setting link*/
	function wpqc_setting_link( $links ) {
		return array_merge( array(
			'<a href="' . admin_url( 'admin.php?page=wpqc' ) . '">' . __( 'Setting', 'wp-query-creator' ) . '</a>',
			), $links );
	}

	/*Add menu*/
	public function wpqc_menu() {
		add_menu_page( __( 'WP query creator', 'wp-query-creator' ), __( 'WP query creator', 'wp-query-creator' ), 'manage_options', 'wpqc', array( $this, 'wpqc_page' ) );
	}

	/*Common option for all query*/
	public function wpqc_register_option_settings() {
		register_setting( 'wpqc-settings-group', 'wpqc_setting' );
	}

	/*Admin listing & query setting*/
	public function wpqc_page() {
		?>
		<div id="<?php echo esc_attr( 'wpqc_box' ); ?>" class="<?php echo esc_attr( 'wrap' ); ?>">
			<h1><?php echo esc_html( 'WP query creator' ); ?></h1>
			<hr>
			<?php
			/*Query setting form*/
			if(isset($_GET['query_no'])) {
				$query_no = sanitize_text_field($_GET['query_no']);
				/*Get form data (common option for all query)*/
				$get_ps_set = get_option("wpqc_setting");
				if($get_ps_set && key($get_ps_set) && key($get_ps_set) == $query_no) {
					if(get_option( "wpqc_setting{$query_no}" )) {
						/*Update query setting*/
						update_option( "wpqc_setting{$query_no}", $get_ps_set[key($get_ps_set)] );
					} else {
						/*Add query setting*/
						delete_option("wpqc_setting{$query_no}");
						add_option("wpqc_setting{$query_no}", $get_ps_set[key($get_ps_set)], "", "yes" );									
					}
				}
				
				/*Get query setting*/
				$get_wpqc_setting = get_option( "wpqc_setting{$query_no}" );

				/*Delete common option*/
				delete_option("wpqc_setting");

				/*Display shortcodes*/
				$sortcodeforcms = "[wp-query id=".$query_no."]";
				$sortcodeforphp = "&lt;?php echo do_shortcode('[wp-query id=".$query_no."]'); ?&gt;";
				
				/*Preview*/
				if(isset($_GET['preview'])) {
					$preview = sanitize_text_field($_GET['preview']);
					?>
					<div class="content-box top">
						<a class="<?php echo esc_attr( 'button button-primary' ); ?>" href="<?php echo admin_url('/admin.php?page=wpqc&query_no='.$query_no.''); ?>">
							<?php echo esc_html('Back'); ?>
						</a>
						<?php echo do_shortcode('[wp-query id='.$preview.']'); ?>
					</div>
					<?php					
				} else {
					?>
					<div class="content-box top">
						<a style="margin-left:5px;" class="<?php echo esc_attr( 'button right' ); ?>" href="<?php echo admin_url('/admin.php?page=wpqc'); ?>"><?php echo esc_html('All WP query'); ?></a>
						<a class="<?php echo esc_attr( 'button right' ); ?>" href="<?php echo admin_url('/admin.php?page=wpqc&query_no='.$query_no.'&preview='.$query_no.''); ?>"><?php echo esc_html('Preview'); ?></a>
						<pre>
							<strong><?php echo esc_html( 'Shortcode for CMS' ) ?> : </strong> <?php echo $sortcodeforcms; ?>
						</pre>
						<pre>
							<strong><?php echo esc_html( 'Shortcode for PHP' ) ?> : </strong> <?php echo $sortcodeforphp; ?>
						</pre>						
					</div>
					<form method="post" action="options.php">
						<?php
						settings_fields( 'wpqc-settings-group' );
						do_settings_sections( 'wpqc-settings-group' );
						?>
						<table class="<?php echo esc_attr( 'form-table psform' ); ?>">
							<tr valign="top">
								<th scope="row"><?php echo esc_html( 'Query name' ); ?></th>
								<td>
									<?php
									$get_enter_name = '';
									if(isset($get_wpqc_setting['enter_name'])) {
										$get_enter_name = $get_wpqc_setting['enter_name'];	
									}
									?>
									<input type="text" name="wpqc_setting[<?php echo $query_no; ?>][enter_name]" class="<?php echo esc_attr( 'regular-text' ); ?>" value="<?php echo esc_attr($get_enter_name); ?>" />
									<p class="<?php echo esc_attr( 'description' ); ?>">
										<?php echo esc_html( 'Enter a unique name for your wp-query' ); ?>
									</p>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php echo esc_html( 'Select type' ); ?></th>
								<td>
									<?php
									$get_post_type = false;
									if(isset($get_wpqc_setting['post_type'])) {
										$get_post_type = $get_wpqc_setting['post_type'];	
									}
									?>
									<select name="wpqc_setting[<?php echo $query_no; ?>][post_type]" class="<?php echo esc_attr( 'regular-text' ); ?>">
										<option value="any"><?php echo esc_html( 'Any' ); ?></option>
										<?php
										foreach ( get_post_types( '', 'names' ) as $post_type ) {
											if($post_type == 'attachment' || $post_type == 'revision' || $post_type == 'nav_menu_item' || $post_type == 'custom_css' || $post_type == 'customize_changeset' || $post_type == 'acf' || $post_type == 'product_variation' || $post_type == 'shop_order' || $post_type == 'shop_order_refund' || $post_type == 'shop_webhook') {

											} else { ?>
											<option <?php if($get_post_type == $post_type) echo 'selected'; ?> value="<?php echo esc_attr($post_type); ?>"><?php echo $post_type; ?></option>
											<?php }
										} ?>								
									</select>
									<p class="<?php echo esc_attr( 'description' ); ?>">
										<?php echo esc_html( 'Display content based on the selected type above' ); ?>
									</p>				
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php echo esc_html( 'Exclude some posts' ); ?></th>
								<td>
									<?php
									$get_exclude_post = '';
									if(isset($get_wpqc_setting['exclude_post'])) {
										$get_exclude_post = $get_wpqc_setting['exclude_post'];	
									}
									?>
									<input type="text" name="wpqc_setting[<?php echo $query_no; ?>][exclude_post]" class="<?php echo esc_attr( 'regular-text' ); ?>" value="<?php echo esc_attr($get_exclude_post); ?>" />
									<p class="<?php echo esc_attr( 'description' ); ?>">
										<?php echo esc_html( 'Add comma-separated post ids like 1,2,3' ); ?>
										<br>
										<?php echo esc_html( 'You can add multiple post ids here, those posts will not be displayed.' ); ?>
									</p>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php echo esc_html( 'Number of posts' ); ?></th>
								<td>
									<?php
									$get_posts_per_page = -1;
									if(isset($get_wpqc_setting['posts_per_page'])) {
										$get_posts_per_page = $get_wpqc_setting['posts_per_page'];	
									}
									?>
									<input type="number" name="wpqc_setting[<?php echo $query_no; ?>][posts_per_page]" class="<?php echo esc_attr( 'regular-text' ); ?>" value="<?php echo esc_attr($get_posts_per_page); ?>" />
									<p class="<?php echo esc_attr( 'description' ); ?>">
										<?php echo esc_html( 'Select the number of posts to display' ); ?>
									</p>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php echo esc_html( 'Order' ); ?></th>
								<td>
									<?php
									$get_order = 'DESC';
									if(isset($get_wpqc_setting['order'])) {
										$get_order = $get_wpqc_setting['order'];	
									}
									?>
									<select name="wpqc_setting[<?php echo $query_no; ?>][order]" class="<?php echo esc_attr( 'regular-text' ); ?>">
										<option <?php if($get_order == 'DESC') echo 'selected'; ?> value="<?php echo esc_attr( 'DESC' ); ?>"><?php echo esc_html( 'DESC' ); ?></option>
										<option <?php if($get_order == 'ASC') echo 'selected'; ?> value="<?php echo esc_attr( 'ASC' ); ?>"><?php echo esc_html( 'ASC' ); ?></option>
									</select>
									<p class="<?php echo esc_attr( 'description' ); ?>">
										<?php echo esc_html( 'Choose ascending or descending order' ); ?>
									</p>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php echo esc_html( 'Order by' ); ?></th>
								<td>
									<?php
									$get_orderby = 'date';
									if(isset($get_wpqc_setting['orderby'])) {
										$get_orderby = $get_wpqc_setting['orderby'];
									}
									?>
									<select name="wpqc_setting[<?php echo $query_no; ?>][orderby]" class="<?php echo esc_attr( 'regular-text' ); ?>">
										<option <?php if($get_orderby == 'date') echo 'selected'; ?> value="<?php echo esc_attr( 'date' ); ?>"><?php echo esc_html( 'date' ); ?></option>
										<option <?php if($get_orderby == 'ID') echo 'selected'; ?> value="<?php echo esc_attr( 'ID' ); ?>"><?php echo esc_html( 'ID' ); ?></option>
										<option <?php if($get_orderby == 'author') echo 'selected'; ?> value="<?php echo esc_attr( 'author' ); ?>"><?php echo esc_html( 'author' ); ?></option>										
										<option <?php if($get_orderby == 'title') echo 'selected'; ?> value="<?php echo esc_attr( 'title' ); ?>"><?php echo esc_html( 'title' ); ?></option>
										<option <?php if($get_orderby == 'name') echo 'selected'; ?> value="<?php echo esc_attr( 'name' ); ?>"><?php echo esc_html( 'name' ); ?></option>
										<option <?php if($get_orderby == 'type') echo 'selected'; ?> value="<?php echo esc_attr( 'type' ); ?>"><?php echo esc_html( 'type' ); ?></option>
										<option <?php if($get_orderby == 'modified') echo 'selected'; ?> value="<?php echo esc_attr( 'modified' ); ?>"><?php echo esc_html( 'modified' ); ?></option>
										<option <?php if($get_orderby == 'parent') echo 'selected'; ?> value="<?php echo esc_attr( 'parent' ); ?>"><?php echo esc_html( 'parent' ); ?></option>
										<option <?php if($get_orderby == 'rand') echo 'selected'; ?> value="<?php echo esc_attr( 'rand' ); ?>"><?php echo esc_html( 'rand' ); ?></option>
										<option <?php if($get_orderby == 'comment_count') echo 'selected'; ?> value="<?php echo esc_attr( 'comment_count' ); ?>"><?php echo esc_html( 'comment_count' ); ?></option>									
									</select>
									<p class="<?php echo esc_attr( 'description' ); ?>">
										<?php echo esc_html( 'Sort all posts by ID or title or name or date' ); ?>
									</p>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php echo esc_html( 'Post status' ); ?></th>
								<td>
									<?php
									$get_post_status = 'publish';
									if(isset($get_wpqc_setting['post_status'])) {
										$get_post_status = $get_wpqc_setting['post_status'];
									}
									?>
									<select name="wpqc_setting[<?php echo $query_no; ?>][post_status]" class="<?php echo esc_attr( 'regular-text' ); ?>">
										<option <?php if($get_post_status == 'publish') echo 'selected'; ?> value="<?php echo esc_attr( 'publish' ); ?>"><?php echo esc_html( 'publish' ); ?></option>
										<option <?php if($get_post_status == 'pending') echo 'selected'; ?> value="<?php echo esc_attr( 'pending' ); ?>"><?php echo esc_html( 'pending' ); ?></option>										
										<option <?php if($get_post_status == 'draft') echo 'selected'; ?> value="<?php echo esc_attr( 'draft' ); ?>"><?php echo esc_html( 'draft' ); ?></option>
										<option <?php if($get_post_status == 'auto-draft') echo 'selected'; ?> value="<?php echo esc_attr( 'auto-draft' ); ?>"><?php echo esc_html( 'auto-draft' ); ?></option>
										<option <?php if($get_post_status == 'future') echo 'selected'; ?> value="<?php echo esc_attr( 'future' ); ?>"><?php echo esc_html( 'future' ); ?></option>
										<option <?php if($get_post_status == 'private') echo 'selected'; ?> value="<?php echo esc_attr( 'private' ); ?>"><?php echo esc_html( 'private' ); ?></option>
										<option <?php if($get_post_status == 'inherit') echo 'selected'; ?> value="<?php echo esc_attr( 'inherit' ); ?>"><?php echo esc_html( 'inherit' ); ?></option>
										<option <?php if($get_post_status == 'trash') echo 'selected'; ?> value="<?php echo esc_attr( 'trash' ); ?>"><?php echo esc_html( 'trash' ); ?></option>
										<option <?php if($get_post_status == 'any') echo 'selected'; ?> value="<?php echo esc_attr( 'any' ); ?>"><?php echo esc_html( 'any' ); ?></option>
									</select>
									<p class="<?php echo esc_attr( 'description' ); ?>">
										<?php echo esc_html( 'Show posts associated with certain status.' ); ?>
									</p>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php echo esc_html( 'Posts by category' ); ?></th>
								<td>
									<?php
									$categories = get_categories( array(
										'orderby' => 'name',
										'order'   => 'ASC'
										) );
									$get_cat = false;
									if(isset($get_wpqc_setting['cat'])) {
										$get_cat = $get_wpqc_setting['cat'];	
									}
									?>
									<select name="wpqc_setting[<?php echo $query_no; ?>][cat][]" multiple class="<?php echo esc_attr( 'regular-text' ); ?>">
										<option value="">Select category</option>
										<?php foreach($categories as $key => $val) { ?>
										<option <?php if ($get_cat && in_array($val->term_id, $get_cat)) { echo "selected"; } ?> value="<?php echo $val->term_id; ?>"><?php echo $val->name; ?></option>
										<?php } ?>
									</select>
									<p class="description">
										<?php echo esc_html( 'Only selected category posts will be displayed' ); ?>
									</p>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php echo esc_html( 'Posts exclude by category' ); ?></th>
								<td>
									<?php
									$get_excat = false;
									if(isset($get_wpqc_setting['excat'])) {
										$get_excat = $get_wpqc_setting['excat'];	
									}
									?>
									<select name="wpqc_setting[<?php echo $query_no; ?>][excat][]" multiple class="<?php echo esc_attr( 'regular-text' ); ?>">
										<option value="">Select category</option>
										<?php foreach($categories as $key => $val) { ?>
										<option <?php if ($get_excat && in_array($val->term_id, $get_excat)) { echo "selected"; } ?> value="<?php echo $val->term_id; ?>"><?php echo $val->name; ?></option>
										<?php } ?>
									</select>
									<p class="description">
										<?php echo esc_html( 'Selected category posts will not be displayed' ); ?>
									</p>
								</td>
							</tr>
							<?php
							$args = array(
								'public'   => true,
								'_builtin' => false
							);
							$taxonomies = get_taxonomies($args);
							if (count($taxonomies)) {
							?>
							<tr valign="top">
								<th scope="row"><?php echo esc_html( 'Posts by taxonomy' ); ?></th>
								<td>
									<?php
									foreach($taxonomies as $key => $val) {
										if($key == 'category' || $key == 'post_tag' || $key == 'nav_menu' || $key == 'link_category' || $key == 'post_format') {
										} else {
											$get_tax = false;
											if(isset($get_wpqc_setting['tax'])) {
												$get_tax = $get_wpqc_setting['tax'];	
											}
											$getterms = get_terms( array(
												'taxonomy' => $key,
												'hide_empty' => false,
												) );
											if(count($getterms)) {
												?>
												<select rows="10" cols="50" name="wpqc_setting[<?php echo $query_no; ?>][tax][]" multiple class="<?php echo esc_attr( 'regular-text' ); ?>">
													<option value=""><?php echo esc_html( 'Select taxonomy' ); ?></option>
													<?php foreach($getterms as $ke => $vl) { ?>
													<option <?php if ($get_tax && in_array($key . "?" . $vl->slug, $get_tax)) { echo "selected"; } ?> value="<?php echo $key . "?" . $vl->slug; ?>"><?php echo $vl->taxonomy . " : " . $vl->name; ?></option>
													<?php }
												} ?>
											</select>					
											<?php }
										} ?>
										<p class="description">
											<?php echo esc_html( 'Only selected taxonomy posts will be displayed' ); ?>
										</p>
									</td>
								</tr>
								<?php
							}
							$tags = get_tags();
							if (count($tags)) {
								?>
								<tr valign="top">
									<th scope="row"><?php echo esc_html( 'Posts by tags' ); ?></th>
									<td>
										<?php
										$tags = get_tags();
										$get_tags = false;
										if(isset($get_wpqc_setting['tags'])) {
											$get_tags = $get_wpqc_setting['tags'];	
										}
										?>
										<select name="wpqc_setting[<?php echo $query_no; ?>][tags][]" multiple class="<?php echo esc_attr( 'regular-text' ); ?>">
											<option value="">Select tags</option>
											<?php foreach($tags as $key => $val) { ?>
											<option <?php if ($get_tags && in_array($val->slug, $get_tags)) { echo "selected"; } ?> value="<?php echo $val->slug; ?>"><?php echo $val->name; ?></option>
											<?php } ?>
										</select>
										<p class="description">
											<?php echo esc_html( 'Display only posts with selected tag above' ); ?>
										</p>
									</td>
								</tr>
							<?php } ?>
								<tr valign="top">
									<th scope="row"><?php echo esc_html( 'Search string' ); ?></th>
									<td>
										<?php
										$get_search_string = false;
										if(isset($get_wpqc_setting['search_string'])) {
											$get_search_string = $get_wpqc_setting['search_string'];	
										}
										?>
										<input type="text" name="wpqc_setting[<?php echo $query_no; ?>][search_string]" class="<?php echo esc_attr( 'regular-text' ); ?>" value="<?php echo esc_attr($get_search_string); ?>" />
										<p class="<?php echo esc_attr( 'description' ); ?>">
											<?php echo esc_html( 'Display only posts that have the above keywords' ); ?>
										</p>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row"><?php echo esc_html( 'Template setting' ); ?></th>
									<td>
										<span class="<?php echo esc_attr( 'description' ); ?>">
											<?php echo esc_html( 'Dynemic tags : Use the below tags to fetch dynamic content.' ); ?>
										</span>
										<p>
											<strong>
												<code title="<?php echo esc_attr( 'dynemic tag for title, get_the_title()' ); ?>">%title%</code>
												<code title="<?php echo esc_attr( 'dynemic tag for title, get_the_date()' ); ?>">%date%</code>
												<code title="<?php echo esc_attr( 'dynemic tag for link, get_permalink()' ); ?>">%permalink%</code>
												<code title="<?php echo esc_attr( 'dynemic tag for content, get_the_content()' ); ?>">%content%</code>
												<code title="<?php echo esc_attr( 'dynemic tag for excerpt, get_the_excerpt()' ); ?>">%excerpt%</code>
												<code title="<?php echo esc_attr( 'dynemic tag for feature thumb image' ); ?>">%feature_img|thumbnail%</code>
												<code title="<?php echo esc_attr( 'dynemic tag for feature medium image' ); ?>">%feature_img|medium%</code>
												<code title="<?php echo esc_attr( 'dynemic tag for feature large image' ); ?>">%feature_img|large%</code>
												<code title="<?php echo esc_attr( 'dynemic tag for feature full image' ); ?>">%feature_img|full%</code>
												<code title="<?php echo esc_attr( 'dynemic tag for previous button' ); ?>">%author_name%</code>
												<code title="<?php echo esc_attr( 'dynemic tag for next button' ); ?>">%author_posts_url%</code>
											</strong>
										</p>
										<?php
										if(isset($get_wpqc_setting['template_setting']) && $get_wpqc_setting['template_setting'] != '') {
											$template_setting = $get_wpqc_setting['template_setting'];
										} else {
$template_setting = '
<div class="box">
	<img class="image" src="%feature_img|thumbnail%">
	<div class="content">
		<h2 class="title">%title%</h2>
		<p><span class="date">%date%</span></p>
		<div class="excerpt">%excerpt%</div>
		<a class="readmore" href="%permalink%">Read more</a>
	</div>
</div>';
										}
										$settings = array(
											'media_buttons' => false,
											'quicktags' => false,
											'tinymce' => false
											);
										$editor_id = "wpqc_setting[".$query_no."][template_setting]";
										wp_editor( $template_setting, $editor_id, $settings );
										?>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row"><?php echo esc_html( 'Length of post content' ); ?></th>
									<td>
										<?php
										$get_excerpt_length = 100;
										if(isset($get_wpqc_setting['excerpt_length'])) {
											$get_excerpt_length = $get_wpqc_setting['excerpt_length'];	
										}
										?>
										<input type="number" name="wpqc_setting[<?php echo $query_no; ?>][excerpt_length]" class="<?php echo esc_attr( 'regular-text' ); ?>" value="<?php echo esc_attr($get_excerpt_length); ?>" />
										<p class="<?php echo esc_attr( 'description' ); ?>">
											<?php echo esc_html( 'You can choose the post content length by this option.' ); ?>
										</p>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row"><?php echo esc_html( 'Remove pagination' ); ?></th>
									<td>
										<?php
										$get_remove_pagi = 0;
										if(isset($get_wpqc_setting['remove_pagi'])) {
											$get_remove_pagi = $get_wpqc_setting['remove_pagi'];	
										}
										?>
										<input type="checkbox" <?php if($get_remove_pagi == 1) { echo 'checked'; } ?> name="wpqc_setting[<?php echo $query_no; ?>][remove_pagi]" class="<?php echo esc_attr( 'regular-text' ); ?>" value="1" />
									</td>
								</tr>
								<tr valign="top">
									<th scope="row"><?php echo esc_html( 'Previous text' ); ?></th>
									<td>
										<?php
										$get_prev_text = '« Previous';
										if(isset($get_wpqc_setting['prev_text'])) {
											$get_prev_text = $get_wpqc_setting['prev_text'];	
										}
										?>
										<input type="text" name="wpqc_setting[<?php echo $query_no; ?>][prev_text]" class="<?php echo esc_attr( 'regular-text' ); ?>" value="<?php echo esc_attr($get_prev_text); ?>" />
										<p class="<?php echo esc_attr( 'description' ); ?>">
											<?php echo esc_html( 'You can change the previous button text from pagination.' ); ?>
										</p>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row"><?php echo esc_html( 'Next Text' ); ?></th>
									<td>
										<?php
										$get_next_text = 'Next »';
										if(isset($get_wpqc_setting['next_text'])) {
											$get_next_text = $get_wpqc_setting['next_text'];	
										}
										?>
										<input type="text" name="wpqc_setting[<?php echo $query_no; ?>][next_text]" class="<?php echo esc_attr( 'regular-text' ); ?>" value="<?php echo esc_attr($get_next_text); ?>" />
										<p class="<?php echo esc_attr( 'description' ); ?>">
											<?php echo esc_html( 'You can change the next button text from pagination.' ); ?>
										</p>
									</td>
								</tr>
							</table>
							<?php submit_button(); ?>
						</form>
						<?php }
					} else {
						/*Listing*/
						$del_query_no = 0;
						if (isset($_GET['del_query_no'])) {
							/*Delete Query*/
							$del_query_no = sanitize_text_field($_GET['del_query_no']);
							update_option("wpqc_setting{$del_query_no}", "blank");
						}

						if(isset($_GET['preview'])) {
							$preview = sanitize_text_field($_GET['preview']);
							/*Get query setting*/
							$get_wpqc_setting = get_option( "wpqc_setting{$preview}" );
							?>
							<div class="<?php echo esc_attr('content-box'); ?>">
								<a class="<?php echo esc_attr( 'button button-primary' ); ?>" href="<?php echo admin_url('/admin.php?page=wpqc'); ?>">
									<?php echo esc_html('Remove Preview'); ?>
								</a>
								<?php echo do_shortcode('[wp-query id='.$preview.']'); ?>
							</div>
							<?php
						}
						?>
						<div>
							<table class="<?php echo esc_attr( 'wp-list-table widefat fixed striped posts' ) ?>">
								<thead>
									<tr>
										<th width="50"><?php echo esc_html( 'No.' ); ?></th>
										<th width="100"><?php echo esc_html( 'Name' ); ?></th>
										<th><?php echo esc_html( 'Shortcode' ); ?></th>
										<th width="100"></th>
										<th width="100"></th>
										<th width="100"></th>
									</tr>
								</thead>
								<tbody>
									<?php
									$i = 1;
									while(get_option( "wpqc_setting{$i}" )) {
										$get_wpqc_setting = get_option( "wpqc_setting{$i}" );
										if($get_wpqc_setting != 'blank') {
											$get_enter_name = '';
											if(isset($get_wpqc_setting['enter_name'])) {
												$get_enter_name = $get_wpqc_setting['enter_name'];	
											}
											?>
											<tr>
												<td><?php echo $i; ?></td>
												<td><?php echo $get_enter_name; ?></td>
												<td>
													<pre>
														<strong><?php echo esc_html( 'Shortcode for CMS' ) ?> :</strong> [wp-query id=<?php echo $i; ?>]
													</pre>
													<pre>
														<strong><?php echo esc_html( 'Shortcode for PHP' ) ?> :</strong> &lt;?php echo do_shortcode('[wp-query id=<?php echo $i; ?>]'); ?&gt;
													</pre>
												</td>
												<td>
													<a class="<?php echo esc_attr('button'); ?>" href="<?php echo admin_url('/admin.php?page=wpqc&preview='.$i); ?>">
														<?php echo esc_html( 'Preview' ); ?>
													</a>
												</td>
												<td>
													<a class="<?php echo esc_attr('button'); ?>" href="<?php echo admin_url('/admin.php?page=wpqc&query_no='.$i); ?>">
														<?php echo esc_html( 'Setting' ); ?>
													</a>
												</td>
												<td>
													<a class="<?php echo esc_attr('button'); ?>" href="<?php echo admin_url('/admin.php?page=wpqc&del_query_no='.$i); ?>">
														<?php echo esc_html( 'Delete' ); ?>
													</a>
												</td>
											</tr>
											<?php
										}
										$i++;
									} ?>
								</tbody>
								<tfoot>
									<tr>
										<th colspan="6">
											<a href="<?php echo admin_url('/admin.php?page=wpqc&query_no='.$i); ?>" class="<?php echo esc_attr( 'button button-primary' ); ?>">
												<?php echo esc_html( 'Add New WP Query' ); ?>
											</a>
										</th>
									</tr>
								</tfoot>
							</table>
						</div>
						<?php } ?>				
					</div>
					<?php }
				}

				$wpqc_Admin = new wpqc_Admin();