<?php
use CFPB\Utils\Taxonomy as TaxUtils;

class TestTaxonomy extends TaxUtils {
	public $name = 'Taxonomy';
	public $plural = 'Taxonomies';
	public $slug = 'taxonomy';
	public $post_type = 'post';
	public $args = array();
}

class DummyTerm {
	public $term_id = 2;
}

class TaxonomiesTest extends PHPUnit_Framework_TestCase {
	function setUp() {
		\WP_Mock::setUp();
	}

	function tearDown() {
		\WP_Mock::tearDown();
	}

	function testBuildTaxonomyExpectsArgsParsedTaxonomyRegistered() {
		// Arrange
		$class = new TestTaxonomy();
		$return = array(
            'labels' => array(
	            'name' => $class->plural,
	            'singular_name' => $class->name,
	            'all_items' => 'All ' . $class->name,
	            'edit_item' => 'Edit ' . $class->name,
	            'view_item' => 'View ' . $class->name,
	            'update_item' => 'Update ' . $class->name,
	            'new_item_name' => 'New ' . $class->name,
	            'add_new_item' => 'Add new ' . $class->name,
	            'search_items' => 'Search ' . $class->plural,
	            'popular_items' => 'Popular '. $class->plural,
	            'add_or_remove_items' => 'Add or remove ' . $class->plural,
	            'choose_from_most_used' => 'Choose from the most used ' . $class->plural,
	            'not_found' => 'No '. $class->plural . ' found. Create one!',
        	),
            'hierarchical'          => false,
            'query_var'             => true,
            'rewrite'               => true,
            'public'                => true,
            'show_admin_column'     => false,
            'update_count_callback' => '',
        );
		$parsed = \WP_Mock::wpFunction('wp_parse_args', array('times' => 1, 'return' => $return ));
		\WP_Mock::wpFunction('register_taxonomy', array('times' => 1, 'with' => array($class->slug, $class->post_type, $parsed)));
		// Act
		$class->build_taxonomy($class->name, $class->plural, $class->slug, $class->post_type, $class->args);

		// Assert
	}

	function testTermAttachedRemovePostTermExpectsTermRemoved( ) {
		// Arrange
		$class = new TestTaxonomy();
		$post = 1;
		$taxonomy = 'category';
		$term = 2;
		$terms = \WP_Mock::wpFunction('wp_get_object_terms', array(
			'times' => 1,
			'with' => array($post, $taxonomy, array('fields' => 'ids')),
			'return' => array(
				0 => 1,
				1 => 2,
				2 => 3,
				),
			)
		);
		\WP_Mock::wpFunction('wp_set_object_terms', array(
			'times' => 1,
			'with' => array(1, $terms, 'category'),
			'return' => array(
				0 => 2,
				1 => 3,
				)
			)
		);
		// Act
		$class->remove_post_term($post, $term, $taxonomy);
		// Assert

		// Test is validated by whether wp_set_object_terms is called using the return of wp_get_object_terms
		// and whether each is called once.
	}

	function testTermAttachedIsNonNumericRemovePostTermExpectsTermConvertedToIDandRemoved() {
		// Arrange
		$class = new TestTaxonomy();
		$post = 1;
		$taxonomy = 'category';
		$term = 'Two';
		$DummyTerm = new DummyTerm();
		$term = \WP_Mock::wpFunction('get_term_by', array(
			'times' => 1,
			'with' => array('slug', $term, $taxonomy),
			'return' => $DummyTerm,
			)
		);
		$terms = \WP_Mock::wpFunction('wp_get_object_terms', array(
			'times' => 1,
			'with' => array($post, $taxonomy, array('fields' => 'ids')),
			'return' => array(
				0 => 1,
				1 => 2,
				2 => 3,
				),
			)
		);
		\WP_Mock::wpFunction('is_wp_error', array('times' => 1, 'return' => false));
		\WP_Mock::wpFunction('wp_set_object_terms', array(
			'times' => 1,
			'with' => array(1, $terms, 'category'),
			'return' => array(
				0 => 1,
				1 => 3,
				)
			)
		);
		// Act
		$class->remove_post_term($post, $term, $taxonomy);
		// Assert

		// Test is validated by whether wp_set_object_terms is called using the return of wp_get_object_terms
		// and whether each is called once.
	}
}
