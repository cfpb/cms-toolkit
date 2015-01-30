<?php
namespace CFPB\Tests;
use \CFPB\Utils\MetaBox\Callbacks;

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
		\WP_Mock::wpFunction('wp_set_object_terms', array('times' => 1));
		$post_id = 0;
		$taxonomy = 'category';
		$data['category'] = 'January 1, 1970';
		// Act
		$c = new Callbacks();
		$c->date($post_id, $taxonomy, false, $data);
		// Assert
	}

	/**
	 * Tests whether the current time will be assigned if non is given.
	 *
	 * @group stable
	 * @group isolated
	 * @group date
	 * @group taxonomy_save
	 * 
	 */
	function testDateExpectsCurrentTime() {
		// Arrange
		$post_id = 0;
		$taxonomy = 'category';
		// replace fix time at 12:00 today just in case the test takes longer than 1 second to run (it shouldn't)
		self::$now = strtotime('12:00');
		$expected = strtotime('now');
		// Act
		$c = new Callbacks();
		$actual = $c->date($post_id, $taxonomy );

		// Assert
		$this->assertEquals($expected, $actual, 'Time should be equal to the current time, but was not.');
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
		$c->date($post_id, $taxonomy, false, array(), 1);

		// Assert: test fails if remove_post_term is called more or fewer than 1 time.
	}
}
?>