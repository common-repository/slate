<?php
/*
 * Plugin Name: SVG Logo and Text Effects
 * Description: Insert text with visually stunning SVG effects into your WordPress site.
 * Version: 1.3.1
 * Plugin URI: http://www.dashed-slug.net/svg-logo-and-text-effects-wordpress-plugin
 * Author: dashed-slug <info@dashed-slug.net>
 * Author URI: http://dashed-slug.net
 * Text Domain: slate
 * Domain Path: /languages/
 * License: GPLv2 or later
 *
 * @package slate
 * @since 1.0.0
 */

/*
 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

 Copyright dashed-slug <info@dashed-slug.net>
 */

// don't load directly
defined( 'ABSPATH' ) || die( '-1' );

// load the core plugin
require_once dirname( __FILE__ ) . '/core.php';

if ( ! class_exists( 'SVG_Logo_and_Text_Effects' ) ) {

	final class SVG_Logo_and_Text_Effects {

		const OPT_PLUGIN_VERSION = 'slate-opt-plugin-version';
		const OPT_CACHE = 'slate-opt-cache';
		const POPUP_35824 = 'slate-popup-35824';
		const POPUP_SLATE_VC = 'slate-popup-vc';
		const POPUP_SLATE_RASTER = 'slate-popup-raster';
		const POPUP_SLATE_TITLES = 'slate-popup-titles';
		const POPUP_SLATE_SHAPES = 'slate-popup-shapes';

		private static $_instance = null;

		private static $_errors = array();

		private static $_plugin_data = array();

		private $_used_fonts = array();

		private $_hash_collisions = array();

		private $_shapes = array();

		private $_fonts = array();

		private $_filters = array();

		private $_fills = array();

		private $_presets = array();

		private static $_text_tspanize_map = array(
			'a' => 'text-decoration: underline',
			'b' => 'font-weight: bold',
			'strong' => 'font-weight: bold',
			'em' => 'font-style: italic',
			'i' => 'font-style: italic',
			'u' => 'text-decoration: underline',
			'del' => 'text-decoration: line-through',
			'strike' => 'text-decoration: line-through',
			'sup' => 'vertical-align: super; text-size: smaller;',
			'sub' => 'vertical-align: sub; text-size: smaller;',
			'p' => '',
			'span' => '',
			'blockquote' => '' );

		private function __clone() {
			// Cloning disabled
		}

		/**
		 * Creates or returns an instance of this class.
		 *
		 * @return SVG_Logo_and_Text_Effects
		 */
		public static function get_instance() {
			if ( ! ( self::$_instance instanceof self ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		public static function error_handler( $errno, $errstr, $errfile, $errline, $errcontext ) {
			if ( ! ( error_reporting() & $errno ) ) {
				return;
			}

			// append error to show on admin_notices
			self::$_errors[] = array(
				'errno' => $errno,
				'errstr' => $errstr,
				'errfile' => $errfile,
				'errline' => $errline,
				'errcontext' => $errcontext,
				'current_filter' => current_filter() );

			error_log( __CLASS__ . ": errno: $errno, errstr: $errstr, errfile: $errfile, errline: $errline" );
		}

		// accessors to the resources for plugins

		public function get_shapes() {
			return @$this->_shapes;
		}

		public function get_fonts() {
			return @$this->_fonts;
		}

		public function get_filters() {
			return @$this->_filters;
		}

		public function get_fills() {
			return @$this->_fills;
		}

		public function get_presets() {
			return @$this->_presets;
		}

		public static function get_permitted_html_tags() {
			return array_keys( self::$_text_tspanize_map );
		}

		private function __construct() {
			set_error_handler( array( __CLASS__, 'error_handler' ) );

			// Plugin lifecycle hooks
			if ( function_exists( 'register_activation_hook' ) ) {
				register_activation_hook( __FILE__, array( __CLASS__, 'action_activation' ) );
			}

			// General functionality hooks
			add_action( 'init', array( &$this, 'action_init' ) );
			add_action( 'plugins_loaded', array( &$this, 'action_plugins_loaded' ) );
			add_action( 'wp_enqueue_scripts', array( &$this, 'action_wp_enqueue_scripts' ) );
			add_action( 'wp_footer', array( &$this, 'action_wp_footer' ) );
			add_filter( 'body_class', array( &$this, 'filter_body_class' ) );

			// Admin area
			add_action( 'admin_init', array( &$this, 'action_admin_init' ) );
			add_action( 'admin_enqueue_scripts', array( &$this, 'action_admin_enqueue_scripts' ) );
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( &$this, 'action_plugin_action_links' ) );

			add_action( 'admin_menu', array( &$this, 'action_admin_menu' ) );
			add_action( 'admin_notices', array( &$this, 'action_admin_notices' ) );
			add_action( 'wp_dashboard_setup', array( &$this, 'action_wp_dashboard_setup' ) );

			// Presets API
			add_filter( 'query_vars', array( &$this, 'filter_query_vars' ), 0 );
			add_action( 'parse_request', array( &$this, 'action_parse_request' ), 0 );

			restore_error_handler();
		} // end function construct

		public function filter_body_class( $classes ) {
			// Only add classes on frontend.
			if ( is_admin() ) {
				return $classes;
			}

			if ( version_compare( get_bloginfo('version'), '4.7' ) < 0) {
				// We are in a WP version where the following bug is not addressed yet:
				// https://core.trac.wordpress.org/ticket/35824
				if ( isset( $_REQUEST['wp_customize'] ) ) {
					// Add class to indicate that we're in the customizer.
					$classes[] = 'slate-customizer';
				}
			}

			return $classes;
		}

		public static function action_activation() {
			update_option( self::OPT_PLUGIN_VERSION, '1.3.1' );
			add_option( self::OPT_CACHE, 'on' );
		}

		public function action_init() {
			set_error_handler( array( __CLASS__, 'error_handler' ) );

			header( 'X-Frame-Options: SAMEORIGIN' );
			restore_error_handler();
		}

		public function action_admin_notices() {
			set_error_handler( array( __CLASS__, 'error_handler' ) );

			if ( isset( $_GET[ self::POPUP_35824 ] ) ) {
				update_option( self::POPUP_35824, true );
			}

			if ( version_compare( get_bloginfo('version'), '4.7' ) < 0) {
				// We are in a WP version where the following bug is not addressed yet:
				// https://core.trac.wordpress.org/ticket/35824

				if ( ! get_option( self::POPUP_35824 ) ) {
					$dismiss_url = add_query_arg( array(
						self::POPUP_35824 => 1
					), admin_url() );
					include 'popups/35824.php';
				}
			}

			$current_screen = get_current_screen();
			if ( 'settings_page_slate_settings_page' === $current_screen->id ||
				  'settings_page_ds_activation_page' === $current_screen->id ) {

				if ( isset( $_GET[ self::POPUP_SLATE_VC ] ) ) {
					update_option( self::POPUP_SLATE_VC, true );
				}

				if ( ! ( is_plugin_active( 'slate-vc/slate-vc.php' ) || get_option( self::POPUP_SLATE_VC ) ) ) {
					$dismiss_url = add_query_arg( array(
						self::POPUP_SLATE_VC => 1
					), admin_url() );
					include 'popups/slate-vc.php';
				}

				if ( isset( $_GET[ self::POPUP_SLATE_RASTER ] ) ) {
					update_option( self::POPUP_SLATE_RASTER, true );
				}

				if ( ! ( is_plugin_active( 'slate-raster/slate-raster.php' ) || get_option( self::POPUP_SLATE_RASTER ) ) ) {
					$dismiss_url = add_query_arg( array(
						self::POPUP_SLATE_RASTER => 1
					), admin_url() );
					include 'popups/slate-raster.php';
				}

				if ( isset( $_GET[ self::POPUP_SLATE_TITLES ] ) ) {
					update_option( self::POPUP_SLATE_TITLES, true );
				}

				if ( ! ( is_plugin_active( 'slate-titles/slate-titles.php' ) || get_option( self::POPUP_SLATE_TITLES ) ) ) {
					$dismiss_url = add_query_arg( array(
						self::POPUP_SLATE_TITLES => 1
					), admin_url() );
					include 'popups/slate-titles.php';
				}

				if ( isset( $_GET[ self::POPUP_SLATE_SHAPES ] ) ) {
					update_option( self::POPUP_SLATE_SHAPES, true );
				}

				if ( ! ( is_plugin_active( 'slate-shapes/slate-shapes.php' ) || get_option( self::POPUP_SLATE_SHAPES ) ) ) {
					$dismiss_url = add_query_arg( array(
						self::POPUP_SLATE_SHAPES => 1
					), admin_url() );
					include 'popups/slate-shapes.php';
				}
			} // end extension notices

			foreach ( self::$_errors as $error ) {
				$level = 'notice';
				if ( E_USER_ERROR == $error['errno'] ) {
					$level = 'error';
				} elseif ( E_USER_WARNING == $error['errno'] ) {
					$level = 'warning';
				}

				echo "<div class=\"notice notice-$level is-dismissible\">";
				echo "<p style=\"font-size: larger;\">{$error['errstr']}</p><pre>" . self::$_plugin_data['Name'] .
					 " on action <code>{$error['current_filter']}</code>, <code>{$error['errfile']}</code>, line <code>{$error['errline']}</code>.</pre></div>";
			}
			restore_error_handler();
		} // end function action_admin_notices

		public function action_admin_enqueue_scripts() {
			set_error_handler( array( __CLASS__, 'error_handler' ) );

			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'jquery-ui-core', false, array( 'jquery' ) );
			wp_enqueue_script( 'jquery-ui-tabs', false, array( 'jquery' ) );

			if ( file_exists( __DIR__ . '/assets/scripts/slate-admin.min.js' ) ) {
				$slate_admin_script = 'assets/scripts/slate-admin.min.js';
			} else {
				$slate_admin_script = 'assets/scripts/slate-admin.js';
			}

			wp_enqueue_script( 'slate-admin', plugins_url( $slate_admin_script, __FILE__ ), array( 'jquery-ui-tabs' ) );

			wp_enqueue_style( 'slate-jquery-ui',
                '//ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css' );

			if ( file_exists( __DIR__ . '/assets/styles/slate-admin.min.css' ) ) {
				$slate_admin_style = 'assets/styles/slate-admin.min.css';
			} else {
				$slate_admin_style = 'assets/styles/slate-admin.css';
			}

			wp_enqueue_style( 'slate-admin', plugins_url( $slate_admin_style, __FILE__ ) );

			restore_error_handler();
		}

		public function action_wp_dashboard_setup() {
			set_error_handler( array( __CLASS__, 'error_handler' ) );

			wp_add_dashboard_widget(
				'slate-dashboard-widget',
				self::$_plugin_data['Name'],
				array( &$this, 'slate_dashboard_widget_function' ) );

			restore_error_handler();
		}

		public function slate_dashboard_widget_function() {
			set_error_handler( array( __CLASS__, 'error_handler' ) );

			global $wp_version;

			$html = '<img src="' . plugins_url( 'assets/sprites/logo-114x154.png', __FILE__ ) . '" alt="SLATE logo"/>';

			$html .= '<div id="slate-widget-tabs"><ul>';
			$html .= '<li><a href="#slate-widget-tabs-1">' . __( 'Get started', 'slate' ) . '</a></li>';
			$html .= '<li><a href="#slate-widget-tabs-2">' . __( 'Versions', 'slate' ) . '</a></li>';
			$html .= '<li><a href="#slate-widget-tabs-3">' . __( 'Resources', 'slate' ) . '</a></li>';
			$html .= '</ul><hr />';

			// Quick start guide tab
			$html .= '<div id="slate-widget-tabs-1">';

			$html .= '<iframe width="320" height="180" src="https://www.youtube.com/embed/rMM3Nft5-mQ" frameborder="0" allowfullscreen></iframe>';

			$html .= '<p>' . __( 'Thanks for taking <a target="_blank" href="https://www.dashed-slug.net/svg-logo-and-text-effects-wordpress-plugin/">SLATE</a> for a spin!', 'slate' ) . '</p><ol>';

			$html .= '<li><strong>' . __( 'To get started, copy and paste the following shortcode into a post or page.', 'slate' );
			$html .= '</strong> ' . __( 'Make sure to switch your editor from "Visual" to "Text", otherwise the HTML tags in the text will not be preserved.', 'slate' );

			$html .= <<<HTML
<textarea onclick="this.focus();this.select();" readonly="readonly" style="width: 100%; min-height: 16em; font-family: 'Courier New', Courier, monospace;">
[slate_plain _font_family="Ubuntu" _font_size="40pt" _stroke="#dd9933" _stroke_width="2px" fill="gradient_linear" fill_gradient_linear_color1="#8224e3" fill_gradient_linear_color2="#ff2323" filter_glow_radius="5" filter_glow_color="#eeee22" filter_shadow_radius="10" filter_shadow_xoff="10" filter_shadow_yoff="20"]Hello <b>SVG</b> World![/slate_plain]
</textarea>
HTML;

			$html .= '</li><li>' . __( 'Consult the <a target="_blank" href="https://www.dashed-slug.net/svg-logo-and-text-effects-wordpress-plugin/svg-shortcode-examples/">dashed-slug.net</a> site for more examples.', 'slate' ) . '</li>';
			$html .= '<li>' . __( 'Make sure you have the <a target="_blank" href="https://www.dashed-slug.net/downloads/">bundle download</a> of SLATE that includes documentation, and:', 'slate' ) . '</li>';
			$html .= '<ol><li>' . __( 'Consult the accompanying quick-start guide, <code>slate-quickstart.pdf</code> for more details.', 'slate' ) . '</li>';
			$html .= '<li>' . __( 'Consult the accompanying documentation, <code>slate-manual.pdf</code> for an extensive reference.', 'slate' ) . '</li>';

			$html .= '</ol></ol></div>';

			// versions tab
			$html .= '<div id="slate-widget-tabs-2"><ul>';
			$html .= '<li><b>' . __( 'PHP version', 'slate' ) . ":</b> " . phpversion() . '</li>';
			$html .= '<li><b>' . __( 'WordPress version', 'slate'  ) . ":</b> $wp_version</li>";

			$tidyRelease = 'n/a';
			if ( class_exists( 'tidy' ) ) {
				$tidyRelease = tidy_get_release();
			}

			$html .= '<li><b>' . __( 'Release of Tidy PHP extension', 'slate' ) . ":</b> $tidyRelease</li>";

			$html .= '<li><b>' . __( 'WPBakery Page Builder version', 'slate'  ) . ':</b> ' .
				 ( defined( 'WPB_VC_VERSION' ) ? WPB_VC_VERSION : __( 'n/a', 'slate' ) ) . '</li>';

			$imagickVersion = 'n/a';
			if ( class_exists( 'Imagick' ) ) {
				try {
					$i = new Imagick();
					$imagickVersionArray = $i->getVersion();
					$imagickVersion = $imagickVersionArray['versionString'];
				} catch ( Exception $e ) {
					trigger_error( "Could not query Imagick for version: " . $e->getMessage(), E_USER_NOTICE );
				}
			}
			$html .= '<li><b>' . __( 'ImageMagick version', 'slate'  ) . ":</b> $imagickVersion</li>";
			$html .= '<li><b>' . __( 'Plugin version', 'slate'  ) . ':</b> ' . self::$_plugin_data['Version'] . '</li>';
			$html .= '</ul></div>'; // end div slate-widget-tabs-1

			// resources tab
			$html .= '<div id="slate-widget-tabs-3"><ul>';
			$html .= '<li title="' . implode( "\n", array_keys( $this->_shapes ) ) . '"><b>' .
				 __( 'Shape templates loaded', 'slate' ) . ':</b> ' . count( $this->_shapes ) . '</li>';
			$html .= '<li title="' . implode( "\n", array_keys( $this->_fonts ) ) . '"><b>' .
				__( 'Fonts loaded', 'slate' ) . ":</b> " . count( $this->_fonts ) . '</li>';
			$html .= '<li title="' . implode( "\n", array_keys( $this->_fills ) ) . '"><b>' .
				 __( 'Fill patterns loaded' ) . ":</b> " . count( $this->_fills ) . '</li>';
			$html .= '<li title="' . implode( "\n", array_keys( $this->_filters ) ) . '"><b>' .
				__(	'Effect filters loaded' ) . ":</b> " . count( $this->_filters ) . '</li>';
			$html .= '<li title="' . implode( "\n", array_keys( $this->_presets ) ) . '"><b>' .
				__( 'Presets loaded', 'slate' ) . ":</b> " . count( $this->_presets ) . '</li></ul>';
			$html .= '</ul></div>'; // end div slate-widget-tabs-2

			$html .= '</div>'; // end div slate-widget-tabs

			echo $html;

			restore_error_handler();
		} // end function slate_dashboard_widget_function

		/**
		 * Plugins can specify 'shape', 'filter', and 'fill' PHP templates.
		 * This function takes lists of files and directories of templates and
		 * stores the templates
		 *
		 */
		private function _register_templates( $resource_type, $templates ) {
			$result = &$this->{"_{$resource_type}s"};

			foreach ( $templates as $file_name ) {

				if ( file_exists( $file_name ) && is_readable( $file_name ) ) {
					if ( is_file( $file_name ) ) {
						$resource_name = basename( $file_name, '.php' );
						if ( 'index' != $resource_name ) {
							$result[ $resource_name ] = self::_parse_phpdoc( $file_name );
							$result[ $resource_name ]['name'] = $resource_name;
						}
					} elseif ( is_dir( $file_name ) ) {
						$dir_name = $file_name;
						foreach ( glob( trailingslashit( $dir_name ) . '*.php' ) as $file_name ) {
							$resource_name = basename( $file_name, '.php' );
							if ( 'index' != $resource_name ) {
								$result[ $resource_name ] = self::_parse_phpdoc( $file_name );
								$result[ $resource_name ]['name'] = $resource_name;
							}
						}
					} else {
						trigger_error(
							"Template $file_name is not file or directory, cannot add to $resource_type",
							E_USER_WARNING );
					}
				} else {

					trigger_error(
						"Template $file_name not found or not readable, cannot add to $resource_type",
						E_USER_WARNING );
				}
			}
		} // end function _register_templates

		public function filter_register_filters( $filters ) {
			return $this->_register_templates( 'filter', $filters );
		}

		public function filter_register_fills( $fills ) {
			return $this->_register_templates( 'fill', $fills );
		}

		/**
		 * Fires after all the plugins have been loaded.
		 *
		 * - Adds resources from plugins by checking the hooked filters
		 *  - slate_font_files_or_urls
		 *  - slate_shape_files
		 *  - slate_filter_files
		 *  - slate_fill_files
		 *  - slate_presets
		 */
		public function action_plugins_loaded() {
			set_error_handler( array( __CLASS__, 'error_handler' ) );

			// load locale
			load_plugin_textdomain( 'slate', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

			$old_dir = getcwd();

			// Load fonts
			$this->_fonts = apply_filters( 'slate_font_files_or_urls', array() );
			foreach ( $this->_fonts as $font_name => &$font ) {
				if ( isset( $font['url'] ) && ( filter_var( $font['url'], FILTER_VALIDATE_URL ) === false ) ) {
					trigger_error( "Cannot detect type of font resource $font_name", E_USER_WARNING );
				}
				if ( ! isset( $font['source'] ) ) {
					$font['source'] = 'custom';
				}
				if ( 'custom' == $font['source'] ) {
					$font_path = parse_url( $font['url'], PHP_URL_PATH );
					$font_extension = strtolower( pathinfo( $font_path, PATHINFO_EXTENSION ) );
					if ( 'ttf' == $font_extension ) {
						$font['format'] = 'truetype';
					} elseif ( false !== array_search( $font_extension,
								array('eot', 'eof', 'woff', 'woff2', 'svg' ) ) ) {
						$font['format'] = $font_extension;
					}
				}
			}

			// Load shape templates
			$this->_register_templates( 'shape', apply_filters( 'slate_shape_files', array() ) );
			foreach ( array_keys( $this->_shapes ) as $shape_name ) {
				add_shortcode( "slate_$shape_name", array( &$this, 'shortcode' ) );
			}

			// Load filter templates
			$this->_register_templates( 'filter', apply_filters( 'slate_filter_files', array() ) );

			// Load fill templates
			$this->_register_templates( 'fill', apply_filters( 'slate_fill_files', array() ) );

			// Load presets
			$this->_presets = apply_filters( 'slate_presets', array() );

			chdir( $old_dir );

			// Sort all resources alphabetically by name
			foreach ( array( 'shape', 'filter', 'fill', 'font', 'preset') as $resource_name ) {
				ksort( $this->{"_{$resource_name}s"});
			}

			restore_error_handler();
		} // end function action_plugins_loaded

		public function action_wp_footer() {
			set_error_handler( array( __CLASS__, 'error_handler' ) );

			$webFontConfig = new stdClass();

			$css = '';
			foreach ( array_keys( $this->_used_fonts ) as $font_name ) {
				if ( isset( $this->_fonts[ $font_name ] ) ) {
					$font = $this->_fonts[ $font_name ];

					if ( isset( $font['source'] ) && $font['source'] ) {
						if ( ! isset( $webFontConfig->{$font['source']} ) ) {
							$webFontConfig->{$font['source']} = new stdClass();
							$webFontConfig->{$font['source']}->families = array();
						}
						$webFontConfig->{$font['source']}->families[] = $font_name;

						if ( 'custom' == $font['source'] ) {
							$css .= "@font-face { font-family: '$font_name'; src: url('{$font['url']}')";
							if ( array_key_exists( 'format', $font ) ) {
								$css .= " format('{$font['format']}')";
							};
							$css .= "; }\n";
						}
					}
				} else {
					trigger_error( "Could not find requested font $font_name", E_USER_WARNING );
				}
			}
			if ( $css ) {
				echo "<style>$css</style>";
			}

			// runtime vars to pass to assets/slate.js
			wp_localize_script( 'slate', 'webFontConfig', $webFontConfig );

			restore_error_handler();
		} // end function action_wp_footer

		public function action_wp_enqueue_scripts() {
			set_error_handler( array( __CLASS__, 'error_handler' ) );

			if ( ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {

				if ( ! is_admin() ) {

					// loads fonts and frontend code

					if ( ! defined( 'jQuery' ) || ! jQuery ) {

						wp_register_script(
							'jquery',
							'https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js',
							true,
							'1.12.4',
							false );
						wp_enqueue_script('jquery');

					}

					wp_enqueue_script(
						'webfont',
						'https://ajax.googleapis.com/ajax/libs/webfont/1.6.16/webfont.js',
						array(),
						'1.6.16',
						true );

					if ( file_exists( __DIR__ . '/assets/scripts/slate.min.js' ) ) {
						$slate_front_script = 'assets/scripts/slate.min.js';
					} else {
						$slate_front_script = 'assets/scripts/slate.js';
					}

					wp_enqueue_script(
						'slate',
						plugins_url( $slate_front_script, __FILE__ ),
						array( 'webfont', 'jquery' ),
						false,
						true );

					if ( file_exists( __DIR__ . '/assets/styles/slate.min.css' ) ) {
						$slate_front_style = 'assets/styles/slate.min.css';
					} else {
						$slate_front_style = 'assets/styles/slate.css';
					}

					wp_enqueue_style( 'slate', plugins_url( $slate_front_style, __FILE__ ) );
				}
			}

			restore_error_handler();
		} // end function action_wp_enqueue_scripts

		public function action_plugin_action_links( $links ) {
			$links[] = '<a href="' . admin_url( 'options-general.php?page=slate_settings_page' ) . '">'
				. __( 'Settings', 'slate' ) . '</a>';
			$links[] = '<a href="http://www.dashed-slug.net/svg-logo-and-text-effects-wordpress-plugin">' . __( 'Visit plugin site', 'undefined' ) . '</a>';
			$links[] = '<a href="https://wordpress.org/support/plugin/slate" style="color: #dd9933;">' . __( 'Support', 'undefined' ) . '</a>';

			return $links;
		}

		public function action_admin_init() {
			set_error_handler( array( __CLASS__, 'error_handler' ) );

			add_settings_section(
				'slate_settings_section',
				str_replace( '_', ' ', __CLASS__ ) . ' settings',
				array( &$this, 'cb_settings_section' ),
				'slate_settings_page' );

			register_setting( 'slate_settings_page', self::OPT_CACHE );

			add_settings_field(
				self::OPT_CACHE,
				'SVG caching',
				array( &$this, 'cb_settings_cache' ),
				'slate_settings_page',
				'slate_settings_section' );

			// remove admin widget, notices, and settings panel for mere subscribers
			if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
				remove_action( 'admin_menu', array( &$this, 'action_admin_menu' ) );
				remove_action( 'admin_notices', array( &$this, 'action_admin_notices' ) );
				remove_action( 'wp_dashboard_setup', array( &$this, 'action_wp_dashboard_setup' ) );
			}

			// add TinyMCE button
			if ( user_can_richedit() && ( current_user_can( 'edit_posts' ) || current_user_can( 'edit_pages' ) ) ) {
				add_filter( 'mce_external_plugins', array( &$this, 'filter_register_tinymce_plugin' ) );
				add_filter( 'mce_buttons', array( &$this, 'filter_register_tinymce_button' ) );
			}

			restore_error_handler();
		}

		public static function filter_register_tinymce_plugin( $plugin_array ) {
			$plugin_array['slate'] = plugins_url( 'assets/scripts/slate-tinymce.js', __FILE__ );
			return $plugin_array;
		}

		public static function filter_register_tinymce_button( $buttons ) {
			array_push( $buttons, 'slate' );
			return $buttons;
		}

		public function filter_query_vars( $vars ) {
			$vars[] = '__slate_presets';
			return $vars;
		}

		public function action_parse_request() {
			set_error_handler( array( __CLASS__, 'error_handler' ) );

			global $wp;

			if ( isset( $wp->query_vars['__slate_presets'] ) ) {
				if ( is_user_logged_in() && user_can_richedit() ) {
					wp_send_json( $this->_presets );
				}
			}
			restore_error_handler();
		}


		public function action_admin_menu() {
			set_error_handler( array( __CLASS__, 'error_handler' ) );

			if ( empty( self::$_plugin_data ) ) {
				self::$_plugin_data = get_plugin_data( __FILE__, false, false );
			}

			if ( current_user_can( 'manage_options' ) ) {
				add_options_page(
					self::$_plugin_data['Name'],
					self::$_plugin_data['Name'],
					'manage_options',
					'slate_settings_page',
					array( &$this, 'cb_settings_page' ) );
			}

			restore_error_handler();
		}

		/**
		 * settings page callback
		 */
		public function cb_settings_page() {
			set_error_handler( array( __CLASS__, 'error_handler' ) );

			echo '<form method="post" action="options.php">';
			settings_fields( 'slate_settings_page' );
			do_settings_sections( 'slate_settings_page' );
			submit_button();
			echo '</form>';

			restore_error_handler();
		}

		public function cb_settings_section() {
			echo '<p>Settings that are specific to the ' . self::$_plugin_data['Name'] . ' plugin</p>';
		}

		public function cb_settings_cache() {
			set_error_handler( array( __CLASS__, 'error_handler' ) );

			echo '<input name="' . self::OPT_CACHE . '" id="' . self::OPT_CACHE . '" type="checkbox" ';

			checked( 'on', get_option( self::OPT_CACHE ) );

			echo ' />' . __(
				'Enable if you wish SVG output to be cached by WordPress (recommended). You might wish to disable this while developing new PHP templates.',
				'slate' );

			restore_error_handler();
		}

		/**
		 * Renders the svg tag
		 *
		 * @param array $atts
		 * @param string $content
		 * @param string $tag
		 * @return string
		 */
		public function shortcode( $atts, $content = '', $tag ) {
			set_error_handler( array( __CLASS__, 'error_handler' ) );

			// defensive programming
			if ( ! isset( $atts ) || ! $atts ) {
				$atts = array();
			}

			// all used fonts are loaded into WebFontConfig js array later, at page footer
			if ( isset( $atts['_font_family'] ) && $atts['_font_family'] ) {
				$this->_used_fonts[ $atts['_font_family'] ] = true;
			}

			// cache check
			$hash = md5( serialize( $atts ) . $content . $tag );
			if ( ! array_key_exists( $hash, $this->_hash_collisions ) ) {
				$this->_hash_collisions[ $hash ] = 1;
			} else {
				$this->_hash_collisions[ $hash ] += 1;
			}
			$hash = $this->_hash_collisions[ $hash ] . '-' . $hash;
			$svg = get_transient( "slate-$hash-svg" );

			// hack that removes leading end tags and trailing start tags
			// tinyMCE does this way more often than you may think
			$content = preg_replace( '/^(<\/\w+>)+/', '', $content );
			$content = preg_replace( '/(<\w+>)+$/', '', $content );

			$do_shortcode = self::_validate_markup( do_shortcode( $content ) );

			// if no caching enabled or cache miss
			if ( ! get_option( self::OPT_CACHE ) || false === $svg ) {

				// if a shape template is specified
				if ( preg_match( '/^slate_([_\w\d]+)$/', $tag, $matches ) ) {
					$shape_template_name = $matches[1];

					// if the specified shape template is loaded
					if ( isset( $this->_shapes[ $shape_template_name ] ) ) {

						// compute shape template attributes
						$shape_atts_defaults =
							self::_get_defaults( $this->_shapes[ $shape_template_name ]['params_defs'] );

						// allows "shortcode_atts_" filter on shape atts
						$shape_atts =
							shortcode_atts( $shape_atts_defaults, $atts, $tag );

						// shape param type coercion
						foreach ( $this->_shapes[ $shape_template_name ]['params_defs'] as $param_name => $param_def ) {

							if ( 'integer' == $param_def['type'] ) {
								$shape_atts[ $param_name ] =
									intval( $shape_atts[ $param_name ] );

							} elseif ( 'float' == $param_def['type'] ) {
								$shape_atts[ $param_name ] =
									floatval( $shape_atts[ $param_name ] );

							} elseif ( 'html' == $param_def['type'] ) {
								$shape_atts[ $param_name ] =
									self::_text_tspanize( self::_validate_markup( $shape_atts[ $param_name ] ) );
							}
						}

						$shape_atts['_id'] = 'slate-' . substr( $hash, 0, 10 );
						$shape_atts['_text'] = $do_shortcode;
						$shape_atts['_text_tspan'] = self::_text_tspanize( $do_shortcode );
						$shape_atts['_fill'] = '';
						$shape_atts['_fill_id'] = $shape_atts['_id'] . '-fill';
						$shape_atts['_filters'] = '';
						$shape_atts['_filters_id'] = $shape_atts['_id'] . '-filters';

						// if a fill template is specified
						if ( isset( $atts['fill'] ) && $atts['fill'] ) {
							$fill_name = $atts['fill'];

							// if the specified fill template is loaded
							if ( isset( $this->_fills[ $fill_name ] ) ) {
								// compute fill template attributes and render
								$fill_atts_defaults = self::_get_defaults( $this->_fills[ $fill_name ]['params_defs'] );
								$fill_atts_given = array();

								foreach ( $atts as $key => $value ) {
									if ( preg_match( "/fill_{$fill_name}_([_\w\d]+)/", $key, $match ) ) {
										$param_name = $match[1];

										$fill_atts_given[ $param_name ] = $value;
									}
								}
								$fill_atts = shortcode_atts( $fill_atts_defaults, $fill_atts_given );

								// fill param type coercion
								foreach ( $this->_fills[ $fill_name ]['params_defs'] as $param_name => $param_def ) {
									if ( 'integer' == $param_def['type'] ) {
										$fill_atts[ $param_name ] = intval( $fill_atts[ $param_name ] );
									} elseif ( 'float' == $param_def['type'] ) {
										$fill_atts[ $param_name ] = floatval( $fill_atts[ $param_name ] );
									}
								}

								$fill_atts['_id'] = $shape_atts['_id'] . '-fill';
								$shape_atts['_fill'] = $this->_render( 'fill', $fill_name, $fill_atts );
							} else {
								trigger_error(
									__CLASS__ . " Tag $tag requested unknown fill pattern $fill_name - ignoring",
									E_USER_WARNING );
							}
						} // end fill rendering

						// group together given filter params by filter template name
						$filter_atts_given = array();
						foreach ( $atts as $key => $value ) {
							if ( preg_match( '/filter_([\w\d]+)_([\w\d]+)/', $key, $match ) ) {
								$filter_name = $match[1];
								$param_name = $match[2];

								$filter_atts_given[ $filter_name ][ $param_name ] = $value;
							}
						}

						// render all filter templates that have any variables with non-default values
						foreach ( array_keys( $filter_atts_given ) as $filter_name ) {
							// if this filter template is loaded
							if ( isset( $this->_filters[ $filter_name ] ) ) {
								// compute filter template attributes and render
								$filter_atts_defaults = self::_get_defaults(
									$this->_filters[ $filter_name ]['params_defs'] );
								$filter_atts = shortcode_atts( $filter_atts_defaults, $filter_atts_given[ $filter_name ] );

								// filter param type coercion
								foreach ( $this->_filters[ $filter_name ]['params_defs'] as $param_name => $param_def ) {
									if ( 'integer' == $param_def['type'] ) {
										$filter_atts[ $param_name ] = intval( $filter_atts[ $param_name ] );
									} elseif ( 'float' == $param_def['type'] ) {
										$filter_atts[ $param_name ] = floatval( $filter_atts[ $param_name ] );
									}
								}

								$filter_atts['_id'] = $shape_atts['_id'] . '-filter';
								$filter_atts['_result'] = $shape_atts['_id'] . "-filter-$filter_name-result";

								$shape_atts['_filters'] .= $this->_render( 'filter', $filter_name, $filter_atts );
							} else {
								trigger_error(
									__CLASS__ . " Tag $tag requested unknown filter pattern $filter_name - ignoring",
									E_USER_WARNING );
							}
						}

						// render feMerge node to mix all filter results with source
						$shape_atts['_filters'] .= "<feMerge>";
						foreach ( array_keys( $filter_atts_given ) as $filter_name ) {
							$shape_atts['_filters'] .= "<feMergeNode in=\"{$shape_atts['_id']}-filter-{$filter_name}-result\" />";
						}
						$shape_atts['_filters'] .= "<feMergeNode in=\"SourceGraphic\" /></feMerge>";

						// begin SVG tag rendering

						$svg = '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" id="' .
							 $shape_atts['_id'] . '"';

						// copy some text attributes into the <svg> tag
						foreach ( array( 'font-family', 'font-size', 'stroke', 'stroke-width' ) as $attribute ) {
							$shortcode_att = '_' . str_replace( '-', '_', $attribute );
							if ( array_key_exists( $shortcode_att, $atts ) && $atts[ $shortcode_att ] ) {
								$svg .= " $attribute=\"{$atts[ $shortcode_att ]}\"";
							}
						}
						if ( array_key_exists( '_skew_x_deg', $atts ) && $atts['_skew_x_deg'] && intval( $atts['_skew_x_deg'] ) ) {
							$svg .= " style=\"transform: perspective(200px) rotateX({$atts['_skew_x_deg']}deg);";
							$svg .= " -webkit-transform: perspective(200px) rotateX({$atts['_skew_x_deg']}deg);\"";
						}
						$svg .= '>';

						$svg .= '<desc>' . htmlspecialchars_decode( strip_tags( $do_shortcode ) ) . '</desc>';

						// render shape template (everything inside the svg tag)
						$svg .= '<g class="slate-crop-this">';
						$svg .= $this->_render( 'shape', $shape_template_name, $shape_atts );
						$svg .= '</g></svg>';

						// end SVG tag rendering
					} else {
						trigger_error( __CLASS__ . " Cannot find shape template to render tag $tag", E_USER_WARNING );
						return;
					}
				} else {
					trigger_error( __CLASS__ . " Tag $tag does not specify a valid shape template name", E_USER_WARNING );
					return;
				}

				if ( get_option( self::OPT_CACHE ) ) {
					set_transient( "slate-$hash-svg", $svg, HOUR_IN_SECONDS );
				}
			}

			// HTML fallback
			$html_style = '';

			if ( array_key_exists('_font_family', $atts) ) {
				$html_style .= "font-family: '{$atts['_font_family']}'; ";
			}

			if ( array_key_exists('_font_size', $atts) ) {
				$html_style .= "font-size: {$atts['_font_size']}; ";
			}

			if ( array_key_exists('_stroke', $atts) ) {
				$html_style .= "color: {$atts['_stroke']}; ";
			}

			$output  = "<span class=\"slate html $hash\" style=\"$html_style\">$do_shortcode</span>\n";
			$output .= "<span class=\"slate svg $hash\">$svg</span>";

			// plugins hook
			$output = apply_filters( 'slate_output', $output, array(
				'atts' => $atts,
				'content' => $content,
				'content_plaintext' => htmlspecialchars_decode( strip_tags( $do_shortcode ) ),
				'tag' => $tag,
				'svg' => $svg,
				'hash' => $hash
			) );

			restore_error_handler();
			return $output;
		} // end function shortcode

		/**
		 * Try to repair unbalanced tags as best as possible
		 * without an HTML purifier dependency.
		 *
		 * @param string $html The markup to parse.
		 * @return The fixed markup or the string with html tags stripped.
		 */
		private static function _validate_markup( $html ) {

			if ( class_exists( 'tidy' ) ) {

				// if tidy is available,
				$config = array(
					'show-body-only' => true);

				$tidy = new tidy();

				if ( $tidy->parseString( $html, $config, 'utf8' ) ) {

					$tidy->cleanRepair();
					$html = $tidy . '';

				}
			} else {

				// hack that removes leading end tags and trailing start tags
				// tinyMCE does this way more often than you may think
				$html = preg_replace( '/^(<\/\w+>)+/', '', $html );
				$html = preg_replace( '/(<\w+>)+$/', '', $html );
			}

			// check if there are still errors
			$dom = new DOMDocument( '1.0', 'utf-8' );
			$dom->recover = true;
			$dom->strictErrorChecking = false;
			$fragment = $dom->createDocumentFragment();

			if ( @$fragment->appendXML( $html ) ) {
				// if markup is valid, use this
				$result = $html;
			} elseif ( @$fragment->appendXML( "<p>$html</p>" ) ) {
				// if fragment just needs a paragraph to make it valid, add the tags
				$result = "<p>$html</p>";
			} else {
				// if markup is still invalid,
				// strip tags so the page markup doesn't break
				trigger_error( "Could not fix markup for HTML: $html", E_USER_WARNING );
				$result = trim( strip_tags( $html ) );
			}
			return $result;
		}

		/**
		 * Converts simple html typography tags like b,i,u,strong,em, etc. to tspan style="..." tags.
		 * The tspan tags are compatible with the svg text tag.
		 *
		 * @return string An svg code fragment with the transformed content.
		 */
		private static function _text_tspanize( $html ) {
			$dom = new DOMDocument( '1.0', 'utf-8' );
			$dom->recover = true;
			$dom->strictErrorChecking = false;
			$fragment = $dom->createDocumentFragment();

			// break lines to paragraphs
			$lines = preg_split( '/(\r?\n)|(<br\s*\/?>)/', $html );

			if ( false === $lines || 1 === count( $lines ) ) {
				$multiline = $html;
			} else {
				$multiline = '';
				foreach ( $lines as $line ) {
					$multiline .= "<span x=\"0\" dy=\"1em\">$line</span>";
				}
			}

			if ( @$fragment->appendXML( $multiline ) ) {
				$dom->appendChild( $fragment );

				foreach ( self::$_text_tspanize_map as $tag => $tag_style ) {
					$node_list = $dom->getElementsByTagName( $tag );

					$nodes_total = $node_list->length;
					$nodes_processed = 0;
					while ( $nodes_processed++ < $nodes_total ) {

						$node = $node_list->item( 0 );

						$new_node = $dom->createElement( 'a' === $tag ? 'a' : 'tspan' );

						while ( $node->hasChildNodes() ) {
							$node_child = $node->childNodes->item( 0 );
							$node_child = $node->ownerDocument->importNode( $node_child, true );
							$new_node->appendChild( $node_child );
						}

						foreach ( $node->attributes as $att_name => $att_node ) {
							if ( 'a' === $tag && 'href' === $att_name ) {
								$new_node->setAttribute( "xlink:href", "$att_node->nodeValue" );
							} else {
								$new_node->setAttribute( $att_name, "$att_node->nodeValue" );
							}
						}

						// use any default styles for the new tag
						$new_style = $tag_style;

						// preserve any existing styles
						if ( $new_node->hasAttribute( 'style' ) ) {
							$old_style = $new_node->getAttribute( 'style' );
							$new_style = "$old_style; $tag_style";
						}

						if ( trim( $new_style ) ) {
							$new_node->setAttribute( 'style', $new_style );
						}

						$node->parentNode->replaceChild( $new_node, $node );
					}
				}

				$result = trim( $dom->saveHTML() );
			} else {
				trigger_error( "Could not parse as HTML: $multiline", E_USER_WARNING );
				$result = trim( strip_tags( $multiline ) );
			}

			return $result;
		} // end function _text_tspanize

		private static function _extract_phpdoc( $file_name ) {
			$source = file_get_contents( $file_name );
			$tokens = token_get_all( $source );
			$comments = '';

			foreach ( $tokens as $token ) {
				if ( T_DOC_COMMENT != $token[0] )
					continue;
				$comments .= $token[1];
			}

			return $comments;
		}

		private static function _extract_phpdoc_no_tokenizer( $file_name ) {
			// Some installations such as AMPPS might have the Zend tokenizer disabled.
			// In this case we just extract the first comment in the file, hoping it will be the template's PHPDoc!
			$source = file_get_contents( $file_name );
			preg_match( '#(\/\*\*.*\*\/)#s', $source, $matches );

			if ( isset( $matches[1] ) ) {
				return $matches[1];
			} else {
				trigger_error( sprintf( __("Could not identify valid PHPDoc comments in %s. Try enabling the Zend tokenizer and try again.", 'slate' ), $file_name ) );
				return '';
			}
		}

		/**
		 * Parses a template from its PHPdoc
		 *
		 * @param string $file_name Full path to template file
		 * @return array template array for 'file_name' template
		 */
		private static function _parse_phpdoc( $file_name ) {
			$has_tokenizer = function_exists( 'token_get_all' );
			if ( $has_tokenizer ) {
				$comments = self::_extract_phpdoc( $file_name );
			} else {
				$comments = self::_extract_phpdoc_no_tokenizer( $file_name );
			}

			$result = array(
				'name' => basename( $file_name, '.php' ),
				'file_name' => $file_name,
				'description' => '',
				'params_defs' => array() );

			if ( preg_match_all(
				'/@var\s+(?<type>\w+)\s+\\$(?<name>\w+)\s+(\(\s*default\s*:\s*(?<default>[^)\s]+)\))?\s*(?<description>.*)$/mx',
				$comments,
				$matches ) ) {
				for ( $i = 0, $max = count( $matches[0] ); $i < $max; $i++ ) {
					$result['params_defs'][ $matches['name'][ $i ] ] = array();
					$result['params_defs'][ $matches['name'][ $i ] ]['name'] = $matches['name'][ $i ];
					$result['params_defs'][ $matches['name'][ $i ] ]['type'] = $matches['type'][ $i ];
					$result['params_defs'][ $matches['name'][ $i ] ]['description'] = $matches['description'][ $i ];
					$result['params_defs'][ $matches['name'][ $i ] ]['default'] = $matches['default'][ $i ];
				}
			}

			foreach ( explode( "\n", $comments ) as $line ) {
				if ( preg_match( '/^\s*\/?\*{1,2}\/?\s*([^@]*)(\*\/)?$/', $line, $matches ) ) {
					$result['description'] .= $matches[1];
				}
			}
			return $result;
		}

		private function _render( $resource_type, $resource_name, $atts ) {
			try {
				$template = $this->{"_{$resource_type}s"}[ $resource_name ];
			} catch ( Exception $e ) {
				trigger_error( "Could not render template for $resource_type $resource_name: $e", E_USER_WARNING );
				return '';
			}

			return self::_render_file_with_vars( $template['file_name'], $atts );
		}

		/**
		 * Get default values for all params in a params definition.
		 *
		 * @param array $params_defs The params definition coming from the template definition.
		 * @return array An assoc array where keys are parameter names and values are default values.
		 */
		private static function _get_defaults( $params_defs ) {
			$defaults = array();
			foreach ( $params_defs as $param_name => $param_def ) {
				if ( array_key_exists( 'default', $param_def ) ) {
					$defaults[ $param_name ] = $param_def['default'];
				} else {
					$defaults[ $param_name ] = '';
				}
			}
			return $defaults;
		}

		private static function _render_file_with_vars( $file_name, $vars ) {
			$how_many = extract( $vars, EXTR_REFS );

			// warning: at this point you might not see the variables when
			// stepping through in the eclipse debugger, but they are there!

			if ( count( $vars ) != $how_many ) {
				trigger_error( "Warning: $how_many variables set out of " . count( $vars ), E_USER_WARNING );
			}
			unset( $how_many );

			ob_start();

			try {
				include $file_name;
			} catch ( Exception $e ) {
				trigger_error( "Error rendering template $file_name: " . $e->getMessage(), E_USER_ERROR );
			}

			return ob_get_clean();
		}
	}
}

// Instantiate the plugin class
SVG_Logo_and_Text_Effects::get_instance();

