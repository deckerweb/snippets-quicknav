<?php
/*
Plugin Name:  Snippets QuickNav
Plugin URI:   https://github.com/deckerweb/snippets-quicknav
Description:  For Code Snippets enthusiasts: Adds a quick-access navigator (aka QuickNav) to the WordPress Admin Bar (Toolbar). It allows easy access to your Code Snippets listed by Active, Inactive, Snippet Type or Tag. Safe Mode is supported. Comes with inspiring links to snippet libraries.
Project:      Code Snippet: DDW Snippets QuickNav
Version:      1.1.0
Author:       David Decker – DECKERWEB
Author URI:   https://deckerweb.de/
Text Domain:  snippets-quicknav
Domain Path:  /languages/
License:      GPL v2 or later
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Requires WP:  6.7
Requires PHP: 7.4
Copyright:    © 2025, David Decker – DECKERWEB

Original Code Snippet Icons, Copyright: © Code Snippets Pro, Code Snippets B.V.
All Other Icons, Copyright: © Remix Icon
	
TESTED WITH:
Product			Versions
--------------------------------------------------------------------------------------------------------------
PHP 			8.0, 8.3
WordPress		6.7.2 ... 6.8 Beta
Code Snippets	3.6.8 / 3.6.9 (free & Pro)
--------------------------------------------------------------------------------------------------------------

VERSION HISTORY:
Date		Version		Description
--------------------------------------------------------------------------------------------------------------
2025-03-24	1.1.0		New: Show Admin Bar also in Block Editor full screen mode
						New: Add info to Site Health Debug, useful for our constants for custom tweaking
						Improved: Disable promo stuff only for free version (not globally)
2025-03-21	1.0.0		Initial release 
						- Supports Code Snippets free & Pro
						- Supports Code Snippets Multisite behavior (and settings)
2025-03-17	0.5.0		Internal test version supporting Code Snippets free & Pro
2025-03-16	0.0.0		Development start
--------------------------------------------------------------------------------------------------------------
*/

/** Prevent direct access */
if ( ! defined( 'ABSPATH' ) ) {
	exit;  // Exit if accessed directly.
}

if ( ! class_exists( 'DDW_Snippets_QuickNav' ) ) :

class DDW_Snippets_QuickNav {

	private static $snippets_active   = 0;
	private static $snippets_inactive = 0;
	
	public static $expert_mode = FALSE;
	
