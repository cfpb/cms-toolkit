<?php
use CFPB\Utils\MetaBox\Models;
use \CFPB\Utils\MetaBox\HTML;
use \Mockery as m;

class MetaBoxHTMLTest extends PHPUnit_Framework_TestCase {

	function setUp() {
		global $wp_locale;
		$wp_locale = $this->getMockBuilder( '\WP_Locale' )
						  ->setMethods( array( 'get_month' ) )
						  ->getMock();
		\WP_Mock::setUp();
	}

	function tearDown() {
		\WP_Mock::tearDown();
	}
	/***************************
	 * HTML method tests *
	 ***************************/
	/**
	 * Tests whether the draw method will return WP_Error if given empty field
	 *
	 * @group stable
	 * @group wp_error
	 */
	function testDrawWithEmptyFieldExpectsWPErrorReturned() {
		// arrange
		$field = array('type'=>'none');
		$HTML = new HTML();
		\WP_Mock::wpFunction('wp_die');

		//act
		$error = $HTML->draw( $field );

		//assert
	}
	/**
	 * Tests that the draw method will call draw_repeated_fields() if given field of
	 * of type 'formset'.
	 *
	 * @group stable
	 * @group formset
	 */
	function testDrawRepeatedFields() {
		//arrange
		$TestRepeatedFields = new \TestRepeatedFields;
		$field = $TestRepeatedFields->fields['fields'];
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( array( 'draw_repeated_fields', ) )
					 ->getMock();
		$HTML->expects( $this->once() )
			 ->method( 'draw_repeated_fields' )
			 ->will( $this->returnValue( true ) );
		// \WP_Mock::wpFunction( 'esc_attr' );

		//act
		$HTML->draw( $field );

		//assert
		// Passes when called for formset type
	}
	/**
	 * Tests that the draw method will call draw_input() if given fields of
	 * input types.
	 *
	 * @group stable
	 * @group draw_input
	 */
	function testDrawWithInputFieldCallsDrawInput() {
		//arrange
		$fields = array(
			array( 'type' => 'text' ),
			array( 'type' => 'text_area' ),
			array( 'type' => 'number' ),
			array( 'type' => 'boolean' ),
			array( 'type' => 'email' ),
			array( 'type' => 'url' ),
			array( 'type' => 'date' ),
			array( 'type' => 'radio' ),
			array( 'type' => 'link' ),
		);
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( array( 'draw_input', ) )
					 ->getMock();
		$HTML->expects( $this->exactly( 9 ) )
			 ->method( 'draw_input' )
			 ->will( $this->returnValue( true ) );

		//act
		foreach ( $fields as $field ) {
			$HTML->draw( $field );
		}

		//assert
		// Passes when called for input types
	}
	/**
	 * Tests that the draw method will call draw_input() if given fields of
	 * select types.
	 *
	 * @group stable
	 * @group select
	 */
	function testDrawWithSelectFieldCallsPassSelect() {
		//arrange
		$fields = array(
			array( 'type' => 'select' ),
			array( 'type' => 'multiselect' ),
			array( 'type' => 'taxonomyselect' ),
			array( 'type' => 'tax_as_meta' ),
			array( 'type' => 'post_select' ),
			array( 'type' => 'post_multiselect' ),
		);
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( array( 'pass_select', ) )
					 ->getMock();
		$HTML->expects( $this->exactly( 6 ) )
			 ->method( 'pass_select' )
			 ->will( $this->returnValue( true ) );

		//act
		foreach ( $fields as $field ) {
			$HTML->draw( $field );
		}

		//assert
		// Passes when called for select types
	}
	/**
	 * Tests that the draw method will call hidden() if given field of
	 * of type 'hidden'.
	 *
	 * @group stable
	 * @group hidden
	 */
	function testDrawWithHiddenFieldCallsHidden() {
		//arrange
		$TestValidTextField = new TestValidTextField();
		$TestValidTextField->fields['field']['type'] = 'hidden';
		$TestValidTextField->fields['field']['value'] = 'data';
		$field = $TestValidTextField->fields['field'];
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( array( 'hidden', ) )
					 ->getMock();
		$HTML->expects( $this->once() )
			 ->method( 'hidden' )
			 ->will( $this->returnValue( true ) );
		\WP_Mock::wpFunction( 'esc_attr' );

		//act
		$HTML->draw( $field );

		//assert
		// Passes when called for hidden type
	}
	/**
	 * Tests that the draw method will call wp_nonce_field() if given field of
	 * of type 'nonce'.
	 *
	 * @group stable
	 * @group nonce
	 */
	function testDrawWithNonceFieldCallsWPNonceField() {
		//arrange
		$TestValidTextField = new TestValidTextField();
		$TestValidTextField->fields['field']['type'] = 'nonce';
		$field = $TestValidTextField->fields['field'];
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( null )
					 ->getMock();
		\WP_Mock::wpPassthruFunction('wp_nonce_field', array( 'times' => 1, ) );
		\WP_Mock::wpPassthruFunction('plugin_basename', array( 'times' => 1, ) );
		\WP_Mock::wpFunction( 'esc_attr' );

		//act
		$HTML->draw( $field );

		//assert
		// Passes when called for nonce type
	}
	/**
	 * Tests that the get_set_id() method will return expected output.
	 *
	 * get_set_id() works by looking for a digit surrounded by underscores
	 * and then concatenates each digit by a '-' and returns it. This is used
	 * for the front-end "Remove" button function to remove only a specific
	 * formset.
	 *
	 * @group stable
	 * @group formset
	 */
	function testGetSetIDReturnsExpectedOutput() {
		// arrange
		$form_key = 'test_0_3';
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( null )
					 ->getMock();
		$expected = '0-3';

		//act
		$actual = $HTML->get_set_id( $form_key );

		//assert
		$this->assertEquals( $actual, $expected );
	}
	/**
	 * Tests that the get_existing_data() method will add existing data to an
	 * array that is passed by reference.
	 *
	 * @group stable
	 */
	function testGetExistingDataWithNonFieldsetFieldAddsExistingDataToArray() {
		//arrange
		$TestValidTextField = new TestValidTextField();
		$field = $TestValidTextField->fields['field'];
		$field['value'] = 'data';
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( null )
					 ->getMock();
		$expected = true;

		// act
		$existing = $HTML->get_existing_data( $field );

		// assert
		$this->assertEquals( $existing, $expected );
	}
	/**
	 * Tests that the pass_select() method will call select() for each select
	 * typed field given.
	 *
	 * @group stable
	 * @group select
	 */
	function testPassSelectCallsSelectForSelectMultiselectAndTaxonomySelectTypes() {
		//arrange
		$selections = array(
			array(
				'type' => 'select',
				'key' => 'select',
				'params' => array(),
				'value' => '',
				'placeholder' => '',
			),
			array(
				'type' => 'multiselect',
				'key' => 'multiselect',
				'params' => array(),
				'value' => '',
				'placeholder' => '',
			),
			array(
				'type' => 'taxonomyselect',
				'key' => 'taxonomyselect',
				'params' => array(),
				'value' => '',
				'placeholder' => '',
			),
		);
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
	/**
	 * Tests that the pass_select() method will call taxonomy_as_meta() if given
	 * field of type 'tax_as_meta'.
	 *
	 * @group stable
	 * @group select
	 */
	function testPassSelectCallsTaxonomyAsMetaForTaxAsMetaType() {		
		//arrange
		$field = array( 'type' => 'tax_as_meta', 'key' => '', 'include' => '', 'value' => '', 'placeholder' => '' );
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( array( 'taxonomy_as_meta', ) )
					 ->getMock();
		$HTML->expects( $this->once() )
			 ->method( 'taxonomy_as_meta' )
			 ->will( $this->returnValue( true ) );
		\WP_Mock::wpFunction( 'get_the_ID' );

		//act
		$HTML->pass_select( $field );

		//assert
		// Passes when taxonomy_as_meta() is called once
	}
	/**
	 * Tests that the pass_select() method will call post_select() twice for
	 * fields of types 'post_select' and 'post_multiselect'.
	 *
	 * @group stable
	 * @group select
	 */
	function testPassSelectCallsSelectForPostSelectAndPostMultiselectTypes() {
		//arrange
		$selections = array(
			array(
				'type' => 'post_select',
				'params' => '',
				'key' => '',
				'value' => '',
				'placeholder' => ''
			),
			array(
				'type' => 'post_multiselect',
				'params' => '',
				'key' => '',
				'value' => '',
				'placeholder' => ''
			),
		);
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( array( 'post_select', ) )
					 ->getMock();
		$HTML->expects( $this->exactly( 2 ) )
			 ->method( 'post_select' )
			 ->will( $this->returnValue( true ) );
		\WP_Mock::wpPassthruFunction( 'get_posts' );
		\WP_Mock::wpPassthruFunction( 'get_post_meta' );
		\WP_Mock::wpFunction( 'get_the_ID' );

		//act
		foreach ( $selections as $selection ) {
			$HTML->pass_select( $selection );
		}

		//assert
		// Passes when select() is called 2 times
	}
	/**
	 * Tests that the pass_select() method will call get_posts() once
	 *  if given field of type 'post_select'.
	 *
	 * @group stable
	 * @group select
	 */
	function testPassSelectCallsGetPosts() {
		//arrange
		$field = array(
			'type' => 'post_select',
			'params' => '',
			'key' => '',
			'placeholder' => '',
			'value' => '',
		);
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( array( 'post_select', ) )
					 ->getMock();

		//act
		$HTML->pass_select( $field );

		//assert
		// Passes when get_posts() is called once
	}
	/**
	 * Tests that the pass_select() method will call get_post_meta() once if 
	 * given field of type 'post_select'.
	 *
	 * @group stable
	 * @group select
	 */
	function testPassSelectCallsGetPostMeta() {
		//arrange
		$field = array(
			'type' => 'post_select',
			'params' => '',
			'key' => '',
			'placeholder' => '',
			'value' => '',
		);
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( array( 'post_select', ) )
					 ->getMock();

		//act
		$HTML->pass_select( $field );

		//assert
		// Passes when get_posts() is called once
	}
	/**
	 * Tests that the draw_input() method will call text_area() if given field of
	 * of type 'text_area'.
	 *
	 * @group stable
	 * @group draw_input
	 */
	function testDrawInputCallsTextAreaForTextAreaType() {
		//arrange
		$TestValidTextField = new TestValidTextField();
		$TestValidTextField->fields['field']['type'] = 'text_area';
		$TestValidTextField->fields['field']['rows'] = 2;
		$TestValidTextField->fields['field']['cols'] = 27;
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( array( 'text_area', ) )
					 ->getMock();
		$HTML->expects( $this->once() )
			 ->method( 'text_area' )
			 ->will( $this->returnValue( true ) );

		//act
		$HTML->draw_input( $TestValidTextField->fields['field'] );

		//assert
		// Passes when text_area() is called once
	}
	/**
	 * Tests that the draw_input() method will call single_input() if given fields of
	 * of input types.
	 *
	 * @group stable
	 * @group draw_input
	 */
	function testDrawInputCallsSingleInputForNumberTextEmailURLTypes() {
		//arrange
		$field = new TestValidTextField();
		$types = array( 'number', 'text', 'email', 'url');
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( array( 'single_input', ) )
					 ->getMock();
		$HTML->expects( $this->exactly( 4 ) )
			 ->method( 'single_input' )
			 ->will( $this->returnValue( true ) );

		//act
		foreach ( $types as $type ) {
			$field->fields['field']['type'] = $type;
			$HTML->draw_input( $field->fields['field'] );
		}

		//assert
		// Passes when single_input() is called 4 times
	}
    /**
     * Tests that the draw_input() method will call date() for field type 'date'.
     *
     * @group stable
     * @group draw_input
     */
    function testDrawInputCallsDateForDateType() {
        //arrange
		$field = new TestValidDateField();
        $HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
                     ->setMethods( array( 'date', 'displayTags' ) )
                     ->getMock();
        $HTML->expects( $this->once() )
             ->method( 'date' )
             ->will( $this->returnValue( true ) );

        //act
		$HTML->draw_input( $field->fields['category'] );

        //assert
        // Passes when date() is called once
    }
    /**
     * Tests that the draw_input() method will call time() for field type 'time'.
     *
     * @group stable
     * @group draw_input
     */
    function testDrawInputCallsTimeForTimeType() {
        //arrange
		$field = new TestValidDateField();
		$field->fields['category']['type'] = 'time';
		$field->fields['category']['timezone'] = 'America/New_York';
        $HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
                     ->setMethods( array( 'time', 'displayTags' ) )
                     ->getMock();
        $HTML->expects( $this->once() )
             ->method( 'time' )
             ->will( $this->returnValue( true ) );

        //act
        $HTML->draw_input( $field->fields['category'] );

        //assert
        // Passes when time() is called once
    }
    /**
     * Tests that the draw_input() method will call datetime() for field type 'datetime'.
     *
     * @group stable
     * @group draw_input
     */
    function testDrawInputCallsDatetimeForDatetimeType() {
        //arrange
		$field = new TestValidDateField();
		$field->fields['category']['type'] = 'datetime';
		$field->fields['category']['timezone'] = 'America/New_York';
        $HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
                     ->setMethods( array( 'datetime', 'displayTags' ) )
                     ->getMock();
        $HTML->expects( $this->once() )
             ->method( 'datetime' )
             ->will( $this->returnValue( true ) );

        //act
        $HTML->draw_input( $field->fields['category'] );

        //assert
        // Passes when datetime() is called once
    }
	/**
	 * Tests that the draw_input() method will call single_input() twice if
	 * field of type 'radio'.
	 *
	 * @group stable
	 * @group draw_input
	 */
	function testDrawInputCallsSingleInputForRadioTypeTwice() {
		//arrange
		$field = new TestValidTextField();
		$field->fields['field']['type'] = 'radio';
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( array( 'single_input', ) )
					 ->getMock();
		$HTML->expects( $this->exactly( 2 ) )
			 ->method( 'single_input' )
			 ->will( $this->returnValue( true ) );

		//act
		$HTML->draw_input( $field->fields['field'] );

		//assert
		// Passes when single_input() is called twice
	}
	/**
	 * Tests that the draw_input() method will call boolean_input() if given field
	 * of type 'boolean'.
	 *
	 * @group stable
	 * @group draw_input
	 */
	function testDrawInputCallsBooleanInputForBooleanType() {
		//arrange
		$field = array( 'type' => 'boolean', 'key' => '' );
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
	/**
	 * Tests that the draw_input() method will call link_input() if given field
	 * of type 'link'.
	 *
	 * @group stable
	 * @group draw_input
	 */
	function testDrawInputCallsURLInputForLinkType() {
		//arrange
		$field = array( 'type' => 'link', 'key' => '' );
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( array( 'link_input', ) )
					 ->getMock();
		$HTML->expects( $this->once() )
			 ->method( 'link_input' )
			 ->will( $this->returnValue( true ) );

		//act
		$HTML->draw_input( $field );

		//assert
		// Passes when link_input() is called once
	}
	/**
	 * Tests that the link_input() method will call single_input() twice if given
	 * field of type 'single_input'.
	 *
	 * @group stable
	 * @group link_input
	 */
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
		$HTML->link_input( 'field', 'title', null, null, null );

		//assert
		// Passes when single_input() is called only once
	}
	/**
	 * Tests that the select() method will call single_input() twice if given
	 * field of type 'single_input'.
	 *
	 * @group stable
	 * @group link_input
	 */
	function testSelectWithGivenTaxonomyCallsWPGetObjectTermsAndGetTheIDAndWPDropdownCategories() {
		//arrange
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( null )
					 ->getMock();
		\WP_Mock::wpFunction( 'get_the_ID' );
		\WP_Mock::wpFunction( 'wp_get_object_terms', array( 'times' => 1, 'return' => array() ) );
		\WP_Mock::wpFunction( 'wp_dropdown_categories' );

		//act
		$HTML->select( null, null, 'tax', null, null, null, null, null, null, null );
	}
	/**
	 * Tests that the date() method taxonomy will draw select.
	 *
	 * @group unstable
	 * @group date
	 */
	function testDateCallsGetMonth12Times() {
		//arrange
		global $wp_locale;
		$term = new \StdClass;
		$term->name = '';
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( null )
					 ->getMock();
		$wp_locale->expects( $this->exactly( 12 ) )
				  ->method( 'get_month' )
				  ->will( $this->returnValue( 'month' ) );
		\WP_Mock::wpPassthruFunction( 'esc_attr' );
		\WP_Mock::wpFunction( 'has_term', array( 'return' => true ) );
		\WP_Mock::wpFunction( 'get_the_terms', array( 'return' => array( $term ) ) );

		//act
		$HTML->date( 'tax', false ,false, '' );

		//assert
		// passes when get month is called 12 times
	}
	/**
	 * Tests that the wysiwyg() method will call wp_editor() once.
	 *
	 * @group stable
	 * @group wysiwyg
	 */
	function testWYSIWYGFieldCallsWPEditor() {
		//arrange
		$HTML = new HTML();
		\WP_Mock::wpFunction( 'wp_editor', array( 'times' => 1 ) );

		//act
		$HTML->wysiwyg( 'content', 'key', array(), null, null);
	}
	/***************************
	 * HTML output tests *
	 ***************************/
	/**
	 * Tests that the draw() method will output the a title if set and if not
	 * field type is not 'formset'.
	 *
	 * @group unstable
	 * @group draw
	 */
	function testDrawNotFormsetWithTitleExpectsTitle() {
		//arrange
		$field = array(
			'type' => 'text',
			'title' => 'Test Title',
			'key' => 'field'
		);
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( array( 'draw_input', ) )
					 ->getMock();
		$needle = '<h4 id="field" >Test Title</h4>';

		//act
		ob_start();
		$HTML->draw( $field );
		$haystack = ob_get_flush();

		//assert
		$this->assertContains( $needle, $haystack );
	}
	/**
	 * Tests that the draw() method will not output the a title if not set.
	 *
	 * @group unstable
	 * @group draw
	 */
	function testDrawNotFormsetWithoutTitleExpectsNoTitle() {
		//arrange
		$field = array(
			'type' => 'text',
			'key' => 'field'
		);
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( array( 'draw_input', ) )
					 ->getMock();
		$needle = '<h4 id="field" >Test Title</h4>';

		//act
		ob_start();
		$HTML->draw( $field );
		$haystack = ob_get_flush();

		//assert
		$this->assertNotContains( $needle, $haystack );
	}
	/**
	 * Tests that the draw() method will output the a title if set and if not
	 * field type is not 'formset'.
	 *
	 * @group unstable
	 * @group draw
	 */
	function testDrawRepeatedFieldsWithTitleExpectsNoTitle() {
		//arrange
		$field = array(
			'type' => 'text',
			'title' => 'Test Title',
			'key' => 'field',
			'params' => array(
				'repeated' => array(
					'min' => 1,
					'max' => 2
				),
			),
		);
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( array( 'draw_repeated_fields', ) )
					 ->getMock();
		$needle = '<h4 id="field" >Test Title</h4>';

		//act
		ob_start();
		$HTML->draw( $field );
		$haystack = ob_get_flush();

		//assert
		$this->assertNotContains( $needle, $haystack );
	}
	/**
	 * Tests that the draw() method will output the 'howto' if set.
	 *
	 * @group unstable
	 * @group draw
	 */
	function testDrawFieldHasHowToGetsEchoed() {
		//arrange
		$TestValidTextField = new TestValidTextField();
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( null )
					 ->getMock();
		\WP_Mock::wpPassthruFunction( 'esc_attr' );
		$needle = '<p class="howto">Up to 255 characters</p>';

		//act
		ob_start();
		$HTML->draw( $TestValidTextField->fields['field'] );
		$haystack = ob_get_flush();

		//assert
		$this->assertContains( $needle, $haystack );
	}
	/**
	 * Tests that the draw() method will not output the 'howto' if not set.
	 *
	 * @group unstable
	 * @group draw
	 */
	function testDrawFieldDoesNotHaveHowToSetDoesNotGetEchoed() {
		//arrange
		$field = array('type'=>'none');
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( null )
					 ->getMock();
		\WP_Mock::wpPassthruFunction( 'esc_attr' );
		$needle = '<p class="howto">';

		//act
		ob_start();
		$HTML->draw( $field );
		$haystack = ob_get_flush();

		//assert
		$this->assertNotContains( $needle, $haystack );
	}
	/**
	 * Tests that the draw() method will not output the 'howto' if not set.
	 *
	 * @group unstable
	 * @group draw
	 */
	function testDrawFieldWrapsWithCMSToolkitWrapperDiv() {
		//arrange
		$TestValidTextField = new TestValidTextField();
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( null )
					 ->getMock();
		\WP_Mock::wpPassthruFunction( 'esc_attr', array( 'return' => 'test') );
		$needle = '<div class="cms-toolkit-wrapper">';

		//act
		ob_start();
		$HTML->draw( $TestValidTextField->fields['field'] );
		$haystack = ob_get_flush();

		//assert
		$this->assertContains( $needle, $haystack );
	}
	/**
	 * Tests that the draw_repeated_fields() method will output div with field key
	 * as html attribute id and concatenates it with 'formset'.
	 *
	 * @group unstable
	 * @group draw_repeated_fields
	 */
	function testDrawRepeatedFieldsShowsNewInitialField() {
		//arrange
		$field = array( 
			'init' => true,
			'key' => 'test',
			'fields' => array(),
		);
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( array( 'get_existing_data', 'get_set_id' ) )
					 ->getMock();
		\WP_Mock::wpFunction( 'get_post_custom' );
		\WP_Mock::wpPassthruFunction( 'esc_attr', array( 'return' => 'test') );
		$needle = '<div id="test" class="form">';

		//act
		ob_start();
		$HTML->draw_repeated_fields( $field );
		$haystack = ob_get_flush();

		//assert
		$this->assertContains( $needle, $haystack );
	}
	/**
	 * Tests that the text_area() method will draw label.
	 *
	 * @group unstable
	 * @group text_area
	 */
	function testTextAreaDrawsLabelForFieldMetaKey() {
		//arrange
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( null )
					 ->getMock();
		\WP_Mock::wpPassthruFunction( 'esc_attr' );
		$needle = '<label class="cms-toolkit-label block-label" for="field">';

		//act
		ob_start();
		$HTML->text_area( 'field', null, null, null, null, 'label-text', null, null, null );
		$haystack = ob_get_flush();

		//assert
		$this->assertContains( $needle, $haystack );
	}
	/**
	 * Tests that the text_area() method will draw textarea element.
	 *
	 * @group unstable
	 * @group text_area
	 */
	function testTextAreaDrawsTextareaElement() {
		//arrange
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( null )
					 ->getMock();
		\WP_Mock::wpPassthruFunction( 'esc_attr' );
		$needle = '<textarea id="field_key" class="cms-toolkit-textarea set-input_12" name="field_key" rows="10" cols="40" value="This is the text." placeholder="Placeholder text." required>This is the text.</textarea>';

		//act
		ob_start();
		$HTML->text_area( 'field_key', 'This is the text.', true, 10, 40, '', 'Placeholder text.', 12 );
		$haystack = ob_get_flush();

		//assert
		$this->assertContains( $needle, $haystack );
	}
	/**
	 * Tests that the single_input() method will draw label.
	 *
	 * @group unstable
	 * @group single_input
	 */
	function testSingleInputDrawsLabel() {		
		//arrange
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( null )
					 ->getMock();
		\WP_Mock::wpPassthruFunction( 'esc_attr' );
		$needle = '<label class="cms-toolkit-label block-label" for="field">label</label>';

		//act
		ob_start();
		$HTML->single_input( 'field', null, 'text', false, null, 'label', null, null );
		$haystack = ob_get_flush();

		//assert
		$this->assertContains( $needle, $haystack );
	}
	/**
	 * Tests that the single_input() method will draw input of given type.
	 *
	 * @group unstable
	 * @group single_input
	 */
	function testSingleInputDrawsGivenTypeInput() {		
		//arrange
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( null )
					 ->getMock();
		\WP_Mock::wpPassthruFunction( 'esc_attr' );
		$needle = '<input id="field" class="cms-toolkit-input set-input_12" name="field" type="text" maxlength="30" value="The text." placeholder="placeholder" required />';

		//act
		ob_start();
		$HTML->single_input( 'field', 'The text.', 'text', true, 30, 'label', 'placeholder', 12 );
		$haystack = ob_get_flush();

		//assert
		$this->assertContains( $needle, $haystack );
	}
	/**
	 * Tests that the boolean_input() method will draw label.
	 *
	 * @group unstable
	 * @group boolean_input
	 */
	function testBooleanInputPrintsLabel() {		
		//arrange
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( null )
					 ->getMock();
		\WP_Mock::wpPassthruFunction( 'esc_attr' );
		$needle = '<label class="cms-toolkit-label" for="field">label</label>';

		//act
		ob_start();
		$HTML->boolean_input( 'field', 'title', false, 'label', null );
		$haystack = ob_get_flush();

		//assert
		$this->assertContains( $needle, $haystack );
	}
	/**
	 * Tests that the boolean_input() method will draw checkbox.
	 *
	 * @group unstable
	 * @group boolean_input
	 */
	function testBooleanInputPrintsCheckboxInput() {		
		//arrange
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( null )
					 ->getMock();
		\WP_Mock::wpPassthruFunction( 'esc_attr' );
		$needle = '<input id="field" class="cms-toolkit-checkbox set-input_12" name="field" type="checkbox" checked required />';

		//act
		ob_start();
		$HTML->boolean_input( 'field', 'on', true, 'label', 12 );
		$haystack = ob_get_flush();

		//assert
		$this->assertContains( $needle, $haystack );
	}
	/**
	 * Tests that the link_input() method will draw div with class 'link-field'.
	 *
	 * @group unstable
	 * @group link_input
	 */
	function testURLInputPrintsDiv() {		
		//arrange
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( null )
					 ->getMock();
		\WP_Mock::wpPassthruFunction( 'esc_attr' );
		$needle = '<div class="link-field field_key">';

		//act
		ob_start();
		$HTML->link_input( 'field_key', null, null, null, null, null, null );
		$haystack = ob_get_flush();

		//assert
		$this->assertContains( $needle, $haystack );
	}
	/**
	 * Tests that the hidden() method will draw hidden input field.
	 *
	 * @group unstable
	 * @group hidden
	 */
	function testHiddenPrintsHiddenField() {		
		//arrange
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( null )
					 ->getMock();
		\WP_Mock::wpPassthruFunction( 'esc_attr' );
		$needle = '<input class="cms-toolkit-input set-input_12" id="field_key" name="field_key" type="hidden" value="value" />';

		//act
		ob_start();
		$HTML->hidden( 'field_key', 'value', 12 );
		$haystack = ob_get_flush();

		//assert
		$this->assertContains( $needle, $haystack );
	}
	/**
	 * Tests that the select() method will draw label.
	 *
	 * @group unstable
	 * @group select
	 */
	function testSelectWithoutTaxonomyPrintsLabel() {
		//arrange
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( null )
					 ->getMock();
		\WP_Mock::wpPassthruFunction( 'esc_attr' );
		$needle = '<label class="cms-toolkit-label block-label" for="field_key">label</label>';

		//act
		ob_start();
		$HTML->select( 'field_key', array(), false, false, null, false, null, 'label', null );
		$haystack = ob_get_flush();

		//assert
		$this->assertContains( $needle, $haystack );
	}
	/**
	 * Tests that the select() method without taxonomy will draw select with
	 * blank option and it selected.
	 *
	 * @group unstable
	 * @group select
	 */
	function testSelectWithoutTaxonomyAndWithoutOptionsPrintsSelectFieldWithOnlyBlankOption() {
		//arrange
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( null )
					 ->getMock();
		\WP_Mock::wpPassthruFunction( 'esc_attr' );
		$needle = '<select id="field_key" name="field_key[]" class="set-input_12" multiple required>';
		$needle .= '<option selected value="">--</option></select>';

		//act
		ob_start();
		$HTML->select( 'field_key', array(), false, false, null, true, '--', null, 12 );
		$haystack = ob_get_flush();

		//assert
		$this->assertContains( $needle, $haystack );
	}
	/**
	 * Tests that the select() method without taxonomy will draw select with
	 * blank option selected and other options.
	 *
	 * @group unstable
	 * @group select
	 */
	function testSelectWithoutTaxonomyAndWithOptionsPrintsSelectFieldWithBlankOptionSelected() {
		//arrange
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( null )
					 ->getMock();
		\WP_Mock::wpPassthruFunction( 'esc_attr' );
		$needle  = '<option selected value="">--</option>';
		$needle .= '<option value="option1">option1</option>';
		$needle .= '<option value="option2">option2</option></select>';

		//act
		ob_start();
		$HTML->select( 'field_key', array( 'option1', 'option2' ), false, true, null, false, '--', null, 12 );
		$haystack = ob_get_flush();

		//assert
		$this->assertContains( $needle, $haystack );
	}
	/**
	 * Tests that the select() method without taxonomy will draw select with
	 * blank option and selected option.
	 *
	 * @group unstable
	 * @group select
	 */
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
	/**
	 * Tests that the post_select() method without taxonomy will draw label.
	 *
	 * @group unstable
	 * @group post_select
	 */
	function testPostSelectPrintsLabel() {		
		//arrange
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( null )
					 ->getMock();
		\WP_Mock::wpPassthruFunction( 'esc_attr' );
		$needle = '<label class="cms-toolkit-label block-label" for="field_key">label</label>';

		//act
		ob_start();
		$HTML->post_select( 'field_key', array(), null, false, false, 'label', null, null );
		$haystack = ob_get_flush();

		//assert
		$this->assertContains( $needle, $haystack );
	}
	/**
	 * Tests that the post_select() method without taxonomy will draw select.
	 *
	 * @group unstable
	 * @group post_select
	 */
	function testPostSelectPrintsSelect() {		
		//arrange
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( null )
					 ->getMock();
		\WP_Mock::wpPassthruFunction( 'esc_attr' );
		$needle = '<select class="set-input_12" id="field_key" name="field_key[]" multi required >';

		//act
		ob_start();
		$HTML->post_select( 'field_key', array(), null, 'multi', true, '', '--', 12 );
		$haystack = ob_get_flush();

		//assert
		$this->assertContains( $needle, $haystack );
	}
	/**
	 * Tests that the post_select() method without taxonomy will draw blank option
	 * selected.
	 *
	 * @group unstable
	 * @group post_select
	 */
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
		$HTML->post_select( 'field_key', $posts, null, null, false, null, '--', 12 );
		$haystack = ob_get_flush();

		//assert
		$this->assertContains( $needle, $haystack );
	}
	/**
	 * Tests that the post_select() method without will draw select.
	 *
	 * @group unstable
	 * @group post_select
	 */
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
	/**
	 * Tests that the taxonomy_as_meta() method will draw select.
	 *
	 * @group unstable
	 * @group taxonomy_as_meta
	 */
	function testTaxonomyAsMetaPrintsSelect() {
		//arrange
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( null )
					 ->getMock();
		\WP_Mock::wpPassthruFunction( 'esc_attr' );
		$needle = '<select class="multi set-input_12" name="field_slug[]" multi required>';

		//act
		ob_start();
		$HTML->taxonomy_as_meta( 'field_slug', array(), 'tax', null, 'multi', true, '', '--', 12 );
		$haystack = ob_get_flush();

		//assert
		$this->assertContains( $needle, $haystack );
	}
	/**
	 * Tests that the taxonomy_as_meta() method will draw blank option
	 * selected when no value given.
	 *
	 * @group unstable
	 * @group taxonomy_as_meta
	 */
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
		$HTML->taxonomy_as_meta( 'field_slug', array( 'option1' ), 'tax', null, 'multi', true, '', '--', 12 );
		$haystack = ob_get_flush();

