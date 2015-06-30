<?php
use CFPB\Utils\MetaBox\Models;
use CFPB\Utils\MetaBox\Callbacks;

class TestValidBox extends Models {
	public $title = 'Meta Box';
	public $slug = 'meta_box';
	public $post_type = 'post';
	public $context = 'side';
	public $priority = 'default';
	public $fields = array(
		'field_one' => array(
			'title' => 'This is a field',
			'key' => 'field_one',
			'type' => 'text_area',
			'params' => array(
				'cols' => 27,
			),
			'placeholder' => 'Enter text',
			'howto' => 'Type some text',
			'value' => '',
		),
		'field_two' => array(
			'key' => 'field_two',
			'title' => 'This is another field',
			'type' => 'number',
			'params' => array(),
			'placeholder' => '0-100',
			'howto' => 'Type a number',
			'key' => 'field_two',
			'value' => '',
		),
	);
}

class TestNumberField extends Models {
	public $title = 'Meta Box';
	public $slug = 'meta_box';
	public $post_type = 'post';
	public $context = 'side';
	public $fields = array(
		'field_one' => array(
			'key' => 'field_one',
			'title' => 'This is another field',
			'type' => 'number',
			'params' => array(),
			'placeholder' => '0-100',
			'howto' => 'Type a number',
		),
	);
}

class TestValidTextField extends Models {
	public $post_type = 'post';
	public $fields = array(
		'field' => array(
			'title' => 'Text Field',
			'key' => 'field',
			'label' => 'Text Label',
			'type' => 'text',
			'params' => array('max_length' => 255),
			'placeholder' => 'Type some text',
			'howto' => 'Up to 255 characters',
		),
	);
}

class TestValidTextAreaField extends Models {
	public $post_type = 'post';
	public $fields = array(
		'field' => array(
			'title' => 'Text Area Field',
			'key' => 'field',
			'label' => 'Text Label',
			'type' => 'text_area',
			'params' => array('max_length' => 255),
			'rows' => 5,
			'cols' => 5,
			'placeholder' => 'Type some text',
			'howto' => 'Up to 255 characters',
		),
	);
}

class TestValidEmailField extends Models {
	public $post_type = 'post';
	public $fields = array(
		'field' => array(
			'title' => 'Email Field',
			'key' => 'field',
			'label' => 'Email Label',
			'type' => 'email',
			'params' => array(),
			'placeholder' => 'Type some text',
			'howto' => 'Up to 255 characters',
		),
	);
}

class TestValidDateField extends Models {
	public $post_type = 'post';
	public $fields = array(
		'category' => array(
			'title' => 'Issued date:',
			'key' => 'category',
			'label' => '',
			'type' => 'date',
			'params' => array(),
			'multiple' => false,
			'placeholder' => '',
			'howto' => '',
			'taxonomy' => 'category',
		),
	);
}

class TestValidFieldsetField extends Models {
	public $post_type = 'post';
	public $fields = array(
		'field' => array(
			'type' => 'fieldset',
			'fields' => array(
				array(
					'type' => 'text',
					'key' => 'num',
					'key' => 'num',
				),
				array(
					'type' => 'text',
					'key' => 'desc',
					'key' => 'desc',
				),
			),
			'params' => array(),
			'key' => 'field',
			'key' => 'field',
		),
	);
}

class TestRepeatedFields extends Models {
	public $post_type = 'post';
	public $fields = array(
		'fields' => array(
			'type' => 'text',
			'key' => 'field',
			'key' => 'field',
			'label' => 'Text',
			'params' => array(
				'repeated' => array(
					'min' => 1,
					'max' => 2,
				),
			),
		),
	);
}

class ValidationTest extends PHPUnit_Framework_TestCase {
	function setUp() {
		global $post;
		$post = new \StdClass;
		$post->ID = 1;
		$post->post_type = 'post';
		\WP_Mock::setUp();
	}

