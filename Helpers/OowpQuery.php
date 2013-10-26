<?php

namespace Outlandish\OowpBundle\Helpers;

use Outlandish\OowpBundle\Manager\QueryManager;
use Outlandish\OowpBundle\Oowp;
use Outlandish\OowpBundle\PostType\Post;

class OowpQuery extends \WP_Query implements \IteratorAggregate, \ArrayAccess, \Countable
{
	/**
	 * @var Oowp
	 */
	protected $postManager;

	/**
	 * @var QueryManager
	 */
	protected $queryManager;

	/**
	 * @param string|array $query
	 * @param Oowp $postManager
	 * @param $queryManager
	 */
	function __construct($query = '', $postManager, $queryManager) {
		global $wp_post_types;

		$this->postManager = $postManager;
		$this->queryManager = $queryManager;

		$defaults = array(
			'posts_per_page' => -1,
			'post_status' => 'publish'
		);
		$query = wp_parse_args($query, $defaults);

		// if there is no post type, or the post type is singular and isn't valid, replace it with 'any'
		if (!isset($query['post_type']) || (!is_array($query['post_type']) && !array_key_exists($query['post_type'], $wp_post_types))) {
			$query['post_type'] = 'any';
		}

		parent::__construct($query);

		if ($this->query_vars['error']) {
			//todo: throw exception
			die('Query error ' . $this->query_vars['error']);
		}
	}

	/* Interfaces */

	public function getIterator() {
		return new \ArrayIterator($this->posts);
	}

	public function offsetExists($offset) {
		return isset($this->posts[$offset]);
	}

	public function offsetGet($offset) {
		return $this->posts[$offset];
	}

	public function offsetSet($offset, $value) {
		$this->posts[$offset] = $value;
	}

	public function offsetUnset($offset) {
		unset($this->posts[$offset]);
	}

	public function count() {
		return count($this->posts);
	}

//	/**
//	 * Stores $this as the global $wp_query, executes the passed-in WP function, then reverts $wp_query
//	 * @return mixed
//	 */
//	protected function callGlobalQuery() {
//		global $wp_query;
//		$args     = func_get_args();
//		$function = array_shift($args);
//		$oldQuery = $wp_query;
//		$wp_query = $this;
//		$returnVal = call_user_func_array($function, $args);
//		$wp_query = $oldQuery;
//		return $returnVal;
//	}
//
//	/**
//	 * Returns the prev/next links for this query
//	 * @param string $sep
//	 * @param string $preLabel
//	 * @param string $nextLabel
//	 * @return mixed
//	 */
//	public function postsNavLink($sep = '', $preLabel = '', $nextLabel = '') {
//		return $this->callGlobalQuery('get_posts_nav_link', $sep, $preLabel, $nextLabel);
//	}
//
//	/**
//	 * @return QueryVars
//	 */
//	public function queryVars() {
//		return new QueryVars($this->query_vars);
//	}
//
//	public function sortByIds($ids) {
//		$indexes = array_flip($ids);
//		usort($this->posts, function($a, $b) use ($indexes) {
//			$aIndex = $indexes[$a->ID];
//			$bIndex = $indexes[$b->ID];
//			return $aIndex < $bIndex ? -1 : 1;
//		});
//	}

	/**
	 * Convert WP_Post objects to oowp Post objects
	 * @return Post[]
	 */
	public function &get_posts() {
		parent::get_posts();

		foreach ($this->posts as $i => $post) {
			$classname = $this->postManager->postTypeClass($post->post_type);
			$this->posts[$i] = new $classname($post, $this->postManager, $this->queryManager);
		}

		if (count($this->posts)) {
			$this->post = $this->posts[0];
			$this->queried_object = $this->post;
			$this->queried_object_id = $this->post->ID;
		}

		return $this->posts;
	}
}
