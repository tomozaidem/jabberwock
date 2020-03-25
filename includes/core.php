<?php
/**
 * Theme core file.
 *
 * @author    Tomo Zaidem
 * @package   Jabberwock
 * @version   1.0.0
 */

/**
 * No direct access to this file.
 *
 * @since 1.0.0
 */
defined( 'ABSPATH' ) || die();

require PARENT_DIR . '/includes/loader.php';
//require PARENT_DIR . '/data-types/loader.php';
//require 'theme-options-functions.php';
//require 'template-functions.php';

/**
 * Returns dependency injection container/element from container by key.
 *
 * @param  string $key dependency key.
 * @return mixed
 */
function &jw_di( $key = null ) {
	static $di;
	if ( ! $di ) {
		$di = new JuiceContainer();
	}
	if ( $key ) {
		$result = $di[ $key ];
		return $result;
	}
	return $di;
}

/**
 * Initialize dependency injector callback.
 *
 * @param array $di dependency injector.
 * @param mixed $config di config.
 */
function jw_init_di_callback( $di, $config ) {
	if ( $config ) {
		foreach ( $config as $key => $value ) {
			$instance = null;
			$class = '';
			$typeof = gettype( $value );
			switch ( $typeof ) {
				case 'string':
					$class = $value;
					break;

				case 'array':
					$class = array_shift( $value );
					break;

				default:
					$instance = $value;
					$class = get_class( $instance );
					break;
			}
			$di_key = is_string( $key ) ? $key : $class;
			if ( isset( $di[ $di_key ] ) ) {
				continue;
			}

			$di[ $di_key ] = $instance ? $instance : JuiceDefinition::create( $class, $value );
		}
	}
}
add_action( 'jw_init_di', 'jw_init_di_callback', 10, 2 );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function jw_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'jw_content_width', 960 );
}
add_action( 'after_setup_theme', 'jw_content_width', 0 );

if ( ! function_exists( 'jw_action_init' ) ) {
	/**
	 * Callback for 'init' action.
	 *
	 * @return void
	 */
	function jw_action_init() {
		if ( jw_check( 'jw_category_taxonomy_exists' ) ) {
			// TODO: Add custom taxonomy init here.
		}

		if ( is_admin() ) {
			jw_init_tiny_mce_integration();
		}
	}
	add_action( 'init', 'jw_action_init' );
}

if ( ! function_exists( 'jw_init_tiny_mce_integration' ) ) {

	/**
	 * Initialize TinyMCE shortcodes integration.
	 */
	function jw_init_tiny_mce_integration() {

		// To init shortcodes menu for tinyMCE.
		$integrator = jw_di( 'shortcodes_tiny_mce_integrator' );
		if ( $integrator && $integrator->register_service ) {
			$shortcodes_register = $integrator->register_service;
			$load_shortcodes = apply_filters( 'jw_shortcodes_register_preload_list', array() );
			if ( $load_shortcodes ) {
				$shortcodes_register->add( '_edit_', esc_html__( 'Edit', 'jabberwock' ), array() );

				foreach ( $load_shortcodes as $shortcode => $details ) {
					$shortcodes_register->add( $shortcode, $details['name'], $details['params'] );
				}
			}
		}
	}
}

