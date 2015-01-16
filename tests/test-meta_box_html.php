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

	function testDrawWithEmptyFieldExpectsWPErrorReturned() {
		// arrange
		$field = array();
		$HTML = new HTML();
		$mock = $this->getMock('WP_Error');

		//act
		$error = $HTML->draw( $field );

		//assert
		$this->assertInstanceOf( 'WP_Error', $error );
	}

	function testDrawNotFormsetWithTitleExpectsH4Title() {
		//arrange
		$field = array(
			'type' => 'text',
			'title' => 'Test Title',
			'meta_key' => 'field'
		);
		$HTML = new HTML();
		$needle = '<h4 id="field" >Test Title</h4>';

		//act
		ob_start();
		$HTML->draw( $field );
		$haystack = ob_get_flush();

		//assert
		$this->assertContains( $needle, $haystack );
	}

	function testDrawWithFieldTypeFormsetCallsDrawFormset() {
		//arrange
		$field = array(
			'type' => 'formset'
		);
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( array( 'draw_formset', ) )
					 ->getMock();
		$HTML->expects( $this->once() )
			 ->method( 'draw_formset' )
			 ->will( $this->returnValue( true ) );

		//act
		$HTML->draw( $field );

		//assert
		// Passes when called for formset type
	}

	function testDrawWithFieldTypeFieldsetCallsPassFieldset() {
		//arrange
		$field = array(
			'type' => 'fieldset'
		);
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( array( 'pass_fieldset', ) )
					 ->getMock();
		$HTML->expects( $this->once() )
			 ->method( 'pass_fieldset' )
			 ->will( $this->returnValue( true ) );

		//act
		$HTML->draw( $field );

		//assert
		// Passes when called for formset type
	}

	function testDrawWithInputFieldCallsDrawInput() {
		//arrange
		$field = array(
			'type' => 'text'
		);
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( array( 'draw_input', ) )
					 ->getMock();
		$HTML->expects( $this->once() )
			 ->method( 'draw_input' )
			 ->will( $this->returnValue( true ) );

		//act
		$HTML->draw( $field );

		//assert
		// Passes when called for formset type
	}

	function testDrawWithSelectFieldCallsPassSelect() {
		//arrange
		$field = array(
			'type' => 'select'
		);
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( array( 'pass_select', ) )
					 ->getMock();
		$HTML->expects( $this->once() )
			 ->method( 'pass_select' )
			 ->will( $this->returnValue( true ) );

		//act
		$HTML->draw( $field );

		//assert
		// Passes when called for formset type
	}

	function testDrawWithHiddenFieldCallsHidden() {
		//arrange
		$field = array(
			'type' => 'hidden'
		);
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( array( 'hidden', ) )
					 ->getMock();
		$HTML->expects( $this->once() )
			 ->method( 'hidden' )
			 ->will( $this->returnValue( true ) );

		//act
		$HTML->draw( $field );

		//assert
		// Passes when called for formset type
	}
}