	function tearDown() {
		\WP_Mock::tearDown();
	}

/***************************
 * Error handling tests *
 ***************************/
	/**
	 * Tests whether the validate_and_save method will throw an error if there
	 * is an empty fields array.
	 *
	 *
	 * @group stable
	 * @group empty_data
	 * @group isolated
	 * @group validation
	 */
	function testEmptyFieldsArrayExpectsError() {
		// arrange
		global $post;
		$TestValidTextField = $this->getMockBuilder( 'TestValidTextField' )
					 ->setMethods( array('delete_old_data') )
					 ->getMock();
		$TestValidTextField->fields = array();
		\WP_Mock::wpFunction( 'wp_die', array( 'times' => 1 ) );

		// act
		$TestValidTextField->validate_and_save( $post->ID );

		// assert
		// Passes when error is called
	}
	/**
	 * Tests if the validate method will throw an error if there
	 * is the type of the field is not set.
	 *
	 *
	 * @group stable
	 * @group empty_data
	 * @group isolated
	 * @group validation
	 */
	function testFieldTypeIsNotSetExpectsError() {
		// arrange
		global $post;
		$TestValidTextField = new TestValidTextField();
		$TestValidTextField->fields['field']['type'] = null;
		\WP_Mock::wpFunction( 'wp_die', array( 'times' => 1 ) );
		$validated = array();
		$saved = array();

		// act
		$TestValidTextField->validate( $post->ID, $TestValidTextField->fields['field'], $validated, $saved );

		// assert
		// Passes when error is called
	}
	/**
	 * Tests if the validate_keys method will throw an error if the field does not have
	 * the key, key, or taxonomy set.
	 *
	 *
	 * @group stable
	 * @group empty_data
	 * @group isolated
	 * @group validation
	 */
	function testFieldMetakeySlugTaxonomyIsNotSetExpectsError() {
		// arrange
		global $post;
		$TestValidTextField = new TestValidTextField();
		$TestValidTextField->fields['field']['key'] = null;
		$TestValidTextField->fields['field']['key'] = null;
		$TestValidTextField->fields['field']['taxonomy'] = null;
		\WP_Mock::wpFunction( 'wp_die', array( 'times' => 1 ) );

		// act
		$TestValidTextField->validate_keys( $TestValidTextField->fields['field'] );

		// assert
		// Passes when error is called
	}
	/**
	 * Tests if the validate method will throw an error if the field does not have
	 * the taxonomy set when required for taxonomyselect field.
	 *
	 *
	 * @group stable
	 * @group empty_data
	 * @group isolated
	 * @group validation
	 */
	function testFieldTypeTaxonomyselectWhereTaxonomyIsNotSetExpectsError() {
		// arrange
		global $post;
		$TestValidTextField = new TestValidTextField();
		$TestValidTextField->fields['field']['taxonomy'] = null;
		$TestValidTextField->fields['field']['type'] = 'taxonomyselect';
		\WP_Mock::wpFunction( 'wp_die', array( 'times' => 1 ) );
		$validated = array();
		$saved = array();

		// act
		$TestValidTextField->validate( $post->ID, $TestValidTextField->fields['field'], $validated, $saved );

		// assert
		// Passes when error is called
	}
	/**
	 * Tests if the validate method will throw an error if the field does not have
	 * the taxonomy set when required for date field.
	 *
	 *
	 * @group stable
	 * @group empty_data
	 * @group isolated
	 * @group validation
	 */
	function testFieldTypeDateWhereTaxonomyIsNotSetExpectsError() {
		// arrange
		global $post;
		$TestValidTextField = $this->getMockBuilder( 'TestValidTextField' )
					 ->setMethods( array('validate_datetime') )
					 ->getMock();
		$TestValidTextField->fields['field']['taxonomy'] = null;
		$TestValidTextField->fields['field']['type'] = 'date';
		\WP_Mock::wpFunction( 'wp_die', array( 'times' => 1 ) );
		$validated = array();
		$saved = array();

		// act
		$TestValidTextField->validate( $post->ID, $TestValidTextField->fields['field'], $validated, $saved );

		// assert
		// Passes when error is called
	}
/***************************
 * Validation method tests *
 ***************************/
	/**
	 * Tests whether the validate method will save a null value to the array if
	 * data from $_POST is missing
	 *
	 * If a form is submitted with a field deleted, no key for that field is
	 * assigned in the $_POST superglobal. This is fine if the field is already
	 * empty but if the field is pre-populated it can make getting rid of the
	 * data impossible. This passes the key with a null value to make the data
	 * more reliable for `save`.
	 *
	 * @group stable
	 * @group empty_data
	 * @group isolated
	 * @group validation
	 */
	function testEmptyPOSTExpectsNullArrayForFieldKey() {
		// arrange
		$_POST = array();
		global $post;
		$TestValidTextField = new TestValidTextField();
		$validated = array();
		$saved = array();

		// act
		$TestValidTextField->validate( $post->ID, $TestValidTextField->fields['field'], $validated, $saved );

		// assert
		$this->assertTrue( empty( $validated ) );
	}
	/**
	 * Tests whether the validate method when called on an email field calls
	 * 'sanitize_email' from the WP API once
	 *
	 * @group stable
	 * @group isolated
	 * @group user_input
	 * @group validation
	 */

	function testEmailExpectsSanitizeMethodCalled() {
		// arrange
		global $post;
		\WP_Mock::wpPassthruFunction('sanitize_email', array('times' => 1));
		$TestValidEmailField = new TestValidEmailField();
		$_POST = array(
			'field' => 'foo@bar.baz',
		);
		$actual = array();
		// act
		$TestValidEmailField->validate($post->ID, $TestValidEmailField->fields['field'], $actual);
	}