// -----------------------------------------------------------------#
// Assets registration
// -----------------------------------------------------------------#
if ( ! function_exists( 'jw_init_theme_assets' ) ) {
	/**
	 * Defines theme assets.
	 *
	 * @return void
	 */
	function jw_init_theme_assets() {
		$min_ext = SCRIPT_DEBUG ? '' : '.min';

		$is_rtl = is_rtl();
		if ( THEME_IS_DEV_MODE ) {
			if ( $is_rtl ) {
				wp_enqueue_style( 'bootstrap-custom-rtl', PARENT_URL . '/assets/csslib/bootstrap-custom-rtl.css' );
			} else {
				wp_enqueue_style( 'bootstrap-custom', PARENT_URL . '/assets/csslib/bootstrap-custom.css' );
			}

			wp_enqueue_style( 'fontawesome', PARENT_URL . '/assets/csslib/font-awesome.min.css' );
			wp_enqueue_style( 'bootstrap-select', PARENT_URL . '/assets/csslib/bootstrap-select/bootstrap-select.min.css', array(), '1.12.2' );
			wp_enqueue_style( 'bxslider', PARENT_URL . '/assets/csslib/bxslider/jquery.bxslider.min.css', array(), '4.2.12' );
			wp_register_style( 'magnific-popup', PARENT_URL . '/assets/csslib/magnific-popup.css', array(), '1.1.0' );

			wp_register_style( 'swipebox', PARENT_URL . '/assets/csslib/swipebox.css' );
			wp_register_style( 'swiper', PARENT_URL . '/assets/csslib/swiper.min.css' );

			wp_enqueue_script( 'bootstrap', PARENT_URL . '/assets/jslib/bootstrap.min.js',array( 'jquery' ), '',true );
			wp_enqueue_script( 'bootstrap-select', PARENT_URL . '/assets/jslib/bootstrap-select/bootstrap-select.min.js', array( 'jquery', 'bootstrap' ), '1.12.2', true );
			wp_enqueue_script( 'bxslider', PARENT_URL . '/assets/jslib/bxslider/jquery.bxslider.min.js', array( 'jquery' ), '4.2.12', true );
			wp_enqueue_script( 'slicknav', PARENT_URL . '/assets/jslib/jquery.slicknav.js',array( 'jquery' ), '',true );
			wp_enqueue_script( 'tabcollapse', PARENT_URL . '/assets/jslib/bootstrap-tabcollapse.js', array( 'jquery' ), '', true );
			wp_register_script( 'fitvid', PARENT_URL . '/assets/jslib/bxslider/vendor/jquery.fitvids.js', array( 'jquery' ), '1.0', true );
			wp_register_script( 'magnific-popup', PARENT_URL . '/assets/jslib/jquery.magnific-popup.min.js', array( 'jquery' ), '1.1.0', true );
			wp_register_script( 'theme', PARENT_URL . '/assets/js/Theme.js', array( 'jquery' ), '', true );
			wp_localize_script( 'theme', 'AjaxHelper', array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'headerSectionNonce' => wp_create_nonce( 'swish-header-section-nonce' ),
			) );
			wp_enqueue_script( 'theme' );

			if ( jw_get_option( 'show_header_search' ) ) {
				wp_enqueue_style( 'magnific-popup' );
				wp_enqueue_script( 'magnific-popup' );
			}

			wp_register_script( 'swipebox', PARENT_URL . '/assets/jslib/jquery.swipebox.js', array( 'jquery' ), '1.3.0.2', true );
			wp_register_script( 'swiper', PARENT_URL . '/assets/jslib/swiper/swiper.jquery.min.js', array(), '3.4.2', true );

			wp_register_script( 'parallax', PARENT_URL . '/assets/jslib/jquery.parallax-1.1.3.js', array( 'jquery' ), '1.1.3', true );

			wp_register_script( 'sharrre', PARENT_URL . '/assets/jslib/jquery.sharrre.js', array( 'jquery' ), '',true );

		} else {
			wp_enqueue_style( 'theme-addons', PARENT_URL . '/assets/csslib/theme-addons' . ( $is_rtl ? '-rtl' : '' ) . $min_ext . '.css', array(), '2.2.7' );
			wp_enqueue_script( 'theme', PARENT_URL . '/assets/js/theme-full' . $min_ext . '.js', array( 'jquery' ), jw_VERSION, true );
		} // End if().

		$style_collection = apply_filters('get_theme_styles', array(
			'style-css' => get_stylesheet_uri(),
		));

		if ( $style_collection ) {
			foreach ( $style_collection as $_item_key => $resource_info ) {
				$_style_text = null;
				$_style_url = null;
				if ( ! is_array( $resource_info ) ) {
					$_style_url = $resource_info;
				} else {
					if ( isset( $resource_info['text'] ) ) {
						$_style_text = $resource_info['text'];
					} elseif ( isset( $resource_info['url'] ) ) {
						$_style_url = $resource_info['url'];
					}
				}
				if ( $_style_url ) {
					wp_enqueue_style( $_item_key, $_style_url );
				} elseif ( $_style_text ) {
					jw_di( 'register' )->push_var( 'header_inline_css_text', array(
						'id' => $_item_key,
						'text' => $_style_text,
					) );
				}
			}
		}

//		wp_register_script( 'jPages', PARENT_URL . '/assets/jslib/jPages.js', array( 'jquery' ), '', true );

		// wp_register_style( 'jquery-ui-datepicker-custom', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css', array(), '1.8.2' );
//		wp_register_style( 'jquery-ui-datepicker-custom', PARENT_URL . '/assets/csslib/jquery-ui-custom/jquery-ui.min.css', array(), '1.11.4' );
	}

	add_action( 'wp_enqueue_scripts', 'jw_init_theme_assets' );
}

