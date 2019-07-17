<?php
/*
Plugin Name: WP Family Tree Renewed
Plugin URI:  https://isabelcastillo.com/free-plugins/wp-family-tree
Description: Family Tree plugin
Version: 2.2-alpha.1
Author: Isabel Castillo
Author URI: https://isabelcastillo.com
License: GNU GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: wpftr @todo update all
Domain Path: /languages

Copyright 2015-2019 Isabel Castillo

This file is part of WP Family Tree Renewed.

WP Family Tree Renewed is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

WP Family Tree Renewed is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with WP Family Tree Renewed. If not, see <http://www.gnu.org/licenses/>.
*/
require_once('wpft_options.php');
require_once('class.node.php');
require_once('class.tree.php');

/* Render a list of nodes. */
function family_list() {
	
	wpft_options::check_options();

	$the_family = tree::get_tree();
	
	$total = count( $the_family );

	$html = '<p>' . sprintf( __( 'This list currently includes %d family members, listed alphabetically by first name.', 'wp-family-tree' ), $total ) . '</p>';

	// alphabetize the list
	function compare_by_name($a, $b) {
	  return strcmp( $a->name, $b->name );
	}
	uasort( $the_family, 'compare_by_name' );
	
	// Print information about each family member...
	foreach ($the_family as $fm) {

		$html .= $fm->get_html($the_family);
		$html .= '<hr>';
	}
	return $html;
}