	/**
	 * Tests whether a number field has data replaced
	 *
	 * @group number
	 * @group stable
	 * @group isolated
	 * @group user_input
	 * @group validation
	 */
	function testValidNumberFieldExpectsDataReturned() {
		$TestNumberField = new TestNumberField();
		$_POST = array(
			'post_ID' => 1,
			'field_one' => 2,
		);
		$validated = array();

		// act
		$TestNumberField->validate($_POST['post_ID'], $TestNumberField->fields['field_one'], $validated['field_one'], array());

		// assert
		$expected = 2;
		$this->assertEquals(
			$expected,
			$validated['field_one'],
			'Numeric strings should be accepted and converted to a number.');
	}

	/**
	 * Tests whether an invalid number is not accepted
	 *
	 * @group stable
	 * @group isolated
	 * @group number
	 * @group user_input
	 * @group validation
	 *
	 */
	function testInvalidNumericFieldExpectsDataRemoved() {

		// arrange
		$TestNumberField = new TestNumberField();
		$_POST = array(
			'post_ID' => 1,
			'field_one' => 'Two',
		);
		$validated = array();

		// act
		$TestNumberField->validate($_POST['post_ID'], $TestNumberField->fields['field_one'], $validated['field_one'], array());
		// assert
		$expected = null;
		$this->assertEquals(
			$expected,
			$validated['field_one'],
			'Non-numeric strings should not be accepted for a number input type.'
		);
	}

	/**
	 * Tests whether a text field accepts and returns a textual string
	 *
	 * @group user_input
	 * @group stable
	 * @group isolated
	 * @group text
	 * @group validation
	 */
	function testTextFieldExpectsStringReturned() {

		// arrange
		$TestValidTextField = new TestValidTextField();
		$TestValidTextField->fields['field']['type'] = 'text';
		$_POST = array(
			'post_ID' => 1,
			'field' => 'Text field expects a string',
		);
		$validated = array();

		// act
		$TestValidTextField->validate($_POST['post_ID'], $TestValidTextField->fields['field'], $validated['field'], array());

		// assert
		$this->assertEquals(
			'Text field expects a string',
			$validated['field']
		);
	}

	/**
	 * Tests wheter an integer is converted to a string if field == text
	 *
	 * @group text
	 * @group user_input
	 * @group stable
	 * @group isolated
	 * @group validation
	 */
	function testTextFieldGivenNumberExpectsNumericStringValueReturned() {

		// arrange
		global $post;
		$TestValidTextField = new TestValidTextField();
		$TestValidTextField->fields['field']['type'] = 'text';
		$_POST = array(
			'post_ID' => 1,
			'field' => 1,
		);
		$validated = array();

		// act
		$TestValidTextField->validate($_POST['post_ID'], $TestValidTextField->fields['field'], $validated['field'], array());

		// assert
		$this->assertEquals('1', $validated['field']);
	}

	/**
	 * Tests whether a text area field accepts and returns a string.
	 *
	 * @group textarea
	 * @group isolated
	 * @group stable
	 * @group validation
	 *
	 */
	function testTextAreaFieldExpectsStringReturned() {
		// arrange
		global $post;
		$post = new \StdClass();
		$post->ID = 1;
		$post->post_type = 'post';
		$TestValidTextAreaField = new TestValidTextAreaField();
		$_POST = array(
			'post_ID' => 1,
			'field' => 'Foo',
		);
		$validated = array();

		// act
		$TestValidTextAreaField->validate($_POST['post_ID'], $TestValidTextAreaField->fields['field'], $validated['field'], array());

		//assert
		$this->assertEquals('Foo', $validated['field']);
	}

	/**
	 * tests whether an array is not accepted and an error is returned.
	 * @group strings
	 * @group isolated
	 * @group user_input
	 * @group stable
	 * @group returns_null
	 * @group validation
	 */
	function testTextAreaFieldNonStringExpectsNullReturned() {
		// arrange
		global $post;
		$TestValidTextAreaField = new TestValidTextAreaField();
		$_POST = array(
			'post_ID' => 1,
			'field' => null,
		);
		$validated = array();

		// act
		$TestValidTextAreaField->validate($_POST['post_ID'], $TestValidTextAreaField->fields['field'], $validated['field'], array());

		// assert
		$this->assertTrue( ! isset( $validated['field'] ) );
	}

	/**
	 * Tests whether url field data will be sent to WordPress and sanitized
	 *
	 * @group urls
	 * @group user_input
	 * @group stable
	 * @group isolated
	 * @group validation
	 */
	function testURLFieldExpectsEscRawURLCall() {
		// arrange
		global $post;
		\WP_Mock::wpPassthruFunction(
			'esc_url_raw',
			array('times' => 1, 'return' => 'http://google.com')
		);
		$TestValidEmailField = new TestValidEmailField();
		$TestValidEmailField->fields['field']['type'] = 'url';
		$_POST = array(
			'field' => 'http://google.com',
		);
		$validated = array();

		// act
		$TestValidEmailField->validate(1, $TestValidEmailField->fields['field'], $validated['field'], array());

		// assert
		$this->assertEquals($validated['field'], 'http://google.com');
	}