// -----------------------------------------------------------------#
// Widgets registration
// -----------------------------------------------------------------#
if ( ! function_exists( 'jw_register_widgets' ) ) {
	/**
	 * Hook for widgets registration.
	 *
	 * @return void
	 */
	function jw_register_widgets() {
		// Make a Wordpress built-in Text widget process shortcodes.
		add_filter( 'widget_text', 'shortcode_unautop' );
		add_filter( 'widget_text', 'do_shortcode', 11 );

		register_widget( 'JW_Widget_Latest_Posts' );
		register_widget( 'JW_Widget_Contact_Us' );
		register_widget( 'JW_Widget_Advanced_Text' );

		// if ( class_exists( 'woocommerce' ) ) {
		// TODO: Widget for woocommerce.
		// }

		register_sidebar(array(
			'id'            => 'sidebar',
			'name'          => esc_html__( 'Sidebar', 'jabberwock' ),
			'description'   => esc_html__( 'Sidebar located on the right side of blog page.', 'jabberwock' ),
			'before_widget' => '<div id="%1$s" class="widget block-after-indent %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3 class="widget__title">',
			'after_title'   => '</h3>',
		));

		register_sidebar(array(
			'id'            => 'footer1',
			'name'          => sprintf( esc_html__( 'Footer %s', 'jabberwock' ), 1 ),
			'description'   => esc_html__( 'Located in 1st column on 4-columns footer layout.', 'jabberwock' ),
			'before_widget' => '<div id="%1$s" class="widget block-after-indent %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3 class="widget__title">',
			'after_title'   => '</h3>',
		));

		$footer_columns_count = jw_get_footer_columns();
		if ( $footer_columns_count >= 2 ) {
			register_sidebar(array(
				'id'            => 'footer2',
				'name'          => sprintf( esc_html__( 'Footer %s', 'jabberwock' ), 2 ),
				'description'   => esc_html__( 'Located in 2nd column on 4-columns footer layout.', 'jabberwock' ),
				'before_widget' => '<div id="%1$s" class="widget block-after-indent %2$s">',
				'after_widget'  => '</div>',
				'before_title'  => '<h3 class="widget__title">',
				'after_title'   => '</h3>',
			));
		}

		if ( $footer_columns_count >= 3 ) {
			register_sidebar(array(
				'id'            => 'footer3',
				'name'          =>sprintf( esc_html__( 'Footer %s', 'jabberwock' ), 3 ),
				'description'   => esc_html__( 'Located in 3rd column on 4-columns footer layout.', 'jabberwock' ),
				'before_widget' => '<div id="%1$s" class="widget block-after-indent %2$s">',
				'after_widget'  => '</div>',
				'before_title'  => '<h3 class="widget__title">',
				'after_title'   => '</h3>',
			));
		}

		if ( $footer_columns_count >= 4 ) {
			register_sidebar(array(
				'id'            => 'footer4',
				'name'          => sprintf( esc_html__( 'Footer %s', 'jabberwock' ), 4 ),
				'description'   => esc_html__( 'Located in 4th column on 4-columns footer layout.', 'jabberwock' ),
				'before_widget' => '<div id="%1$s" class="widget block-after-indent %2$s">',
				'after_widget'  => '</div>',
				'before_title'  => '<h3 class="widget__title">',
				'after_title'   => '</h3>',
			));
		}
	}
	add_action( 'widgets_init', 'jw_register_widgets' );
}

add_theme_support( 'title-tag' );
add_theme_support( 'automatic-feed-links' );
add_theme_support( 'post-thumbnails' );
add_theme_support( 'menus' );
add_theme_support( 'html5', array( 'gallery', 'caption', 'search-form' ) );
register_nav_menus(array(
	'header-menu' => esc_html__( 'Header Menu', 'jabberwock' ),
	//'footer-menu' => esc_html__( 'Footer Menu', 'jabberwock' ),
));

