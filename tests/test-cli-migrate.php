<?php
class MigrateTest extends PHPUnit_Framework_TestCase {
	
	private $testClass;
	private $post0;
	private $post1;
	private $posts;
	private $term1;
	private $term2;
	private $terms;

	function setUp() {
		$this->getMock('\WP_CLI_Command');
		$this->getMock('\CFPB\CLI_Common');
		$this->cli = $this->getMockBuilder('\WP_CLI')
							->setMethods(array('success'))
							->getMock();
		require_once( 'cli/migrate.php');

		$this->validTaxonomyArgs = array(
			'category',
			'new_category',
		);
		$this->missingTaxonomyArg= array( 'category' );
		$this->authorWithArg = array( 'category' );
		$this->include = array('include' => 1,);
		$this->exclude = array('exclude' => 1,);
		$this->post_type = array('post_type' => 'custom_post_type');
		$this->before = array('before' => '2013-05-01');
		$this->after = array('after' => '2013-01-01');
		$this->ignore_term = array('ignore_term' => 'custom_taxonomy,term,term-2');
		$this->post0 = new stdClass();
		$this->post1 = new stdClass();
		$this->term0 = new stdClass();
		$this->term1 = new stdClass();
		$this->post0->ID = 1;
		$this->post1->ID = 2;
		$this->term0->name = "Term";
		$this->term0->slug = "term";
		$this->term1->name = "Another";
		$this->term1->slug = "another";
		$this->terms = array( $this->term0, $this->term1 );
		$this->posts = array( 
			'posts' => array($this->post0, $this->post1), 
			'message' => 'Posts to migrate', );
		\WP_Mock::setUp();
	}

	function tearDown() {
		// \WP_Mock::tearDown();
	}

	/**
	 * Tests our ability to mock the cli interface
	 *
	 * @group wip
	 * @covers taxonomy
	 * 
	 */
	function testTaxonomyWithExistingToAndFromExpectsTaxonomiesMigrated() {
		// arrange
		
		$args = $this->validTaxonomyArgs;
		$assoc_args = array('post_type' => 'post');
		$this->posts['args'] = $args + $assoc_args;
		var_dump($this->posts);
		$mock = $this->getMockBuilder('\CFPB\Migrate_Command')
					->setMethods(array('get_specified_posts'))
					->getMock();
		$mock->expects($this->once())
			->method('get_specified_posts')
			->will($this->returnValue($this->posts));
		$new_term0 = array( 'slug' => 'term' );
		$new_term1 = array( 'slug' => 'another' );
		// mock taxonomy_exists form the WordPress API which we expect to be fired exactly twice
		\WP_Mock::wpFunction(
			'taxonomy_exists', 
			array(
				'times' => 2,
				'return' => true
			)
		);
		// mock wp_get_post_terms from the WordPress API which we expect to fire exactly twice 
		$terms = \WP_Mock::wpFunction(
			'wp_get_post_terms',
			array(
				'times' => 2,
				'with' => array( array($this->post0->ID, $args[0] ), array( $this->post1->ID, $args[0] ) ),
				'return' => $this->terms,
			)
		);
		// mock wp_insert_term from the WordPress API which we expect to fire exactly twice
		$new_term = \WP_Mock::wpFunction(
			'wp_insert_term',
			array(
				'times' => 2,
				'with' => array( array( $this->term0->name, $args[1], array('slug' => $this->term0->slug) ) ),
				'return' => array('term_id' => 0, 'term_id' => 1),
			)
		);
		// we expect get_term to fire twice with 
		\WP_Mock::wpFunction(
			'get_term',
			array(
				'times' => 2,
				'with' => array( array(1, $args[1]) ),
				'return' => $this->term0
			)
		);
		// we expect wp_set_objec_terms to fire twice
		\WP_Mock::wpFunction(
			'wp_set_object_terms',
			array(
				'times' => 2,
				'return' => true,
			)
		);
		$cli = $this->cli->getMock();
		$cli->expects($this->once())
					->method('success')
					->will($this->returnValue('Success'));
		// act
		$action = $mock->taxonomy($args, $assoc_args);
		$this->assertTrue($action == 'Success');
	}

}