/* Render the tree. */
function family_tree( $root = '' ) {
	$the_family = tree::get_tree();
	$out = '';
	$ancestor = '';
	
	if (!empty($_GET['ancestor'])) {
		$ancestor = $_GET['ancestor'];
	} else { 
		if (!empty($root)) {
			$ancestor = $root;
		} else {
			$node = reset($the_family);
			$ancestor = ($node!==false)?$node->post_id:'-1';
		}
	}

	if (!is_numeric($ancestor)) {
		// find post by post title and assigns the post id to ancestor
		$ancestor = tree::get_id_by_name($ancestor, $the_family);
	}

	$render_from_parent = true;
	if ($render_from_parent) {
		$node = tree::get_node_by_id($ancestor, $the_family);	
		if (!empty($node->father)) {
			$ancestor = $node->father;
		} else if (!empty($node->mother)) {
			$ancestor = $node->mother;
		}
	}
	
	$out .= "<script type='text/javascript'>";	

	// Generate javascript tree text...
	$tree_data_js = "var tree_txt = new Array(\n";	
	$the_family = tree::get_tree();
	$first = true;
	foreach ($the_family as $node) {
		if (!$first) {
			$tree_data_js .= ','."\n";
		} else {
			$first = false;
		}
		$str  = '"EsscottiFTID='.$node->post_id.'",'."\n";
		$str .= '"Name='.addslashes($node->name).'",'."\n";
		if ($node->gender=='m') {
			$str .= '"Male",'."\n";
		} else if ($node->gender=='f') {
			$str .= '"Female",'."\n";
		}
		$str .= '"Birthday='.$node->born.'",'."\n";
		if (!empty($node->died) && $node->died != '-') {
			$str .= '"Deathday='.$node->died.'",'."\n";
		}

		if (isset($node->partners) && is_array($node->partners)) {
			foreach ($node->partners as $partner) {
				if (is_numeric($partner)) {
					if ( ! empty( $the_family[$partner] ) ) {
						$str .= '"Spouse=' . $the_family[$partner]->post_id . '",' . "\n";
					}
				}
			}
		}

		$str .= '"Toolbar=toolbar'.$node->post_id.'",'."\n";
		$str .= '"Thumbnaildiv=thumbnail'.$node->post_id.'",'."\n";

		$str .= '"Parent=';
		if ( isset($the_family[$node->mother]) ) {
			$str .= $the_family[$node->mother]->post_id;
		}
		$str .= '",'."\n";

		$str .= '"Parent=';
		if ( isset($the_family[$node->father]) ) {
			$str .= $the_family[$node->father]->post_id;

		}
		$str .= '"';

		$tree_data_js .= $str;	
	}
	$tree_data_js .= ');'."\n";
	$out .= $tree_data_js;
	// End generate javascript tree text.
	$out .= 'BOX_LINE_Y_SIZE = "'. 	wpft_options::get_option('generationheight').'";'."\n";
	$out .= 'canvasbgcol = "'. 	wpft_options::get_option('canvasbgcol').'";'."\n";
	$out .= 'nodeoutlinecol = "'.wpft_options::get_option('nodeoutlinecol').'";'."\n";
	$out .= 'nodefillcol	= "'. wpft_options::get_option('nodefillcol').'";'."\n";
	$out .= 'nodefillopacity = '.wpft_options::get_option('nodefillopacity').';'."\n";
	$out .= 'nodetextcolour = "'.wpft_options::get_option('nodetextcolour').'";'."\n";
	$out .= 'setOneNamePerLine('.wpft_options::get_option('bOneNamePerLine').');'."\n";
	$out .= 'setOnlyFirstName('.wpft_options::get_option('bOnlyFirstName').');'."\n";
	$out .= 'setBirthAndDeathDates('.wpft_options::get_option('bBirthAndDeathDates').');'."\n";
	$out .= 'setConcealLivingDates('.wpft_options::get_option('bConcealLivingDates').');'."\n";
	$out .= 'setShowSpouse('.wpft_options::get_option('bShowSpouse').');'."\n";
	$out .= 'setShowOneSpouse('.wpft_options::get_option('bShowOneSpouse').');'."\n";	
	$out .= 'setVerticalSpouses('.wpft_options::get_option('bVerticalSpouses').');'."\n";
	$out .= 'setMaidenName('.wpft_options::get_option('bMaidenName').');'."\n";
	$out .= 'setShowGender('.wpft_options::get_option('bShowGender').');'."\n";
	$out .= 'setDiagonalConnections('.wpft_options::get_option('bDiagonalConnections').');'."\n";
	$out .= 'setRefocusOnClick('.wpft_options::get_option('bRefocusOnClick').');'."\n";
	$out .= 'setShowToolbar('.wpft_options::get_option('bShowToolbar').');'."\n";
	$out .= 'setNodeRounding('.wpft_options::get_option('nodecornerradius').');'."\n";

	if (wpft_options::get_option('bShowToolbar') == 'true') {
		$out .= 'setToolbarYPad(20);'."\n";
	} else {
		$out .= 'setToolbarYPad(0);'."\n";
	}
	$out .= 'setToolbarPos(true, 3, 3);'."\n";
	$out .= 'setMinBoxWidth('.wpft_options::get_option('nodeminwidth').');'."\n";

	$out .= 'jQuery(document).ready(function($){'."\n";
	$out .= "	$('#dragableElement').draggableTouch();"."\n";
	$out .= '	familytreemain();'."\n";
	$out .= "	var midpos = $('#familytree svg').width()/2 - $('#borderBox').width()/2;"."\n";
	$out .= "	$('#dragableElement').css('left', -midpos);"."\n";
	$out .= '});'."\n";
	
	$out .= '</script>';	

/*
	setDeath = function(bState) 				
*/
	$out .= '<input type="hidden" size="30" name="focusperson" id="focusperson" value="'.$ancestor.'">'."\n";

	$out .= '<div id="borderBox">'."\n";
	$out .= '<div id="dragableElement">';
	$out .= '<div id="tree-container">'."\n";
	$out .= '<div id="toolbar-container">'."\n";
	foreach ($the_family as $node) {
		$out .= $node->get_toolbar_div();
	}
	$out .= '</div>'."\n";
	$out .= '<div id="thumbnail-container">'."\n";
	foreach ($the_family as $node) {
		$out .= $node->get_thumbnail_div();
	}
	$out .= '</div>'."\n";
	$out .= '<div id="familytree"></div>'."\n";

	// @todo remove image on hover since it will now be shown by default

	$out .= '<img name="hoverimage" id="hoverimage" style="visibility:hidden;" >'."\n";
	
	$out .= '</div>'."\n"; // tree-container
	$out .= '</div>'."\n"; // borderBox
	$out .= '</div>'."\n"; // dragableElement
	return $out;
}
function bio_data() {
	global $post;
	$ftlink = wpft_options::get_option('family_tree_link');
	if (strpos($ftlink, '?') === false) {
		echo '<p><a href="'.$ftlink.'?ancestor='.$post->post_title.'">click here to view family tree</a>';
	} else {
		echo '<p><a href="'.$ftlink.'&ancestor='.$post->post_title.'">click here to view family tree</a>';
	}
}


