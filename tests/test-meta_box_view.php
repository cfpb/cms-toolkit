<?php
use \CFPB\Utils\MetaBox\View;
use \Mockery as m;

class MetaBoxGeneratorTest extends PHPUnit_Framework_TestCase {
	function setUp() {
		\WP_Mock::setUp();
	}

	function tearDown() {
		\WP_Mock::tearDown();
	}

	/**
	* Tests whether one field is calls assign_defaults()
	* @group stable
	* @group isolated
	* @group process_defaults
	* @group defaults
	*
	**/
	function testHiddenFieldExpectsAssignDefaultsToBeCalled() {
		// Arrange
		$fields = array(
			'field_one' => array(
				'title' => 'First',
				'slug' => 'field_one',
				'type' => 'hidden',
				'params' => array(),
				'howto' => 'hidden',
				'meta_key' => 'field_one',
				),
			);
		$stub = $this->getMockBuilder('\CFPB\Utils\MetaBox\View')
					 ->setMethods(array('assign_defaults'))
					 ->getMock();
		$stub->expects($this->once())
			 ->method('assign_defaults');
		
		// Act
		$stub->process_defaults(1, $fields['field_one'], array());
	}
	/**
	 * Tests whether default values are given when user does not supply a value
	 * for default_rows
	 *
	 * @group stable
	 * @group defaults
	 * @group isolated
	 */
	function testNoRowsExpects2RowsAdded() {
		// Arrange
		$field = array('key' => 'key', 'type'=>'text_area');
		$expected = 2;
		// Act
		$View = new View();
		$View->assign_defaults( 1, $field, null);
		// Assert
		$this->assertEquals($expected, $field['rows'], 'Default value for rows should be 2');
	}

	/**
	 * Tests whether given values are used for default_rows
	 *
	 * @group stable
	 * @group defaults
	 * @group isolated
	 *
	 */
	function testRowsGivenDefaultRowsExpectsGivenValuesUsed() {
		// Arrange
		$field = array('key' => 'key', 'type'=>'text_area','params' => array('rows' => 1));
		$expected = 1;

		// Act
		$View = new View();
		$View->assign_defaults(1,$field,null);

		// Assert
		$this->assertEquals($expected, $field['rows'], 'Number of rows should be 1');
	}

/**
	 * Tests whether default values are given when user does not supply a value
	 * for default_cols
	 *
	 * @group stable
	 * @group defaults
	 * @group isolated
	 */
	function testNoColsExpects27ColsAdded() {
		// Arrange
		$field = array('key' => 'key', 'type'=>'text_area','params' => array('cols' => 1));
		$expected = 1;

		// Act
		$View = new View();
		$View->assign_defaults(1,$field,null);

		// Assert
		$this->assertEquals($expected, $field['cols'], 'Number of rows should be 1');
	}

	/**
	 * Tests whether an empty field array reurns an mpty array for input_defaults
	 * @group stable
	 * @group isolated
	 * @group defaults
	 *
	 */
	function testIncludedOptionsEmptyDefaultOptionsExpectsEmptyIncludeKey () {
		// Arrange
		$field = array('key' => 'key', 'type'=>'tax_as_meta');
		$expected = array();

		// Act
		$View = new View();
		$actual = $View->assign_defaults(1,$field, null);

		// Assert
		$this->assertEquals($expected, $field['include'], 'Options array should be empty but isn\'t');
	}

	/**
	 * Tests whether a field passed with an includes value under the params key is
	 * returned
	 *
	 * @group stable
	 * @group isolated
	 * @group defaults
	 */
	function testDefaultOptionsExpectsGivenArrayReturned() {
		$field = array('key' => 'key', 'type'=>'tax_as_meta','params'=>array('include' => array(1,2,3,4,5)));
		$expected = array(1,2,3,4,5,);

		// Act
		$View = new View();
		$View = new View();
		$actual = $View->assign_defaults(1,$field, null);

		// Assert
		$this->assertEquals($expected, $field['include'], 'The given array should have been assigned');
	}

	/**
	 * Tests whether ready_and_print calls \HTML->Draw();
	 *
	 * @group stable
	 * @group isolated
	 * @group defaults
	 *
	 */
	function testReadyAndPrintExpectsProcessDefaultsAndHTMLDraw() {
		// Arrange
		$fields = array(
			'args' => array(
				'field_one' => array(
					'title' => 'First',
					'slug' => 'field_one',
					'type' => 'text',
					'params' => array(),
					'howto' => 'hidden',
					'meta_key' => 'field_one',
					),
				),
			);
		$expected = array(
			'field_one' => array(
				'title' => 'First',
				'slug' => 'field_one',
				'type' => 'text',
				'params' => array(),
				'howto' => 'hidden',
				'meta_key' => 'field_one',
				'max_length' => 255,
				'placeholder' => null,
				'label' => null,
				'value' => null
			),
		);
		$HTML = $this->getMock('\CFPB\Utils\MetaBox\HTML', array('draw'));

		$ready = $this->getMockBuilder('\CFPB\Utils\MetaBox\View')
					->setMethods(array('process_defaults'))
					->getMock();
		$ready->replace_HTML($HTML);
		$returned = $ready->expects($this->once())
						  ->method('process_defaults');
		\WP_Mock::wpPassThruFunction('get_post_meta');
		\WP_Mock::wpFunction('get_the_ID');
		$post = new StdClass;
		$HTML->expects($this->once())
					->method('draw');
		// Act
		$ready->ready_and_print_html($post, $fields);

		// Assert

	}