	/**
	 * Tests whether validate will call the appropriate special validator for
	 * a taxonomy select field.
	 *
	 * @group stable
	 * @group isolated
	 * @group taxonomy_select
	 * @group validation
	 */
	function testTaxonomySelectFieldExpectsTaxonomySelectValidatorCalled() {
		// arrange
		global $post;
		$factory = $this->getMockBuilder('TestValidDateField')
						->setMethods(array('validate_taxonomyselect',))
						->getMock();
		$factory->fields['category']['type'] = 'taxonomyselect';

		$factory->expects($this->once())
				->method('validate_taxonomyselect')
				->will($this->returnValue(true));
		$actual = array();

		// act
		$factory->validate($post->ID, $factory->fields['category'], $actual);

		// assert
		// Test will fail if validate_taxonomyselect called more than once
	}

	/**
	 * Tests whether validate will call the appropriate special validator for
	 * a select field.
	 *
	 * @group stable
	 * @group isolated
	 * @group taxonomy_select
	 * @group validation
	 */
	function testSelectFieldExpectsTaxonomySelectValidatorCalled() {
		// arrange
		global $post;
		$factory = $this->getMockBuilder('TestNumberField')
						->setMethods(array('validate_select',))
						->getMock();

		$factory->expects($this->once())
				->method('validate_select')
				->will($this->returnValue(true));
		$factory->fields['field_one']['type'] = 'select';
		$actual = array();

		// act
		$factory->validate($post->ID, $factory->fields['field_one'], $actual);

		// assert
		// Test will fail if validate_taxonomyselect called more than once
	}

	/**
	 * Tests whether validate will call the appropriate special validator for
	 * a link field.
	 *
	 * @group stable
	 * @group isolated
	 * @group taxonomy_select
	 * @group validation
	 */
	function testLinkFieldExpectsTaxonomySelectValidatorCalled() {
		// arrange
		global $post;
		$factory = $this->getMockBuilder('TestNumberField')
						->setMethods(array('validate_link',))
						->getMock();

		$factory->expects($this->once())
				->method('validate_link')
				->will($this->returnValue(true));
		$factory->fields['field_one']['type'] = 'link';
		$actual = array();

		// act
		$factory->validate($post->ID, $factory->fields['field_one'], $actual);

		// assert
		// Test will fail if validate_taxonomyselect called more than once
	}

	/**
	 * Tests whether a field with do_not_validate in the key will continue to
	 * validate. Expects `validate` to return.
	 *
	 * @group stable
	 * @group isolated
	 * @group negative
	 * @group validation
	 *
	 */

	function testDoNotValidateReturnsInsteadOfValidates() {
		$TestNumberField = new TestNumberField();
		$_POST = array(
			'post_ID' => 1,
			'field_one' => 2,
		);
		$actual = array();

		// act
		$TestNumberField->fields['field_one']['do_not_validate'] = true;
		$TestNumberField->validate($_POST['post_ID'], $TestNumberField->fields['field_one'], $actual );

		// assert
		$this->assertTrue(empty($actual));
	}
	/**
	 * Tests whether the validate_fieldset method is called when validating a
	 * field of type 'fieldset'
	 *
	 * @group stable
	 * @group fieldset
	 * @group isolated
	 * @group validate_fieldset
	 */
	function testValidFieldsetExpectsValidateToBeCalled() {
		// arrange
		global $post;
		$factory = $this->getMockBuilder('TestValidFieldsetField')
						->setMethods( array( 'validate_fieldset', ) )
						->getMock();
		$factory->expects($this->once())
				->method('validate_fieldset')
				->will($this->returnValue(true));
		$validate = array();

		// act
		$factory->validate( $post->ID, $factory->fields['field'], $validate );

		// assert
		// Test will fail if validate_fieldset isn't executed once and only once
	}
	/**
	 * Tests whether the validate_repeated_field method is called when validating a
	 * field that has a 'repeated' parameter set
	 *
	 * @group stable
	 * @group fieldset
	 * @group isolated
	 * @group validate_fieldset
	 */
	function testValidRepeatedFieldToCallFunctions() {
		// arrange
		global $post;
		$factory = $this->getMockBuilder('TestRepeatedFields')
						->setMethods( array( 'validate_repeated_field', ) )
						->getMock();
		$factory->expects($this->once())
				->method('validate_repeated_field')
				->will($this->returnValue(true));
		$View = $this->getMockBuilder('\CFPB\Utils\MetaBox\View')
						->setMethods( array( 'process_repeated_field_params', ) )
						->getMock();
		$View->expects($this->once())
				->method('process_repeated_field_params')
				->will($this->returnValue(true));
		$factory->set_view($View);
		$validate = array();

		// act
		$factory->validate( $post->ID, $factory->fields['fields'], $validate );

		// assert
		// Test will fail if validate_fieldset isn't executed once and only once
	}
/***************
 * Save method *
 ***************/

