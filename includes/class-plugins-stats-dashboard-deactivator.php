<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://dream-encode.com
 * @since      1.0.0
 *
 * @package    Plugins_Stats_Dashboard
 * @subpackage Plugins_Stats_Dashboard/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Plugins_Stats_Dashboard
 * @subpackage Plugins_Stats_Dashboard/includes
 * @author     David Baumwald <david.baumwald@gmail.com>
 */
class Plugins_Stats_Dashboard_Deactivator {

	/**
	 * Deactivation hook.
	 *
	 * Remove options set by the plugin.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {

		delete_option( 'plugins-stats-dashboard' );

		delete_transient( 'plugins-stats-dashboard-results' );
		delete_transient( 'plugins-stats-dashboard-current-stat' );
		
	}

}
