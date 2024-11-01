<?php
class wpqc_Shortcode extends wpqc_Cls {
	
	public function __construct() {
		/*Shortcode*/
		add_shortcode('wp-query', array( $this, 'wpqc_shortcode' ));
	}

	/*Shortcode function*/
	public function wpqc_shortcode($atts) {
		$id = 1;
		if(isset($atts['id'])) {
			$id = $atts['id'];
		}
		if (get_option("wpqc_setting{$id}")) {
			$get_wpqc_setting = get_option("wpqc_setting{$id}");

			$get_post_type = 'any';
			if(isset($get_wpqc_setting['post_type'])) {
				$get_post_type = $get_wpqc_setting['post_type'];	
			}

			$get_posts_per_page = -1;
			if(isset($get_wpqc_setting['posts_per_page'])) {
				$get_posts_per_page = $get_wpqc_setting['posts_per_page'];
			}

			$get_exclude_post = 0;
			if(isset($get_wpqc_setting['exclude_post'])) {
				$get_exclude_post = $get_wpqc_setting['exclude_post'];
				$get_exclude_post = trim($get_exclude_post);
				$get_exclude_post = explode(",", $get_exclude_post);
			}

			$get_order = 'DESC';
			if(isset($get_wpqc_setting['order'])) {
				$get_order = $get_wpqc_setting['order'];
			}

			$get_orderby = 'date';
			if(isset($get_wpqc_setting['orderby'])) {
				$get_orderby = $get_wpqc_setting['orderby'];
			}

			$get_post_status = 'publish';
			if(isset($get_wpqc_setting['post_status'])) {
				$get_post_status = $get_wpqc_setting['post_status'];
			}

			$get_cat = array();
			if(isset($get_wpqc_setting['cat'])) {
				$get_cat = $get_wpqc_setting['cat'];
			}

			if(isset($get_wpqc_setting['excat'])) {
				$get_excat = $get_wpqc_setting['excat'];
				foreach ($get_excat as $key => $value) {
					array_push($get_cat, intval($value) * -1);
				}
			}
			$get_cat = array_values(array_filter($get_cat));

			$get_tax = '';
			if(isset($get_wpqc_setting['tax'])) {
				$get_tax = $get_wpqc_setting['tax'];
			}

			$get_tags = array();
			if(isset($get_wpqc_setting['tags'])) {
				$get_tags = $get_wpqc_setting['tags'];
			}
			$get_tags = array_values(array_filter($get_tags));
			$get_tags = implode(",", $get_tags);

			$get_search_string = '';
			if(isset($get_wpqc_setting['search_string'])) {
				$get_search_string = $get_wpqc_setting['search_string'];
			}

			$get_excerpt_length = 100;
			if(isset($get_wpqc_setting['excerpt_length'])) {
				$get_excerpt_length = $get_wpqc_setting['excerpt_length'];
			}

			$tax_query = false;
			if($get_tax) {
				$tax_query = array( 'relation' => 'OR' );
				foreach($get_tax as $key => $val) {
					$tax_term = explode("?", trim($val));
					$tax_query[] = array(
						'taxonomy' => ''.$tax_term[0].'',
						'field'    => 'slug',
						'terms'    => ''.$tax_term[1].''
						);	
				}
			}

			$get_remove_pagi = 0;
			if(isset($get_wpqc_setting['remove_pagi'])) {
				$get_remove_pagi = $get_wpqc_setting['remove_pagi'];	
			}

			$get_prev_text = '« Previous';
			if(isset($get_wpqc_setting['prev_text'])) {
				$get_prev_text = $get_wpqc_setting['prev_text'];	
			}

			$get_next_text = 'Next »';
			if(isset($get_wpqc_setting['next_text'])) {
				$get_next_text = $get_wpqc_setting['next_text'];	
			}

			$paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;

			if ($get_post_type == 'page') {
				$args = array(
					'post_type' => $get_post_type,
					'posts_per_page' => $get_posts_per_page,
					'post__not_in' => $get_exclude_post,
					'order' => $get_order,
					'orderby' => $get_orderby,
					'post_status' => $get_post_status,
					'tag' => $get_tags,
					's' => $get_search_string,
					'tax_query' => $tax_query,
					'paged' => $paged,
				);
			} else {
				$args = array(
					'post_type' => $get_post_type,
					'posts_per_page' => $get_posts_per_page,
					'post__not_in' => $get_exclude_post,
					'order' => $get_order,
					'orderby' => $get_orderby,
					'post_status' => $get_post_status,
					'cat' => $get_cat,
					'tag' => $get_tags,
					's' => $get_search_string,
					'tax_query' => $tax_query,
					'paged' => $paged,
				);
			}
			

			$wpqc_query = new WP_Query( $args );

			if ( $wpqc_query->have_posts() ) :
				global $post;
			$returnhtml = false;
			while ( $wpqc_query->have_posts() ) : $wpqc_query->the_post();
			$postid = get_the_ID();
			$title = get_the_title();
			$author_name = get_the_author();
			$author_id = get_the_author_meta('id');
			$author_posts_url = get_author_posts_url($author_id);
			$date = get_the_date();
			$content = get_the_content();
			$excerpt = parent::wpqc_the_excerpt($get_excerpt_length);
			$permalink = get_permalink();
			$feature_img = false;
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
			$find_rep_str = parent::get_inbetween_strings('%', '%', $template_setting);
			foreach($find_rep_str as $key => $val) {
				if(strrpos($val, "getfield") > -1) {
					$get_field_arr = explode("|", $val);
					if(!$get_field_arr[2]) {
						$get_field_arr[2] = 'text';
					}
					if($get_field_arr[2] == 'text' || $get_field_arr[2] == 'number' || $get_field_arr[2] == 'email' || $get_field_arr[2] == 'editor' || $get_field_arr[2] == 'textarea') {
						if (function_exists('get_field')) {
							$getfields = get_field($get_field_arr[1]);
							$template_setting = str_replace("%{$val}%", "{$getfields}", $template_setting);
						}
					}
					if($get_field_arr[2] == 'image') {
						if(!$get_field_arr[3]) {
							$get_field_arr[3] = 'url';
						}
						if (function_exists('get_field')) {
							$getfields = get_field($get_field_arr[1]);
							if(is_array ($getfields)) {
								if($get_field_arr[3] == 'url') {
									if($getfields['url']) {
										$template_setting = str_replace("%{$val}%", "<img src='".$getfields['url']."'>", $template_setting);
									}
								} else {
									if($getfields['sizes']) {
										if($getfields['sizes'][$get_field_arr[3]]) {
											$template_setting = str_replace("%{$val}%", "<img src='".$getfields['sizes'][$get_field_arr[3]]."'>", $template_setting);
										}
									}
								}																	
							} else {
								if($getfields) {
									if(strrpos($getfields, "http") > -1) {
										$template_setting = str_replace("%{$val}%", "<img src='".$getfields."'>", $template_setting);
									} else {
										$img = wp_get_attachment_image( $getfields, $get_field_arr[3] );
										$template_setting = str_replace("%{$val}%", $img, $template_setting);
									}
								} else {
									$template_setting = str_replace("%{$val}%", "", $template_setting);
								}
							}
						}
					}
				} else {
					if(strrpos($val, "feature_img") > -1) {
						$get_feature_img = explode("|", $val);
						if(isset($get_feature_img[1])) {
							if ( has_post_thumbnail() ) {
								$img_path = wp_get_attachment_image_src( get_post_thumbnail_id( $postid ), $get_feature_img[1] );
								if($img_path[0]) {
									$feature_img = $img_path[0];
								}
							}
						} else {
							if ( has_post_thumbnail() ) {
								$img_path = wp_get_attachment_image_src( get_post_thumbnail_id( $postid ), 'thumbnail' );
								if($img_path[0]) {
									$feature_img = $img_path[0];
								}
							}
						}
						if(!$feature_img){
							$feature_img = plugins_url( 'assets/img/no-img.png', dirname(__FILE__) );
						}
						$template_setting = str_replace("%{$val}%", $feature_img, $template_setting);			
					} else {
						$template_setting = str_replace("%{$val}%", $$val, $template_setting);
					}
				}						
			}
			$returnhtml .= $template_setting;

			endwhile;

			if($get_remove_pagi == 0) {
				$big = 999999999;
				$returnhtml .= paginate_links( array(
					'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
					'format' => '?paged=%#%',
					'current' => max( 1, get_query_var('paged') ),
					'total' => $wpqc_query->max_num_pages,
					'prev_text' => __($get_prev_text),
					'next_text' => __($get_next_text)
					) );
			}

			wp_reset_postdata();

			else :
				endif;

			return $returnhtml;
			wp_die();
		}
	}      
}

$wpqc_Shortcode = new wpqc_Shortcode();