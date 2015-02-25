<?php
use \CFPB\Utils\MetaBox\HTML;
use \Mockery as m;

class MetaBoxHTMLTest extends PHPUnit_Framework_TestCase {
	function setUp() {
		\WP_Mock::setUp();
	}

	function tearDown() {
		\WP_Mock::tearDown();
	}

	function testDrawExpectsHTML() {
		// Arrange

		$fields = array(
			'title' => 'This is a field',
			'slug' => 'field_one',
			'type' => 'text_area',
			'cols' => 27,
			'rows' => 2,
			'placeholder' => 'Enter text',
			'howto' => 'Type some text',
			'meta_key' => 'field_one',
			'value' => null,
			'label' => null,
		);

		$HTML = new HTML();
		\WP_Mock::wpPassthruFunction('esc_attr');
		\WP_Mock::wpPassthruFunction('esc_html');
		// Act
		$HTML->draw($fields);
	}

	function testWYSIWYGFieldCallsWPEditor() {
		//arrange
		$HTML = new HTML();
		\WP_Mock::wpFunction( 'wp_editor', array( 'times' => 1 ) );

		//act
		$HTML->wysiwyg( 'content', 'meta_key', array(), null, null);
	}
}