<?php
use \CFPB\Utils\PostType;

class PostTypeObj extends PostType {
	public $singular = 'Post type';
	public $plural = 'Post types';
	public $prefix = 'cfpb_';
	public $slug = 'post_type';
}

class testRewrite {
	public $extra_rules_top = array();
}

$wp_rewrite = new testRewrite();

class PostTypesTest extends PHPUnit_Framework_TestCase {
	function setUp() {
		\WP_Mock::setUp();
	}

	function tearDown() {
		\WP_Mock::tearDown();
	}

	function testBuildPostTypeExpectsRegisterPostTypeCalledOnce() {
		// Arrange
		$PostTypeObj = new PostTypeObj();
		\WP_Mock::wpPassthruFunction('wp_parse_args', array( 'times' => 1 ) );
		\WP_Mock::wpPassthruFunction('register_post_type', array( 'times' => 1 ) );
		// Act
		$PostTypeObj->build_post_type( 'Custom', 'Customs', 'custom', $prefix = 'cfpb_', $args = array('has_archive' => false, 'rewrite' => array('slug' => 'regulations', 'with_front' => false), 'supports' => array('title' , 'editor' , 'revisions' , 'page-attributes', 'custom-fields')));
	}

	function testInvalidKeyMaybeFlushRulesExpectsFlushRewriteRulesCalledOnce(){
		// Arrange
		$postTypeObj = new PostTypeObj();
		\WP_Mock::wpFunction('flush_rewrite_rules', array(
			'times' => 1,
			'with' => array(true),
			)
		);
		global $wp_rewrite;
		$wp_rewrite->extra_rules_top = array();
		// Act
		$postTypeObj->maybe_flush_rewrite_rules('key');
		// Assert
	}

	function testMissingKeyMaybeFlushRulesExpectsFlushRewriteRulesCalledOnce() {
		// Arrange
		$postTypeObj = new PostTypeObj();
		\WP_Mock::wpFunction('flush_rewrite_rules', array(
			'times' => 1,
			'with' => array(true),
			)
		);
		global $wp_rewrite;
		$wp_rewrite->extra_rules_top = array();
		// Act
		$postTypeObj->maybe_flush_rewrite_rules('key');
		// Assert		
	}

	function testProperArrayKeyValuePairMaybeFlushRulesExpectsFlushRewriteRulesNotCalled() {
		// Arrange
		$postTypeObj = new PostTypeObj();
		global $wp_rewrite;
		$wp_rewrite->extra_rules_top = array('key/?$' => 'index.php?post_type=key/?$');

		// Act
		$postTypeObj->maybe_flush_rewrite_rules('key');
	}
}