	/**
	 * Tests whether save will call update_post_meta
	 *
	 * @group isolated
	 * @group stable
	 * @group save
	 */
	function testSaveExpectsUpdatePostMetaCalledOnce() {

		// arrange
		$post_id = 100;
		$postvalues = array(
			'field' => 'Some text',
		);
		\WP_Mock::wpFunction( 'get_post_meta', array(
			'times' => 1,
			'return' => false,
			)
		);
		\WP_Mock::wpFunction( 'update_post_meta', array(
			'times' => 1,
			'return' => true,
			'with' => array(
				$post_id,
				'field',
				'Some text',
			),
			)
		);

		$form = new TestValidTextField();
		$form->post_type = 'post';

		// act
		$form->save($post_id, $postvalues);

		// assert

	}

	/**
	 * Tests whether save() will call update_post_meta if $_POST is empty, expects
	 * it not to.
	 * @group stable
	 * @group isolated
	 * @group negative
	 * @group save
	 */
	function testEmptyPostDataSaveExpectsNothing() {

		// arrange
		$post_id = 100;
		$postvalues = null;

		\WP_Mock::wpFunction( 'update_post_meta', array(
			'times' => 0,)
		);

		$form = new TestValidTextField();

		// act
		$form->save( $post_id, $postvalues );
	}

	/**
	 * Tests whether save() will call update_post_meta if $_POST is empty, expects
	 * it not to.
	 * @group stable
	 * @group isolated
	 * @group negative
	 * @group save
	 */
	function testEmptyPostKeySaveExpectsReturn() {

		// arrange
		$post_id = 100;
		$postvalues = array('field' => null);
		\WP_Mock::wpFunction( 'get_post_meta', array(
			'times' => 1,
			'return' => false,
			'with' => array( $post_id, 'field')
			)
		);
		\WP_Mock::wpFunction( 'update_post_meta', array(
			'times' => 0,)
		);

		$form = new TestValidTextField();

		// act
		$form->save( $post_id, $postvalues );
	}
	/**
	 * Tests whether an empty key will call delete_post_meta()
	 *
	 * @group stable
	 * @group save
	 * @group isolated
	 */
	function testEmptyKeyValueExpectsDeletePostMeta() {
		// arrange
		$post_id = 1;
		$postvalues = array('field' => null);
		$existing = 'exists';
		\WP_Mock::wpFunction(
			'get_post_meta',
			array(
				'times' => 1,
				'return' => $existing,
			)
		);
		$form = new TestValidTextField();
		// act
		$form->save( $post_id, $postvalues);
		// assert
	}

	/**
	 * Tests whether validate() and save() can be called in succession.
	 *
	 * @group isolated
	 * @group stable
	 * @group save
	 */
	function testVerifyAndSaveExpectsSuccess() {
		// arrange
		$factory = $this->getMockBuilder('TestNumberField')
						->setMethods( array( 'validate', 'delete_old_data' ) )
						->getMock();
		$factory->fields['field_one']['old_key'] = $factory->fields['field_one']['key'];
		$factory->expects($this->once())
				->method('validate')
				->will($this->returnValue(null));
		// act
		$factory->validate_and_save( 1 );
	}

/**************************
 * Specialized validators *
 **************************/

