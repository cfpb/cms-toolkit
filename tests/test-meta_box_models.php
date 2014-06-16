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
			'slug' => 'field_one',
			'type' => 'text_area',
			'params' => array(
				'cols' => 27,
			),
			'placeholder' => 'Enter text',
			'howto' => 'Type some text',
			'meta_key' => 'field_one',
		),
		'field_two' => array(
			'slug' => 'field_two',
			'title' => 'This is another field',
			'type' => 'number',
			'params' => array(),
			'placeholder' => '0-100',
			'howto' => 'Type a number',
			'meta_key' => 'field_two',
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
			'slug' => 'field_one',
			'title' => 'This is another field',
			'type' => 'number',
			'params' => array(),
			'placeholder' => '0-100',
			'howto' => 'Type a number',
			'meta_key' => 'field_one',
		),
	);
}

class TestValidTextField extends Models {
	public $fields = array(
		'one' => array(
			'title' => 'Text Field',
			'slug' => 'one',
			'label' => 'Text Label',
			'type' => 'text',
			'params' => array('max_length' => 255),
			'placeholder' => 'Type some text',
			'howto' => 'Up to 255 characters',
			'meta_key' => 'one',
		),
	);
}

class TestValidTextAreaField extends Models {
	public $fields = array(
		'one' => array(
			'title' => 'Text Area Field',
			'slug' => 'one',
			'label' => 'Text Label',
			'type' => 'text_area',
			'params' => array('max_length' => 255),
			'placeholder' => 'Type some text',
			'howto' => 'Up to 255 characters',
			'meta_key' => 'one',
		),
	);
}

class TestValidEmailField extends Models {
	public $fields = array(
		'one' => array(
			'title' => 'Email Field',
			'slug' => 'one',
			'label' => 'Email Label',
			'type' => 'email',
			'params' => array(),
			'placeholder' => 'Type some text',
			'howto' => 'Up to 255 characters',
			'meta_key' => 'one',
		),
	);
}

class TestValidDateField extends Models {
	public $fields = array(
		'category' => array(
			'title' => 'Issued date:',
			'slug' => 'category',
			'label' => '',
			'type' => 'date',
			'params' => array(),
			'multiples' => false,
			'placeholder' => '',
			'howto' => '',
			'taxonomy' => 'category',
		),
	);
}

class ValidationTest extends PHPUnit_Framework_TestCase {
	function setUp() {
		\WP_Mock::setUp();
	}

	function tearDown() {
		\WP_Mock::tearDown();
	}

	/**
	 * Tests whether the validate method when called on an email field calls
	 * 'sanitize_email' from the WP API once
	 *
	 * @group stable
	 * @group isolated
	 * @group user_input
	 */

	function testEmailExpectsSanitizeMethodCalled() {
		// arrange
		\WP_Mock::wpPassthruFunction('sanitize_email', array('times' => 1));
		$TestValidEmailField = new TestValidEmailField();
		$_POST = array(
			'one' => 'foo@bar.baz',
			'post_ID' => 1,
		);
		// act
		$actual = $TestValidEmailField->validate();
	}

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

		// $stub->expects( $this->once() )
		// 	->method( 'method' );

		$form = new TestValidDateField();
		$form->set_callbacks($stub);
		$_POST = array(
			'post_ID' => 1,
			'category_year' => '1970' ,
			'category_month' => 'January',
			'category_day' => '01');