	private const DEFAULT_MENU_POSITION	= 999;  // default: 999
	private const VERSION = '1.1.0';

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'admin_bar_menu',              array( $this, 'add_admin_bar_menu' ), self::DEFAULT_MENU_POSITION );
		add_action( 'admin_enqueue_scripts',       array( $this, 'enqueue_admin_bar_styles' ) );  // for Admin
		add_action( 'wp_enqueue_scripts',          array( $this, 'enqueue_admin_bar_styles' ) );  // for front-end
		add_action( 'enqueue_block_editor_assets', array( $this, 'adminbar_block_editor_fullscreen' ) );  // for Block Editor
		add_action( 'init',                        array( $this, 'free_cs_free' ), 20 );
		add_filter( 'debug_information',           array( $this, 'site_health_debug_info' ), 9 );
	}
	
	/**
	 * Is expert mode active?
	 *   Gives some more stuff that is more focused at (plugin/snippet) developers
	 *   and mostly not needed for fast site building.
	 *
	 * @return bool
	 */
	private function is_expert_mode(): bool {
		
		self::$expert_mode = ( defined( 'SNQN_EXPERT_MODE' ) ) ? (bool) SNQN_EXPERT_MODE : self::$expert_mode;
			
		return self::$expert_mode;
	}
	
	/**
	 * Get specific Admin Color scheme colors we need. Covers all 9 default
	 *	 color schemes coming with a default WordPress install.
	 *   (helper function)
	 */
	private function get_scheme_colors() {
		
		$scheme_colors = array(
			'fresh' => array(
				'bg'    => '#1d2327',
				'base'  => 'rgba(240,246,252,.6)',
				'hover' => '#72aee6',
			),
			'light' => array(
				'bg'    => '#e5e5e5',
				'base'  => '#999',
				'hover' => '#04a4cc',
			),
			'modern' => array(
				'bg'    => '#1e1e1e',
				'base'  => '#f3f1f1',
				'hover' => '#33f078',
			),
			'blue' => array(
				'bg'    => '#52accc',
				'base'  => '#e5f8ff',
				'hover' => '#fff',
			),
			'coffee' => array(
				'bg'    => '#59524c',
				'base'  => 'hsl(27.6923076923,7%,95%)',
				'hover' => '#c7a589',
			),
			'ectoplasm' => array(
				'bg'    => '#523f6d',
				'base'  => '#ece6f6',
				'hover' => '#a3b745',
			),
			'midnight' => array(
				'bg'    => '#363b3f',
				'base'  => 'hsl(206.6666666667,7%,95%)',
				'hover' => '#e14d43',
			),
			'ocean' => array(
				'bg'    => '#738e96',
				'base'  => '#f2fcff',
				'hover' => '#9ebaa0',
			),
			'sunrise' => array(
				'bg'    => '#cf4944',
				'base'  => 'hsl(2.1582733813,7%,95%)',
				'hover' => 'rgb(247.3869565217,227.0108695652,211.1130434783)',
			),
		);
		
		/** No filter currently b/c of sanitizing issues with the above CSS values */
		//$scheme_colors = (array) apply_filters( 'ddw/quicknav/csn_scheme_colors', $scheme_colors );
		
		return $scheme_colors;
	}
	
	/**
	 * Enqueue custom styles for the Admin Bar.
	 *   NOTE: Used within Admin and on the front-end (if Toolbar enabled).
	 */
	public function enqueue_admin_bar_styles() {
		
		/**
		 * Depending on user color scheme get proper base and hover color values for the main item (svg) icon.
		 */
		$user_color_scheme = get_user_option( 'admin_color' );
		$admin_scheme      = $this->get_scheme_colors();
		
		$base_color  = $admin_scheme[ $user_color_scheme ][ 'base' ];
		$hover_color = $admin_scheme[ $user_color_scheme ][ 'hover' ];
		
		/**
		 * Build the inline CSS
		 *   NOTE: We need to use 'sprintf()' because of the percentage values and similar!
		 */
		$inline_css = sprintf(
			'	
			#wpadminbar .snqn-snippet-list .ab-sub-wrapper ul li span.scope {
			  font-family: monospace;
			  font-size: %1$s;
			  vertical-align: super;
			  /* filter: brightness(%2$s); */
			  color: hsl(0, %3$s, %4$s);
			}
			
			#wpadminbar .snqn-safemode {
				background-color: #9C1005;
			}
			#wpadminbar .snqn-safemode:hover,
			#wpadminbar ul li.snqn-safemode:hover {
				background: #BD3126;
			}
			#wpadminbar .snqn-safemode a {
				color: #FBE4C6;
				font-weight: 700;
			}
			
			#wpadminbar .has-icon .icon-svg svg {
				display: inline-block;
				margin-bottom: 3px;
				vertical-align: middle;
				width: 16px;
				height: 16px;
			}
			
			.icon-svg.ab-icon svg {
				/* color: inherit; */ /* currentColor; */	/* rgba(240,246,252,.6); */
				width: 15px;
				height: 15px;
			}
			
			.snqn-snippet-list .ab-item .icon-svg.ab-icon svg {
				color: %5$s;
			}
			
			.snqn-snippet-list .ab-item:hover .icon-svg.ab-icon svg {
				color: %6$s;  /* inherit; */
			}
			
			/** Badges */
			#wp-admin-bar-snqn-snippets-types-default .cs-badge.badge-type,
			#wp-admin-bar-snqn-wpnewcontent-add-snippet-default .cs-badge.badge-type,
			#wp-admin-bar-snqn-add-snippet-default .cs-badge.badge-type {
				background-color: #DCDCDE;
				border: 1px solid;
				border-radius: 5px;
				font-size: %1$s;
				margin-left: 3px;
				padding: 1px 3px;
				text-transform: uppercase;
				vertical-align: middle;
			}
			
			.ab-submenu .has-badge > a:hover > .cs-badge.badge-type {
				background-color: #eee !important;
			}
			
			.cs-badge.badge-php {
				border-color: #0073aa;
				color: #0073aa;
			}

			.cs-badge.badge-html {
				border-color: #548b54;
				color: #548b54;
			}
			
			.cs-badge.badge-css {
				border-color: #8000ff;
				color: #8000ff;
			}
			
			.cs-badge.badge-js {
				border-color: #cd6600;
				color: #cd6600;
			}

			.cs-badge.badge-cloud {
				border-color: #00bcd4;
				color: #00bcd4;
			}
			
			.cs-badge.badge-cloud_search {
				border-color: #e91e63;
				color: #e91e63;
			}
			
			.cs-badge.badge-bundles {
				border-color: #50575E;
				color: #50575E;
			}
			
			.cs-badge.badge-cloud svg,
			.cs-badge.badge-cloud_search svg,
			.cs-badge.badge-bundles svg {
				margin-bottom: -2px !important;
				width: 12px !important;
				height: 12px !important;
			}								
			',
			'80%',
			'120%',
			'100%',
			'70%',
			$base_color,
			$hover_color
		);
		
		/** Only add the styles if Admin Bar is showing */
		if ( is_admin_bar_showing() ) {
			wp_add_inline_style( 'admin-bar', $inline_css );
		}
	}

	/**
	 * Check for active SCRIPT_DEBUG constant, and also filter (helper function)
	 *
	 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/#script_debug
	 */
	private function is_wp_dev_mode_active() {
		
		return defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG;
	}
	
	/**
	 * Check for active Safe Mode constant, and also filter (helper function)
	 *
	 * @link https://codesnippets.pro/docs/emergency-fixes/
	 * @link https://github.com/codesnippetspro/safe-mode
	 */
	private function is_safe_mode_active() {
		
		if ( defined( 'CODE_SNIPPETS_SAFE_MODE' ) && CODE_SNIPPETS_SAFE_MODE ) return TRUE;
		
		elseif ( has_filter( 'code_snippets/execute_snippets', '__return_false' ) ) return TRUE;
		
		return FALSE;
	}
	
	/**
	 * Check for active Code Snippets Pro Version (licensed) (helper function)
	 *
	 * @TODO Make more future proof ...!
	 */
	private function is_pro_version_active() {
		
		if ( class_exists( '\Code_Snippets\Cloud\Cloud_GPT_API' ) ) return TRUE;
		
		return FALSE;
	}
	
	/**
	 * Check for active Cloud Connection (CS Pro) (helper function)
	 *
	 * @TODO Make more future proof ...!
	 */
	private function is_cloud_connected() {
		
		if ( class_exists( '\Code_Snippets\Cloud\Cloud_API' ) && \Code_Snippets\Cloud\Cloud_API::is_cloud_connection_available() ) return TRUE;
		
		return FALSE;
	}
	
	/**
	 * Returns 'TRUE' if plugin settings are unified on a Multisite installation
	 *   under the Network Admin settings menu.
	 *
	 * This option is controlled by the "Enable administration menus" setting
	 *   on the Network Settings menu.
	 *
	 * @return bool
	 */
	private function are_settings_unified(): bool {
		if ( ! is_multisite() ) return FALSE;
	
		$is_site_option_set = get_site_option( 'menu_items', array() );
		return empty( $is_site_option_set[ 'snippets_settings' ] );
	}
	
	/**
	 * Switch settings URL, depending on Multisite setting
	 *
	 * @param  string $url  URL of Network Admin or (Site) admin page.
	 * @return string $url  URL for Network or Admin context.
	 */
	private function get_settings_url( $url ) {
		
		if ( ! $this->are_settings_unified() ) return admin_url( $url );
		
		else return network_admin_url( $url );
	}
			
	/**
	 * Switch URL by context
	 *
	 * @param  string $url  URL of Network Admin or (Site) admin page.
	 * @return string $url  URL for Network or Admin context.
	 */
	private function get_url( $url ) {
		
		if ( is_network_admin() ) return network_admin_url( $url );
		
		else return admin_url( $url );
	}
	
	/**
	 * Adds the main (Code) Snippets menu and its submenus to the Admin Bar.
	 *
	 * @param WP_Admin_Bar $wp_admin_bar The WP_Admin_Bar instance.
	 */
	public function add_admin_bar_menu( $wp_admin_bar ) {
		
		/** Don't do anything if Code Snippets plugin is NOT active */
		if ( ! defined( 'CODE_SNIPPETS_VERSION' ) ) {
			return;
		}
		
		/** Also, don't do anything if the current user has no permission */
		$snqn_permission = ( defined( 'SNQN_VIEW_CAPABILITY' ) ) ? SNQN_VIEW_CAPABILITY : 'activate_plugins';
		
		if ( ! current_user_can( sanitize_key( $snqn_permission ) ) ) {
			return;
		}
		
		/** Build the main item title, optional snippets count value */
		$all_snippets = \Code_Snippets\get_snippets();
		$counter      = ( defined( 'SNQN_COUNTER' ) && 'yes' === sanitize_key( SNQN_COUNTER ) ) ? ' (' . intval( count( $all_snippets ) ) . ')' : '';
		$snqn_name    = ( defined( 'SNQN_NAME_IN_ADMINBAR' ) ) ? esc_html( SNQN_NAME_IN_ADMINBAR ) : esc_html__( 'Snippets', 'snippets-quicknav' );
		$snqn_name    = $snqn_name . $counter;

		/** Default "snip icon" (scissor) */
		$snip_icon = '<span class="icon-svg ab-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 1024" fill="currentColor"><path d="M191.968 464.224h158.912c23.68 0 42.656 19.2 42.656 42.656 0 11.488-4.48 21.984-11.968 29.632l0.192 0.448-108.768 108.736c-75.104 75.136-75.104 196.512 0 271.584 74.88 74.848 196.448 74.848 271.552 0 74.88-75.104 74.88-196.48 0-271.584-21.76-21.504-47.36-37.12-74.464-46.272l28.608-28.576h365.248c87.040 0 157.856-74.016 159.968-166.4 0-1.472 0.224-2.752 0-4.256-2.112-23.904-22.368-42.656-46.912-42.656h-264.96l191.328-191.328c17.504-17.504 18.56-45.024 3.2-63.36-1.024-1.28-2.080-2.144-3.2-3.2-66.528-63.552-169.152-65.92-230.56-4.48l-262.368 262.368h-46.528c12.8-25.6 20.032-54.624 20.032-85.344 0-106.016-85.952-192-192-192-106.016 0-191.968 85.984-191.968 192 0.032 106.080 85.984 192.032 192 192.032zM277.312 272.256c0 47.136-38.176 85.344-85.344 85.344-47.136 0-85.312-38.176-85.312-85.344s38.176-85.344 85.312-85.344c47.168 0 85.344 38.208 85.344 85.344zM469.088 721.312c33.28 33.248 33.28 87.264 0 120.512-33.28 33.472-87.264 33.472-120.736 0-33.28-33.248-33.28-87.264 0-120.512 33.472-33.504 87.456-33.504 120.736 0z" /></svg></span> ';
		
		/** Optional code icon by Remix Icon */
		$remix_icon = '<span class="icon-svg ab-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12L18.3431 17.6569L16.9289 16.2426L21.1716 12L16.9289 7.75736L18.3431 6.34315L24 12ZM2.82843 12L7.07107 16.2426L5.65685 17.6569L0 12L5.65685 6.34315L7.07107 7.75736L2.82843 12ZM9.78845 21H7.66009L14.2116 3H16.3399L9.78845 21Z"></path></svg></span> ';
		
		/** Optional Code Snippets logo icon (the red/blue one) */
		$cs_menu_icon     = 'code-snippets/assets/icon.svg';
		$cs_icon_path     = trailingslashit( WP_PLUGIN_DIR ) . $cs_menu_icon;
		$display_icon_url = file_exists( $cs_icon_path ) ? trailingslashit( plugins_url() ) . $cs_menu_icon : '';
		
		/** "Fire" Emoji for Safe Mode */
		$safemode = ( $this->is_safe_mode_active() ) ? ' 🔥' : '';
			
		$title_html = $snip_icon . '<span class="ab-label">' . $snqn_name . '</span>' . $safemode;
		
		if ( ( defined( 'SNQN_ICON' ) && 'red_blue' === sanitize_key( SNQN_ICON ) ) && ! empty( $display_icon_url ) ) {
			$title_html = '<img src="' . $display_icon_url . '" style="display:inline-block;padding-bottom:3px;padding-right:6px;vertical-align:middle;width:16px;height:16px;" alt="" />' . $snqn_name . $safemode;
			$title_html = wp_kses( $title_html, array(
				'img' => array(
					'src'   => array(),
					'style' => array(),
					'alt'   => array(),
				),
			) );
		} elseif ( defined( 'SNQN_ICON' ) && 'remix' === sanitize_key( SNQN_ICON ) ) {
			$title_html = $remix_icon . '<span class="ab-label">' . $snqn_name . '</span>' . $safemode;
		}
		
		/**
		 * Add the parent menu item with an icon (main node)
		 */
		$wp_admin_bar->add_node( array(
			'id'    => 'ddw-snippets-quicknav',
			'title' => $title_html,
			'href'  => esc_url( $this->get_url( 'admin.php?page=snippets' ) ),
			'meta'  => array( 'class' => 'snqn-snippet-list has-icon' ),
		) );
		
		/** Add submenus */
		$this->add_safemode_submenu( $wp_admin_bar );  // group node
		$this->add_snippets_by_status_group( $wp_admin_bar );  // group node
		$this->add_snippets_by_type_group( $wp_admin_bar );  // group node
		$this->add_snippets_new_group( $wp_admin_bar );  // group node
		$this->add_settings_group( $wp_admin_bar );  // group node
		$this->add_library_group( $wp_admin_bar );  // group node
		$this->add_libraries_submenu( $wp_admin_bar );
		$this->add_footer_group( $wp_admin_bar );  // group node
		$this->add_links_submenu( $wp_admin_bar );
		$this->add_about_submenu( $wp_admin_bar );
	}

	/**
	 * Add group node for Safe Mode, and Script Debug
	 */
	private function add_safemode_submenu( $wp_admin_bar ) {
		
		$wp_admin_bar->add_group( array(
			'id'     => 'snqn-group-safemode',
			'parent' => 'ddw-snippets-quicknav',
		) );
		
		/** Warning for Code Snippet's own Safe Mode */
		if ( $this->is_safe_mode_active() ) {
			$wp_admin_bar->add_node( array(
				'id'     => 'snqn-safemode-active',
				'title'  => esc_html__( 'SAFE MODE is active 🔥', 'snippets-quicknav' ),
				'href'   => esc_url( admin_url( 'admin.php?page=snippets' ) ),
				'parent' => 'snqn-group-safemode',
				'meta'   => array( 'class' => 'snqn-safemode' ),
			) );
			
			$wp_admin_bar->add_node( array(
				'id'     => 'snqn-safemode-active-topright',
				'title'  => strtoupper( esc_html__( 'Code Snippets Safe Mode active 🔥', 'snippets-quicknav' ) ),
				'href'   => 'https://codesnippets.pro/docs/emergency-fixes/',
				'parent' => 'top-secondary',	/** Puts the text on the right side of the Toolbar! */
				'meta'   => array( 'class' => 'snqn-safemode', 'target' => '_blank', 'rel' => 'nofollow noopener noreferrer' ),
			) );
		}  // end if
		
		/** Warning for WordPress' SCRIPT_DEBUG constant */
		if ( $this->is_wp_dev_mode_active() ) {
			$wp_admin_bar->add_node( array(
				'id'     => 'snqn-devmode-active',
				'title'  => esc_html__( 'SCRIPT_DEBUG is on ⚠', 'snippets-quicknav' ),
				'href'   => 'https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/#script_debug',
				'parent' => 'snqn-group-safemode',
				'meta'   => array( 'class' => 'snqn-safemode', 'rel' => 'nofollow noopener noreferrer' ),
			) );
			
			$wp_admin_bar->add_node( array(
				'id'     => 'snqn-devmode-active-topright',
				'title'  => strtoupper( esc_html__( 'SCRIPT_DEBUG is on ⚠', 'snippets-quicknav' ) ),
				'href'   => 'https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/#script_debug',
				'parent' => 'top-secondary',	/** Puts the text on the right side of the Toolbar! */
				'meta'   => array( 'class' => 'snqn-safemode', 'target' => '_blank', 'rel' => 'nofollow noopener noreferrer' ),
			) );
		}  // end if
	}
					
	/**
	 * Add group node for Active & Inactive Snippets
	 */
	private function add_snippets_by_status_group( $wp_admin_bar ) {
		
		$wp_admin_bar->add_group( array(
			'id'     => 'snqn-group-status',
			'parent' => 'ddw-snippets-quicknav',
		) );
		
		$this->add_code_snippets_to_admin_bar( $wp_admin_bar );
	}

	/**
	 * Status Group: Add Active & Inactive Snippets
	 */
	private function add_code_snippets_to_admin_bar( $wp_admin_bar ) {
		
		/** Get all Snippets from the DB (official operator function) */
		$snippets = \Code_Snippets\get_snippets();
		
		$count_active   = 0;
		$count_inactive = 0;
		
		/** First, iterate through all Snippets, use only Active ones! */
		if ( $snippets ) {
			
			$wp_admin_bar->add_node( array(
				'id'     => 'snqn-active',
				//'title'  => esc_html__( 'Active Snippets', 'snippets-quicknav' ) . ' (' . $count_active . ')',
				'href'   => esc_url( $this->get_url( 'admin.php?page=snippets&status=active' ) ),
				'parent' => 'snqn-group-status',
			) );
			
			foreach ( $snippets as $snippet ) {
				
				/** List only active snippets for now */
				if ( ! $snippet->active ) {
					continue;
				}

				$count_active++;
				
				$edit_link = $this->get_url( 'admin.php?page=edit-snippet&id=' . intval( $snippet->id ) );
				
				$scope = sprintf(
					' <span class="scope">%s</span>',
					esc_html( $snippet->scope )
				);
				
				$wp_admin_bar->add_node( array(
					'id'     => 'snqn-snippet-' . intval( $snippet->id ),
					'title'  => esc_html( $snippet->display_name ) . $scope,
					'href'   => esc_url( $edit_link ),
					'parent' => 'snqn-active',
				) );
				
			}  // end foreach
		}  // end if - active check
		
		/** Populate the "Active" title string with counter result after foreach iteration */
		$title_active_node = $wp_admin_bar->get_node( 'snqn-active' );
		$title_active_node->title = esc_html__( 'Active Snippets', 'snippets-quicknav' ) . ' (' . $count_active . ')';
		$wp_admin_bar->add_node( $title_active_node );
		
		/** Set active counter for class */
		self::$snippets_active = $count_active;
		
		/** Second, iterate through all Snippets, use only In-Active ones! */
		if ( $snippets ) {
			
			$wp_admin_bar->add_node( array(
				'id'     => 'snqn-inactive',
				//'title'  => esc_html__( 'Inactive Snippets', 'snippets-quicknav' ) . ' (' . $count_inactive . ')',
				'href'   => esc_url( $this->get_url( 'admin.php?page=snippets&status=inactive' ) ),
				'parent' => 'snqn-group-status',
			) );
			
			foreach ( $snippets as $snippet ) {
				
				/** List only In-active snippets for now */
				if ( $snippet->active ) {
					continue;
				}
		
				$count_inactive++;
		
				$edit_link = $this->get_url( 'admin.php?page=edit-snippet&id=' . intval( $snippet->id ) );
				
				$scope = sprintf(
					' <span class="scope">%s</span>',
					esc_html( $snippet->scope )
				);
				
				$wp_admin_bar->add_node( array(
					'id'     => 'snqn-snippet-' . intval( $snippet->id ),
					'title'  => esc_html( $snippet->display_name ) . $scope,
					'href'   => esc_url( $edit_link ),
					'parent' => 'snqn-inactive',
				) );
			}  // end foreach
		}  // end if - in-active check
		
		/** Populate the "Inactive" title string with counter result after foreach iteration */
		$title_inactive_node = $wp_admin_bar->get_node( 'snqn-inactive' );
		$title_inactive_node->title = esc_html__( 'Inactive Snippets', 'snippets-quicknav' ) . ' (' . $count_inactive . ')';
		$wp_admin_bar->add_node( $title_inactive_node );
		
		/** Set inactive counter for class */
		self::$snippets_inactive = $count_inactive;
	}

	/**
	 * Add group node for Snippets by type
	 */
	private function add_snippets_by_type_group( $wp_admin_bar ) {
		
		$wp_admin_bar->add_group( array(
			'id'     => 'snqn-group-types',
			'parent' => 'ddw-snippets-quicknav',
		) );
		
		$this->add_snippets_listings_submenu( $wp_admin_bar );
		$this->add_snippets_type_submenu( $wp_admin_bar );
	}
	
	/**
	 * Types Group: Add Snippets listings submenu (by status)
	 */
	private function add_snippets_listings_submenu( $wp_admin_bar ) {
		
		$wp_admin_bar->add_node( array(
			'id'     => 'snqn-snippets-listings',
			'title'  => esc_html__( 'Snippets by Status', 'snippets-quicknav' ),
			'href'   => esc_url( $this->get_url( 'admin.php?page=snippets' ) ),
			'parent' => 'snqn-group-types',
		) );

		/** Set the counter for all snippets */
		$all_snippets = self::$snippets_active + self::$snippets_inactive;
			
		$status_submenus = array(
			'all'                => __( 'All Snippets', 'snippets-quicknav' ) . ' (' . $all_snippets . ')',
			'active'             => __( 'Active Snippets', 'snippets-quicknav' ) . ' (' . self::$snippets_active . ')',
			'inactive'           => __( 'Inactive Snippets', 'snippets-quicknav' ) . ' (' . self::$snippets_inactive . ')',
			'recently_activated' => __( 'Recently Active Snippets', 'snippets-quicknav' ),
		);
		
		/** Make status array filterable */
		apply_filters( 'ddw/quicknav/csn_status', $status_submenus );
		
		foreach ( $status_submenus as $tab => $title ) {
			$wp_admin_bar->add_node( array(
				'id'     => 'snqn-status-' . sanitize_key( $tab ),
				'title'  => esc_html( $title ),
				'href'   => esc_url( $this->get_url( 'admin.php?page=snippets&status=' . urlencode( $tab ) ) ),
				'parent' => 'snqn-snippets-listings',
			) );
		}  // end foreach
	}

	/**
	 * Types Group: Add Snippets by Type & Tag submenus
	 */
	private function add_snippets_type_submenu( $wp_admin_bar ) {
		
		$wp_admin_bar->add_node( array(
			'id'     => 'snqn-snippets-types',
			'title'  => esc_html__( 'Snippets by Type', 'snippets-quicknav' ),
			'href'   => esc_url( $this->get_url( 'admin.php?page=snippets' ) ),
			'parent' => 'snqn-group-types',
		) );
	
		$type_submenus = array(
			'php'  => array(
				'label' => __( 'Functions', 'snippets-quicknav' ),
				'badge' => '',
			),
			'html' => array(
				'label' => __( 'Content', 'snippets-quicknav' ),
				'badge' => '',
			),
		);
		
		if ( $this->is_pro_version_active() ) {
			$type_submenus[ 'css' ] = array(
				'label' => __( 'Styles', 'snippets-quicknav' ),
				'badge' => '',
			);
			
			$type_submenus[ 'js' ] = array(
				'label' => __( 'Scripts', 'snippets-quicknav' ),
				'badge' => '',
			);
				
			if ( $this->is_cloud_connected() ) {
				$type_submenus[ 'cloud' ] = array(
					'label' => __( 'Codevault', 'snippets-quicknav' ),
					'badge' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C15.866 2 19 5.13401 19 9C19 9.11351 18.9973 9.22639 18.992 9.33857C21.3265 10.16 23 12.3846 23 15C23 18.3137 20.3137 21 17 21H7C3.68629 21 1 18.3137 1 15C1 12.3846 2.67346 10.16 5.00804 9.33857C5.0027 9.22639 5 9.11351 5 9C5 5.13401 8.13401 2 12 2ZM12 4C9.23858 4 7 6.23858 7 9C7 9.08147 7.00193 9.16263 7.00578 9.24344L7.07662 10.7309L5.67183 11.2252C4.0844 11.7837 3 13.2889 3 15C3 17.2091 4.79086 19 7 19H17C19.2091 19 21 17.2091 21 15C21 12.79 19.21 11 17 11C15.233 11 13.7337 12.1457 13.2042 13.7347L11.3064 13.1021C12.1005 10.7185 14.35 9 17 9C17 6.23858 14.7614 4 12 4Z"></path></svg>',
				);
				
				$type_submenus[ 'cloud_search' ] = array(
					'label' => __( 'Cloud Search', 'snippets-quicknav' ),
					'badge' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M18.031 16.6168L22.3137 20.8995L20.8995 22.3137L16.6168 18.031C15.0769 19.263 13.124 20 11 20C6.032 20 2 15.968 2 11C2 6.032 6.032 2 11 2C15.968 2 20 6.032 20 11C20 13.124 19.263 15.0769 18.031 16.6168ZM16.0247 15.8748C17.2475 14.6146 18 12.8956 18 11C18 7.1325 14.8675 4 11 4C7.1325 4 4 7.1325 4 11C4 14.8675 7.1325 18 11 18C12.8956 18 14.6146 17.2475 15.8748 16.0247L16.0247 15.8748Z"></path></svg>',
				);
				$type_submenus[ 'bundles' ] = array(
					'label' => __( 'Bundles', 'snippets-quicknav' ),
					'badge' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M20 3L22 7V20C22 20.5523 21.5523 21 21 21H3C2.44772 21 2 20.5523 2 20V7.00353L4 3H20ZM20 9H4V19H20V9ZM12 10L16 14H13V18H11V14H8L12 10ZM18.764 5H5.236L4.237 7H19.764L18.764 5Z"></path></svg>',
				);
			}  // end if Cloud check
		}  // end if Pro version check
		
		/** Make types array filterable */
		apply_filters( 'ddw/quicknav/csn_types', $type_submenus );

		$badge = '';
		
		foreach ( $type_submenus as $type => $name ) {
			$badge = sprintf(
				' <span class="cs-badge badge-type badge-%1$s">%2$s</span>',
				sanitize_key( $type ),
				( ! empty( $name[ 'badge' ] ) ) ? stripcslashes( $name[ 'badge' ] ) : sanitize_key( $type )
			);
			
			$wp_admin_bar->add_node( array(
				'id'     => 'snqn-type-' . sanitize_key( $type ),
				'title'  => esc_html( $name[ 'label' ] ) . $badge,
				'href'   => esc_url( $this->get_url( 'admin.php?page=snippets&type=' . urlencode( $type ) ) ),
				'parent' => 'snqn-snippets-types',
				'meta'   => array( 'class' => 'has-badge' ),
			) );
		}  // end foreach
		
		/** Get all Snippet Tags from the DB (official operator) */
		$snippet_tags = \Code_Snippets\get_all_snippet_tags();
		
		/** Sort Tags alphabetically */
		asort( $snippet_tags, SORT_STRING | SORT_FLAG_CASE );
		
		$wp_admin_bar->add_node( array(
			'id'     => 'snqn-snippets-tags',
			'title'  => esc_html__( 'Snippets by Tag', 'snippets-quicknav' ),
			'href'   => esc_url( $this->get_url( 'admin.php?page=snippets' ) ),
			'parent' => 'snqn-group-types',
		) );
		
		$icon = '<span class="icon-svg"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M10.9042 2.10025L20.8037 3.51446L22.2179 13.414L13.0255 22.6063C12.635 22.9969 12.0019 22.9969 11.6113 22.6063L1.71184 12.7069C1.32131 12.3163 1.32131 11.6832 1.71184 11.2926L10.9042 2.10025ZM11.6113 4.22157L3.83316 11.9997L12.3184 20.485L20.0966 12.7069L19.036 5.28223L11.6113 4.22157ZM13.7327 10.5855C12.9516 9.80448 12.9516 8.53815 13.7327 7.7571C14.5137 6.97606 15.78 6.97606 16.5611 7.7571C17.3421 8.53815 17.3421 9.80448 16.5611 10.5855C15.78 11.3666 14.5137 11.3666 13.7327 10.5855Z"></path></svg></span> ';
		
		foreach ( $snippet_tags as $tag ) {	
			$wp_admin_bar->add_node( array(
				'id'     => 'snqn-tag-' . sanitize_key( $tag ),
				'title'  => $icon . sanitize_key( $tag ),
				'href'   => esc_url( $this->get_url( 'admin.php?page=snippets&tag=' . urlencode( $tag ) ) ),
				'parent' => 'snqn-snippets-tags',
				'meta'   => array( 'class' => 'has-icon' ),
			) );
		}  // end foreach
	}

	/**
	 * Add group node for New Snippets
	 */
	private function add_snippets_new_group( $wp_admin_bar ) {
		
		$wp_admin_bar->add_group( array(
			'id'     => 'snqn-group-new',
			'parent' => 'ddw-snippets-quicknav',
		) );
		
		$this->add_snippets_new_submenu( $wp_admin_bar );
	}

	/**
	 * New Snippets Group: Add Code Snippets - New submenu
	 */
	private function add_snippets_new_submenu( $wp_admin_bar ) {
		
		/* translators: addition to label in Network Admin (Multisite) */
		$for_network = sprintf( ' (%s)', esc_html__( 'Network', 'snippets-quicknav' ) );
		$for_network = ( is_network_admin() ) ? $for_network : '';
		
		$addnew_types = array(
			'php'  => __( 'Function', 'snippets-quicknav' ),
			'html' => __( 'Content', 'snippets-quicknav' ),
		);
		
		if ( $this->is_pro_version_active() ) {
			$addnew_types[ 'css' ] = __( 'Style', 'snippets-quicknav' );
			$addnew_types[ 'js' ]  = __( 'Script', 'snippets-quicknav' );
		}
		
		$icon_add = '<span class="icon-svg"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M13.0001 10.9999L22.0002 10.9997L22.0002 12.9997L13.0001 12.9999L13.0001 21.9998L11.0001 21.9998L11.0001 12.9999L2.00004 13.0001L2 11.0001L11.0001 10.9999L11 2.00025L13 2.00024L13.0001 10.9999Z"></path></svg></span> ';
		
		/** Add New Snippet – also by type */
		$wp_admin_bar->add_node( array(
			'id'     => 'snqn-add-snippet',
			'title'  => $icon_add . esc_html__( 'Add New', 'snippets-quicknav' ) . $for_network,
			'href'   => esc_url( $this->get_url( 'admin.php?page=add-snippet' ) ),
			'parent' => 'snqn-group-new',
			'meta'   => array( 'class' => 'has-icon' ),
		) );
	
		$badge = '';
		
		foreach ( $addnew_types as $tab => $title ) {				
			$badge = sprintf(
				' <span class="cs-badge badge-type badge-%1$s">%1$s</span>',
				sanitize_key( $tab )
			);
				
			$wp_admin_bar->add_node( array(
				'id'     => 'snqn-newtype-' . sanitize_key( $tab ),
				'title'  => esc_html( $title ) . $badge,
				'href'   => esc_url( $this->get_url( 'admin.php?page=add-snippet&type=' . urlencode( $tab ) ) ),
				'parent' => 'snqn-add-snippet',
				'meta'   => array( 'class' => 'has-badge' ),
			) );
		}  // end foreach
	
		$icon_import = '<span class="icon-svg"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M13 10H18L12 16L6 10H11V3H13V10ZM4 19H20V12H22V20C22 20.5523 21.5523 21 21 21H3C2.44772 21 2 20.5523 2 20V12H4V19Z"></path></svg></svg></span> ';
	
		$wp_admin_bar->add_node( array(
			'id'     => 'snqn-import-snippet',
			'title'  => $icon_import . esc_html__( 'Import', 'snippets-quicknav' ) . $for_network,
			'href'   => esc_url( $this->get_url( 'admin.php?page=import-code-snippets' ) ),
			'parent' => 'snqn-group-new',
			'meta'   => array( 'class' => 'has-icon' ),
		) );
		
		/** WP's own "New Content" section: Add New Snippet - also by type */
		$wp_admin_bar->add_node( array(
			'id'     => 'snqn-wpnewcontent-add-snippet',
			'title'  => esc_html__( 'Code Snippet', 'snippets-quicknav' ) . $for_network,
			'href'   => esc_url( $this->get_url( 'admin.php?page=add-snippet' ) ),
			'parent' => 'new-content',
		) );
		
		foreach ( $addnew_types as $tab => $title ) {		
			$badge = sprintf(
				' <span class="cs-badge badge-type badge-%1$s">%1$s</span>',
				sanitize_key( $tab )
			);
			
			$wp_admin_bar->add_node( array(
				'id'     => 'snqn-wpnc-newtype-' . sanitize_key( $tab ),
				'title'  => esc_html( $title ) . $badge,
				'href'   => esc_url( $this->get_url( 'admin.php?page=add-snippet&type=' . urlencode( $tab ) ) ),
				'parent' => 'snqn-wpnewcontent-add-snippet',
				'meta'   => array( 'class' => 'has-badge' ),
			) );
		}  // end foreach
		
		$wp_admin_bar->add_node( array(
			'id'     => 'snqn-wpnewcontent-import-snippet',
			'title'  => esc_html__( 'Import Snippet', 'snippets-quicknav' ) . $for_network,
			'href'   => esc_url( $this->get_url( 'admin.php?page=import-code-snippets' ) ),
			'parent' => 'new-content',
		) );
	}

	/**
	 * Add group node for Code Snippets settings
	 */
	private function add_settings_group( $wp_admin_bar ) {
		
		$wp_admin_bar->add_group( array(
			'id'     => 'snqn-group-settings',
			'parent' => 'ddw-snippets-quicknav',
		) );
		
		$this->add_settings_submenu( $wp_admin_bar );
		$this->add_expert_submenu( $wp_admin_bar );
	}
	
	/**
	 * Add Code Snippets Settings submenu
	 */
	private function add_settings_submenu( $wp_admin_bar ) {
		
		$icon = '<span class="icon-svg"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M5.32943 3.27158C6.56252 2.8332 7.9923 3.10749 8.97927 4.09446C10.1002 5.21537 10.3019 6.90741 9.5843 8.23385L20.293 18.9437L18.8788 20.3579L8.16982 9.64875C6.84325 10.3669 5.15069 10.1654 4.02952 9.04421C3.04227 8.05696 2.7681 6.62665 3.20701 5.39332L5.44373 7.63C6.02952 8.21578 6.97927 8.21578 7.56505 7.63C8.15084 7.04421 8.15084 6.09446 7.56505 5.50868L5.32943 3.27158ZM15.6968 5.15512L18.8788 3.38736L20.293 4.80157L18.5252 7.98355L16.7574 8.3371L14.6361 10.4584L13.2219 9.04421L15.3432 6.92289L15.6968 5.15512ZM8.97927 13.2868L10.3935 14.7011L5.09018 20.0044C4.69966 20.3949 4.06649 20.3949 3.67597 20.0044C3.31334 19.6417 3.28744 19.0699 3.59826 18.6774L3.67597 18.5902L8.97927 13.2868Z"></path></svg></span> ';
		
		$wp_admin_bar->add_node( array(
			'id'     => 'snqn-settings',
			'title'  => $icon . esc_html__( 'Settings', 'snippets-quicknav' ),
			'href'   => esc_url( $this->get_settings_url( 'admin.php?page=snippets-settings' ) ),
			'parent' => 'snqn-group-settings',
		) );

		$settings_submenus = array(
			'general' => __( 'General', 'snippets-quicknav' ),
			'editor'  => __( 'Code Editor', 'snippets-quicknav' ),
			'debug'   => __( 'Debug', 'snippets-quicknav' ),
		);
		
		/** Make settings array filterable */
		apply_filters( 'ddw/quicknav/csn_settings', $settings_submenus );
		
		foreach ( $settings_submenus as $tab => $title ) {
			$wp_admin_bar->add_node( array(
				'id'     => 'snqn-settings-' . sanitize_key( $tab ),
				'title'  => esc_html( $title ),
				'href'   => esc_url( $this->get_settings_url( 'admin.php?page=snippets-settings&section=' . urlencode( $tab ) ) ),
				'parent' => 'snqn-settings',
			) );
		}  // end foreach
		
		$wp_admin_bar->add_node( array(
			'id'     => 'snqn-settings-welcome',
			'title'  => esc_html__( "What's New", "snippets-quicknav" ),
			'href'   => esc_url( $this->get_url( 'admin.php?page=code-snippets-welcome' ) ),
			'parent' => 'snqn-settings',
		) );
		
		/** "Account" – only in Pro version */
		if ( $this->is_pro_version_active() ) {
			$wp_admin_bar->add_node( array(
				'id'     => 'snqn-settings-account',
				'title'  => esc_html__( 'Account', 'snippets-quicknav' ),
				'href'   => esc_url( network_admin_url( 'admin.php?page=snippets-account' ) ),
				'parent' => 'snqn-settings',
			) );
		}  // end if Pro check
	}

	private function add_expert_submenu( $wp_admin_bar ) {
		
		if ( ! $this->is_expert_mode() ) return $wp_admin_bar;
		
		$icon_info = '<span class="icon-svg"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M20 22H4C3.44772 22 3 21.5523 3 21V3C3 2.44772 3.44772 2 4 2H20C20.5523 2 21 2.44772 21 3V21C21 21.5523 20.5523 22 20 22ZM19 20V4H5V20H19ZM7 6H11V10H7V6ZM7 12H17V14H7V12ZM7 16H17V18H7V16ZM13 7H17V9H13V7Z"></path></svg></span> ';
		
		$wp_admin_bar->add_node( array(
			'id'     => 'snqn-sitehealth-info',
			'title'  => $icon_info . esc_html__( 'Site Health Info', 'snippets-quicknav' ),
			'href'   => esc_url( admin_url( 'site-health.php?tab=debug' ) ),
			'parent' => 'snqn-group-settings',
			'meta'   => array( 'class' => 'has-icon' ),
		) );
		
		$icon_code = '<span class="icon-svg"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M3 3H21C21.5523 3 22 3.44772 22 4V20C22 20.5523 21.5523 21 21 21H3C2.44772 21 2 20.5523 2 20V4C2 3.44772 2.44772 3 3 3ZM4 5V19H20V5H4ZM12 15H18V17H12V15ZM8.66685 12L5.83842 9.17157L7.25264 7.75736L11.4953 12L7.25264 16.2426L5.83842 14.8284L8.66685 12Z"></path></svg></span> ';
		
		if ( defined( 'VARIABLE_INSPECTOR_VERSION' ) ) {
			$wp_admin_bar->add_node( array(
				'id'     => 'snqn-variable-inspector',
				'title'  => $icon_code . esc_html__( 'Variable Inspector', 'snippets-quicknav' ),
				'href'   => esc_url( admin_url( 'tools.php?page=variable-inspector' ) ),
				'parent' => 'snqn-group-settings',
				'meta'   => array( 'class' => 'has-icon' ),
			) );
		}
		
		$icon_bug = '<span class="icon-svg"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M13 19.9C15.2822 19.4367 17 17.419 17 15V12C17 11.299 16.8564 10.6219 16.5846 10H7.41538C7.14358 10.6219 7 11.299 7 12V15C7 17.419 8.71776 19.4367 11 19.9V14H13V19.9ZM5.5358 17.6907C5.19061 16.8623 5 15.9534 5 15H2V13H5V12C5 11.3573 5.08661 10.7348 5.2488 10.1436L3.0359 8.86602L4.0359 7.13397L6.05636 8.30049C6.11995 8.19854 6.18609 8.09835 6.25469 8H17.7453C17.8139 8.09835 17.88 8.19854 17.9436 8.30049L19.9641 7.13397L20.9641 8.86602L18.7512 10.1436C18.9134 10.7348 19 11.3573 19 12V13H22V15H19C19 15.9534 18.8094 16.8623 18.4642 17.6907L20.9641 19.134L19.9641 20.866L17.4383 19.4077C16.1549 20.9893 14.1955 22 12 22C9.80453 22 7.84512 20.9893 6.56171 19.4077L4.0359 20.866L3.0359 19.134L5.5358 17.6907ZM8 6C8 3.79086 9.79086 2 12 2C14.2091 2 16 3.79086 16 6H8Z"></path></svg></span> ';
		
		/**
		 * We need double check here as there is the "Downdload Manager" plugin
		 *   with the same 'DLM' prefix & constant.
		 */
		if ( defined( 'DLM_SLUG' ) && 'debug-log-manager' === DLM_SLUG ) {
			$wp_admin_bar->add_node( array(
				'id'     => 'snqn-debuglog-manager',
				'title'  => $icon_bug . esc_html__( 'Debug Log Manager', 'snippets-quicknav' ),
				'href'   => esc_url( admin_url( 'tools.php?page=debug-log-manager' ) ),
				'parent' => 'snqn-group-settings',
				'meta'   => array( 'class' => 'has-icon' ),
			) );
		}
		
		add_action( 'admin_bar_menu', array( $this, 'remove_adminbar_nodes' ), 9999 );
	}
	
	/**
	 * Add group node for Snippet Library items (external links)
	 */
	private function add_library_group( $wp_admin_bar ) {
		
		if ( defined( 'SNQN_DISABLE_LIBRARY' ) && 'yes' === sanitize_key( SNQN_DISABLE_LIBRARY ) ) {
			return $wp_admin_bar;
		}
		
		$wp_admin_bar->add_group( array(
			'id'     => 'snqn-library',
			'parent' => 'ddw-snippets-quicknav',
			'meta'   => array( 'class' => 'ab-sub-secondary' ),
		) );
	}

	/**
	 * Libraries Group: Add linked Libraries submenu
	 */
	private function add_libraries_submenu( $wp_admin_bar ) {
		
		$icon = '<span class="icon-svg"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12L18.3431 17.6569L16.9289 16.2426L21.1716 12L16.9289 7.75736L18.3431 6.34315L24 12ZM2.82843 12L7.07107 16.2426L5.65685 17.6569L0 12L5.65685 6.34315L7.07107 7.75736L2.82843 12ZM9.78845 21H7.66009L14.2116 3H16.3399L9.78845 21Z"></path></svg></span> ';
		
		$wp_admin_bar->add_node( array(
			'id'     => 'snqn-libraries',
			'title'  => $icon . esc_html__( 'Find Snippets', 'snippets-quicknav' ),
			'href'   => '#',
			'parent' => 'snqn-footer',
			'meta'   => array( 'class' => 'has-icon' ),
		) );
	
		$codelibs = array(
			'codesnippets-cloud' => array(
				'title' => __( 'Code Snippets Cloud', 'snippets-quicknav' ),
				'url'   => 'https://codesnippets.cloud/search',
			),
			'wpsnippets-library' => array(
				'title' => __( 'WP Snippets Library', 'snippets-quicknav' ),
				'url'   => 'https://wpsnippets.org/library/',
			),
			'websquadron-codes' => array(
				'title' => __( 'Codes by Web Squadron', 'snippets-quicknav' ),
				'url'   => 'https://learn.websquadron.co.uk/codes/',
			),
			'wpsnippetclub-archive' => array(
				'title' => __( 'WP SnippetClub Archive', 'snippets-quicknav' ),
				'url'   => 'https://wpsnippet.club/snippet/',
			),
			'dplugins-code' => array(
				'title' => __( 'Snippets Library by dPlugins', 'snippets-quicknav' ),
				'url'   => 'https://code.dplugins.com/',
			),
			'wpcodebin' => array(
				'title' => __( 'WPCodeBin by WPCodeBox', 'snippets-quicknav' ),
				'url'   => 'https://wpcodebin.com/',
			),
			'wpcode-library' => array(
				'title' => __( 'Snippets Library by WPCode', 'snippets-quicknav' ),
				'url'   => 'https://library.wpcode.com/',
			),
		);
	
		/** Make code libs array filterable */
		apply_filters( 'ddw/quicknav/csn_codelibs', $codelibs );
	
		foreach ( $codelibs as $id => $info ) {
			$wp_admin_bar->add_node( array(
				'id'     => 'snqn-link-' . sanitize_key( $id ),
				'title'  => esc_html( $info[ 'title' ] ),
				'href'   => esc_url( $info[ 'url' ] ),
				'parent' => 'snqn-libraries',
				'meta'   => array( 'target' => '_blank', 'rel' => 'nofollow noopener noreferrer' ),
			) );
		}  // end foreach
	}
					
	/**
	 * Add group node for footer items (Links & About)
	 */
	private function add_footer_group( $wp_admin_bar ) {
		
		if ( defined( 'SNQN_DISABLE_FOOTER' ) && 'yes' === sanitize_key( SNQN_DISABLE_FOOTER ) ) {
			return $wp_admin_bar;
		}
		
		$wp_admin_bar->add_group( array(
			'id'     => 'snqn-footer',
			'parent' => 'ddw-snippets-quicknav',
			'meta'   => array( 'class' => 'ab-sub-secondary' ),
		) );
	}
	
	/**
	 * Footer Group: Add Links submenu
	 */
	private function add_links_submenu( $wp_admin_bar ) {
		
		$icon = '<span class="icon-svg"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M10 6V8H5V19H16V14H18V20C18 20.5523 17.5523 21 17 21H4C3.44772 21 3 20.5523 3 20V7C3 6.44772 3.44772 6 4 6H10ZM21 3V11H19L18.9999 6.413L11.2071 14.2071L9.79289 12.7929L17.5849 5H13V3H21Z"></path></svg></span> ';
		
		$wp_admin_bar->add_node( array(
			'id'     => 'snqn-links',
			'title'  => $icon . esc_html__( 'Links', 'snippets-quicknav' ),
			'href'   => '#',
			'parent' => 'snqn-footer',
			'meta'   => array( 'class' => 'has-icon' ),
		) );

		$links = array(
			'codesnippets' => array(
				'title' => __( 'Code Snippets HQ', 'snippets-quicknav' ),
				'url'   => 'https://codesnippets.pro/',
			),
			'codesnippets-docs' => array(
				'title' => __( 'Plugin´s Documentation', 'snippets-quicknav' ),
				'url'   => 'https://codesnippets.pro/docs/',
			),
			'codesnippets-emergency' => array(
				'title' => __( 'Emergency Fixes 🔥', 'snippets-quicknav' ),
				'url'   => 'https://codesnippets.pro/docs/emergency-fixes/',
			),
			'codesnippets-learn' => array(
				'title' => __( 'Learning Ressources', 'snippets-quicknav' ),
				'url'   => 'https://codesnippets.pro/learn/',
			),
			'codesnippets-blog' => array(
				'title' => __( 'Code Snippets Blog', 'snippets-quicknav' ),
				'url'   => 'https://codesnippets.pro/blog/',
			),
			'codesnippets-youtube' => array(
				'title' => __( 'CS YouTube Channel', 'snippets-quicknav' ),
				'url'   => 'https://www.youtube.com/c/CodeSnippetsPro',
			),
			'codesnippets-fb-group' => array(
				'title' => __( 'CS Facbook Group (official)', 'snippets-quicknav' ),
				'url'   => 'https://www.facebook.com/groups/codesnippetsplugin',
			),
			'codesnippets-discord' => array(
				'title' => __( 'CS Discord Community (official)', 'snippets-quicknav' ),
				'url'   => 'https://snipco.de/discord',
			),
			'codesnippets-status' => array(
				'title' => __( 'CS Status Page (official)', 'snippets-quicknav' ),
				'url'   => 'https://status.codesnippets.pro/',
			),
			'codesnippets-dev' => array(
				'title' => __( 'CS GitHub Development (official)', 'snippets-quicknav' ),
				'url'   => 'https://github.com/codesnippetspro/code-snippets',
			),
		);

		/** Make links array filterable */
		apply_filters( 'ddw/quicknav/csn_links', $links );
		
		foreach ( $links as $id => $info ) {
			$wp_admin_bar->add_node( array(
				'id'     => 'snqn-link-' . sanitize_key( $id ),
				'title'  => esc_html( $info[ 'title' ] ),
				'href'   => esc_url( $info[ 'url' ] ),
				'parent' => 'snqn-links',
				'meta'   => array( 'target' => '_blank', 'rel' => 'nofollow noopener noreferrer' ),
			) );
		}  // end foreach
	}

	/**
	 * Footer Group: Add About submenu
	 */
	private function add_about_submenu( $wp_admin_bar ) {
		
		$icon = '<span class="icon-svg"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M17.841 15.659L18.017 15.836L18.1945 15.659C19.0732 14.7803 20.4978 14.7803 21.3765 15.659C22.2552 16.5377 22.2552 17.9623 21.3765 18.841L18.0178 22.1997L14.659 18.841C13.7803 17.9623 13.7803 16.5377 14.659 15.659C15.5377 14.7803 16.9623 14.7803 17.841 15.659ZM12 14V16C8.68629 16 6 18.6863 6 22H4C4 17.6651 7.44784 14.1355 11.7508 14.0038L12 14ZM12 1C15.315 1 18 3.685 18 7C18 10.2397 15.4357 12.8776 12.225 12.9959L12 13C8.685 13 6 10.315 6 7C6 3.76034 8.56434 1.12237 11.775 1.00414L12 1ZM12 3C9.78957 3 8 4.78957 8 7C8 9.21043 9.78957 11 12 11C14.2104 11 16 9.21043 16 7C16 4.78957 14.2104 3 12 3Z"></path></svg></span> ';
		
		$wp_admin_bar->add_node( array(
			'id'     => 'snqn-about',
			'title'  => $icon . esc_html__( 'About', 'snippets-quicknav' ),
			'href'   => '#',
			'parent' => 'snqn-footer',
			'meta'   => array( 'class' => 'has-icon' ),
		) );

		$about_links = array(
			'author' => array(
				'title' => __( 'Author: David Decker', 'snippets-quicknav' ),
				'url'   => 'https://deckerweb.de/',
			),
			'github' => array(
				'title' => __( 'Plugin on GitHub', 'snippets-quicknav' ),
				'url'   => 'https://github.com/deckerweb/snippets-quicknav',
			),
			'kofi' => array(
				'title' => __( 'Buy Me a Coffee', 'snippets-quicknav' ),
				'url'   => 'https://ko-fi.com/deckerweb',
			),
		);

		foreach ( $about_links as $id => $info ) {
			$wp_admin_bar->add_node( array(
				'id'     => 'snqn-about-' . sanitize_key( $id ),
				'title'  => esc_html( $info[ 'title' ] ),
				'href'   => esc_url( $info[ 'url' ] ),
				'parent' => 'snqn-about',
				'meta'   => array( 'target' => '_blank', 'rel' => 'nofollow noopener noreferrer' ),
			) );
		}  // end foreach
	}
	
	/**
	 * Show the Admin Bar also in Block Editor full screen mode.
	 */
	public function adminbar_block_editor_fullscreen() {
		
		if ( ! is_admin_bar_showing() ) {
			return;
		}
		
		/**
		 * Depending on user color scheme get proper bg color value for admin bar.
		 */
		$user_color_scheme = get_user_option( 'admin_color' );
		$admin_scheme      = $this->get_scheme_colors();
		
		$bg_color = $admin_scheme[ $user_color_scheme ][ 'bg' ];
		
		$inline_css = sprintf(
			'
				@media (min-width: 600px) {
					body.is-fullscreen-mode .block-editor__container {
						top: var(--wp-admin--admin-bar--height);
					}
				}
				
				@media (min-width: 782px) {
					body.js.is-fullscreen-mode #wpadminbar {
						display: block;
					}
				
					body.is-fullscreen-mode .block-editor__container {
						min-height: calc(100vh - var(--wp-admin--admin-bar--height));
					}
				
					body.is-fullscreen-mode .edit-post-layout .editor-post-publish-panel {
						top: var(--wp-admin--admin-bar--height);
					}
					
					.edit-post-fullscreen-mode-close.components-button {
						background: %s;
					}
					
					.edit-post-fullscreen-mode-close.components-button::before {
						box-shadow: none;
					}
				}
				
				@media (min-width: 783px) {
					.is-fullscreen-mode .interface-interface-skeleton {
						top: var(--wp-admin--admin-bar--height);
					}
				}
			',
			sanitize_hex_color( $bg_color )
		);
		
		wp_add_inline_style( 'wp-block-editor', $inline_css );
		
		add_action( 'admin_bar_menu', array( $this, 'remove_adminbar_nodes' ), 999 );
	}
	
	/**
	 * Remove Admin Bar nodes.
	 */
	public function remove_adminbar_nodes( $wp_admin_bar ) {
		$wp_admin_bar->remove_node( 'wp-logo' );  
	}
	
	/**
	 * Fair play: If you want the Pro promotions go away, use the following snippet:
	 *   define( 'SNQN_FREE_CS_FREE', 'yes' );
	 *   ... or just purchase the Pro version to support the developers. Thank you in advance!
	 */
	public function free_cs_free() {
		
		if ( defined( 'SNQN_FREE_CS_FREE' ) && 'yes' === sanitize_key( SNQN_FREE_CS_FREE ) ) {
		
			/** Disable welcome banner */
			add_filter( 'code_snippets/hide_welcome_banner', '__return_true' );
			
			/** Remove Pro Promos (but only if in free version) */
			if ( ! $this->is_pro_version_active() ) {
				add_action( 'admin_enqueue_scripts', array( $this, 'free_cs_free_admin_inline_styles' ), 50 );
				
				/** Remove Go Pro submenu within CS Admin Menu */
				$settings = get_option( 'code_snippets_settings' );
				$settings[ 'general' ][ 'hide_upgrade_menu' ] = 1;
				update_option( 'code_snippets_settings', $settings );
			}

		}  // end if
	}
	
	/**
	 * Add additional inline styles on the admin pages.
	 */
	public function free_cs_free_admin_inline_styles() {
	
		/**
		 * For WordPress Admin Area
		 *   Style handle: 'wp-admin' (WordPress Core)
		 */
		$inline_css = sprintf(
			'		
			/** Plugin: Code Snippets (free) */
			#snippet-type-tabs .go-pro-button,
			#snippet-type-tabs a[data-snippet-type="css"],
			#snippet-type-tabs a[data-snippet-type="js"],
			#snippet-type-tabs a[data-snippet-type="cloud"],
			#snippet-type-tabs a[data-snippet-type="cloud_search"],
			#snippet-type-tabs a[data-snippet-type="bundles"] {
				display: none;
			}
			'
		);
	
		wp_add_inline_style( 'wp-admin', $inline_css );
	
	}  // end function
	
	/**
	 * Add additional plugin related info to the Site Health Debug Info section.
	 *
	 * @link https://make.wordpress.org/core/2019/04/25/site-health-check-in-5-2/
	 *
	 * @param array $debug_info Array holding all Debug Info items.
	 * @return array Modified array of Debug Info.
	 */
	public function site_health_debug_info( $debug_info ) {
	
		$string_undefined = esc_html_x( 'Undefined', 'Site Health Debug info', 'snippets-quicknav' );
		$string_enabled   = esc_html_x( 'Enabled', 'Site Health Debug info', 'snippets-quicknav' );
		$string_disabled  = esc_html_x( 'Disabled', 'Site Health Debug info', 'snippets-quicknav' );
		$string_value     = ' – ' . esc_html_x( 'value', 'Site Health Debug info', 'snippets-quicknav' ) . ': ';
		$string_version   = defined( 'CODE_SNIPPETS_VERSION' ) ? CODE_SNIPPETS_VERSION : '';
		$string_free_pro  = sprintf(
			esc_html__( '%s version', 'snippets-quicknav' ) . ' (' . $string_version . ')',
			( $this->is_pro_version_active() ) ? esc_html__( 'Pro', 'snippets-quicknav' ) : esc_html__( 'free', 'snippets-quicknav' )
		);
	
		/** Add our Debug info */
		$debug_info[ 'snippets-quicknav' ] = array(
			'label'  => esc_html__( 'Snippets QuickNav', 'snippets-quicknav' ) . ' (' . esc_html__( 'Plugin', 'snippets-quicknav' ) . ')',
			'fields' => array(
	
				/** Various values */
				'snqn_plugin_version' => array(
					'label' => esc_html__( 'Plugin version', 'snippets-quicknav' ),
					'value' => self::VERSION,
				),
				'snqn_install_type' => array(
					'label' => esc_html__( 'WordPress Install Type', 'snippets-quicknav' ),
					'value' => ( is_multisite() ? esc_html__( 'Multisite install', 'snippets-quicknav' ) : esc_html__( 'Single Site install', 'snippets-quicknav' ) ),
				),
	
				/** Snippets QuickNav constants */
				'SNQN_VIEW_CAPABILITY' => array(
					'label' => 'SNQN_VIEW_CAPABILITY',
					'value' => ( ! defined( 'SNQN_VIEW_CAPABILITY' ) ? $string_undefined : ( SNQN_VIEW_CAPABILITY ? $string_enabled : $string_disabled ) ),
				),
				'SNQN_NAME_IN_ADMINBAR' => array(
					'label' => 'SNQN_NAME_IN_ADMINBAR',
					'value' => ( ! defined( 'SNQN_NAME_IN_ADMINBAR' ) ? $string_undefined : ( SNQN_NAME_IN_ADMINBAR ? $string_enabled . $string_value . esc_html( SNQN_NAME_IN_ADMINBAR )  : $string_disabled ) ),
				),
				'SNQN_COUNTER' => array(
					'label' => 'SNQN_COUNTER',
					'value' => ( ! defined( 'SNQN_COUNTER' ) ? $string_undefined : ( SNQN_COUNTER ? $string_enabled : $string_disabled ) ),
				),
				'SNQN_ICON' => array(
					'label' => 'SNQN_ICON',
					'value' => ( ! defined( 'SNQN_ICON' ) ? $string_undefined : ( SNQN_ICON ? $string_enabled . $string_value . sanitize_key( SNQN_ICON ) : $string_disabled ) ),
				),
				'SNQN_DISABLE_LIBRARY' => array(
					'label' => 'SNQN_DISABLE_LIBRARY',
					'value' => ( ! defined( 'SNQN_DISABLE_LIBRARY' ) ? $string_undefined : ( SNQN_DISABLE_LIBRARY ? $string_enabled : $string_disabled ) ),
				),
				'SNQN_DISABLE_FOOTER' => array(
					'label' => 'SNQN_DISABLE_FOOTER',
					'value' => ( ! defined( 'SNQN_DISABLE_FOOTER' ) ? $string_undefined : ( SNQN_DISABLE_FOOTER ? $string_enabled : $string_disabled ) ),
				),
				'SNQN_EXPERT_MODE' => array(
					'label' => 'SNQN_EXPERT_MODE',
					'value' => ( ! defined( 'SNQN_EXPERT_MODE' ) ? $string_undefined : ( SNQN_EXPERT_MODE ? $string_enabled : $string_disabled ) ),
				),
				'SNQN_FREE_CS_FREE' => array(
					'label' => 'SNQN_FREE_CS_FREE',
					'value' => ( ! defined( 'SNQN_FREE_CS_FREE' ) ? $string_undefined : ( SNQN_FREE_CS_FREE ? $string_enabled : $string_disabled ) ),
				),
				'SNQN_CODE_SNIPPETS_SAFE_MODE' => array(
					'label' => 'CODE_SNIPPETS_SAFE_MODE',
					'value' => ( ! defined( 'CODE_SNIPPETS_SAFE_MODE' ) ? $string_undefined : ( CODE_SNIPPETS_SAFE_MODE ? $string_enabled : $string_disabled ) ),
				),
				'SNQN_SCRIPT_DEBUG' => array(
					'label' => 'SCRIPT_DEBUG',
					'value' => ( ! defined( 'SCRIPT_DEBUG' ) ? $string_undefined : ( SCRIPT_DEBUG ? $string_enabled : $string_disabled ) ),
				),
				'snqn_cs_free_pro' => array(
					'label' => esc_html( 'Code Snippets Version', 'snippets-quicknav' ),
					'value' => ( ! defined( 'CODE_SNIPPETS_VERSION' ) ? esc_html__( 'Plugin not installed', 'snippets-quicknav' ) : $string_free_pro ),
				),
			),  // end array
		);
	
		/** Return modified Debug Info array */
		return $debug_info;
	
	}  // end function

}  // end of class

/** Start instance of Class */
new DDW_Snippets_QuickNav();
	
endif;