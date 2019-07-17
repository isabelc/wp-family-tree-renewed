<?php
require_once('wpft_options.php');


$the_family_store = null;

class tree {

	static function get_id_by_name($name, $tree) {
		if (is_array($tree)) {
			foreach ($tree as $node) {
				if ($node->name == $name) {
					return $node->post_id;
				}
			}
		}
		return -1;
	}
	static function get_node_by_id($id, $tree) {
		if (is_array($tree)) {
			foreach ($tree as $node) {
				if ($node->post_id == $id) {
					return $node;
				}
			}
		}
		return false;
	}


	/* Load and return the entire tree from the database. */
	static function get_tree() {
		global $wpdb, $the_family_store;
	
		if (!empty($the_family_store)) {
			return $the_family_store;
		}
		
		$category = wpft_options::get_option('family_tree_category_key');
		$catid = get_cat_ID( $category );

		$q = new WP_Query();
		$q->query('cat='.$catid.'&showposts=-1');
		$q->get_posts();

		$the_family = array();	
		foreach ($q->posts as $post) {
//			echo $post->ID."<br>";
//			echo $post->post_title."<br>";
			$the_family[$post->ID] = node::get_node($post);
		}
		wp_reset_postdata();
		
		// Set father/mother child relationships...
		foreach ($the_family as $fm) {
			if (isset($fm->father) && !empty($fm->father) && is_numeric($fm->father)) {

				if ( ! empty( $the_family[$fm->father] ) ) {
					$the_family[$fm->post_id]->name_father 	= $the_family[$fm->father]->name;	
					$the_family[$fm->post_id]->url_father 		= $the_family[$fm->father]->url;
					$father = $the_family[$fm->father];
				}
	
				$father->children[] = $fm->post_id;
			}
			if (isset($fm->mother) && !empty($fm->mother) && is_numeric($fm->mother)) {
				$the_family[$fm->post_id]->name_mother 	= $the_family[$fm->mother]->name;
				$the_family[$fm->post_id]->url_mother 		= $the_family[$fm->mother]->url;
				$mother = $the_family[$fm->mother];
				$mother->children[] = $fm->post_id;
			}
		}
	
		// Set sibling relationships...
		foreach ($the_family as $fm) {
			$siblings = array();	// Siblings are your fathers children + your mothers children but not you
			$siblings_f = array();
			$siblings_m = array();
			
			if (isset($fm->father) && !empty($fm->father) && is_numeric($fm->father)) {
				if ( ! empty( $the_family[$fm->father] ) ) {
					$father = $the_family[$fm->father];
				}
				if (is_array($father->children)) {
					$siblings_f = $father->children; 
				}
			}
			if (isset($fm->mother) && !empty($fm->mother) && is_numeric($fm->mother)) {
				$mother = $the_family[$fm->mother];
				if (is_array($mother->children)) {
					$siblings_m = $mother->children; 
				}
			}
			$siblings = array_merge( $siblings_f, array_diff($siblings_m, $siblings_f));
			$temp = array();
			$temp[] = $fm->post_id;
			$fm->siblings = array_diff($siblings, $temp);
		}

		// Set partner...
		foreach ( $the_family as $fm ) {
			$fm->partners = array();

			// If partner has been set (by database meta data) then add that one first
			if (!empty($fm->spouse)) {
				$fm->partners[] = $fm->spouse;

				$the_family[$fm->post_id]->name_spouse = empty( $the_family[$fm->spouse]->name ) ? '' : $the_family[$fm->spouse]->name;
				
				$the_family[$fm->post_id]->url_spouse = empty( $the_family[$fm->spouse]->url ) ? '' : $the_family[$fm->spouse]->url;
			}
		
			if (is_array($fm->children)) {
				// Calculate the other partners as parents of your children...
				foreach ($fm->children as $childid) {
					$prospective_partner = "";
					
					$child = $the_family[$childid];

					if ($fm->gender == 'm') {
						if (!empty($child->mother)) {
							$prospective_partner = $child->mother;
						}
					} else if ($fm->gender == 'f') {
						if (!empty($child->father)) {
							$prospective_partner = $child->father;
						}
					}
					if (!empty($prospective_partner) && is_numeric($prospective_partner)) {
						$found = false;
						foreach ($fm->partners as $p) {
							if ($p == $prospective_partner) {
								$found = true;
								break;
							}
						}
						if (!$found) {
							$fm->partners[] = $prospective_partner;
						}
					}
				}
			}
		}

		uasort($the_family, "cmp_birthdates");

		$the_family_store = $the_family;
		return $the_family;
	}	
}

function cmp_birthdates($a, $b) {	
	$a = explode("-", $a->born); 
	$b = explode("-", $b->born);
	if (! is_numeric($a[0]) || ! is_numeric($b[0])) {
		return 0;
	}

	$yd = $a[0] - $b[0];	// check year difference..
	if ($yd != 0) {
		return $yd;
	} else {
		if ( isset($a[1]) && isset($b[1]) ) {
			$md = $a[1] - $b[1];
			if ($md != 0) {		// check month difference...
				return $md;
			} else {
				$dd = $a[2] - $b[2];
				return $dd;		// check day difference...
			}
		}

	}
}




?>