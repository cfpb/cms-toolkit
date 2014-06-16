<?php
use \CFPB\Utils\Capabilities\Custom_Caps;
class CapabilitiesTest extends PHPUnit_Framework_TestCase {

	function tearDown() {
		\WP_Mock::tearDown();
	}

	function setUp() {
		\WP_Mock::setUp();
	}

	function testRAMPPermissionsExpectsDegraded() {
        \WP_Mock::onFilter( 'ramp-user-access-base' )->with( $this->anything() )->reply( 'publish_pages' );
	}

	function testEditableRolesExpectsAdminRemoved() {
		// arrange
		$roles = array('author' => 1, 'editor' => 2, 'administrator' => 3);
		\WP_Mock::wpFunction('current_user_can', array(
			'times' => 1,
			'return' => false,
			)
		);
		$expected = array('author' => true, 'editor' => true);
		// act
		$Custom_Caps = new Custom_Caps();
		$actual = $Custom_Caps->editable_roles($roles);

		// assert
		$this->assertEquals($expected, $actual, 'Given an array containing key: administrator, editable_roles should remove it instead was not removed.');

	}

	function testRampUserAccessFilterExpectsPublishPages() {
		$this->assertEquals('publish_pages', \CFPB\Utils\Capabilities\Custom_Caps::ramp_user_access_filter('publish_pages') );
	}

	function testNonAdminPromoteUserMapMetaCapExpectsCapabilityRemoved() {
		// Arrage
		$caps = array();
		$cap = 'promote_user';
		$user_id = 1;
		$args = array();
		// $Custom_Caps = new Custom_Caps(0);

		\WP_Mock::wpFunction('current_user_can', array(
			'times' => 1,
			'return' => false,
			)
		);
		\WP_Mock::wpFunction('wp_get_current_user', array(
			'times' => 1,
			'return' => 1,
			)
		);
		$expected = array('do_not_allow');

		// Act
		$actual = Custom_Caps::map_meta_cap( $caps, $cap, $user_id, $args );
		// Assert
		$this->assertEquals($expected, $actual, 'User is not an admin but allowed to promote users.');
	}

	function testAdminPromoteUserMapMetaCapExpectsCapabilityLeftAlone() {
		// Arrage
		$caps = array();
		$cap = 'promote_user';
		$user_id = 1;
		$args = array();
		// $Custom_Caps = new Custom_Caps(0);

		\WP_Mock::wpFunction('current_user_can', array(
			'times' => 1,
			'return' => true,
			)
		);
		\WP_Mock::wpFunction('wp_get_current_user', array(
			'times' => 1,
			'return' => 1,
			)
		);
		$expected = array();

		// Act
		$actual = Custom_Caps::map_meta_cap( $caps, $cap, $user_id, $args );
		// Assert
		$this->assertEquals($expected, $actual, 'The user is an administrator and do_not_allow added to array.');
	}

	function testNonAdminDeleteUserMapMetaCapExpectsCapabilityRemoved() {
		// Arrage
		$caps = array();
		$cap = 'delete_users';
		$user_id = 1;
		$args = array();
		// $Custom_Caps = new Custom_Caps(0);

		\WP_Mock::wpFunction('current_user_can', array(
			'times' => 1,
			'return' => false,
			)
		);
		\WP_Mock::wpFunction('wp_get_current_user', array(
			'times' => 1,
			'return' => 1,
			)
		);
		$expected = array('do_not_allow');

		// Act
		$actual = Custom_Caps::map_meta_cap( $caps, $cap, $user_id, $args );
		// Assert
		$this->assertEquals($expected, $actual, 'User is not an admin but still allowed to delete users.');
	}

	function testAdminDeleteUsersMapMetaCapExpectsCapabilityLeftAlone() {
		// Arrage
		$caps = array();
		$cap = 'promote_user';
		$user_id = 1;
		$args = array();
		// $Custom_Caps = new Custom_Caps(0);

		\WP_Mock::wpFunction('current_user_can', array(
			'times' => 1,
			'return' => true,
			)
		);
		\WP_Mock::wpFunction('wp_get_current_user', array(
			'times' => 1,
			'return' => 1,
			)
		);
		$expected = array();

		// Act
		$actual = Custom_Caps::map_meta_cap( $caps, $cap, $user_id, $args );
		// Assert
		$this->assertEquals($expected, $actual, 'The user is an administrator and do_not_allow added to array.');
	}
}