		// act
		$form->validate_date( $form->fields['category'], $_POST['post_ID']);
		// $stub->method();
	}

	/**
	 * Tests whether WP_Error is returned if missing a piece of a date.
	 *
	 * @group stable
	 * @group isolated
	 * @group date
	 * @group user_input
	 */
	function testInvalidDateValidateExpectsDateCalledNone() {
		// arrange
		$stub = $this->getMockBuilder('Callbacks')
			->getMock();

		$form = new TestValidDateField();
		$form->set_callbacks($stub);
		$_POST =array('post_ID' => 1, 'category_year' => '1970');
		// act
		$actual = $form->validate();

		// assert
	}

	/**
	 * Tests whether a number field has data replaced
	 *
	 * @group number
	 * @group stable
	 * @group isolated
	 * @group user_input
	 */
	function testValidNumberFieldExpectsDataReturned() {
		$TestNumberField = new TestNumberField();
		$_POST = array(
			'post_ID' => 1,
			'field_one' => 2,
		);

		// act
		$actual = $TestNumberField->validate();

		// assert
		$expected = array('field_one' => 2);
		$this->assertEquals(
			$expected,
			$actual,
			'Numeric strings should be accepted and converted to a number.');
	}

	/**
	 * Tests whether an invalid number is not accepted
	 *
	 * @group stable
	 * @group isolated
	 * @group number
	 * @group user_input
	 *
	 */
	function testInvalidNumericFieldExpectsDataRemoved() {

		// arrange
		$TestNumberField = new TestNumberField();
		$_POST = array(
			'post_ID' => 1,
			'field_one' => 'Two',
		);

		// act
		$actual = $TestNumberField->validate();
		// assert
		$expected = array();
		$this->assertEquals(
			$expected,
			$actual,
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
	 */
	function testTextFieldExpectsStringReturned() {

		// arrange
		$TestValidTextField = new TestValidTextField();
		$TestValidTextField->fields['one']['type'] = 'text';
		$_POST = array(
			'post_ID' => 1,
			'one' => 'Text field expects a string',
		);

		// act
		$actual = $TestValidTextField->validate();

		// assert
		$this->assertEquals(
			array('one' => 'Text field expects a string'),
			$actual
		);
	}

	/**
	 * Tests wheter an integer is converted to a string if field == text
	 *
	 * @group text
	 * @group user_input
	 * @group stable
	 * @group isolated
	 */
	function testTextFieldGivenNumberExpectsNumericStringValueReturned() {

		// arrange
		$TestValidTextField = new TestValidTextField();
		$TestValidTextField->fields['one']['type'] = 'text';
		$_POST = array(
			'post_ID' => 1,
			'one' => 1,
		);

		// act
		$actual = $TestValidTextField->validate($_POST);

		// assert
		$this->assertEquals(array('one' => '1'), $actual);
	}

	/**
	 * Tests whether a text area field accepts and returns a string.
	 *
	 * @group textarea
	 * @group isolated
	 * @group stable
	 *
	 */
	function testTextAreaFieldExpectsStringReturned() {
		// arrange
		$TestValidTextField = new TestValidTextAreaField();
		$_POST = array(
			'post_ID' => 1,
			'one' => 'Foo',
		);

		// act
		$actual = $TestValidTextField->validate($_POST);

		//assert
		$this->assertEquals(array('one' => 'Foo'), $actual);
	}

	/**
	 * tests whether an array is not accepted and an error is returned.
	 * @group strings
	 * @group isolated
	 * @group user_input
	 * @group stable
	 * @group wip
	 */
	function testTextAreaFieldNonStringExpectsSringReturned() {
		// arrange
		$stub = $this->getMock('\WP_Error', array('get_error_message'));
		// $stub->expects($this->once())
		//      ->method('get_error_message')
		     // ->will($this->returnValue('Error'));
		$TestValidTextField = new TestValidTextAreaField();
		$TestValidTextField->error_handler($stub);
		$_POST = array(
			'post_ID' => 1,
			'one' => array('one' => 'two'),
		);

		// act
		$actual = $TestValidTextField->validate($_POST);

		// assert
		$this->assertTrue(is_array($actual));
	}

	/**
	 * Tests whether url field data will be sent to WordPress and sanitized
	 *
	 * @group urls
	 * @group user_input
	 * @group stable
	 * @group isolated
	 */
	function testURLFieldExpectsSanitizedTextCall() {
		// arrange
		\WP_Mock::wpPassthruFunction(
			'esc_url_raw',
			array('times' => 1, 'return' => 'http://google.com')
		);
		$TestValidEmailField = new TestValidEmailField();
		$TestValidEmailField->fields['one']['type'] = 'url';
		$_POST = array(
			'post_ID' => 1,
			'one' => 'http://google.com',
		);

		// act
		$actual = $TestValidEmailField->validate($_POST);
	}

	/**
	 * Tests whether save will call update_post_meta
	 *
	 * @group isolated
	 * @group stable
	 * @group save
	 */
	function testSaveExpectsUpdatePostMetaCalledOnce() {

		// arrange
		global $post;
		$post = new StdClass;
		$post_id = 100;
		$post->post_type = 'post';
		$postvalues = array(
			'one' => 'Some text',
		);
		\WP_Mock::wpFunction( 'update_post_meta', array(
			'times' => 1,
			'return' => true,
			'args' => array(
				$post_id,
				'one',
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
	 * Tests whether validate() and save() can be called in succession.
	 *
	 * @group isolated
	 * @group stable
	 * @group save
	 */
	function testVerifyAndSaveExpectsSuccess() {
		// arrange
		$_POST = array('post_ID' => 1, 'field_one' => 'value');
		$sanitized = array('three' => 'Values');
		$factory = $this->getMockBuilder('TestNumberField')
						->setMethods(array('filter_postdata', 'validate', 'save'))
						->getMock();

		$factory->expects($this->once())
				->method('validate')
				->will($this->returnValue($sanitized));
		$factory->expects($this->once())
				->method('save')
				->with(1, $sanitized);

		// Act
		$factory->validate_and_save( 1 );
	}

	/**
	 * Tests whether calling date_meta_box from this class results in an error
	 *
	 * @group stable
	 * @group isolated
	 * @group wp_error
	 * @group doing_it_wrong
	 *
	 */
	function testDateMetaBoxExpectsWP_Error() {
		//arrange
		$taxonomy = 'category';
		$tax_nice_name = 'Category';
		$multiples = false;
		$stub = $this->getMock('\WP_Error', array('get_error_message'));
		$form = new TestValidEmailField();
		$form->error_handler($stub);

		// Act
		$form->date_meta_box($taxonomy, $tax_nice_name, $multiples);

		// Assert
		// $this->assertInstanceOf('WP_Error', $actual);
	}

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

		// Act
		$form->generate();

		// Assert: test will fail if add_meta_box is not called, or called more than once
	}

	/**
	 * Tests whether post_type exists is called once
	 *
	 * @group stable
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
		// Act
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
		// Act
		$actual = $form->check_post_type($form->post_type);
		// Assert
		$this->assertEquals($expected, $actual, 'Post type did not verify correctly');
	}
	/**
	 * Tests whether a meta box can be attached to more than one post type
	 *
	 * @group stable
	 * @group meta_boxes
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
		// Act
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
		// Act
		$actual = $form->generate();

		// Assert
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

		// Act
		$form->validate_taxonomyselect($form->fields['field_one'], $_POST['post_ID']);

		// Assert: test will fail if wp_set_object_terms, get_term_by or
		// sanitize_text_field do not fire or fire more than once
	}

	/**
	 * Tests whether validate_link will call add post meta with the correct values
	 *
	 * @group wip
	 * @group isolated
	 * @group stable
	 * @group validate_link
	**/
	function testValidateLinkCount2ExpectsAddPostMetaTwice() {
		$_POST = array(
			'link_url_0' => 'http://example.com',
			'link_url_1' => 'http://example.com',
			'link_text_0' => 'Example dot com',
			'link_text_1' => 'example.com',
		);
		$field = array(
			'slug' => 'link',
			'type' => 'link',
			'params' => array(
				'count' => 1,
			),
			'meta_key' => 'link',
			'howto' => 'Some howto text',
		);
		$form = new TestNumberField();
		global $post;
		$post = new StdClass();
		$post_id = 1;
		$form->fields = $field;
		\WP_Mock::wpFunction(
			'add_post_meta',
			array( 'times' => 2, 'return' => true )
		);
		$post = new \StdClass;
		\WP_Mock::wpFunction('get_post_meta', array( 'times' => 1, 'return' => false) );
		\WP_Mock::wpFunction('delete_post_meta', array('times' => 0));
		$form->validate_link( $field, $post_id);
	}

	/**
	* Tests whether count will default to 1 if none is passed in the model
	*
	* @group stable
	* @group isolated
	* @group validate_link
	**/
	function testValidateLinkCountNotGivenExpectsUpdatePostMetaCalledOnce() {
		// arrange
		$_POST = array(
			'link_url_0' => 'http://example.com',
			'link_text_0' => 'Example dot com',
		);
		$field = array(
			'slug' => 'link',
			'type' => 'link',
			'params' => array(),
			'meta_key' => 'link',
			'howto' => 'Some howto text',
		);
		$form = new TestNumberField();
		global $post;
		$post_id = 1;
		$form->fields = $field;
		\WP_Mock::wpFunction(
			'add_post_meta',
			array( 'times' => 2, 'return' => true )
		);
		$post = new \StdClass;
		\WP_Mock::wpFunction('get_post_meta', array( 'times' => 1, 'return' => false) );

		// act
		$form->validate_link( $field, $post_id);

		// assert: Test will fail if get_ or update_post_meta called more than once.
	}
	/**
	 * Tests whether validate_link will use the $existing variable if it is set
	 *
	 * @group wip
	**/
	function testValidateLinkWithExistingDataExpectsDataDeletedAndReplaced() {
		// arrange
		$_POST = array(
			'link_url_0' => 'http://example.com',
			'link_text_0' => 'Example Dot Com, Your Example Website',
		);
		$field = array(
			'slug' => 'link',
			'type' => 'link',
			'params' => array( 'count' => 1),
			'meta_key' => 'link',
			'howto' => 'Some howto text',
		);
		$existing = array( 'http://google.com', 'Google');
		$form = new TestNumberField();
		global $post;
		$post_id = 1;
		$form->fields = $field;
		\WP_Mock::wpFunction(
			'delete_post_meta',
			array( 'times' => 1, 'return' => true)
		);
		\WP_Mock::wpFunction(
			'add_post_meta',
			array( 'times' => 2, 'return' => true)
		);
		\WP_Mock::wpFunction(
			'get_post_meta',
			array( 'times' => 1, 'return' => $existing )
		);


		// act
		$form->validate_link( $field, $post_id );

		// assert
		// Test will fail if add_post_meta is called more than twice, and if
		// get_post_meta or delete_post_meta are called more than once.
	}

	/**
	* Tests whether validate_link will return rather than re-save existing data
	*
	* @group wip
	*
	**/
	function testValidateLinkWithExistingDataMatchingSubmittedExpectsNoAction() {
		// arrange
		$_POST = array(
			'link_url_0' => 'http://example.com',
			'link_text_0' => 'Example Dot Com, Your Example Website',
		);
		$existing = array( 'http://example.com', 'Example Dot Com, Your Example Website');
		$field = array(
			'slug' => 'link',
			'type' => 'link',
			'params' => array( 'count' => 1 ),
			'meta_key' => 'link',
			'howto' => 'Some howto text',
		);
		$form = new TestNumberField();
		global $post;
		$post_id = 1;
		$form->fields = $field;
		\WP_Mock::wpFunction(
			'add_post_meta',
			array( 'times' => 0 )
		);
		\WP_Mock::wpFunction(
			'get_post_meta',
			array( 'times' => 1, 'return' => $existing )
		);
		\WP_Mock::wpFunction(
			'update_post_meta',
			array( 'times' => 0)
		);
		// act
		$form->validate_link( $field, $post_id );
		// assert
		// Test will fail if add_post_meta or delete_post_meta is called and if
		// get_post_meta is called more than once.
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
		$form = new TestNumberField();
		$form->fields['field_one']['type'] = 'taxonomyselect';
		$form->fields['field_one']['taxonomy'] = 'category';
		$form->fields['field_one']['multiple'] = false;
		$_POST = array( 'post_ID' => 1, 'field_one' => 'term' );
		$existing = new \StdClass;
		$existing->name = 'Sluggo';
		\WP_Mock::wpFunction('sanitize_text_field', array('times' => 1));
		\WP_Mock::wpFunction('get_term_by', array('times' => 1, 'return' => $existing));
		\WP_Mock::wpFunction('wp_set_object_terms', array( 'times' => 1 ) );

		// Act
		$form->validate();

		// Assert: test will fail if wp_set_object_terms, get_term_by or
		// sanitize_text_field do not fire or fire more than once
	}

	/**
	* Tests whether a box will validate if no ID is passed to $_POST
	*
	* @group stable
	* @group isolated
	* @group taxonomyselect
	*/
	function testMissingIDExpectsBoxNotValidated() {
		// Arrange
		$form = new TestNumberField();
		$form->fields['field_one']['type'] = 'taxonomyselect';
		$form->fields['field_one']['taxonomy'] = 'category';
		$form->fields['field_one']['multiple'] = false;
		$_POST = array( 'field_one' => 'term' );
		$existing = new \StdClass;
		$existing->name = 'Sluggo';
		\WP_Mock::wpFunction('sanitize_text_field', array('times' => 0));
		\WP_Mock::wpFunction('get_term_by', array('times' => 0, 'return' => $existing));
		\WP_Mock::wpFunction('wp_set_object_terms', array( 'times' => 0 ) );

		// Act
		$form->validate();

		// Assert: test will fail if wp_set_object_terms, get_term_by or
		// sanitize_text_field do not fire or fire more than once
	}

	/**
	 * Tests the ability replace the internal View class
	 *
	 * @group stable
	 * @group set_view
	 * @group isolated
	 */
	function testSetViewExpectsViewReplaced() {
		// Arrange
		$newView = new \StdClass;
		$form = new TestNumberField();

		// Act
		$form->set_view($newView);

		// Assert
		$this->assertInstanceOf('StdClass', $form->View, 'Not working.');
	}
}
