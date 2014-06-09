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
	* Tests whether a field given minimal details is assigned certain defaults
	* @group stable
	* @group isolated
	* @group process_defaults
	*
	**/
	function testHiddenFieldExpectsDefaults() {
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

		\WP_Mock::wpFunction(
			'get_post_meta',
			array('times' => 1,
				'return' => false
				)
			);

		\WP_Mock::wpFunction('get_the_ID', array('times' => 1));
		$View = new View();
		$expected = array(
			'field_one' => array(
				'title' => 'First',
				'slug' => 'field_one',
				'type' => 'hidden',
				'meta_key' => 'field_one',
				'value' => '',
				'params' => array(),
				'howto' => 'hidden',
				'label' => '',
				),
			);
		// Act
		$cleaned = $View->process_defaults($fields);

		// Assert
		$this->assertEquals($expected, $cleaned, 'Defaults were not assigned for some elements.');
	}

	/**
	 * Tests whether when given a value, process_defaults will use that value and
	 * not the default empty value
	 *
	 * @group stable
	 * @group isolated
	 * @group process_defaults
	 *
	 */
	function testHiddenFieldValueGivenExpectsDrawCalledValueUnchanged() {
		// Arrange
		$fields = array(
			'field_one' => array(
				'title' => 'First',
				'slug' => 'field_one',
				'type' => 'hidden',
				'params' => array(),
				'howto' => 'hidden',
				'meta_key' => 'field_one',
				'value' => 'Default',
				),
			);

		\WP_Mock::wpFunction('get_post_meta', array('times' => 1, 'return' => false));
		\WP_Mock::wpFunction('get_the_ID', array('times' => 1));
		$View = new View();
		$expected = array(
			'field_one' => array(
				'title' => 'First',
				'slug' => 'field_one',
				'type' => 'hidden',
				'meta_key' => 'field_one',
				'value' => 'Default',
				'params' => array(),
				'howto' => 'hidden',
				'label' => '',
				),
			);
		// Act
		$cleaned = $View->process_defaults($fields);

		// Assert
		$actual = $cleaned['field_one']['value'];
		$this->assertEquals(
			$expected,
			$cleaned,
			'Draw value was changed from Default to ' . $actual
		);
	}

	/**
	 * Tests the 'meta data exists' path of the View\default_value method
	 *
	 * @group stable
	 * @group isolated
	 * @group defaults
	 *
	 */
	function testExistingDataDefaultValueExpectsExistingDataUsed() {
		// arrange
		$field['meta_key'] = 'key';
		$field['type'] = 'text';
		$ID = 1;
		\WP_Mock::wpFunction('get_post_meta', array('times' => 1, 'return' => 'data'));
		$View = new View();
		$expected = 'data';

		// act
		$actual = $View->default_value($ID, $field);

		// assert
		$this->assertEquals($expected, $actual, 'Expected value was ' . $expected . ' but returned ' . $actual);
	}

	/**
	 * Tests the 'meta data does not exist' path of the View\default_value method
	 *
	 * @group stable
	 * @group isolated
	 * @group defaults
	 *
	 */
	function testNoExistingDataDefaultValueExpectsDefaultUsed() {
		// arrange
		$field['meta_key'] = 'key';
		$field['type'] = 'text';
		$ID = 1;
		\WP_Mock::wpFunction('get_post_meta', array('times' => 1, 'return' => false));
		$View = new View();
		$expected = '';

		// act
		$actual = $View->default_value($ID, $field);

		// assert
		$this->assertEquals($expected, $actual, 'Expected value was ' . $expected . ' but returned ' . $actual);
	}

	function testFieldTypeLinkExpectsDefaultValueNull() {
		// arrange
		$field['type'] = 'link';
		$field['meta_key'] = 'foo';
		$ID = 1;
		$expected = null;
		$View = new View();

		// act
		$actual = $View->default_value( $ID, $field );

		// assert
		$this->assertEquals( $expected, $actual, 'Expected value was ' . $expected . ' but returned ' . $actual );
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
		$field = array();
		$expected = 2;
		// Act
		$View = new View();
		$actual = $View->default_rows($field);
		// Assert
		$this->assertEquals($expected, $actual, 'Default value for rows should be 2');
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
		$field = array('params' => array('rows' => 1));
		$expected = 1;

		// Act
		$View = new View();
		$actual = $View->default_rows($field);

		// Assert
		$this->assertEquals($expected, $actual, 'Number of rows should be 1');
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
		$field = array();
		$expected = 27;
		// Act
		$View = new View();
		$actual = $View->default_cols($field);
		// Assert
		$this->assertEquals($expected, $actual, 'Default value for columns should be 27');
	}

	/**
	 * Tests whether given values are used for default_cols
	 *
	 * @group stable
	 * @group defaults
	 * @group isolated
	 *
	 */
	function testColsGivenDefaultColsExpectsGivenValuesUsed() {
		// Arrange
		$field['params'] = array('cols' => 1);
		$expected = 1;

		// Act
		$View = new View();
		$actual = $View->default_cols($field);

		// Assert
		$this->assertEquals($expected, $actual, 'Number of columns should be ' . $expected . ' but was ' . $actual);
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
		$field = array();
		$expected = array();

		// Act
		$View = new View();
		$actual = $View->default_options($field);

		// Assert
		$this->assertEquals($expected, $actual, 'Options array should be empty but isn\'t');
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
		$field['params'] = array('include' => array(1,2,3,4,5));
		$expected = array(1,2,3,4,5,);

		// Act
		$View = new View();
		$actual = $View->default_options($field);

		// Assert
		$this->assertEquals($expected, $actual, 'The given array should have been assigned');
	}

	/**
	 * Tests whether when more than one field is passed and one is hidden if both
	 * fields are generated with the correct values.
	 * @group maybe_delete
	 * @group defaults
	 * @group incomplete
	 *
	 */
	function testHiddenAndNonHiddenFieldsPassedExpectsWPAPIMethodsTwice() {
		// Arrange
		$fields = array(
			'field_one' => array(
				'title' => 'First',
				'slug' => 'field_one',
				'type' => 'text',
				'params' => array(),
				'howto' => 'hidden',
				'meta_key' => 'field_one',
				),
			'field_two' => array(
				'title' => 'First',
				'slug' => 'field_two',
				'type' => 'hidden',
				'params' => array(),
				'howto' => 'hidden',
				'meta_key' => 'field_two',
				),
			);

		\WP_Mock::wpFunction('get_post_meta', array('times' => 2, 'return' => null));
		\WP_Mock::wpFunction('get_the_ID', array('times' => 2));
		$HTML = $this->getMock('HTML');
		$View = new View();
		$View->replace_HTML($HTML);
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
			'field_two' => array(
				'title' => 'First',
				'slug' => 'field_two',
				'type' => 'hidden',
				'params' => array(),
				'howto' => 'hidden',
				'meta_key' => 'field_two',
				'value' => '',
				'label' => '',
			),
		);
		// Act
		$cleaned = $View->process_defaults($fields);

		// Assert
		$this->assertEquals($expected, $cleaned);
	}
	/**
	 * Tests whether an invalid input type generates a WordPress error
	 *
	 * @group stable
	 * @group isolated
	 * @group defaults
	 */
	function testInvalidInputExpectsWPError() {
		$fields= array(
			0 => array(
				'type' => 'foo',
			)
		);

		$mock = $this->getMock('WP_Error');
		$HTML = $this->getMock('HTML');
		$View = new View();
		$View->replace_HTML($HTML);

		$cleaned = $View->process_defaults($fields);

		$this->assertInstanceOf('WP_Error', $cleaned);
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
			->method('process_defaults')
			// ->with($fields)
			->will($this->returnValue($expected));
		$post = new StdClass;
		// print_r($HTML);
		// $HTML->expects($this->once())
		// 	->method('draw')
		// 	->with($returned);
		// Act
		$ready->ready_and_print_html($post, $fields);

		// Assert

	}

	/**
	 * If not given a meta_key, the metabox should use the object terms attached
	 * to $field['taxonomy']
	 *
	 * @group stable
	 * @group isolated
	 * @group defaults
	 *
	 */
	function testProcessDefaultsTaxonomyGivenWithoutMetaKeyExpectsGetObjectTermsReturned() {
		// Arrange
		$fields = array(
			'field' => array(
				'type' => 'taxonomyselect',
				'taxonomy' => 'category',
				'slug' => 'field'),
			);
		$expected = array(
			'field' => array(
				'type' => 'taxonomyselect',
				'taxonomy' => 'category',
				'slug' => 'field',
				'max_length' => 255,
				'label' => '',
				'placeholder' => '',
				'value' => 'term'
			),
		);
		$stub = $this->getMockBuilder('\CFPB\Utils\MetaBox\View')
			->setMethods(array('default_max_length',
				'default_placeholder',
				'default_label',
				'default_value',)
			)
			->getMock();
		$stub->expects($this->once())
			->method('default_max_length')
			->will($this->returnValue(255));
		$stub->expects($this->once())
			->method('default_label')
			->will($this->returnValue(''));
		$stub->expects($this->once())
			->method('default_placeholder')
			->will($this->returnValue(''));
		\WP_Mock::wpFunction(
			'wp_get_object_terms',
			array('times'=>1, 'return' => 'term'));
		\WP_Mock::wpFunction(
			'get_the_ID',
			array('times' => 1, 'return' => 1));
		// Act
		$actual = $stub->process_defaults($fields);

		// Assert
		$this->assertEquals($expected, $actual);
	}

	/**
	 * Tests that a text_area field calls for default_rows and default_cols
	 *
	 * @group stable
	 * @group isolated
	 * @group defaults
	 *
	 */
	function testTextAreaProcessDefaultsExpectsRowsAndCols() {
		// Arrange
		$fields = array(
			'field' => array(
				'slug' => 'field',
				'type' => 'text_area',
				'meta_key' => 'cfpb',
				),
			);
		$expected = array(
			'field' => array(
				'slug' => 'field',
				'type' => 'text_area',
				'meta_key' => 'cfpb',
				'max_length' => 255,
				'placeholder' => '',
				'label' => '',
				'rows' => 2,
				'cols' => 27,
				'value' => '',
				),
			);
		$stub = $this->getMockBuilder('\CFPB\Utils\MetaBox\View')
			->setMethods(array('default_max_length',
				'default_placeholder',
				'default_label',
				'default_value',
				'default_rows',
				'default_cols',
				))
			->getMock();
		$stub->expects($this->once())
			->method('default_max_length')
			->will($this->returnValue(255));
		$stub->expects($this->once())
			->method('default_label')
			->will($this->returnValue(''));
		$stub->expects($this->once())
			->method('default_placeholder')
			->will($this->returnValue(''));
		$stub->expects($this->once())
			->method('default_value')
			->will($this->returnValue(''));
		$stub->expects($this->once())
			->method('default_rows')
			->will($this->returnValue(2));
		$stub->expects($this->once())
			->method('default_cols')
			->will($this->returnValue(27));
		\WP_Mock::wpFunction(
			'get_the_ID',
			array('times' => 1, 'return' => 1));
		// Act
		$actual = $stub->process_defaults($fields);

		// Assert
		$this->assertEquals($expected, $actual);
	}

	/**
	 * Tests that a tax_as_meta field calls for default_rows and default_cols
	 *
	 * @group stable
	 * @group isolated
	 * @group defaults
	 *
	 */
	function testTaxAsMetaProcessDefaultsExpectsDefaultOptions() {
		// Arrange
		$fields = array(
			'field' => array(
				'slug' => 'field',
				'type' => 'tax_as_meta',
				'meta_key' => 'cfpb',
				),
			);
		$expected = array(
			'field' => array(
				'slug' => 'field',
				'type' => 'tax_as_meta',
				'meta_key' => 'cfpb',
				'max_length' => 255,
				'placeholder' => '',
				'label' => '',
				'value' => '',
				'include' => '',
				),
			);
		$stub = $this->getMockBuilder('\CFPB\Utils\MetaBox\View')
			->setMethods(array('default_max_length',
				'default_placeholder',
				'default_label',
				'default_value',
				'default_options',
				))
			->getMock();
		$stub->expects($this->once())
			->method('default_max_length')
			->will($this->returnValue(255));
		$stub->expects($this->once())
			->method('default_label')
			->will($this->returnValue(''));
		$stub->expects($this->once())
			->method('default_placeholder')
			->will($this->returnValue(''));
		$stub->expects($this->once())
			->method('default_value')
			->will($this->returnValue(''));
		$stub->expects($this->once())
			->method('default_options')
			->will($this->returnValue(''));
		\WP_Mock::wpFunction(
			'get_the_ID',
			array('times' => 1, 'return' => 1));
		// Act
		$actual = $stub->process_defaults($fields);

		// Assert
		$this->assertEquals($expected, $actual);
	}
}
