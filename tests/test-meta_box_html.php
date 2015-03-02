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

	function testTimeCallsSelect3Times() {
		//arrange
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( array( 'select' ) )
					 ->getMock();
		$HTML->expects( $this->exactly( 3 ) )
			 ->method( 'select' );

		//act
		$HTML->time( 'slug', 'taxonomy', false, 'label', 1 );
	}

	function testDatetimeCallsDateAndTime() {
		//arrange
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( array( 'time', 'date' ) )
					 ->getMock();
		$HTML->expects( $this->once() )
			 ->method( 'date' );
		$HTML->expects( $this->once() )
			 ->method( 'time' );

		//act
		$HTML->datetime( 'slug', 'taxonomy', false, 'label', 1 );
	}

	function testDisplayTagsCallsHasTermToSeeIfTagsExistToBeShown() {
		//arrange
		$HTML = new HTML();
		\WP_Mock::wpFunction( 'has_term', array( 'times' => 1, 'return' => false ) );

		//act
		$HTML->displayTags( 'tax', 'type' );
	}

	function testDisplayTagsCallsGetTheTerms() {
		//arrange
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( array( 'hidden' ) )
					 ->getMock();
		$term = new \StdClass;
		$term->name = strtotime( 'now' );
		\WP_Mock::wpFunction( 'has_term', array( 'return' => true ) );
		\WP_Mock::wpFunction( 'get_the_terms', array( 'times' => 1, 'return' => array( $term ) ) );

		//act
		$HTML->displayTags( 'tax', 'time' );
	}

	function testDisplayTagsCallsHiddenForEachTag() {
		//arrange
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( array( 'hidden' ) )
					 ->getMock();
		$HTML->expects( $this->once() )
			 ->method( 'hidden' );
		$term = new \StdClass;
		$term->name = strtotime( 'now' );
		\WP_Mock::wpFunction( 'has_term', array( 'return' => true ) );
		\WP_Mock::wpFunction( 'get_the_terms', array( 'return' => array( $term ) ) );

		//act
		$HTML->displayTags( 'tax', 'time' );
	}
}