// -----------------------------------------------------------------#
// Rendering: filters & helpers
// -----------------------------------------------------------------#
if ( ! function_exists( 'jw_render_header_share_meta' ) ) {
	/**
	 * Renders social network related meta tags.
	 *
	 * @return void
	 */
	function jw_render_header_share_meta() {
		if ( ! is_singular( 'post' ) ) {
			return;
		}

		$is_sharing_active = apply_filters( 'jw_render_header_social_meta', jw_get_option( 'social_sharing_blog' ) );
		if ( ! $is_sharing_active ) {
			return;
		}

		$title = esc_attr( get_the_title() );
		$description = esc_attr( jw_get_short_description( null, 300 ) );

		$thumb_id = get_post_thumbnail_id();
		$image = $thumb_id ? esc_url( wp_get_attachment_url( $thumb_id ) ) : '';

		$tags = array(
			'og' => array(
				'title' => $title,
				'description' => $description,
				'image' => $image,
			)
		);
		if ( jw_get_option( 'social_sharing_twitter' ) ) {
			$tags['twitter'] = array(
				'title' => $title,
				'description' => $description,
				'image' => $image,
				'card' => $image ? 'summary_large_image' : 'summary',
			);
		}

		$tags = apply_filters( 'jw_header_social_meta_tags', $tags );
		if ( $tags ) {
			if ( !empty( $tags['og'] ) ) {
				foreach ( $tags['og'] as $mata_name => $meta_value ) {
					if ( $meta_value ) {
						printf( PHP_EOL . '<meta property="og:%s" content="%s">', $mata_name, $meta_value );
					}
				}
			}

			if ( ! empty( $tags['twitter'] ) ) {
				foreach ( $tags['twitter'] as $mata_name => $meta_value ) {
					if ( $meta_value ) {
						printf( PHP_EOL . '<meta name="twitter:%s" content="%s">', $mata_name, $meta_value );
					}
				}
			}
		}
	}
	// Disabled for performance reason. Can be added to child theme functions.php
	// add_action( 'wp_head', 'jw_render_header_share_meta' );
} // End if().

if ( ! function_exists( 'jw_render_header_resources' ) ) {
	/**
	 * Renders theme header resources.
	 *
	 * @return void
	 */
	function jw_render_header_resources() {
		$inline_pieces = jw_di( 'register' )->get_var( 'header_inline_css_text' );
		if ( $inline_pieces ) {
			foreach ( $inline_pieces as $inline_piece_info ) {
				if ( empty( $inline_piece_info['text'] ) ) {
					continue;
				}
				printf( "<style type=\"text/css\">%s</style>\n", $inline_piece_info['text'] );
			}
			jw_di( 'register' )->set_var( 'header_inline_css_text', array() );
		}

		$custom_css = jw_get_option( 'custom_css_text' );
		if ( $custom_css ) {
			printf( "<style type=\"text/css\">\n%s\n</style>\n", $custom_css );
		}
	}
	add_action( 'wp_head', 'jw_render_header_resources' );
} // End if().

if ( ! function_exists( 'jw_filter_theme_styles' ) ) {
	/**
	 * Filter for theme style files list.
	 *
	 * @param  array $default_set list of default files that should be used.
	 * @return array
	 */
	function jw_filter_theme_styles(array $default_set) {
		$is_customize_request = isset( $_POST['wp_customize'] ) && 'on' == $_POST['wp_customize'];

		$is_rtl = is_rtl();

		$cache_id = $is_customize_request || THEME_IS_DEV_MODE ? '' : ( 'jw_generated_styles_list' . ( $is_rtl ? '_rtl' : '' ) );

		$cached_value = $cache_id ? get_transient( $cache_id ) : false;

		if ( false === $cached_value || empty( $cached_value['version'] ) || JW_VERSION !== $cached_value['version'] ) {
			$app = jw_di( 'app' );
			$style_options = $app->get_style_options( $is_customize_request );
			// Special variable used to point url locations.
			if ( ! isset( $style_options['assets_url'] ) ) {
				$style_options['assets_url'] = '"' . PARENT_URL . '/assets/"';
			}

			if ( $is_rtl ) {
				$style_options['bi-app-left'] = 'right';
				$style_options['bi-app-right'] = 'left';
				$style_options['bi-app-direction'] = 'rtl';
				$style_options['bi-app-invert-direction'] = 'ltr';
			} else {
				$style_options['bi-app-left'] = 'left';
				$style_options['bi-app-right'] = 'right';
				$style_options['bi-app-direction'] = 'ltr';
				$style_options['bi-app-invert-direction'] = 'rtl';
			}

			$compiled = $app->generate_custom_css(
				jw_di( 'register' )->get_var( 'main_scss_file' ),
				$style_options,
				$is_customize_request ? 'preview-main' :  ( 'main-custom' . ( $is_rtl ? '-rtl' : '' ) )
			);

			$cached_value = array(
				'version' => JW_VERSION,
				'value' => array_merge( $default_set, $compiled ),
			);
			if ( $cache_id ) {
				set_transient( $cache_id, $cached_value );
			}
		}

		return isset( $cached_value['value'] ) ? $cached_value['value'] : $default_set;
	}
	add_filter( 'get_theme_styles', 'jw_filter_theme_styles', 1, 1 );
} // End if().