	/**
	 * Tests whether the validate method when called on a date field calls the
	 * date method from the Callbacks class.
	 *
	 * @group stable
	 * @group date
	 * @group isolated
	 * @group user_input
	 */
	function testValidDateFieldExpectsCallbackCalledOnce() {
		// arrange
		$stub = $this->getMockBuilder('Callbacks')
					->setMethods(array('date'))
					->getMock();

		$stub->expects( $this->once() )
			 ->method( 'date' )
			 ->with( $this->anything(), $this->anything(), $this->anything() );

		$form = new TestValidDateField();
		$form->set_callbacks($stub);
		$term = \WP_Mock::wpFunction( 'wp_get_post_terms' );
		$_POST = array(
			'post_ID' => 1,
			'category_year' => '1970' ,
			'category_month' => 'January',
			'category_day' => '01',
			'category_timezone' => array('America/New_York')
		);
		$anything = $this->anything();

		// act
		$form->validate_datetime($_POST['post_ID'], $form->fields['category'], $anything);
	}
	/**
	 * Tests whether the validate_datetime method when called on an invalid field
	 * does not call the callback method.
	 *
	 * @group stable
	 * @group date
	 * @group isolated
	 * @group user_input
	 */
	function testInvalidDateFieldExpectsCallbackNotCalled() {
		// arrange
		$stub = $this->getMockBuilder('Callbacks')
					->setMethods(array('date'))
					->getMock();

		$stub->expects( $this->exactly( 0 ) )
			 ->method( 'date' )
			 ->with( $this->anything(), $this->anything(), $this->anything() );

		// $stub->expects( $this->once() )
		// 	->method( 'method' );

		$form = new TestValidDateField();
		$form->set_callbacks($stub);
		$term = \WP_Mock::wpFunction( 'wp_get_post_terms' ); 
		$_POST = array(
			'post_ID' => 1,
			'category_year' => '' ,
			'category_month' => 'January',
			'category_day' => '01',
			'category_timezone' => 'America/New_York');
		$anything = $this->anything();

		// act
		$form->validate_datetime($_POST['post_ID'], $form->fields['category'], $anything);
	}
	/**
	 * Tests whether the validate_datetime method when called on a time field calls the
	 * date method from the Callbacks class.
	 *
	 * @group stable
	 * @group date
	 * @group isolated
	 * @group user_input
	 */
	function testValidTimeFieldExpectsCallbackCalledOnce() {
		// arrange
		$stub = $this->getMockBuilder('Callbacks')
					->setMethods(array('date'))
					->getMock();

		$stub->expects( $this->once() )
			 ->method( 'date' )
			 ->with( $this->anything(), $this->anything(), $this->anything() );

		// $stub->expects( $this->once() )
		// 	->method( 'method' );

		$form = new TestValidDateField();
		$form->fields['category']['type'] = 'time';
		$form->set_callbacks($stub);
		$term = \WP_Mock::wpFunction( 'wp_get_post_terms' ); 
		$_POST = array(
			'post_ID' => 1,
			'category_hour' => array('9') ,
			'category_minute' => array('30'),
			'category_ampm' => array('am'),
			'category_timezone' => array('America/New_York'),
			);
		$anything = $this->anything();

		// act
		$form->validate_datetime($_POST['post_ID'], $form->fields['category'], $anything);
	}
	/**
	 * Tests whether the validate_datetime method when called on an invalid time 
	 * does not call the date callback method.
	 *
	 * @group stable
	 * @group date
	 * @group isolated
	 * @group user_input
	 */
	function testInvalidTimeFieldExpectsCallbackNotCalled() {
		// arrange
		$stub = $this->getMockBuilder('Callbacks')
					->setMethods(array('date'))
					->getMock();

		$stub->expects( $this->exactly( 0 ) )
			 ->method( 'date' )
			 ->with( $this->anything(), $this->anything(), $this->anything() );

		// $stub->expects( $this->once() )
		// 	->method( 'method' );

		$form = new TestValidDateField();
		$form->fields['category']['type'] = 'time';
		$form->set_callbacks($stub);
		$term = \WP_Mock::wpFunction( 'wp_get_post_terms' ); 
		$_POST = array(
			'post_ID' => 1,
			'category_hour' => array('9') ,
			'category_minute' => array('30'),
			'category_ampm' => array(''),
			'category_timezone' => array('America/New_York'),
		);
		$anything = $this->anything();

		// act
		$form->validate_datetime($_POST['post_ID'], $form->fields['category'], $anything);
	}
	/**
	 * Tests whether the validate_datetime method when called on a datetime field calls the
	 * date method from the Callbacks class.
	 *
	 * @group stable
	 * @group date
	 * @group isolated
	 * @group user_input
	 */
	function testValidDatetimeFieldExpectsCallbackCalledOnce() {
		// arrange
		$stub = $this->getMockBuilder('Callbacks')
					->setMethods(array('date'))
					->getMock();

		$stub->expects( $this->once() )
			 ->method( 'date' )
			 ->with( $this->anything(), $this->anything(), $this->anything() );

		// $stub->expects( $this->once() )
		// 	->method( 'method' );

		$form = new TestValidDateField();
		$form->fields['category']['type'] = 'datetime';
		$form->set_callbacks($stub);
		$term = \WP_Mock::wpFunction( 'wp_get_post_terms' ); 
		$_POST = array(
			'post_ID' => 1,
			'category_hour' => array('9') ,
			'category_minute' => array('30'),
			'category_ampm' => array('am'),
			'category_timezone' => array('America/New_York'),
			'category_year' => '2014' ,
			'category_month' => 'January',
			'category_day' => '01');
		$anything = $this->anything();

		// act
		$form->validate_datetime($_POST['post_ID'], $form->fields['category'], $anything);
	}
	/*
	 * Tests whether the validate_datetime method when called on an invalid datetime 
	 * does not call the date callback method.
	 *
	 * @group stable
	 * @group date
	 * @group isolated
	 * @group user_input
	 */
	function testInvalidDatetimeFieldExpectsCallbackNotCalled() {
		// arrange
		$stub = $this->getMockBuilder('Callbacks')
					->setMethods(array('date'))
					->getMock();

		$stub->expects( $this->exactly( 0 ) )
			 ->method( 'date' )
			 ->with( $this->anything(), $this->anything(), $this->anything() );

		// $stub->expects( $this->once() )
		// 	->method( 'method' );

		$form = new TestValidDateField();
		$form->fields['category']['type'] = 'datetime';
		$form->set_callbacks($stub);
		$term = \WP_Mock::wpFunction( 'wp_get_post_terms' ); 
		$_POST = array(
			'post_ID' => 1,
			'category_hour' => array('9') ,
			'category_minute' => array('30'),
			'category_ampm' => array(''),
			'category_timezone' => array('America/New_York'),
			'category_year' => '' ,
			'category_month' => 'January',
			'category_day' => '01');
		$anything = $this->anything();

		// act
		$form->validate_datetime($_POST['post_ID'], $form->fields['category'], $anything);
	}