		//assert
		$this->assertContains( $needle, $haystack );
	}
	/**
	 * Tests that the taxonomy_as_meta() method will draw option with
	 * blank option and value option selected.
	 *
	 * @group unstable
	 * @group taxonomy_as_meta
	 */
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
	/**
	 * Tests that the date() method will draw select with blank option
	 * selected.
	 *
	 * @group unstable
	 * @group date
	 */
	function testDatePrintSelectElementWithGenericOptionSelected() {
		//arrange
		global $wp_locale;
		$term = new \StdClass;
		$term->name = '';
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( null )
					 ->getMock();
		$wp_locale->expects( $this->exactly( 12 ) )
				  ->method( 'get_month' )
				  ->will( $this->returnValue( 'month' ) );
		\WP_Mock::wpPassthruFunction( 'esc_attr' );
		\WP_Mock::wpFunction( 'has_term', array( 'return' => true ) );
		\WP_Mock::wpFunction( 'get_the_terms', array( 'return' => array( $term ) ) );
		$needle = '<select id="tax_month" name="tax_month" class="set-input_12">';
		$needle .= '<option selected="selected" value="" >Month</option>';

		//act
		ob_start();
		$HTML->date( 'tax', false ,false, '', 12 );
		$haystack = ob_get_flush();

		//assert
		$this->assertContains( $needle, $haystack );
	}
	/**
	 * Tests that the date() method will draw 12 options
	 *
	 * @group unstable
	 * @group date
	 */
	function testDatePrintsAll12Months() {
		//arrange
		global $wp_locale;
		$term = new \StdClass;
		$term->name = '';
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( null )
					 ->getMock();
		$wp_locale->expects( $this->exactly( 12 ) )
				  ->method( 'get_month' )
				  ->will( $this->returnValue( 'month' ) );
		\WP_Mock::wpPassthruFunction( 'esc_attr' );
		\WP_Mock::wpFunction( 'has_term', array( 'return' => true ) );
		\WP_Mock::wpFunction( 'get_the_terms', array( 'return' => array( $term ) ) );
		$needle = '<option value="month">month</option><option value="month">month</option>';
		$needle .= '<option value="month">month</option><option value="month">month</option>';
		$needle .= '<option value="month">month</option><option value="month">month</option>';
		$needle .= '<option value="month">month</option><option value="month">month</option>';
		$needle .= '<option value="month">month</option><option value="month">month</option>';
		$needle .= '<option value="month">month</option><option value="month">month</option></select>';

		//act
		ob_start();
		$HTML->date( 'tax', false ,false, '', 12 );
		$haystack = ob_get_flush();

		//assert
		$this->assertContains( $needle, $haystack );
	}
	function testTimeCallsSelect4Times() {
		//arrange
		$HTML = $this->getMockBuilder( '\CFPB\Utils\MetaBox\HTML' )
					 ->setMethods( array( 'select' ) )
					 ->getMock();
		$HTML->expects( $this->exactly( 4 ) )
			 ->method( 'select' );

		//act
		$HTML->time( 'slug', array('date' => '1970-01-01T00:33:35+00:00'), false, 'label', 1 );
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
		$HTML->datetime( 'slug', array('date' => '1970-01-01T00:33:35+00:00'), false, 'label', 1 );
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
