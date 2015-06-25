<?php
namespace CFPB\Tests;
use \CFPB\Utils\MetaBox\Callbacks;
use \CFPB\Utils\Taxonomy;
use \DateTime;

function strtotime() {
	return MetaBoxCallbacksTest::$now ?: time('now');
}

class MetaBoxCallbacksTest extends \PHPUnit_Framework_TestCase {
	
	public static $now;

	function setUp() {
		\WP_Mock::setUp();
	}

	function tearDown() {
		\WP_Mock::tearDown();
	}

	/**
	 * Tests whether the date() method properly calls wp_set_objeect_terms
	 *
	 * @group isolated
	 * @group stable
	 * @group date
	 * @group taxonomy_save
	 * 
	 */
	function testDateExpectsWPSetObjectTermsFromAPI() {
		// Arrange
		\WP_Mock::wpFunction( 'get_post_meta' );
		\WP_Mock::wpFunction( 'remove_post_meta' );
		\WP_Mock::wpFunction( 'update_post_meta' );
		\WP_Mock::wpFunction('wp_set_object_terms', array('times' => 1));
		$post_id = 0;
		$taxonomy = 'category';
		$data = Datetime::createFromFormat('F j Y', 'January 12 2000');
		// Act
		$c = new Callbacks();
		$c->date($post_id, $taxonomy, false, $data);
		// Assert
	}

	/**
	 * Tests whether the date() method properly calls get_term_by when attempting
	 * to delete a term from a taxonomy
	 *
	 * @group isolated
	 * @group stable
	 * @group date
	 * @group taxonomy_save
	 * 
	 */
	function testDateCallsGetTermByWhenAttemptingToDeleteATerm() {
		// Arrange
		\WP_Mock::wpFunction( 'get_post_meta' );
		\WP_Mock::wpFunction( 'remove_post_meta' );
		\WP_Mock::wpFunction( 'update_post_meta' );
		$c = new Callbacks();
		$_POST = array( 'rm_tax_0' => 'term' );
		\WP_Mock::wpFunction('get_term_by', array('times' => 1, 'return' => false ) );
		// Act
		$c->date( 0, 'tax', false, array(), null, 0 );
		// Assert
	}

	/**
	 * Tests whether the date() method properly calls remove_post_term when attempting
	 * to delete a term from a taxonomy after it confirms the term exists
	 *
	 * @group isolated
	 * @group stable
	 * @group date
	 * @group taxonomy_save
	 * 
	 */
	function testDateCallsRemovePostTermWhenAttemptingToDeleteATerm() {
		// Arrange
		\WP_Mock::wpFunction( 'get_post_meta' );
		\WP_Mock::wpFunction( 'remove_post_meta' );
		\WP_Mock::wpFunction( 'update_post_meta' );
		$_POST = array( 'rm_tax_0' => 'term' );
		$term = new \StdClass;
		$term->term_id = 1;
		\WP_Mock::wpFunction('get_term_by', array( 'return' => $term ) );
		$Taxonomy = $this->getMockBuilder( 'Taxonomy' )
						 ->setMethods( array( 'remove_post_term' ) )
						 ->getMock();
		$Taxonomy->expects( $this->once() )
				 ->method( 'remove_post_term' );
		$c = new Callbacks();
		$c->replace_Taxonomy( $Taxonomy );
		// Act
		$c->date( 0, 'tax', false, array(), null, 0 );
		// Assert
	}

	/**
	 * Test whether a post term will be removed if a rm_{taxonomy} key is passed
	 *
	 * @group stable
	 * @group remove_term
	 * @group isolated
	 * @group date
	 */
	function testRmTermKeyDateExpectsRemovePostTermCalled() {
		// Arrange
		\WP_Mock::wpFunction( 'get_post_meta' );
		\WP_Mock::wpFunction( 'remove_post_meta' );
		\WP_Mock::wpFunction( 'update_post_meta' );
		$post_id = 0;
		$_POST['rm_category_1'] = 'term';
		$taxonomy = 'category';
		$return = new \StdClass;
		$return->term_id = 1;
		$term = \WP_Mock::wpFunction('get_term_by', array('times' => 1, 'return' => $return));
		$Mock = $this->getMock('Taxonomy', array('remove_post_term'));
		$Mock->expects($this->once())
			->method('remove_post_term');
		$c = new Callbacks();
		$c->replace_Taxonomy($Mock);

		// Act
		$c->date($post_id, $taxonomy, false, array(), null, 1);

		// Assert: test fails if remove_post_term is called more or fewer than 1 time.
	}

	/**
	 * Tests whether the date() method properly calls wp_set_objeect_terms,
	 * get_term_by, wp_update_term
	 *
	 * @group isolated
	 * @group stable
	 * @group date
	 * @group taxonomy_save
	 * 
	 */
	function testDateGetTermByAndWPUpdateTermCalledOnceForDateData() {
		// Arrange
		\WP_Mock::wpFunction('wp_set_object_terms', array('times' => 1));
		$term = new \StdClass;
		$term->term_id = 1;
		\WP_Mock::wpFunction('get_term_by', array('times' => 1, 'return' => $term));
		\WP_Mock::wpFunction('wp_update_term', array('times' => 1));
		$post_id = 0;
		$taxonomy = 'category';
		$data = Datetime::createFromFormat('F j Y', 'January 12 2000');
		// Act
		$c = new Callbacks();
		$c->date($post_id, $taxonomy, false, $data, 'America/New_York');
		// Assert
	}
}
?>