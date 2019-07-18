<?php
function family_tree_options_page() {
	if (function_exists('add_options_page')) {
		add_options_page('WP Family Tree', 'WP Family Tree', 'manage_options', 'wp-family-tree', 'family_tree_options_subpanel');
	}
}
function family_tree_options_subpanel() {
	global $wp_version;
	
	if (isset($_POST['update_options'])) {
		
		if ( function_exists('check_admin_referer') ) {
			check_admin_referer('family-tree-action_options');
		}

		if ($_POST['family_tree_category_key'] != "")  {
			update_option('family_tree_category_key', stripslashes(strip_tags($_POST['family_tree_category_key'])));
		}
		if ($_POST['family_tree_link'] != "")  {
			update_option('family_tree_link', stripslashes(strip_tags($_POST['family_tree_link'])));
		}

		update_option('show_biodata_on_posts_page', 		($_POST['show_biodata_on_posts_page']=='Y')?'true':'false');

		update_option('family_tree_toolbar_blogpage', stripslashes(strip_tags($_POST['family_tree_toolbar_blogpage'])));
		update_option('family_tree_toolbar_treenav', stripslashes(strip_tags($_POST['family_tree_toolbar_treenav'])));
		update_option('bOneNamePerLine', 		($_POST['bOneNamePerLine']=='Y')?'true':'false');
		if ( isset( $_POST['bOnlyFirstName'] ) ) {
			$bOnlyFirstName = $_POST['bOnlyFirstName'] == 'Y' ? 'true' : 'false';
			update_option('bOnlyFirstName', $bOnlyFirstName );
		} else {
			update_option( 'bOnlyFirstName', 'false' );
		}

		update_option('bBirthAndDeathDates', 	($_POST['bBirthAndDeathDates']=='Y')?'true':'false');

		if ( isset( $_POST['bConcealLivingDates'] ) ) {
			$bConcealLivingDates = $_POST['bConcealLivingDates'] == 'Y' ? 'true' : 'false';
			update_option('bConcealLivingDates', $bConcealLivingDates );
		} else {
			update_option( 'bConcealLivingDates', 'false' );
		}
		update_option('bShowSpouse', 				($_POST['bShowSpouse']=='Y')?'true':'false');
		if ( isset( $_POST['bShowOneSpouse'] ) ) {
			$bShowOneSpouse = $_POST['bShowOneSpouse'] == 'Y' ? 'true' : 'false';
			update_option('bShowOneSpouse', $bShowOneSpouse );
		} else {
			update_option( 'bShowOneSpouse', 'false' );
		}		
		update_option('bVerticalSpouses', 		($_POST['bVerticalSpouses']=='Y')?'true':'false');
		// @todo see why this is not used anymore update_option('bMaidenName', 				($_POST['bMaidenName']=='Y')?'true':'false');

		if ( isset( $_POST['bShowGender'] ) ) {
			update_option('bShowGender', ($_POST['bShowGender']=='Y')?'true':'false');
		} else {
			update_option('bShowGender', 'false');
		}

		if ( isset( $_POST['bDiagonalConnections'] ) ) {
			$bDiagonalConnections = $_POST['bDiagonalConnections'] == 'Y' ? 'true' : 'false';
			update_option( 'bDiagonalConnections', $bDiagonalConnections );
		} else {
			update_option( 'bDiagonalConnections', 'false' );
		}
		if ( isset( $_POST['bRefocusOnClick'] ) ) {
			$bRefocusOnClick = $_POST['bRefocusOnClick'] == 'Y' ? 'true' : 'false';
			update_option('bRefocusOnClick', $bRefocusOnClick );
		} else {
			update_option( 'bRefocusOnClick', 'false' );
		}
		update_option('bShowToolbar', 			($_POST['bShowToolbar']=='Y')?'true':'false');

		if ($_POST['canvasbgcol'] != "")  {
			update_option('canvasbgcol', stripslashes(strip_tags($_POST['canvasbgcol'])));
		}
		if ($_POST['nodeoutlinecol'] != "")  {
			update_option('nodeoutlinecol', stripslashes(strip_tags($_POST['nodeoutlinecol'])));
		}
		if ($_POST['nodefillcol'] != "")  {
			update_option('nodefillcol', stripslashes(strip_tags($_POST['nodefillcol'])));
		}
		if ($_POST['nodefillopacity'] != "")  {
			update_option('nodefillopacity', stripslashes(strip_tags($_POST['nodefillopacity'])));
		}
		if ($_POST['nodetextcolour'] != "")  {
			update_option('nodetextcolour', stripslashes(strip_tags($_POST['nodetextcolour'])));
		}
		if ($_POST['nodecornerradius'] != "")  {
			update_option('nodecornerradius', stripslashes(strip_tags($_POST['nodecornerradius'])));
		}
		if ($_POST['nodeminwidth'] != "")  {
			update_option('nodeminwidth', stripslashes(strip_tags($_POST['nodeminwidth'])));
		}
		if ($_POST['generationheight'] != "")  {
			update_option('generationheight', stripslashes(strip_tags($_POST['generationheight'])));
		}
		echo '<div class="updated"><p>Options saved.</p></div>';
	}

 ?>
	<div class="wrap">
	<h2>WP Family Tree Options</h2>
	<form name="ft_main" method="post">
<?php
	if (function_exists('wp_nonce_field')) {
		wp_nonce_field('family-tree-action_options');
	}
?>
	<h3>General settings</h3>
	<table class="form-table">
		<tr valign="top">
			<th scope="row"><label for="family_tree_category_key">Name of category for family members (default: "Family")</label></th>
			<td><input name="family_tree_category_key" type="text" id="family_tree_category_key" value="<?php echo wpft_options::get_option('family_tree_category_key'); ?>" size="40" /></td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="family_tree_link">Link to page with family tree</label></th>
			<td><input name="family_tree_link" type="text" id="family_tree_link" value="<?php echo wpft_options::get_option('family_tree_link'); ?>" size="40" /></td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="show_biodata_on_posts_page">Show biodata info on posts pages</label></th>
			<td><input name="show_biodata_on_posts_page" type="checkbox" id="show_biodata_on_posts_page" value="Y" <?php echo (wpft_options::get_option('show_biodata_on_posts_page')=='true')?' checked':''; ?> /></td>
		</tr>
	</table>
	<h3>Family tree options</h3>
	<table class="form-table">
		<tr valign="top">
			<th scope="row"><label for="bOneNamePerLine">Wrap names</label></th>
			<td><input name="bOneNamePerLine" type="checkbox" id="bOneNamePerLine" value="Y" <?php echo (wpft_options::get_option('bOneNamePerLine')=='true')?' checked':''; ?> /></td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="bOnlyFirstName">Only show first name</label></th>
			<td><input name="bOnlyFirstName" type="checkbox" id="bOnlyFirstName" value="Y" <?php echo (wpft_options::get_option('bOnlyFirstName')=='true')?' checked':''; ?> /></td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="bBirthAndDeathDates">Show living dates</label></th>
			<td><input name="bBirthAndDeathDates" type="checkbox" id="bBirthAndDeathDates" value="Y" <?php echo (wpft_options::get_option('bBirthAndDeathDates')=='true')?' checked':''; ?> /></td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="bConcealLivingDates">Conceal living dates for those alive</label></th>
			<td><input name="bConcealLivingDates" type="checkbox" id="bConcealLivingDates" value="Y" <?php echo (wpft_options::get_option('bConcealLivingDates')=='true')?' checked':''; ?> /></td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="bShowSpouse">Show spouse</label></th>
			<td><input name="bShowSpouse" type="checkbox" id="bShowSpouse" value="Y" <?php echo (wpft_options::get_option('bShowSpouse')=='true')?' checked':''; ?> /></td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="bShowOneSpouse">Show only one spouse</label></th>
			<td><input name="bShowOneSpouse" type="checkbox" id="bShowOneSpouse" value="Y" <?php echo (wpft_options::get_option('bShowOneSpouse')=='true')?' checked':''; ?> /></td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="bVerticalSpouses">Display spouses vertically</label></th>
			<td><input name="bVerticalSpouses" type="checkbox" id="bVerticalSpouses" value="Y" <?php echo (wpft_options::get_option('bVerticalSpouses')=='true')?' checked':''; ?> /></td>
		</tr>
<!--
		<tr valign="top">
			<th scope="row"><label for="bMaidenName">Maiden name</label></th>
			<td><input name="bMaidenName" type="checkbox" id="bMaidenName" value="Y" <?php 
			// echo (wpft_options::get_option('bMaidenName')=='true')?' checked':''; ?> /></td>
		</tr>
-->
		<tr valign="top">
			<th scope="row"><label for="bShowGender">Show gender</label></th>
			<td><input name="bShowGender" type="checkbox" id="bShowGender" value="Y" <?php echo (wpft_options::get_option('bShowGender')=='true')?' checked':''; ?> /></td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="bDiagonalConnections">Diagonal connections</label></th>
			<td><input name="bDiagonalConnections" type="checkbox" id="bDiagonalConnections" value="Y" <?php echo (wpft_options::get_option('bDiagonalConnections')=='true')?' checked':''; ?> /></td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="bRefocusOnClick">Refocus on click</label></th>
			<td><input name="bRefocusOnClick" type="checkbox" id="bRefocusOnClick" value="Y" <?php echo (wpft_options::get_option('bRefocusOnClick')=='true')?' checked':''; ?> /></td>
		</tr>
	</table>

	<h3>Node navigation toolbar</h3>
	Each node in the family tree can have a toolbar which can show a number of additional options. Here you can define how the toolbar should work.
	<table class="form-table">
		<tr valign="top">
			<th scope="row"><label for="bShowToolbar">Enable toolbar</label></th>
			<td><input name="bShowToolbar" type="checkbox" id="bShowToolbar" value="Y" <?php echo (wpft_options::get_option('bShowToolbar')=='true')?' checked':''; ?> /></td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="family_tree_toolbar_blogpage">Enable blogpage link <img src="<?php echo WPFAMILYTREE_URL; ?>open-book.png"></label></th>
			<td><input name="family_tree_toolbar_blogpage" type="checkbox" id="family_tree_toolbar_blogpage" value="Y" <?php echo (wpft_options::get_option('family_tree_toolbar_blogpage')=='Y')?' checked':''; ?> /></td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="family_tree_toolbar_treenav">Enable tree nav link <img src="<?php echo WPFAMILYTREE_URL; ?>tree.gif"></label></th>
			<td><input name="family_tree_toolbar_treenav" type="checkbox" id="family_tree_toolbar_treenav" value="Y" <?php echo (wpft_options::get_option('family_tree_toolbar_treenav')=='Y')?' checked':''; ?> /></td>
		</tr>
	</table>

	<h3>Family tree styling</h3>
	<table class="form-table">
		<tr valign="top">
			<th scope="row"><label for="canvasbgcol">Background colour (#rgb)</label></th>
			<td><input name="canvasbgcol" type="text" id="canvasbgcol" value="<?php echo wpft_options::get_option('canvasbgcol'); ?>" size="40" /></td>
		</tr>
	
		<tr valign="top">
			<th scope="row"><label for="nodeoutlinecol">Node outline colour (#rgb)</label></th>
			<td><input name="nodeoutlinecol" type="text" id="nodeoutlinecol" value="<?php echo wpft_options::get_option('nodeoutlinecol'); ?>" size="40" /></td>
		</tr>
	
		<tr valign="top">
			<th scope="row"><label for="nodefillcol">Node fill colour (#rgb)</label></th>
			<td><input name="nodefillcol" type="text" id="nodefillcol" value="<?php echo wpft_options::get_option('nodefillcol'); ?>" size="40" /></td>
		</tr>
	
		<tr valign="top">
			<th scope="row"><label for="nodefillopacity">Node opacity (0.0 to 1.0)</label></th>
			<td><input name="nodefillopacity" type="text" id="nodefillopacity" value="<?php echo wpft_options::get_option('nodefillopacity'); ?>" size="40" /></td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="nodetextcolour">Node text colour (#rgb)</label></th>
			<td><input name="nodetextcolour" type="text" id="nodetextcolour" value="<?php echo wpft_options::get_option('nodetextcolour'); ?>" size="40" /></td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="nodetextcolour">Node corner radius (pixels)</label></th>
			<td><input name="nodecornerradius" type="text" id="nodecornerradius" value="<?php echo wpft_options::get_option('nodecornerradius'); ?>" size="40" /></td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="nodeminwidth">Node minimum width (pixels)</label></th>
			<td><input name="nodeminwidth" type="text" id="nodeminwidth" value="<?php echo wpft_options::get_option('nodeminwidth'); ?>" size="40" /></td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="generationheight">Height of generations (pixels)</label></th>
			<td><input name="generationheight" type="text" id="generationheight" value="<?php echo wpft_options::get_option('generationheight'); ?>" size="40" /> * This parameter is useful if spouses are displayed vertically..</td>
		</tr>
	</table>
	
	<p class="submit">
	<input type="hidden" name="action" value="update" />
<!--
	<input type="hidden" name="page_options" value="family_tree_category_key"/>
-->
	<input type="submit" name="update_options" class="button" value="<?php _e('Save Changes', 'wpftr') ?> &raquo;" />
	</p>

	</form>
	</div>
<?php
}

class wpft_options {

	static function get_option($option) {
		$value = get_option($option);

		if ($value !== false) { 
			return $value;
		}
		// Option did not exist in database so return default values...
		switch ($option) {
		case "family_tree_link":
			return '/family-tree/';	// Default link to where the family tree sits
		case "family_tree_category_key":
			return 'Family';	// Default category for posts included in the tree
		case "show_biodata_on_posts_page":
			return 'true';	// Whether or not to show table with bio data on posts page
		case "canvasbgcol":
			return '#f3f3f3';		// Background colour for tree canvas
		case "nodeoutlinecol":
			return '#05c';		// Outline colour for nodes
		case "nodefillcol":
			return '#5cf';		// Fill colour for nodes
		case "nodefillopacity":
			return '0.4';		// Node opacity (0 to 1)
		case "nodetextcolour":
			return '#000';		// Node text colour
		case "family_tree_toolbar_enable":
			return 'Y';			// Show/enable toolbar
		case "family_tree_toolbar_blogpage":
			return 'Y';			// Toolbar button for navigating to the node's blog page
		case "family_tree_toolbar_treenav":
			return 'Y';			// Toolbar button for navigating to the node's tree

		case "bOneNamePerLine":		// Wrap names
			return 'true';
		case "bOnlyFirstName":	
			return 'false';
		case "bBirthAndDeathDates":	
			return 'true';
		case "bConcealLivingDates":	
			return 'true';
		case "bShowSpouse":	
			return 'true';
		case "bShowOneSpouse":	
			return 'false';
		case "bVerticalSpouses":	
			return 'true';
		case "bMaidenName":	
			return 'true';
		case "bShowGender":
			return 'true';
		case "bDiagonalConnections":
			return 'false';
		case "bRefocusOnClick":
			return 'false';
		case "bShowToolbar":
			return 'true';
		case "nodecornerradius":
			return '5';
		case "nodeminwidth":
			return '0';
		case "generationheight":	
			return '100';
		} 
		return '';
	}
	
	static function check_options() {
		$value = get_option('family_tree_link');
		if ($value === false) { 
			echo '<script language="javascript">alert("You need to configure the WP Family Tree plugin and set the \"family tree link\" parameter in the administrator panel.\n\nThis parameter will tell the family tree plugin which page is used to display the main family tree. I.e, the page where you have put the [family-tree] shortcode.\n\n");</script>';
		}
	}
		
}

?>