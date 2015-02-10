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

	function testDrawWithNonceFieldCallsWPNonceField() {
		//arrange
		$field = array(
			'type' => 'nonce'
		);
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( null )
					 ->getMock();
		\WP_Mock::wpPassthruFunction('wp_nonce_field', array( 'times' => 1, ) );
		\WP_Mock::wpPassthruFunction('plugin_basename', array( 'times' => 1, ) );

		//act
		$HTML->draw( $field );

		//assert
		// Passes when called for formset type
	}

	function testDrawFieldHasHowToGetsEchoed() {
		//arrange
		$field = array(
			'howto' => 'Testing howto'
		);
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( null )
					 ->getMock();
		\WP_Mock::wpPassthruFunction( 'esc_attr' );
		$needle = '<p class="howto">Testing howto</p>';

		//act
		ob_start();
		$HTML->draw( $field );
		$haystack = ob_get_flush();

		//assert
		$this->assertContains( $needle, $haystack );
	}

	function testDrawFieldWrapsWithCMSToolkitWrapperDiv() {
		//arrange
		$field = array( 'class' => 'test' );
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( null )
					 ->getMock();
		\WP_Mock::wpPassthruFunction( 'esc_attr', array( 'return' => 'test') );
		$needle = '<div class="cms-toolkit-wrapper test"></div>';

		//act
		ob_start();
		$HTML->draw( $field );
		$haystack = ob_get_flush();

		//assert
		$this->assertContains( $needle, $haystack );
	}

	function testDrawFormsetShowsNewInitialField() {
		//arrange
		$field = array( 
			'init' => true,
			'meta_key' => 'test',
		);
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( array( 'get_existing_data', 'get_formset_id', 'pass_fieldset' ) )
					 ->getMock();
		\WP_Mock::wpFunction( 'get_post_custom' );
		\WP_Mock::wpPassthruFunction( 'esc_attr', array( 'return' => 'test') );
		$needle = '<div id="test_formset">';

		//act
		ob_start();
		$HTML->draw_formset( $field );
		$haystack = ob_get_flush();

		//assert
		$this->assertContains( $needle, $haystack );
	}

	function testDrawFormsetHidesAddLinkForExistingField() {
		//arrange
		$field = array( 
			'fields' => array(
				array(
					'meta_key' => 'test_field',
				),
			),
			'meta_key' => 'test' 
		);
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( array( 'get_formset_id', 'pass_fieldset' ) )
					 ->getMock();
		$HTML->method( 'get_formset_id' )
			 ->will( $this->returnValue( 1 ) );
		\WP_Mock::wpFunction( 'get_post_custom', array( 'return' => array('test_field' => 'add 1 hidden' ) ) );
		\WP_Mock::wpPassthruFunction( 'esc_attr', array( 'return' => 'test') );
		$needle = 'add 1 hidden';

		//act
		ob_start();
		$HTML->draw_formset( $field );
		$haystack = ob_get_flush();

		//assert
		$this->assertContains( $needle, $haystack );
	}

	function testDrawFormsetHidesNonexistentNoninitialField() {
		//arrange
		$field = array( 'meta_key' => 'test' );
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( array( 'get_existing_data', 'get_formset_id', 'pass_fieldset' ) )
					 ->getMock();
		\WP_Mock::wpFunction( 'get_post_custom' );
		\WP_Mock::wpPassthruFunction( 'esc_attr', array( 'return' => 'test') );
		$needle = '<div id="test_formset" class="hidden new" disabled>';

		//act
		ob_start();
		$HTML->draw_formset( $field );
		$haystack = ob_get_flush();

		//assert
		$this->assertContains( $needle, $haystack );
	}

	function testDrawFormsetHidesHeaderForInvisibleField() {
		//arrange
		$field = array(
			'title' => 'Test Title',
			'fields' => array(
				array(
					'meta_key' => 'test_field',
				),
			),
			'meta_key' => 'test' 
		);
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( array( 'get_existing_data', 'get_formset_id', 'pass_fieldset' ) )
					 ->getMock();
		\WP_Mock::wpPassthruFunction( 'get_post_custom' );
		\WP_Mock::wpPassthruFunction( 'esc_attr' );
		$needle = '<h4 id="test_header" class="formset-header hidden">';
		
		//act
		ob_start();
		$HTML->draw_formset( $field );
		$haystack = ob_get_flush();

		//assert
		$this->assertContains( $needle, $haystack );
	}

	function testDrawFormsetShowsHeaderForVisibleField() {
		//arrange
		$field = array(
			'title' => 'Test Title',
			'fields' => array(
				array(
					'meta_key' => 'test_field',
				),
			),
			'meta_key' => 'test',
			'init' => true
		);
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( array( 'get_existing_data', 'get_formset_id', 'pass_fieldset' ) )
					 ->getMock();
		\WP_Mock::wpPassthruFunction( 'get_post_custom' );
		\WP_Mock::wpPassthruFunction( 'esc_attr' );
		$needle = '<h4 id="test_header" class="formset-header">';
		
		//act
		ob_start();
		$HTML->draw_formset( $field );
		$haystack = ob_get_flush();

		//assert
		$this->assertContains( $needle, $haystack );
	}

	function testDrawFormsetShowsRemoveButtonAndHidesAddButtonForVisibleField() {
		//arrange
		$field = array(
			'title' => 'Test Title',
			'fields' => array(
				array(
					'meta_key' => 'test_field',
				),
			),
			'meta_key' => 'test' 
		);
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( array( 'get_formset_id', 'pass_fieldset' ) )
					 ->getMock();
		\WP_Mock::wpFunction( 'get_post_custom', array( 'return' => array('test_field' => 'data' ) ) );
		\WP_Mock::wpPassthruFunction( 'esc_attr', array( 'return' => 'test') );
		$needle = '<div id="test_formset">';
		$remove_button = '<a class="toggle_form_manager test remove " href="#remove-formset_">Remove</a>';
		$add_button = '<a class="toggle_form_manager test add  hidden" href="#add-formset_">Add Test Title</a>';
		
		//act
		ob_start();
		$HTML->draw_formset( $field );
		$haystack = ob_get_flush();

		//assert
		$this->assertContains( $needle, $haystack );
		$this->assertContains( $remove_button, $haystack );
		$this->assertContains( $add_button, $haystack );
	}	

	function testDrawFormsetHidesRemoveButtonAndShowsAddButtonForInvisibleField() {
		//arrange
		$field = array(
			'title' => 'Test Title',
			'fields' => array(
				array(
					'meta_key' => 'test_field',
				),
			),
			'meta_key' => 'test' 
		);
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( array( 'get_existing_data', 'get_formset_id', 'pass_fieldset' ) )
					 ->getMock();
		\WP_Mock::wpPassthruFunction( 'get_post_custom' );
		\WP_Mock::wpPassthruFunction( 'esc_attr' );
		$needle = '<div id="test_formset" class="hidden new" disabled>';
		$remove_button = '<a class="toggle_form_manager test remove  hidden" href="#remove-formset_">Remove</a>';
		$add_button = '<a class="toggle_form_manager test add " href="#add-formset_">Add Test Title</a>';
		
		//act
		ob_start();
		$HTML->draw_formset( $field );
		$haystack = ob_get_flush();

		//assert
		$this->assertContains( $needle, $haystack );
		$this->assertContains( $remove_button, $haystack );
		$this->assertContains( $add_button, $haystack );
	}

	function testDrawFormsetShouldCallGetPostCustom() {
		//arrange
		$field = array();
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( array( 'get_existing_data', 'get_formset_id', 'pass_fieldset' ) )
					 ->getMock();
		\WP_Mock::wpFunction( 'get_post_custom', array( 'times' => 1 ) );
		
		//act
		$HTML->draw_formset( $field );

		//assert
		// Passes if get_post_custom() is called once.
	}

	function testDrawFormsetShouldCallGetFormsetID() {
		//arrange
		$field = array(
			'meta_key' => 'test_0'
		);
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( array( 'get_existing_data', 'get_formset_id', 'pass_fieldset' ) )
					 ->getMock();
		$HTML->expects( $this->once() )
			 ->method( 'get_formset_id' )
			 ->	will( $this->returnValue( true ) );

		//act
		$HTML->draw_formset( $field );

		//assert
		// Passes if get_formset_id() is called once.
	}

	function testDrawFormsetShouldCallGetExistingData() {
		//arrange
		$field = array(
			'meta_key' => 'test_0'
		);
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( array( 'get_existing_data', 'get_formset_id', 'pass_fieldset' ) )
					 ->getMock();
		$HTML->expects( $this->once() )
			 ->method( 'get_existing_data' )
			 ->	will( $this->returnValue( true ) );

		//act
		$HTML->draw_formset( $field );

		//assert
		// Passes if get_existing_data() is called once.
	}

	function testDrawFormsetShouldCallPassFieldset() {
		//arrange
		$field = array(
			'meta_key' => 'test_0'
		);
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( array( 'get_existing_data', 'get_formset_id', 'pass_fieldset' ) )
					 ->getMock();
		$HTML->expects( $this->once() )
			 ->method( 'pass_fieldset' )
			 ->	will( $this->returnValue( true ) );

		//act
		$HTML->draw_formset( $field );

		//assert
		// Passes if pass_fieldset() is called once.
	}

	function testPassFieldsetCallsDrawThreeTimesForThreeFields() {
		//arrange
		$field = array(
			'fields' => array(
				'field',
				'field',
				'field',
			),
		);
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( array( 'draw', ) )
					 ->getMock();
		$HTML->expects( $this->exactly( 3 ) )
			 ->method( 'draw' )
			 ->will( $this->returnValue( true ) );

		//act
		$HTML->pass_fieldset( $field );

		//arrange
		// Passes if draw() is called 3 times
	}

	function testGetFormsetIDReturnsExpectedOutput() {
		// arrange
		$form_meta_key = 'test_0_3';
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( null )
					 ->getMock();
		$expected = '0-3';

		//act
		$actual = $HTML->get_formset_id( $form_meta_key );

		//assert
		$this->assertEquals( $actual, $expected );
	}

	function testGetExistingDataWithNonFieldsetFieldAddsExistingDataToArray() {
		//arrange
		$field = array(
			'fields' => array(
				array( 'meta_key' => 'field'),
			),
		);
		$existing = array();
		$data = array( 'field' => 'data' );
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( null )
					 ->getMock();
		$expected = array( 0 => 'data' );

		// act
		$HTML->get_existing_data( $field, $existing, $data );

		// assert
		$this->assertEquals( $existing, $expected );
	}

	function testPassSelectCallsSelectForSelectMultiselectAndTaxonomySelectTypes() {
		//arrange
		$selections = array();
		$selections[0] = array('type' => 'select');
		$selections[1] = array('type' => 'multiselect');
		$selections[2] = array('type' => 'taxonomyselect');
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( array( 'select', ) )
					 ->getMock();
		$HTML->expects( $this->exactly( 3 ) )
			 ->method( 'select' )
			 ->will( $this->returnValue( true ) );

		//act
		foreach ( $selections as $selection ) {
			$HTML->pass_select( $selection );
		}

		//assert
		// Passes when select() is called 3 times
	}

	function testPassSelectCallsTaxonomyAsMetaForTaxAsMetaType() {		
		//arrange
		$field = array( 'type' => 'tax_as_meta' );
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( array( 'taxonomy_as_meta', ) )
					 ->getMock();
		$HTML->expects( $this->once() )
			 ->method( 'taxonomy_as_meta' )
			 ->will( $this->returnValue( true ) );

		//act
		$HTML->pass_select( $field );

		//assert
		// Passes when taxonomy_as_meta() is called once
	}

	function testPassSelectCallsGetPosts() {
		//arrange
		$field = array('type' => 'post_select');
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( array( 'post_select', ) )
					 ->getMock();
		\WP_Mock::wpPassthruFunction( 'get_posts', array( 'times' => 1 ) );
		\WP_Mock::wpPassthruFunction( 'get_post_meta' );

		//act
		$HTML->pass_select( $field );

		//assert
		// Passes when get_posts() is called once
	}

	function testPassSelectCallsGetPostMeta() {
		//arrange
		$field = array('type' => 'post_select');
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( array( 'post_select', ) )
					 ->getMock();
		\WP_Mock::wpPassthruFunction( 'get_posts' );
		\WP_Mock::wpPassthruFunction( 'get_post_meta', array( 'times' => 1 ) );

		//act
		$HTML->pass_select( $field );

		//assert
		// Passes when get_posts() is called once
	}

	function testPassSelectCallsSelectForPostSelectAndPostMultiselectTypes() {
		//arrange
		$selections = array();
		$selections[0] = array('type' => 'post_select');
		$selections[1] = array('type' => 'post_multiselect');
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( array( 'post_select', ) )
					 ->getMock();
		$HTML->expects( $this->exactly( 2 ) )
			 ->method( 'post_select' )
			 ->will( $this->returnValue( true ) );
		\WP_Mock::wpPassthruFunction( 'get_posts' );
		\WP_Mock::wpPassthruFunction( 'get_post_meta' );

		//act
		foreach ( $selections as $selection ) {
			$HTML->pass_select( $selection );
		}

		//assert
		// Passes when select() is called 2 times
	}

	function testDrawInputCallsTextAreaForTextAreaType() {
		//arrange
		$field = array( 'type' => 'text_area' );
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( array( 'text_area', ) )
					 ->getMock();
		$HTML->expects( $this->once() )
			 ->method( 'text_area' )
			 ->will( $this->returnValue( true ) );

		//act
		$HTML->draw_input( $field );

		//assert
		// Passes when text_area() is called once
	}

	function testDrawInputCallsSingleInputForNumberTextEmailURLTypes() {
		//arrange
		$fields = array(
			array( 'type' => 'number' ),
			array( 'type' => 'text' ),
			array( 'type' => 'email' ),
			array( 'type' => 'url' ),
		);
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( array( 'single_input', ) )
					 ->getMock();
		$HTML->expects( $this->exactly( 4 ) )
			 ->method( 'single_input' )
			 ->will( $this->returnValue( true ) );

		//act
		foreach ( $fields as $field ) {
			$HTML->draw_input( $field );
		}

		//assert
		// Passes when single_input() is called 4 times
	}

	function testDrawInputCallsDateForDateType() {
		//arrange
		$field = array( 'type' => 'date' );
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( array( 'date', ) )
					 ->getMock();
		$HTML->expects( $this->once() )
			 ->method( 'date' )
			 ->will( $this->returnValue( true ) );

		//act
		$HTML->draw_input( $field );

		//assert
		// Passes when date() is called once
	}

	function testDrawInputCallsSingleInputForRadioTypeTwice() {
		//arrange
		$field = array( 'type' => 'radio' );
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( array( 'single_input', ) )
					 ->getMock();
		$HTML->expects( $this->exactly( 2 ) )
			 ->method( 'single_input' )
			 ->will( $this->returnValue( true ) );

		//act
		$HTML->draw_input( $field );

		//assert
		// Passes when single_input() is called twice
	}

	function testDrawInputCallsBooleanInputForBooleanType() {
		//arrange
		$field = array( 'type' => 'boolean' );
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( array( 'boolean_input', ) )
					 ->getMock();
		$HTML->expects( $this->once() )
			 ->method( 'boolean_input' )
			 ->will( $this->returnValue( true ) );

		//act
		$HTML->draw_input( $field );

		//assert
		// Passes when boolean_input() is called once
	}

	function testDrawInputCallsURLInputForLinkType() {
		//arrange
		$field = array( 'type' => 'link' );
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( array( 'url_input', ) )
					 ->getMock();
		$HTML->expects( $this->once() )
			 ->method( 'url_input' )
			 ->will( $this->returnValue( true ) );

		//act
		$HTML->draw_input( $field );

		//assert
		// Passes when url_input() is called once
	}

	function testTextAreaDrawsLabelForFieldMetaKey() {
		//arrange
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( null )
					 ->getMock();
		\WP_Mock::wpPassthruFunction( 'esc_attr' );
		$needle = '<label class="cms-toolkit-label block-label" for="field">';

		//act
		ob_start();
		$HTML->text_area( null, null, 'field', null, null, null, null, null, null );
		$haystack = ob_get_flush();

		//assert
		$this->assertContains( $needle, $haystack );
	}

	function testTextAreaDrawsLabelForFieldMetaKeyWithTitle() {
		//arrange
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( null )
					 ->getMock();
		\WP_Mock::wpPassthruFunction( 'esc_attr' );
		$needle = '<label class="cms-toolkit-label block-label" for="field">title</label>';

		//act
		ob_start();
		$HTML->text_area( null, null, 'field', null, 'title', null, null, null, null );
		$haystack = ob_get_flush();

		//assert
		$this->assertContains( $needle, $haystack );
	}

	function testTextAreaDrawsLabelForFieldMetaKeyWithLabelIfNoTitle() {
		//arrange
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( null )
					 ->getMock();
		\WP_Mock::wpPassthruFunction( 'esc_attr' );
		$needle = '<label class="cms-toolkit-label block-label" for="field">label</label>';

		//act
		ob_start();
		$HTML->text_area( null, null, 'field', null, null, 'label', null, null, null );
		$haystack = ob_get_flush();

		//assert
		$this->assertContains( $needle, $haystack );
	}

	function testTextAreaDrawsTitleOverLabelIfBothExist() {
		//arrange
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( null )
					 ->getMock();
		\WP_Mock::wpPassthruFunction( 'esc_attr' );
		$needle = '<label class="cms-toolkit-label block-label" for="field">title</label>';

		//act
		ob_start();
		$HTML->text_area( null, null, 'field', null, 'title', 'label', null, null, null );
		$haystack = ob_get_flush();

		//assert
		$this->assertContains( $needle, $haystack );
	}

	function testTextAreaDrawsTextareaElement() {
		//arrange
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( null )
					 ->getMock();
		\WP_Mock::wpPassthruFunction( 'esc_attr' );
		$needle = '<textarea id="field_key" class="cms-toolkit-textarea form-input_12" name="field_key" rows="10" cols="40" value="This is the text." placeholder="Placeholder text." required>This is the text.</textarea>';

		//act
		ob_start();
		$HTML->text_area( 10, 40, 'field_key', 'This is the text.', null, null, 'Placeholder text.', true, 12 );
		$haystack = ob_get_flush();

		//assert
		$this->assertContains( $needle, $haystack );
	}

	function testSingleInputDrawsLabel() {		
		//arrange
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( null )
					 ->getMock();
		\WP_Mock::wpPassthruFunction( 'esc_attr' );
		$needle = '<label class="cms-toolkit-label block-label" for="field">title</label>';

		//act
		ob_start();
		$HTML->single_input( 'field', 'text', null, null, 'title', 'label', null, null, null );
		$haystack = ob_get_flush();

		//assert
		$this->assertContains( $needle, $haystack );
	}

	function testSingleInputDrawsGivenTypeInput() {		
		//arrange
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( null )
					 ->getMock();
		\WP_Mock::wpPassthruFunction( 'esc_attr' );
		$needle = '<input id="field" class="cms-toolkit-input form-input_12" name="field" type="text" maxlength="30" value="The text." placeholder="placeholder" required />';

		//act
		ob_start();
		$HTML->single_input( 'field', 'text', 30, 'The text.', 'title', 'label', 'placeholder', true, 12 );
		$haystack = ob_get_flush();

		//assert
		$this->assertContains( $needle, $haystack );
	}

	function testBooleanInputPrintsLabel() {		
		//arrange
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( null )
					 ->getMock();
		\WP_Mock::wpPassthruFunction( 'esc_attr' );
		$needle = '<label class="cms-toolkit-label" for="field">title</label>';

		//act
		ob_start();
		$HTML->boolean_input( 'field', 'title', null, null, null );
		$haystack = ob_get_flush();

		//assert
		$this->assertContains( $needle, $haystack );
	}

	function testBooleanInputPrintsCheckboxInput() {		
		//arrange
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( null )
					 ->getMock();
		\WP_Mock::wpPassthruFunction( 'esc_attr' );
		$needle = '<input id="field" class="cms-toolkit-checkbox form-input_12" name="field" type="checkbox" checked required />';

		//act
		ob_start();
		$HTML->boolean_input( 'field', 'title', 'label', 'on', true, 12 );
		$haystack = ob_get_flush();

		//assert
		$this->assertContains( $needle, $haystack );
	}

	function testURLInputCallsSingleInputTwice() {		
		//arrange
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( array( 'single_input' ) )
					 ->getMock();
		\WP_Mock::wpPassthruFunction( 'esc_attr' );
		$HTML->expects( $this->exactly( 2 ) )
			 ->method( 'single_input' )
			 ->will( $this->returnValue( true ) );

		//act
		$HTML->url_input( 'field', 'title', null, null, null );

		//assert
		// Passes when single_input() is called only once
	}

	function testURLInputPrintsDiv() {		
		//arrange
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( null )
					 ->getMock();
		\WP_Mock::wpPassthruFunction( 'esc_attr' );
		$needle = '<div class="link-field field_key">';

		//act
		ob_start();
		$HTML->url_input( 'field_key', null, null, null, null, null, null );
		$haystack = ob_get_flush();

		//assert
		$this->assertContains( $needle, $haystack );
	}

	function testHiddenPrintsHiddenField() {		
		//arrange
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( null )
					 ->getMock();
		\WP_Mock::wpPassthruFunction( 'esc_attr' );
		$needle = '<input class="cms-toolkit-input form-input_12" id="field_key" name="field_key" type="hidden" value="value" />';

		//act
		ob_start();
		$HTML->hidden( 'field_key', 'value', 12 );
		$haystack = ob_get_flush();

		//assert
		$this->assertContains( $needle, $haystack );
	}

	function testSelectWithGivenTaxonomyCallsWPGetObjectTermsAndGetTheIDAndWPDropdownCategories() {
		//arrange
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( null )
					 ->getMock();
		\WP_Mock::wpFunction( 'get_the_ID' );
		\WP_Mock::wpFunction( 'wp_get_object_terms', array( 'return' => array() ) );
		\WP_Mock::wpFunction( 'wp_dropdown_categories' );

		//act
		$HTML->select( null, null, 'tax', null, null, null, null, null, null, null );
	}

	function testSelectWithoutTaxonomyPrintsLabel() {
		//arrange
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( null )
					 ->getMock();
		\WP_Mock::wpPassthruFunction( 'esc_attr' );
		$needle = '<label for="field_key">Title</label>';

		//act
		ob_start();
		$HTML->select( 'field_key', array(), false, null, null, null, null, 'Title', null, null, null );
		$haystack = ob_get_flush();

		//assert
		$this->assertContains( $needle, $haystack );
	}

	function testSelectWithoutTaxonomyAndWithoutOptionsPrintsSelectFieldWithOnlyBlankOption() {
		//arrange
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( null )
					 ->getMock();
		\WP_Mock::wpPassthruFunction( 'esc_attr' );
		$needle = '<select id="field_key" name="field_key[]" class="form-input_12" multiple required>';
		$needle .= '<option selected value="">--</option></select>';

		//act
		ob_start();
		$HTML->select( 'field_key', array(), false, true, null, '--', 'Title', null, true, 12 );
		$haystack = ob_get_flush();

		//assert
		$this->assertContains( $needle, $haystack );
	}

	function testSelectWithoutTaxonomyAndWithOptionsPrintsSelectFieldWithBlankOptionSelected() {
		//arrange
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( null )
					 ->getMock();
		\WP_Mock::wpPassthruFunction( 'esc_attr' );
		$needle = '<option selected value="">--</option>';
		$needle .= '<option value="option1">option1</option>';
		$needle .= '<option value="option2">option2</option></select>';

		//act
		ob_start();
		$HTML->select( 'field_key', array( 'option1', 'option2' ), false, true, null, '--', 'Title', null, true, 12 );
		$haystack = ob_get_flush();

		//assert
		$this->assertContains( $needle, $haystack );
	}

	function testSelectWithoutTaxonomyAndWithOptionsPrintsSelectFieldWithValueSelected() {
		//arrange
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( null )
					 ->getMock();
		\WP_Mock::wpPassthruFunction( 'esc_attr' );
		$needle = '<option selected="selected" value="option1">option1</option>';
		$needle .= '<option value="option2">option2</option></select>';

		//act
		ob_start();
		$HTML->select( 'field_key', array( 'option1', 'option2' ), false, true, 'option1', '--', 'Title', null, true, 12 );
		$haystack = ob_get_flush();

		//assert
		$this->assertContains( $needle, $haystack );
	}

	function testPostSelectPrintsLabel() {		
		//arrange
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( null )
					 ->getMock();
		\WP_Mock::wpPassthruFunction( 'esc_attr' );
		$needle = '<label for="field_key">title</label>';

		//act
		ob_start();
		$HTML->post_select( 'field_key', array(), null, null, null, 'title', null, null, null );
		$haystack = ob_get_flush();

		//assert
		$this->assertContains( $needle, $haystack );
	}

	function testPostSelectPrintsSelect() {		
		//arrange
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( null )
					 ->getMock();
		\WP_Mock::wpPassthruFunction( 'esc_attr' );
		$needle = '<select class="multi form-input_12" id="field_key" name="field_key[]" multi required >';

		//act
		ob_start();
		$HTML->post_select( 'field_key', array(), null, 'multi', 'title', null, true, '--', 12 );
		$haystack = ob_get_flush();

		//assert
		$this->assertContains( $needle, $haystack );
	}

	function testPostSelectPrintsBlankOptionSelectedWithoutValue() {
		//arrange
		$posts = array();
		$post = new \StdClass;
		$post->post_name = 'name1';
		$post->post_title = 'title1';
		array_push($posts, $post);
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( null )
					 ->getMock();
		\WP_Mock::wpPassthruFunction( 'esc_attr' );
		$needle = '<option selected value="">--</option>';

		//act
		ob_start();
		$HTML->post_select( 'field_key', $posts, null, null, 'Title', null, true, '--', 12 );
		$haystack = ob_get_flush();

		//assert
		$this->assertContains( $needle, $haystack );
	}

	function testPostSelectPrintsBlankOptionWithSelectedValue() {
		//arrange
		$posts = array();
		$post = new \StdClass;
		$post->post_name = 'name1';
		$post->post_title = 'title1';
		array_push($posts, $post);
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( null )
					 ->getMock();
		\WP_Mock::wpPassthruFunction( 'esc_attr' );
		$needle = '<option selected value="name1">title1</option>';

		//act
		ob_start();
		$HTML->post_select( 'field_key', $posts, 'name1', null, 'Title', null, true, '--', 12 );
		$haystack = ob_get_flush();

		//assert
		$this->assertContains( $needle, $haystack );
	}

	function testTaxonomyAsMetaPrintsSelect() {
		//arrange
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( null )
					 ->getMock();
		\WP_Mock::wpPassthruFunction( 'esc_attr' );
		$needle = '<select class="multi form-input_12" name="field_slug[]" multi required>';

		//act
		ob_start();
		$HTML->taxonomy_as_meta( 'field_slug', array(), 'tax', null, 'multi', '--', true, 12 );
		$haystack = ob_get_flush();

		//assert
		$this->assertContains( $needle, $haystack );
	}

	function testTaxonomyAsMetaPrintsBlankOptionSelectedWithoutValue() {
		//arrange
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( null )
					 ->getMock();
		$term = new \StdClass;
		$term->slug = 'option1';
		$term->name = 'Option 1';
		$term->count = 2;
		\WP_Mock::wpPassthruFunction( 'esc_attr' );
		\WP_Mock::wpFunction( 'get_term_by', array( 'return' => $term ) );
		$needle = '<option selected value="" id="no_field_slug" name="field_slug">--</option>';
		$needle .= '<option value="option1">Option 1 (2)</option></select>';

		//act
		ob_start();
		$HTML->taxonomy_as_meta( 'field_slug', array( 'option1' ), 'tax', null, 'multi',  '--', true, 12 );
		$haystack = ob_get_flush();

		//assert
		$this->assertContains( $needle, $haystack );
	}

	function testTaxonomyAsMetaPrintsBlankOptionWithValueSelected() {
		//arrange
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( null )
					 ->getMock();
		$term = new \StdClass;
		$term->slug = 'option2';
		$term->name = 'Option 2';
		$term->count = 1;
		\WP_Mock::wpPassthruFunction( 'esc_attr' );
		\WP_Mock::wpFunction( 'get_term_by', array( 'return' => $term ) );
		$needle = '<option selected value="option1" id="field_slug" name="field_slug">option1</option>';
		$needle .= '<option value="option2">Option 2 (1)</option></select>';

		//act
		ob_start();
		$HTML->taxonomy_as_meta( 'field_slug', array( 'option2' ), 'tax', 'option1', 'multi', '--', true, 12 );
		$haystack = ob_get_flush();

		//assert
		$this->assertContains( $needle, $haystack );
	}

	function testDatePrintSelectElementWithGenericOptionSelected() {
		//arrange
		global $wp_locale;
		$wp_locale = $this->getMockBuilder( '\WP_Locale' )
						  ->setMethods( array( 'get_month' ) )
						  ->getMock();
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( null )
					 ->getMock();
		$wp_locale->expects( $this->exactly( 24 ) )
				  ->method( 'get_month' )
				  ->will( $this->returnValue( 'month' ) );
		\WP_Mock::wpPassthruFunction( 'esc_attr' );
		\WP_Mock::wpPassthruFunction( 'sanitize_text_field' );
		\WP_Mock::wpFunction( 'has_term', array( 'return' => true ) );
		\WP_Mock::wpFunction( 'get_the_terms', array( 'return' => array( 'term' ) ) );
		$needle = '<select id="tax_month" name="tax_month" class="form-input_12">';
		$needle .= '<option selected="selected" value="" >Month</option>';

		//act
		ob_start();
		$HTML->date( 'tax', false ,false, 12 );
		$haystack = ob_get_flush();

		//assert
		$this->assertContains( $needle, $haystack );
	}

	function testDatePrintsAll12Months() {
		//arrange
		global $wp_locale;
		$wp_locale = $this->getMockBuilder( '\WP_Locale' )
						  ->setMethods( array( 'get_month' ) )
						  ->getMock();
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( null )
					 ->getMock();
		$wp_locale->expects( $this->exactly( 24 ) )
				  ->method( 'get_month' )
				  ->will( $this->returnValue( 'month' ) );
		\WP_Mock::wpPassthruFunction( 'esc_attr' );
		\WP_Mock::wpPassthruFunction( 'sanitize_text_field' );
		\WP_Mock::wpFunction( 'has_term', array( 'return' => true ) );
		\WP_Mock::wpFunction( 'get_the_terms', array( 'return' => array( 'term' ) ) );
		$needle = '<option value="month">month</option><option value="month">month</option>';
		$needle .= '<option value="month">month</option><option value="month">month</option>';
		$needle .= '<option value="month">month</option><option value="month">month</option>';
		$needle .= '<option value="month">month</option><option value="month">month</option>';
		$needle .= '<option value="month">month</option><option value="month">month</option>';
		$needle .= '<option value="month">month</option><option value="month">month</option></select>';

		//act
		ob_start();
		$HTML->date( 'tax', false ,false, 12 );
		$haystack = ob_get_flush();

		//assert
		$this->assertContains( $needle, $haystack );
	}

	function testDatePrintsInputsForDayAndYear() {
		//arrange
		global $wp_locale;
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( null )
					 ->getMock();
		\WP_Mock::wpPassthruFunction( 'esc_attr' );
		\WP_Mock::wpPassthruFunction( 'sanitize_text_field' );
		\WP_Mock::wpFunction( 'has_term', array( 'return' => true ) );
		\WP_Mock::wpFunction( 'get_the_terms', array( 'return' => array( 'term' ) ) );
		$needle = '<input id="tax_day" type="text" name="tax_day" class="form-input_12" value="" size="2" maxlength="2" placeholder="DD"/>';
		$needle .= '<input id="tax_year" type="text" name="tax_year" class="form-input_12" value="" size="4" maxlength="4" placeholder="YYYY"/>';

		//act
		ob_start();
		$HTML->date( 'tax', false ,false, 12 );
		$haystack = ob_get_flush();

		//assert
		$this->assertContains( $needle, $haystack );
	}

	function testDatePrintsOutputMessageForSingleDate() {
		//arrange
		global $wp_locale;
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( null )
					 ->getMock();
		\WP_Mock::wpPassthruFunction( 'esc_attr' );
		\WP_Mock::wpPassthruFunction( 'sanitize_text_field' );
		\WP_Mock::wpFunction( 'has_term', array( 'return' => true ) );
		\WP_Mock::wpFunction( 'get_the_terms', array( 'return' => array( 'term' ) ) );
		$needle = '<p class="howto">If one is set already, selecting a new month, day and year will override it.</p>';

		//act
		ob_start();
		$HTML->date( 'tax' );
		$haystack = ob_get_flush();

		//assert
		$this->assertContains( $needle, $haystack );
	}

	function testDatePrintsMessageForMultipleDates() {
		//arrange
		global $wp_locale;
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( null )
					 ->getMock();
		\WP_Mock::wpPassthruFunction( 'esc_attr' );
		\WP_Mock::wpPassthruFunction( 'sanitize_text_field' );
		\WP_Mock::wpFunction( 'has_term', array( 'return' => true ) );
		\WP_Mock::wpFunction( 'get_the_terms', array( 'return' => array( 'term' ) ) );
		$needle = '<p class="howto">Select a month, day and year to add another.</p>';

		//act
		ob_start();
		$HTML->date( 'tax', true );
		$haystack = ob_get_flush();

		//assert
		$this->assertContains( $needle, $haystack );
	}

	// function testDatePrintsMessageForMultipleDates() {
	// 	//arrange
	// 	global $wp_locale;
	// 	$term = new \StdClass;
	// 	$term->name = 'Term';
	// 	$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
	// 				 ->setMethods( null )
	// 				 ->getMock();
	// 	\WP_Mock::wpPassthruFunction( 'esc_attr' );
	// 	\WP_Mock::wpPassthruFunction( 'sanitize_text_field' );
	// 	\WP_Mock::wpFunction( 'has_term', array( 'return' => true ) );
	// 	\WP_Mock::wpFunction( 'get_the_terms', array( 'return' => array( $term ) ) );
	// 	$needle = 'Select a month, day and year to add another.</p>';

	// 	//act
	// 	ob_start();
	// 	$HTML->date( 'tax', true );
	// 	$haystack = ob_get_flush();

	// 	//assert
	// 	$this->assertContains( $needle, $haystack );
	// }

}
/**
 * Call protected/private method of a class.
 *
 * @param object &$object    Instantiated object that we will run method on.
 * @param string $methodName Method name to call
 * @param array  $parameters Array of parameters to pass into method.
 *
 * @return mixed Method return.
 */
function invokeMethod( &$object, $methodName, array $parameters = array() ) {
	$reflection = new \ReflectionClass(get_class($object));
	$method = $reflection->getMethod($methodName);
	$method->setAccessible(true);

	return $method->invokeArgs($object, $parameters);
}