function family_tree_edit_page_form()
{
    global $post;
    ?>
    <div id="ftdiv" class="postbox">
    <h3>Family tree info (optional)</h3>
    <div class="inside">

	<table>
<?php

	$family 	= get_posts('category_name='.wpft_options::get_option('family_tree_category_key').'&numberposts=-1&orderby=title&order=asc');
	$males 		= array();
	$females 	= array();
	foreach ($family as $f) {
		if ($f->ID != $post->ID) {
			$postgender = get_post_meta($f->ID, 'gender', true);
			if ($postgender == "m") {
				$males[] = $f;
			} else if ($postgender = "f") {
				$females[] = $f;
			} else {
				$males[] = $f;
				$females[] = $f;
			}
		}
	}

	// @todo localize.

	$gender = get_post_meta( $post->ID, 'gender', true );
	$mother = get_post_meta( $post->ID, 'mother', true );
	$father = get_post_meta( $post->ID, 'father', true );
	$spouse = get_post_meta( $post->ID, 'spouse', true );
?>
	<tr><td>Gender:</td><td> 
    <select name="gender" id="gender">
    <option value="" <?php if (empty($gender)) echo "selected=\"selected\""; ?>></option>
    <option value="m" <?php if ($gender == "m") echo "selected=\"selected\""; ?>>Male</option>
    <option value="f" <?php if ($gender == "f") echo "selected=\"selected\""; ?>>Female</option>
	</select></td></tr>
    <tr><td>Born (YYYY-MM-DD):</td><td><input type="text" name="born" value="<?php echo esc_html( get_post_meta( $post->ID, 'born', true ), true ) ?>" id="born" size="80" /></td></tr>
    <tr><td>Died (YYYY-MM-DD):</td><td><input type="text" name="died" value="<?php echo esc_html( get_post_meta( $post->ID, 'died', true ), true ) ?>" id="died" size="80" /></td></tr>
    <tr><td>Mother:</td><td>
    <select style="width:200px" name="mother" id="mother">
    <option value="" <?php if (empty($mother)) echo "selected=\"selected\""; ?>> </option>
<?php
	foreach ($females as $f) {
		echo '<option value="'.$f->ID.'" ';
		if ($f->ID == $mother) echo "selected=\"selected\"";
		echo '>'.$f->post_title.'</option>';
	}
?>
	</select>
	</td></tr>

    <tr><td>Father:</td><td>
    <select style="width:200px" name="father" id="father">
    <option value="" <?php if (empty($father)) echo "selected=\"selected\""; ?>> </option>
<?php
	foreach ($males as $f) {
		echo '<option value="'.$f->ID.'" ';
		if ($f->ID == $father) echo "selected=\"selected\"";
		echo '>'.$f->post_title.'</option>';
	}
?>
	</select>
	</td></tr>

    <tr><td>Spouse:</td><td>
    <select style="width:200px" name="spouse" id="spouse">
    <option value="-" <?php if (empty($spouse) || $spouse=="-") echo "selected=\"selected\""; ?>> </option>
<?php
/*
	if ($gender == "f") {
		foreach ($males as $f) {
			echo '<option value="'.$f->ID.'" ';
			if ($f->ID == $spouse) echo "selected=\"selected\"";
			echo '>'.$f->post_title.'</option>';
		}
	} else if ($gender == "m") {
		foreach ($females as $f) {
			echo '<option value="'.$f->ID.'" ';
			if ($f->ID == $spouse) echo "selected=\"selected\"";
			echo '>'.$f->post_title.'</option>';
		}
	} else {
*/
		foreach ($family as $f) {
			echo '<option value="'.$f->ID.'" ';
			if ($f->ID == $spouse) echo "selected=\"selected\"";
			echo '>'.$f->post_title.'</option>';
		}
//	}
?>
	</select>
	</td></tr>

    </table>
    </div>
    </div>
    <?php
}


// @todo Occupation
// @todo Locations: birthplace, died at, current location


/**
 * Update the family post meta. Called on post save_post.
 * @param int $post_id The post ID.
 * @param post $post The post object.
 */