	/**
	 * Tests whether taxonomyselect will use a string if the given term does not exist.
	 *
	 * @group stable
	 * @group isolated
	 * @group taxonomyselect
	 */
	function testTermDoesNotExistExpectsStringUsed() {
		// Arrange
		$form = new TestNumberField();
		$form->fields['field_one']['type'] = 'taxonomyselect';
		$form->fields['field_one']['taxonomy'] = 'category';
		$form->fields['field_one']['multiple'] = false;
		$field = $form->fields['field_one'];
		$_POST = array('post_ID' => 1, 'field_one' => 'term');
		$term = \WP_Mock::wpFunction(
			'sanitize_text_field',
			array(
				'times' => 1,
				'args' => array( $_POST['field_one'] ),
				'return' => 'term',
			)
		);
		\WP_Mock::wpFunction('get_term_by', array('times' => 1, 'return' => false));
		\WP_Mock::wpFunction(
			'wp_set_object_terms',
			array(
				'times' => 1,
			)
		);

		// act
		$form->validate_taxonomyselect($_POST['post_ID'], $form->fields['field_one'], 'field_one');

		// Assert: test will fail if wp_set_object_terms, get_term_by or
		// sanitize_text_field do not fire or fire more than once
	}

	/**
	 * Tests whether taxonomyselect will use the term object when a term exists
	 *
	 * @group stable
	 * @group isolated
	 * @group taxonomyselect
	 */
	function testTermExistsExpectsStringUsed() {
		// Arrange
		global $post;
		$form = new TestNumberField();
		$form->fields['field_one']['type'] = 'taxonomyselect';
		$form->fields['field_one']['taxonomy'] = 'category';
		$form->fields['field_one']['multiple'] = false;
		$_POST = array( 'field_one' => 'term' );
		$existing = new \StdClass;
		$existing->name = 'Sluggo';
		\WP_Mock::wpFunction('sanitize_text_field', array('times' => 1));
		\WP_Mock::wpFunction(
			'get_term_by', 
			array('times' => 1, 'return' => $existing));
		\WP_Mock::wpFunction('wp_set_object_terms', array( 'times' => 1 ) );

		// act
		$form->validate_taxonomyselect(1, $form->fields['field_one'], 'field_one');

		// Assert: test will fail if wp_set_object_terms, get_term_by or
		// sanitize_text_field do not fire or fire more than once
	}

	/**
	* Tests a fieldset to make sure that validate is called for each of the 
	* fieldset's fields.
	*
	 * @group stable
	 * @group validate
	 * @group fieldset
	 * @group validate_fieldset
	 * @group isolated
	*/
	function testValidFieldsetOfTwoFieldsCallsValidateTwice() {
		//arrange
		global $post;
		$factory = $this->getMockBuilder('TestValidFieldsetField')
						->setMethods( array( 'validate',) )
						->getMock();
		$factory->expects( $this->exactly( 2 ) )
				->method( 'validate' )
				->will( $this->returnValue( true ) );
		$validated = array();

		//act
		$factory->validate_fieldset(  $post->ID, $factory->fields['field'], $validated, array());

		//assert
		// Test will fail if only each of the fields in the fieldset are validated
	}
	/**
	* Tests a fieldset to make sure that validate adds validated values to the 
	* array that was passed in by reference.
	*
	 * @group stable
	 * @group validate
	 * @group fieldset
	 * @group validate_fieldset
	 * @group isolated
	*/
	function testValidateAddsValidatedValuesToPassedInArrayByValidateFieldset() {
		// arrange
		global $post;
		$_POST = array( 'field_num' => '0123456789', 'field_desc' => 'description' );
		$testValidFieldsetField = new TestValidFieldsetField();
		$actual = array();
		$expected = array( 'num' => '0123456789', 'desc' => 'description' );

		//act
		$testValidFieldsetField->validate_fieldset( $post->ID, $testValidFieldsetField->fields['field'], $actual, array() );

		//assert
		$this->assertEquals( $expected, $actual );
	}
	/**
	* Tests a repeated field to make sure that validate is called for each repetition
	*
	 * @group stable
	 * @group validate
	 * @group fieldset
	 * @group validate_fieldset
	 * @group isolated
	*/
	function testValidateRepeatedFieldCallsValidateOnEachRepetition() {
		// arrange
		global $post;
		$TestRepeatedFields = $this->getMockBuilder('TestRepeatedFields')
						->setMethods( array( 'validate' ) )
						->getMock();
		$TestRepeatedFields->expects($this->exactly(2))
						    ->method('validate');
		$validated = array();

		//act
		$TestRepeatedFields->validate_repeated_field( $post->ID, $TestRepeatedFields->fields['fields'], $validated, array() );

		//assert
	}
/**************
 * Generators *
/**************/
	/**
	 * Tests that a meta box is generated if the given post type exists.
	 *
	 * @group isolated
	 * @group stable
	 * @group meta_boxes
	 * @group generate
	 */
	function testPostTypeExistsGenerateExpectsMetaBoxAdded() {
		// Arrange
		$form = new TestValidBox();
		\WP_Mock::wpFunction('post_type_exists', array(
			'times' => 1,
			'args' => $form->post_type,
			'return' => true,
			)
		);
		\WP_Mock::wpPassthruFunction('sanitize_key');
		\WP_Mock::wpFunction('add_meta_box', array(
			'times' => 1,
			'return' => true,
			)
		);

		// act
		$form->generate();

		// Assert: test will fail if add_meta_box is not called, or called more than once
	}