	function testFieldsetFieldCallsAssignDefaultsAndDefaultValuePerField() {
		// arrange
		$fields = array(
			'field' => array(
				'type' => 'fieldset',
				'fields' => array(
					array(
						'type' => 'text',
						'meta_key' => 'num',
					),
					array(
						'type' => 'text',
						'meta_key' => 'desc',
					),
				),
				'params' => array(),
				'key' => 'field',
			),
		);
		$stub = $this->getMockBuilder('\CFPB\Utils\MetaBox\View')
					 ->setMethods( array( 'assign_defaults', ) )
					 ->getMock();
		$stub->expects( $this->exactly( 2 ) )
			 ->method( 'assign_defaults' )
			 ->will( $this->returnValue( array() ) );

		// act
		$stub->process_defaults( 1, $fields['field'], null );

		// assert
		// Passes when each field (including the fieldset field) gets a call to assign_defaults and default_value
	}

	function testOtherFieldTypesWithMetakeySetToCallAssignDefaults() {
		$fields = array(
			'field' => array(
				'slug' => 'field',
				'title' => 'This is another field',
				'type' => 'number',
				'params' => array(),
				'placeholder' => '0-100',
				'howto' => 'Type a number',
				'meta_key' => 'field',
			),
		);
		$stub = $this->getMockBuilder('\CFPB\Utils\MetaBox\View')
					 ->setMethods( array( 'assign_defaults', ) )
					 ->getMock();
		$stub->expects( $this->once() )
			 ->method( 'assign_defaults' )
			 ->will( $this->returnValue( true ) );

		//act
		$stub->process_defaults( 1, $fields['field'], null );

		//assert
		// Passes when process_defaults is called on field
	}

	function testOtherFieldTypesWithoutMetakeySetAndSlugSetToCallAssignDefaults() {
		$fields = array(
			'field' => array(
				'meta_key' => 'field',
				'title' => 'This is another field',
				'type' => 'number',
				'params' => array(),
				'placeholder' => '0-100',
				'howto' => 'Type a number',
			),
		);
		$stub = $this->getMockBuilder('\CFPB\Utils\MetaBox\View')
					 ->setMethods( array( 'assign_defaults', 'default_value' ) )
					 ->getMock();
		$stub->expects( $this->once() )
			 ->method( 'assign_defaults' )
			 ->will( $this->returnValue( true ) );

		//act
		$stub->process_defaults( 1, $fields['field'], null );

		//assert
		// Passes when process_defaults is called on field
	}

	function testProcessDefaultsIsCalledForEachFieldInProcessFormsetDefaults() {
		// arrange
		$fields = array(
			'field' => array(
				'title' => 'Title',
				'type' => 'text',
				'key' => 'title',
				'params' => array(
					'repeated' => array(
						'min'=>1,
						'max'=>2
					),
				),
			),
		);
		$stub = $this->getMockBuilder('\CFPB\Utils\MetaBox\View')
					 ->setMethods( array( 'process_repeated_field_params' ) )
					 ->getMock();
		$stub->expects( $this->exactly( 1 ) )
			 ->method( 'process_repeated_field_params' );
		$ready = array();

		// act
		$stub->process_defaults( 1, $fields['field'], null );

		//assert
		// Passes when each field of each formset calls process_defaults
	}

	function testAssignDefaultsSetsWYSIWYGSettingsArrayIfNotSet() {
		//arrange
		$View = new View();
		$field = array('key' => 'key', 'type' => 'wysiwyg', 'settings' => null );

		//act
		$View->assign_defaults( 1, $field, null );

		// assert
		$this->assertTrue( isset( $field['settings'] ) );
	}

	function testAssignDefaultsAddsClassToWYSIWYGEditorClassSetting() {
		//arrange
		$View = new View();
		$field = array('key' => 'key', 'type' => 'wysiwyg', 'settings' => array( 'editor_class' => 'class') );

		//act
		$View->assign_defaults( 1, $field, null );

		// assert
		$this->assertEquals( $field['settings']['editor_class'], "class cms-toolkit-wysiwyg" );
	}

	function testAssignDefaultsDoesNotSetTaxonomyToFalseForGivenFields() {
		// arrange
		$View = new View();
		$field = array('key' => 'key', 'type' => '', 'taxonomy' => true );
		$types = array( 'taxonomyselect', 'tax_as_meta', 'date', 'time', 'datetime' );

		//act
		foreach ( $types as $type ) {
			$field['type'] = $type;		
			$View->assign_defaults( 1, $field, null );
			// assert
			$this->assertEquals( $field['taxonomy'], true );
		}
	}

	function testDefaultValueDoesNotSetValueForGivenFields() {
		// arrange
		global $post;
		$View = new View();
		$field = array('key' => 'key', 'type' => '' );
		$types = array( 'link', 'date', 'time', 'datetime' );

		foreach ( $types as $type ) {
			$field['type'] = $type;		
			// act
			$View->assign_defaults( 1, $field, null );
			// assert
			$this->assertEquals( isset( $field['value'] ), false );
		}
	}
}
