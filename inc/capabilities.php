<?php
/**
* Capabilities.php, where we manage user permissions and access controls
**/
namespace CFPB\Utils\Capabilities;

class Custom_Caps
{
	// Add our filters
	public static function filter_roles() {
		add_filter( 'editable_roles', array( $this, 'editable_roles' ) );
		add_filter( 'map_meta_cap', array( $this, 'map_meta_cap' ), 10, 4 );
		add_filter( 'ramp-user-access-base', array( $this, 'ramp_user_access_filter' ) );
	}
	
	/**
	* Prevent access to RAMP
	**/
	public static function ramp_user_access_filter( $level ) {
		return 'publish_pages';
	}

	// Remove 'Administrator' from the list of roles if the current user is not an admin
	function editable_roles( $roles ) {
		if ( isset( $roles['administrator'] ) && ! current_user_can( 'administrator' ) ) {
			unset( $roles['administrator'] );
		}
		return $roles;
	}

	// If someone is trying to edit or delete and admin and that user isn't an admin, don't allow it
	/**
	* Map primitive capabilities to meta capabilities
	*
	* Like the builtin map_meta_cap, this accepts four args and allows filtering of capabilities out of
	* less privileged users. In this case, we're modifying the ability to delete and modify 
	* administrators, restricting it to only admins.
	*
	* @param arr $caps an array of all available capabilities
	* @param str $cap name of the meta capability to map to
	* @param int $user_id the current or specified user ID
	* @param arr $args An array of extra arguments for the capability
	* 
	* @return arr An array of capabilities, should be hooked into map_meta_cap
	*
	* @see WordPress' [map_meta_cap filter](http://fotd.werdswords.com/map_meta_cap/)
	*
	**/
	public static function map_meta_cap( $caps, $cap, $user_id, $args ) {

		$user = wp_get_current_user();
		if ( $cap == 'promote_user' && ! current_user_can( 'administrator' ) ) {
			$caps[] = 'do_not_allow';
		} elseif ( $cap == 'delete_users' && ! current_user_can( 'administrator' ) ) {
			$caps[] = 'do_not_allow';
		}

		return $caps;
	}
}

$Custom_caps = new Custom_Caps();
?>