	/**
	 * Tests whether post_type exists is called once
	 *
	 * @group stable
	 * @group generate
	 **/
	function testPostTypeExistsCheckPostTypeExpectsPostTypeNameReturned() {
		// Arrange
		\WP_Mock::wpFunction('sanitize_key', array(
			'times' => 1,
			'return' => 'post'
			)
		);
		\WP_Mock::wpFunction('post_type_exists', array(
			'times' => 1,
			'arg' => array( \WP_Mock\Functions::type('string') ),
			'return' => true
			)
		);
		$form = new TestValidBox();
		$expected = 'post';
		// act
		$actual = $form->check_post_type($form->post_type);
		// Assert
		$this->assertEquals($expected, $actual, 'Post type did not verify correctly');
	}
	/**
	 * Tests whether check_post_type returns false if post type doesn't exist
	 *
	 * @group stable
	 * @group isolated
	 * @group meta_boxes
	 * @group generate
	 */
	function testPostTypeNotExistsCheckPostTypeExpectsPostTypeNameReturned() {
		// Arrange
		\WP_Mock::wpFunction('sanitize_key', array(
			'times' => 0,
			)
		);
		\WP_Mock::wpFunction('post_type_exists', array(
			'times' => 1,
			'return' => false
			)
		);
		$form = new TestValidBox();
		$expected = false;
		// act
		$actual = $form->check_post_type($form->post_type);
		// Assert
		$this->assertEquals($expected, $actual, 'Post type did not verify correctly');
	}
	/**
	 * Tests whether a meta box can be attached to more than one post type
	 *
	 * @group stable
	 * @group meta_boxes
	 * @group generate
	 *
	 */
	function testArrayinPostTypeExpectsBoxesGenerated() {
		// arrange
		$Box = new TestValidBox();
		$Box->post_type = array('post', 'page');
		\WP_Mock::wpFunction('post_type_exists', array(
			'times' => 2,
			'return' => true,)
		);
		\WP_Mock::wpPassthruFunction('sanitize_key');
		\WP_Mock::wpFunction('add_meta_box', array(
			'times' => 2,
			'return' => true,
			)
		);
		// act
		$Box->generate();
		// assert
	}
	/**
	 * Tests whether generate will make a meta box if the post type does not exist
	 *
	 * @group wp_error
	 * @group isolated
	 * @group stable
	 * @group generate
	 */
	function testPostTypeNotExistsGenerateExpectsWPError() {
		// Arrange
		$stub = $this->getMockBuilder('TestValidBox')
			->setMethods(array('check_post_type'))
			->getMock();
		$stub->expects($this->once())
			->method('check_post_type')
			->with($stub->post_type[0])
			->will($this->returnValue(false));
		$error = $this->getMock('\WP_Error', array('get_error_message'));
		$stub->error_handler(get_class($error));
		// act
		$actual = $stub->generate();

		// Assert
	}

	/**
	 * Tests whether generate will make a meta box if the post type does not exist
	 *
	 * @group wp_error
	 * @group isolated
	 * @group stable
	 * @group generate
	 */
	function testInvalidContextGenerateExpectsWPError() {
		// Arrange
		$stub = $this->getMock('\WP_Error', array('get_error_message'));
		$form = new TestValidDateField();
		$form->error_handler(($stub));
		$form->context = 'context';
		// act
		$actual = $form->generate();

		// Assert
	}
/************************
 * Dependency injection *
 ************************/
	/**
	 * Tests the ability replace the internal View class
	 *
	 * @group stable
	 * @group set_view
	 * @group isolated
	 * @group dependency_injection
	 */
	function testSetViewExpectsViewReplaced() {
		// Arrange
		$newView = new \StdClass;
		$form = new TestNumberField();

		// act
		$form->set_view($newView);

		// Assert
		$this->assertInstanceOf('StdClass', $form->View, 'Not working.');
	}
}