function family_tree_update_post( $post_id, $post = false ) {

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// Avoid running this for large picture gallery imports. Only update meta if this is a family tree post.

	$family_category = wpft_options::get_option('family_tree_category_key');
	$cats = get_the_category( $post_id );	// get array of category objects that apply to this post
	if ( is_array( $cats ) ) {
		foreach ( $cats as $cat ) {
			if ( $cat->slug == $family_category || $cat->name == $family_category ) {
				// This post is a family member post so do the work...

				if ( isset( $_POST['born'] ) ) {
					update_post_meta( $post_id, 'born', sanitize_text_field( $_POST['born'] ) );
				}

				if ( isset( $_POST['died'] ) ) {
					update_post_meta( $post_id, 'died', sanitize_text_field( $_POST['died'] ) );
				}

				if ( isset( $_POST['mother'] ) ) {
					update_post_meta( $post_id, 'mother', sanitize_text_field( $_POST['mother'] ) );
				}

				if ( isset( $_POST['father'] ) ) {
					update_post_meta( $post_id, 'father', sanitize_text_field( $_POST['father'] ) );
				}

				if ( isset( $_POST['spouse'] ) ) {
					update_post_meta( $post_id, 'spouse', sanitize_text_field( $_POST['spouse'] ) );
				}

				if ( isset( $_POST['gender'] ) ) {
					update_post_meta( $post_id, 'gender', sanitize_text_field( $_POST['gender'] ) );
				}
			}
		}
	}
}

// Function to deal with showing the family tree on pages
function family_list_insert($content) {
	if (preg_match('{FAMILY-MEMBERS}',$content)) {
		$ft_output = family_list();
		$content = str_replace('{FAMILY-MEMBERS}', $ft_output, $content);
	}
	return $content;
}
// Function to deal with showing the family tree on pages
function family_tree_insert($content) {
	if (preg_match('{FAMILY-TREE}',$content)) {
		$ft_output = family_tree();
		$content = str_replace('{FAMILY-TREE}', $ft_output, $content);
	}
	return $content;
}
// Function to deal with showing biodata on posts
function bio_data_insert($content) {
	global $post;
	$category = wpft_options::get_option('family_tree_category_key');
	$cats = get_the_category();	// get array of category objects that apply to this post
	foreach ($cats as $cat) {
		if ($cat->slug == $category || $cat->name == $category) {
			// This post is a family member post so do the work...
			$the_family = tree::get_tree();
			if (isset($the_family[$post->ID])) {
				$html = $the_family[$post->ID]->get_html($the_family);
				$content = $html.$content;
			}
			break;	
		}	
	}
	return $content;
}

function wpft_family_tree_shortcode($atts, $content=NULL) {
	$root = isset( $atts['root'] ) ? $atts['root'] : '';
	$ft_output = family_tree($root);

	wpft_options::check_options();

	return $ft_output;
}

function wpft_family_members_shortcode($atts, $content=NULL) {
	$root = isset( $atts['root'] ) ? $atts['root'] : '';
	$ft_output = family_tree($root);
	$ft_output = family_list();
		
	wpft_options::check_options();

	return $ft_output;
}

add_shortcode('family-tree', 'wpft_family_tree_shortcode');
add_shortcode('family-members', 'wpft_family_members_shortcode');


add_action('admin_menu', 'family_tree_options_page');

function wpft_addHeaderCode() {
	$plugloc = plugin_dir_url( __FILE__ );
	wp_enqueue_script('jquery');
	wp_enqueue_script('raphael', $plugloc.'raphael.js');
	wp_enqueue_script('familytree', $plugloc.'familytree.js');
	wp_enqueue_script('draggabletouch', $plugloc.'jquery.draggableTouch.js', array( 'jquery' ));
	wp_enqueue_style('ft-style', $plugloc.'styles.css');
}
// Enable the ability for the family tree to be loaded from pages
add_filter('the_content','family_list_insert');
add_filter('the_content','family_tree_insert');

if (wpft_options::get_option('show_biodata_on_posts_page') == 'true') {
	add_filter('the_content','bio_data_insert');
}
add_action('wp_enqueue_scripts', 'wpft_addHeaderCode');
add_action( 'save_post_post', 'family_tree_update_post', 10, 2 );
add_action('edit_page_form', 'family_tree_edit_page_form');
add_action('edit_form_advanced', 'family_tree_edit_page_form');
add_action('simple_edit_form', 'family_tree_edit_page_form');
