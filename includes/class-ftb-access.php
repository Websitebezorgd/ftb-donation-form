<?php
/**
 * Who may manage the plugin (settings and donations).
 *
 * @since      1.0.3
 * @package    FTB_Donation_Form
 * @subpackage FTB_Donation_Form/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Applies the access choice from "Toegang en beheer" to the actual WordPress capabilities.
 *
 * The choice is stored in the options ftb_editor_access_mode and ftb_designated_managers;
 * WordPress itself checks the ftb_manage_settings capability. Saving the choice and
 * activating the plugin both go through apply(), so the two never drift apart.
 */
class FTB_Access {

	const CAPABILITY = 'ftb_manage_settings';

	/** Access modes: administrators only, all editors, chosen editors only. */
	const MODES = array( 'admin_only', 'all', 'specific' );

	/** Mode for a new installation. */
	const DEFAULT_MODE = 'admin_only';

	/**
	 * Give or take the capability according to the access mode.
	 *
	 * @param string $mode         One of self::MODES; anything else falls back to the default.
	 * @param int[]  $user_ids     Chosen editors (only used in "specific" mode).
	 * @param int[]  $previous_ids Previously chosen editors, whose capability is removed
	 *                             unless they are still chosen.
	 * @return int[] The chosen editors that actually got access (users that no longer
	 *               exist or are no longer an editor are left out). Store this list.
	 */
	public static function apply( string $mode, array $user_ids, array $previous_ids = array() ): array {
		$mode = in_array( $mode, self::MODES, true ) ? $mode : self::DEFAULT_MODE;

		$admin = get_role( 'administrator' );
		if ( $admin ) {
			$admin->add_cap( self::CAPABILITY );
		}

		$editor = get_role( 'editor' );
		if ( $editor ) {
			if ( 'all' === $mode ) {
				$editor->add_cap( self::CAPABILITY );
			} else {
				$editor->remove_cap( self::CAPABILITY );
			}
		}

		$granted = array();
		if ( 'specific' === $mode ) {
			foreach ( array_map( 'absint', $user_ids ) as $id ) {
				$user = get_user_by( 'id', $id );
				if ( $user && in_array( 'editor', (array) $user->roles, true ) ) {
					$user->add_cap( self::CAPABILITY );
					$granted[] = (int) $user->ID;
				}
			}
		}

		foreach ( array_diff( array_map( 'absint', $previous_ids ), $granted ) as $id ) {
			$user = get_user_by( 'id', $id );
			if ( $user ) {
				$user->remove_cap( self::CAPABILITY );
			}
		}

		return array_values( array_unique( $granted ) );
	}
}