if ( ! function_exists( 'jw_flush_style_cache' ) ) {
	/**
	 * Resets generated styles cache.
	 *
	 * @return void
	 */
	function jw_flush_style_cache() {
		delete_transient( 'jw_generated_styles_list' );
		delete_transient( 'jw_generated_styles_list_rtl' );
	}
	add_action( 'customize_save_after', 'jw_flush_style_cache' );
	add_action( 'after_switch_theme', 'jw_flush_style_cache' );
} // End if().

if ( ! function_exists( 'jw_get_fonts_icons' ) ) {
	/**
	 * Filter function for 'jw_get_jw_icon_shortcode_icons' action.
	 * Filter sets of icons. Name of the set should be defined via key.
	 * Each set is assoc where key is icon class, value is icon label.
	 *
	 * @example
	 * <pre>
	 * array(
	 *     'Collection 1' => array(
	 *         'icon icon-1' => 'Icon #1',
	 *         'icon icon-1' => 'Icon #2',
	 *     ),
	 *     'Set #2' => array(
	 *         'iset icon-1' => 'ISet icon #1',
	 *         'iset icon-2' => 'ISet icon #2',
	 *     ),
	 * )
	 * </pre>
	 *
	 * @param  mixed $icons the icons.
	 * @return assoc
	 */
	function jw_filter_theme_fonts_icon_sets( $icons ) {
		$di = jw_di();
		if ( isset( $di['icons_manager'] ) ) {
			$jw_icons_list = jw_di( 'icons_manager' )->get_list();
			if ( $jw_icons_list ) {
				$set = array();
				foreach ( $jw_icons_list as $icon ) {
					$set[ $icon['value'] ] = $icon['label'];
				}
				$icons['Swish Design'] = $set;
			}
		}

		$icons_manager = new BT_Font_Icons_Manager( array(
			'font_file_url' => PARENT_URL . '/assets/csslib/font-awesome.min.css',
			'pattern' => '/\.(fa-(?:\w+(?:-)?)+):before\s*{\s*content/',
			'cache_key' => 'qed-font-awesome-icons-list',
		) );
		$font_awesome_icons_list = $icons_manager->get_list();
		if ( $font_awesome_icons_list ) {
			$set = array();
			foreach ( $font_awesome_icons_list as $icon ) {
				$icon_class = 'fa ' . $icon['value'];
				$set[ $icon_class ] = $icon['label'];
			}
			$icons['Font Awesome'] = $set;
		}

		return $icons;
	}
	add_filter( 'jw_get_jw_icon_shortcode_icons', 'jw_filter_theme_fonts_icon_sets' );
} // End if().

if ( ! function_exists( 'jw_check' ) ) {
	/**
	 * Theme function for checks.
	 *
	 * @param string     $check_name value to check.
	 * @param bool|false $ignore_cache flag to determine if will ignore cache.
	 *
	 * @return mixed
	 */
	function jw_check( $check_name, $ignore_cache = false ) {
		static $cache = array();

		if ( ! isset( $cache[ $check_name ] ) || $ignore_cache ) {
			$result = false;
			switch ( $check_name ) {
				case 'media_category_taxonomy_exists':
					$result = taxonomy_exists( 'media_category' );
					break;

				case 'is_wpml_in_use':
					$result = defined( 'ICL_SITEPRESS_VERSION' ); // function_exists( 'icl_object_id' );.
					break;

				case 'is_wordpress_seo_in_use':
					$result = defined( 'WPSEO_VERSION' );
					break;
			}

			$cache[ $check_name ] = $result;
		}

		return $cache[ $check_name ];
	}
}

function jw_remove_menus() {

	remove_menu_page('edit.php?post_type=jw_header_section');

}
add_action( 'admin_menu', 'jw_remove_menus', 999 );

function explain_less_login_issues(){ return '<strong>ERROR</strong>: Entered credentials are incorrect.';}
add_filter( 'login_errors', 'explain_less_login_issues' );

// disable emoji fontawesome //
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );

