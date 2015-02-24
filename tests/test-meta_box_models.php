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
    public $post_type = 'post';
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
    public $post_type = 'post';
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
    public $post_type = 'post';
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
    public $post_type = 'post';
    public $fields = array(
        'category' => array(
            'title' => 'Issued date:',
            'slug' => 'category',
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
                    'meta_key' => 'num',
                ),
                array(
                    'type' => 'text',
                    'meta_key' => 'desc',
                ),
            ),
            'params' => array(),
            'meta_key' => 'field',
        ),
    );
}

class TestValidFormsetField extends Models {
    public $post_type = 'post';
    public $fields = array(
        'field' => array(
            'type' => 'formset',
            'fields' => array(
                array(
                    'title' => 'Title',
                    'type' => 'text',
                    'meta_key' => 'title',
                ),
            ),
            'params' => array(
                'init_num_forms' => 1,
                'max_num_forms' => 2,
            ),
            'meta_key' => 'field',
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
        $TestValidTextField = new TestValidTextField();
        $TestValidTextField->fields = array();
        $stub = $this->getMockBuilder( '\WP_Error' )
                     ->setMethods( array('get_error_message') )
                     ->getMock();
        $TestValidTextField->error_handler($stub);
        $TestValidTextField->error = new $TestValidTextField->error( 'TEST' );
        $TestValidTextField->error->expects( $this->once() )
             ->method( 'get_error_message' )
             ->will( $this->returnValue( true ) );

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
        $TestValidTextField = new TestValidTextField();
        $TestValidTextField->fields['one']['type'] = null;
        $stub = $this->getMockBuilder( '\WP_Error' )
                     ->setMethods( array('get_error_message') )
                     ->getMock();
        $TestValidTextField->error_handler($stub);
        $TestValidTextField->error = new $TestValidTextField->error( 'TEST' );
        $TestValidTextField->error->expects( $this->once() )
             ->method( 'get_error_message' )
             ->will( $this->returnValue( true ) );

        // act
        $TestValidTextField->validate( $post->ID, array_pop( $TestValidTextField->fields['one'] ), $actual );
        
        // assert
        // Passes when error is called
    }
    /**
     * Tests if the validate method will throw an error if the field does not have
     * the meta_key, slug, or taxonomy set.
     *
     *
     * @group stable
     * @group empty_data
     * @group isolated
     * @group validation
     */
    function testFieldMetakeySlugTaxonomyIsNotSetExpectsError() {
        // arrange
        $TestValidTextField = new TestValidTextField();
        $TestValidTextField->fields['one']['meta_key'] = null;
        $TestValidTextField->fields['one']['slug'] = null;
        $TestValidTextField->fields['one']['taxonomy'] = null;
        $stub = $this->getMockBuilder( '\WP_Error' )
                     ->setMethods( array('get_error_message') )
                     ->getMock();
        $TestValidTextField->error_handler($stub);
        $TestValidTextField->error = new $TestValidTextField->error( 'TEST' );
        $TestValidTextField->error->expects( $this->once() )
             ->method( 'get_error_message' )
             ->will( $this->returnValue( true ) );

        // act
        $TestValidTextField->validate( $post->ID, array_pop( $TestValidTextField->fields['one'] ), $actual );
        
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
        $TestValidTextField = new TestValidTextField();
        $TestValidTextField->fields['one']['taxonomy'] = null;
        $TestValidTextField->fields['one']['type'] = 'taxonomyselect';
        $stub = $this->getMockBuilder( '\WP_Error' )
                     ->setMethods( array('get_error_message') )
                     ->getMock();
        $TestValidTextField->error_handler($stub);
        $TestValidTextField->error = new $TestValidTextField->error( 'TEST' );
        $TestValidTextField->error->expects( $this->once() )
             ->method( 'get_error_message' )
             ->will( $this->returnValue( true ) );

        // act
        $TestValidTextField->validate( $post->ID, array_pop( $TestValidTextField->fields['one'] ), $actual );
        
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
        $TestValidTextField = new TestValidTextField();
        $TestValidTextField->fields['one']['taxonomy'] = null;
        $TestValidTextField->fields['one']['type'] = 'date';
        $stub = $this->getMockBuilder( '\WP_Error' )
                     ->setMethods( array('get_error_message') )
                     ->getMock();
        $TestValidTextField->error_handler($stub);
        $TestValidTextField->error = new $TestValidTextField->error( 'TEST' );
        $TestValidTextField->error->expects( $this->once() )
             ->method( 'get_error_message' )
             ->will( $this->returnValue( true ) );

        // act
        $TestValidTextField->validate( $post->ID, array_pop( $TestValidTextField->fields['one'] ), $actual );
        
        // assert
        // Passes when error is called
    }
    /**
     * Tests if the validate method will throw an error if the field does not have
     * the meta_key set when required for select field(s). This will only test 
     * one of the fields of the selects array.
     *
     * @group stable
     * @group empty_data
     * @group isolated
     * @group validation
     */
    function testFieldTypeOfSelectsArrayWhereMetakeyIsNotSetExpectsError() {
        // arrange
        $TestValidTextField = new TestValidTextField();
        $TestValidTextField->fields['one']['meta_key'] = null;
        $TestValidTextField->fields['one']['type'] = 'select';
        $stub = $this->getMockBuilder( '\WP_Error' )
                     ->setMethods( array('get_error_message') )
                     ->getMock();
        $TestValidTextField->error_handler($stub);
        $TestValidTextField->error = new $TestValidTextField->error( 'TEST' );
        $TestValidTextField->error->expects( $this->once() )
             ->method( 'get_error_message' )
             ->will( $this->returnValue( true ) );

        // act
        $TestValidTextField->validate( $post->ID, array_pop( $TestValidTextField->fields['one'] ), $actual );
        
        // assert
        // Passes when error is called
    }
    /**
     * Tests if the validate method will throw an error if the field of formset
     * type does not have a params array set.
     *
     * @group stable
     * @group empty_data
     * @group isolated
     * @group validation
     */
    function testFieldTypeFormsetWhereParamsArrayIsNotSetExpectsError() {
        // arrange
        $TestValidTextField = new TestValidFormsetField();
        $TestValidTextField->fields['field']['params'] = null;
        $stub = $this->getMockBuilder( '\WP_Error' )
                     ->setMethods( array('get_error_message') )
                     ->getMock();
        $TestValidTextField->error_handler($stub);
        $TestValidTextField->error = new $TestValidTextField->error( 'TEST' );
        $TestValidTextField->error->expects( $this->once() )
             ->method( 'get_error_message' )
             ->will( $this->returnValue( true ) );

        // act
        $TestValidTextField->validate( $post->ID, array_pop( $TestValidTextField->fields['field'] ), $actual );
        
        // assert
        // Passes when error is called
    }
    /**
     * Tests if the validate method will throw an error if the field of formset
     * type does not have a max_num_forms set in the params array.
     *
     * @group stable
     * @group empty_data
     * @group isolated
     * @group validation
     */
    function testFieldTypeFormsetWhereMaxNumFormsInParamsArrayIsNotSetExpectsError() {
        // arrange
        $TestValidTextField = new TestValidFormsetField();
        $TestValidTextField->fields['field']['params']['max_num_forms'] = null;
        $stub = $this->getMockBuilder( '\WP_Error' )
                     ->setMethods( array('get_error_message') )
                     ->getMock();
        $TestValidTextField->error_handler($stub);
        $TestValidTextField->error = new $TestValidTextField->error( 'TEST' );
        $TestValidTextField->error->expects( $this->once() )
             ->method( 'get_error_message' )
             ->will( $this->returnValue( true ) );

        // act
        $TestValidTextField->validate( $post->ID, array_pop( $TestValidTextField->fields['field'] ), $actual );
        
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
        $actual = array();
        // act
        $TestValidTextField->validate( $post->ID, array_pop( $TestValidTextField->fields ), $actual );
        // assert
        $this->assertTrue( empty( $actual ) );
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
            'one' => 'foo@bar.baz',
        );
        $actual = array();
        // act
        $TestValidEmailField->validate($post->ID, $TestValidEmailField->fields['one'], $actual);
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
        $actual = array();

        // act
        $TestNumberField->validate($_POST['post_ID'], $TestNumberField->fields['field_one'], $actual);

        // assert
        $expected = 2;
        $this->assertEquals(
            $expected,
            $actual['field_one'],
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
        $actual = array();

        // act
        $TestNumberField->validate($_POST['post_ID'], $TestNumberField->fields['field_one'], $actual);
        // assert
        $expected = null;
        $this->assertEquals(
            $expected,
            $actual['field_one'],
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
        $TestValidTextField->fields['one']['type'] = 'text';
        $_POST = array(
            'post_ID' => 1,
            'one' => 'Text field expects a string',
        );
        $actual = array();

        // act
        $TestValidTextField->validate($_POST['post_ID'], $TestValidTextField->fields['one'], $actual);

        // assert
        $this->assertEquals(
            'Text field expects a string',
            $actual['one']
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
        $TestValidTextField->fields['one']['type'] = 'text';
        $_POST = array(
            'post_ID' => 1,
            'one' => 1,
        );
        $actual = array();

        // act
        $TestValidTextField->validate($_POST['post_ID'], $TestValidTextField->fields['one'], $actual);

        // assert
        $this->assertEquals('1', $actual['one']);
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
        // \WP_Mock::wpFunction(
        //  'get_post',
        //  array('times' => 1, 'returns' => $post)
        // );
        $_POST = array(
            'post_ID' => 1,
            'one' => 'Foo',
        );
        $actual = array();

        // act
        $TestValidTextAreaField->validate($post->ID, $TestValidTextAreaField->fields['one'], $actual);

        //assert
        $this->assertEquals('Foo', $actual['one']);
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
        // $post = new StdClass;
        // $post->post_type = 'post';
        // $post->ID = 1;
        $stub = $this->getMock('\WP_Error', array('get_error_message'));
        // \WP_Mock::wpFunction(
        //  'get_post',
        //  array('times' => 1, 'return' => $post));
        $TestValidTextAreaField = new TestValidTextAreaField();
        $TestValidTextAreaField->error_handler($stub);
        $_POST = array(
            'post_ID' => 1,
            'one' => null,
        );
        $actual = array();

        // act
        $TestValidTextAreaField->validate($post->ID, $TestValidTextAreaField->fields['one'], $actual);

        // assert
        $this->assertTrue( is_null($actual['one']) );
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
        // \WP_Mock::wpFunction(
        //  'get_post',
        //  array('times' => 1,'return' => $post,)
        // );
        $TestValidEmailField = new TestValidEmailField();
        $TestValidEmailField->fields['one']['type'] = 'url';
        $_POST = array(
            'one' => 'http://google.com',
        );
        $actual = array();

        // act
        $TestValidEmailField->validate($post->ID, $TestValidEmailField->fields['one'], $actual );

        // assert
        $this->assertEquals($actual['one'], 'http://google.com');
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
     * Tests whether the validate_formset method is called when validating a 
     * field of type 'formset'
     *
     * @group stable
     * @group formset
     * @group isolated
     * @group validate_formset
     */
    function testValidFormsetExpectsValidateToBeCalled() {
        // arrange
        global $post;
        $factory = $this->getMockBuilder('TestValidFormsetField')
                        ->setMethods( array( 'validate_formset', ) )
                        ->getMock();
        $factory->expects($this->once())
                ->method('validate_formset')
                ->will($this->returnValue(true));
        $validate = array();

        // act
        $factory->validate( $post->ID, $factory->fields['field'], $validate );

        // assert
        // Test will fail if validate_formset isn't executed once and only once
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
            'one' => 'Some text',
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
        $postvalues = array('one' => null);
        \WP_Mock::wpFunction( 'get_post_meta', array(
            'times' => 1,
            'return' => false,
            'with' => array( $post_id, 'one')
            )
        );
        \WP_Mock::wpFunction( 'delete_post_meta', array(
            'times' => 1)
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
        $postvalues = array('one' => null);
        $existing = 'exists';
        \WP_Mock::wpFunction( 
            'get_post_meta', 
            array(
                'times' => 1,
                'return' => $existing,
            )
        );
        \WP_Mock::wpFunction(
            'delete_post_meta',
            array(
                'times' => 1,
                'return' => true,
                'with' => array('post_ID' => 1, 'meta_key' => 'one'),   
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
        $_POST = array('post_ID' => 1, 'field_one' => 'value');
        $actual = array();
        $factory = $this->getMockBuilder('TestNumberField')
                        ->setMethods( array( 'validate', 'save' ) )
                        ->getMock();

        $factory->expects($this->once())
                ->method('validate')
                ->will($this->returnValue(true))
                ->with(1, $factory->fields['field_one'], $actual);
        $factory->expects($this->once())
                ->method('save')
                ->with(1, $actual);

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

        // $stub->expects( $this->once() )
        //  ->method( 'method' );

        $form = new TestValidDateField();
        $form->set_callbacks($stub);
        $term = \WP_Mock::wpFunction( 'wp_get_post_terms' ); 
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
        $form->validate_taxonomyselect($form->fields['field_one'], $_POST['post_ID']);

        // Assert: test will fail if wp_set_object_terms, get_term_by or
        // sanitize_text_field do not fire or fire more than once
    }

    /**
     * Tests whether validate_link will call add post meta with the correct values
     *
     * @group isolated
     * @group stable
     * @group validate_link
    **/
    function testValidateLinkExpectsAddPostMetaTwice() {
        $_POST = array(
            'link_url' => 'http://example.com',
            'link_text' => 'example.com',
        );
        $field = array(
            'slug' => 'link',
            'type' => 'link',
            'params' => array(),
            'meta_key' => 'link',
            'howto' => "Some howto text",
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
            'link_url' => 'http://example.com',
            'link_text' => 'example.com',
        );
        $field = array(
            'slug' => 'link',
            'type' => 'link',
            'params' => array(),
            'meta_key' => 'link',
            'howto' => "Some howto text",
        );
        $form = new TestNumberField();
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
    **/
    function testValidateLinkWithExistingDataExpectsDataDeletedAndReplaced() {
        // arrange
        $_POST = array(
            'link_url' => 'http://example.com',
            'link_text' => 'example.com',
        );
        $field = array(
            'slug' => 'link',
            'type' => 'link',
            'params' => array(),
            'meta_key' => 'link',
            'howto' => "Some howto text",
        );
        $existing = array( 'http://google.com', 'Google');
        $form = new TestNumberField();
        $post_id = 1;
        $form->fields = $field;
        // \WP_Mock::wpFunction(
        //  'delete_post_meta',
        //  array( 'times' => 1, 'return' => true)
        // );
        // \WP_Mock::wpFunction(
        //  'add_post_meta',
        //  array( 'times' => 2, 'return' => true)
        // );
        \WP_Mock::wpFunction(
            'update_post_meta',
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
    * @group stable
    * @group link_validate
    *
    **/
    function testValidateLinkWithExistingDataMatchingSubmittedExpectsNoaction() {
        // arrange
        $_POST = array(
            'link_url' => 'http://example.com',
            'link_text' => 'example.com',
        );
        $field = array(
            'slug' => 'link',
            'type' => 'link',
            'params' => array(),
            'meta_key' => 'link',
            'howto' => "Some howto text",
        );
        $existing = array( 'http://example.com', 'example.com');
        $form = new TestNumberField();
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
        $form->validate_taxonomyselect($form->fields['field_one'], $post->ID);

        // Assert: test will fail if wp_set_object_terms, get_term_by or
        // sanitize_text_field do not fire or fire more than once
    }

    /**
     * Tests whether if no existing metadata, add_post_meta is called by validate_select
     *
     * @group stable
     * @group select
     * @group isolated
    **/
    function testNoExistingDataValidateSelectExpectsAddPostMetaFiredOnce() {
        // Arrange
        global $post;
        $form = new TestNumberField();
        $form->fields['field_one']['type'] = 'select';
        $form->fields['field_one']['multiple'] = false;
        $_POST = array( 'field_one' => 'metadata');
        \WP_Mock::wpFunction('sanitize_text_field', array('times' => 1));
        \WP_Mock::wpFunction(
            'get_post_meta',
            array('times' => 1, 'return' => array() )
        );
        \WP_Mock::wpFunction('add_post_meta', array( 'times' => 1 ) );

        // act
        $form->validate_select($form->fields['field_one'],$post->ID);
    }

    /**
     * Tests whether if no existing metadata, add_post_meta will be called twice by 
     * validate_select if multiple values are in $_POST
     *
     * A multi select field will pass an array of values to the $_POST array, we 
     * expect cms-toolkit to iterate over that array adding new post data for each
     * entry.
     *
     * @group stable
     * @group select
     * @group isolated
     */
    function testNoExistingDataValidateSelectExpectsAddPostMetaTwice() {
        global $post;
        $form = new TestNumberField();
        $form->fields['field_one']['type'] = 'select';
        $form->fields['field_one']['multiple'] = false;
        $_POST = array( 'field_one' => array( 'metadata', 'otherdata' ) );
        \WP_Mock::wpFunction('sanitize_text_field', array('times' => 2));
        \WP_Mock::wpFunction(
            'get_post_meta',
            array('times' => 1, 'return' => array() )
        );
        \WP_Mock::wpFunction('add_post_meta', array( 'times' => 2 ) );

        // act
        $form->validate_select($form->fields['field_one'],$post->ID);

    }

    /**
     * Tests whether, if post has existing custom data but those data are
     * not in the $_POST array, delete_post_meta will be called on each
     * existing value.
     *
     * When a select field is submitted it will may contian values that 
     * exist already for this post in addition to new ones the user wants
     * to add. If a term is in both arrays ($existing and $_POST), we 
     * should keep it. If the term is in $existing but not $_POST, then a
     * user has removed it or chosen a different value and we should 
     * delete the previously stored metadata. This test verifies the latter
     * condition specifically where there is no _POST data set at all (a user
     * has set the <select> to a null value indicating they wish to delete 
     * the value and not replace it.
     *
     * @group stable
     * @group select
     * @group isolated
     */
    function testExistingDataButEmptyPostValidateSelectExpectsDeletePostMetaOnce() {
        global $post;
        $form = new TestNumberField();
        $form->fields['field_one']['type'] = 'select';
        $form->fields['field_one']['multiple'] = false;
        $_POST = array( 'field_one' => array( ) );
        \WP_Mock::wpFunction(
            'get_post_meta',
            array('times' => 1, 'return' => array('existing') )
        );
        \WP_Mock::wpFunction('delete_post_meta', array( 'times' => 1 ) );

        // act
        $form->validate_select($form->fields['field_one'],$post->ID);

    }

    /**
     * Tests whether, if $_POST contains non-identical values to $existing delete_post_meta
     * will be called on each existing value. Similar to L978 _supra_
     *
     * @group stable
     * @group select
     * @group isolated
     */
    function testExistingDataMismatchPostValidateSelectExpectsDeletePostMetaAndAddPostMetaOnceEach() {
        global $post;
        $form = new TestNumberField();
        $form->fields['field_one']['type'] = 'select';
        $form->fields['field_one']['multiple'] = false;
        $_POST = array( 'field_one' => array( 'non-existent' ) );
        \WP_Mock::wpFunction(
            'get_post_meta',
            array('times' => 1, 'return' => array('existing') )
        );
        \WP_Mock::wpFunction( 'sanitize_text_field', array( 'times' => 1 ) );
        \WP_Mock::wpFunction( 'delete_post_meta', array( 'times' => 1 ) );
        \WP_Mock::wpFunction( 'add_post_meta', array( 'times' => 1 ) );

        // act
        $form->validate_select($form->fields['field_one'],$post->ID);

    }

    /**
     * Tests whether, if $_POST is completley empty delete_post_meta will be called
     * on each existing value. Similar to L978 _supra_
     *
     * @group stable
     * @group select
     * @group isolated
     */
    function testExistingDataEmptyPostValidateSelectExpectsDeletePostMetaAndAddPostMetaOnceEach() {
        global $post;
        $form = new TestNumberField();
        $form->fields['field_one']['type'] = 'select';
        $form->fields['field_one']['multiple'] = false;
        $_POST = array();
        \WP_Mock::wpFunction(
            'get_post_meta',
            array('times' => 0, 'return' => array('existing') )
        );
        \WP_Mock::wpFunction( 'sanitize_text_field', array( 'times' => 0 ) );
        \WP_Mock::wpFunction( 'delete_post_meta', array( 'times' => 1 ) );
        \WP_Mock::wpFunction( 'add_post_meta', array( 'times' => 0 ) );

        // act
        $form->validate_select($form->fields['field_one'],$post->ID);

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
        $expected = array();

        //act
        $factory->validate_fieldset( $factory->fields['field'], $expected, $post->ID );

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
        $expected = array( 'field_num' => '0123456789', 'field_desc' => 'description' );

        //act
        $testValidFieldsetField->validate_fieldset( $testValidFieldsetField->fields['field'], $actual, $post->ID );

        //assert
        $this->assertEquals( $expected, $actual );
    }
    /**
    * Tests a formset to make sure that validate is called for each of the 
    * formset's fields.
    *
     * @group stable
     * @group validate
     * @group fieldset
     * @group validate_formset
     * @group isolated
    */
    function testValidFormsetOfTwoFieldsCallsValidateTwice() {
        //arrange
        global $post;
        $factory = $this->getMockBuilder('TestValidFormsetField')
                        ->setMethods( array( 'validate', ) )
                        ->getMock();
        $factory->expects( $this->exactly( 2 ) )
                ->method( 'validate' )
                ->will( $this->returnValue( true ) );
        $actual = array();

        //act
        $factory->validate_formset( $factory->fields['field'], $actual, $post->ID );

        //assert
    }
    /**
    * Tests a formset to make sure that validate adds validated values to the 
    * array that was passed in by reference.
    *
     * @group stable
     * @group validate
     * @group formset
     * @group validate_fieldset
     * @group isolated
    */
    function testValidateAddsValidatedValuesToPassedInArrayByValidateFormset() {
        // arrange
        global $post;
        $_POST = array( 'field_0_title' => 'Title 1', 'field_1_title' => 'Title 2' );
        $testValidFormsetField = new TestValidFormsetField();
        $actual = array();
        $expected = array( 'field_0_title' => 'Title 1', 'field_1_title' => 'Title 2' );

        //act
        $testValidFormsetField->validate_formset( $testValidFormsetField->fields['field'], $actual, $post->ID );

        //assert
        $this->assertEquals( $expected, $actual );
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
