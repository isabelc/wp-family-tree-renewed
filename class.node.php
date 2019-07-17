<?php
class node {
	var $post_id;
	var $gender;
	var $spouse;
	var $partners;
	var $father;
	var $mother;
	var $born;
	var $died;
	var $thumbsrc;
	var $thumbhtml;

	var $children;
	var $siblings;
	
	var $name;
	var $name_father;
	var $name_mother;
	var $url;
	var $url_father;
	var $url_mother;
	var $name_spouse;
	var $url_spouse;

	function __construct() {
		$children = array();
	}

	static function get_node($post_detail) {
		$fm = new node();
		$fm->post_id 	= $post_detail->ID;
		$fm->name 		= $post_detail->post_title;
		$fm->url		= get_permalink($post_detail->ID);			
		$fm->gender	= get_post_meta($post_detail->ID, 'gender', true);
		$fm->father	= get_post_meta($post_detail->ID, 'father', true);
		$fm->mother	= get_post_meta($post_detail->ID, 'mother', true);
		$fm->spouse	= get_post_meta($post_detail->ID, 'spouse', true);
		$fm->born	= get_post_meta($post_detail->ID, 'born', true);
		$fm->died	= get_post_meta($post_detail->ID, 'died', true);
		if (function_exists('get_post_thumbnail_id')) {
			$thumbid = get_post_thumbnail_id($post_detail->ID);
			$thumbsrc = wp_get_attachment_image_src($thumbid, 'thumbnail');
			$fm->thumbsrc = $thumbsrc[0];
			$fm->thumbhtml = get_the_post_thumbnail($post_detail->ID, 'thumbnail',array('itemprop' => 'image'));
		}
		return $fm;
	}
	function get_html($the_family) {

		$html = '<table border="0" width="100%" itemscope itemtype="https://schema.org/Person">';
		$html .= '<tr><td width="150" style="vertical-align:bottom"><b><a href="'.$this->url.'">';
		if (!empty($this->thumbhtml)) {
			$html .= "<br>".$this->thumbhtml;
		}
		$html .= '<span itemprop="name">'.$this->name.'</span></a></b> </td>';
		$html .= '<td width="80" style="vertical-align:bottom">';
		$plugloc = plugin_dir_url( __FILE__ );
//		$html .= ($this->gender == 'm') ? 'Male' : 'Female';
		if ($this->gender == 'm') {
			$html .= '<img alt="Male" title="Male" src="'.$plugloc.'icon-male-small.gif"/>';
		} else if ($this->gender == 'f') {
			$html .= '<img alt="Female" title="Female" src="'.$plugloc.'icon-female-small.gif"/>';
		} else {
			$html .= '<img alt="Gender not specified" title="Gender not specified" src="'.$plugloc.'icon-qm-small.gif"/>';
		}
//		$html .= ($this->gender == 'm') ? 'Male' : 'Female';
		
		$ftlink = wpft_options::get_option('family_tree_link');
		if (strpos($ftlink, '?') === false) {
			$html .=' <a href="'.$ftlink.'?ancestor='.$this->post_id.'"><img border="0" alt="View tree" title="View tree" src="'.$plugloc.'icon-tree-small.gif"/></a>';
		} else {
			$html .=' <a href="'.$ftlink.'&ancestor='.$this->post_id.'"><img border="0" alt="View tree" title="View tree" src="'.$plugloc.'icon-tree-small.gif"/></a>';
		}
		
		$html .= '</td>';
		$html .= '<td style="vertical-align:bottom">Born: <span itemprop="birthDate">'.$this->born.' </span> </td>';
		if (!empty($this->died) && strlen($this->died) > 1) {
			$html .= '<td style="vertical-align:bottom">Died: <span itemprop="deathDate">'. $this->died.' </span> </td>';
		} else {
			$html .= '<td></td>';	
		}
		$html .= '</tr>';
		$html .= '<tr><td colspan="2">Father: ';
		if (isset($this->name_father)) {
			$html .= '<span itemprop="parent" itemscope="" itemtype="https://schema.org/Person"><a href="'.$this->url_father.'" itemprop="sameAs"><span itemptop="name">'.$this->name_father.'</span></a></span> ';
		} else {
			$html .= 'Unspecified ';
		}
		$html .= '</td>';	
		$html .= '<td colspan="2">Mother: ';
		if (isset($this->name_mother)) {
			$html .= '<span itemprop="parent" itemscope="" itemtype="https://schema.org/Person"><a href="'.$this->url_mother.'" itemprop="sameAs"><span itemptop="name">'.$this->name_mother.'</span></a></span> ';
		} else {
			$html .= 'Unspecified ';
		}
		$html .= '</td></tr>';
		$html .= '<tr><td colspan="4">Children: ';
		if (! empty($this->children) && (count($this->children) > 0)) {
			$first = true; 
			foreach ($this->children as $child) {
				if (!$first) {
					$html .= ', ';
				} else {
					$first = false;
				}
				$html .= '<span itemprop="children" itemscope="" itemtype="https://schema.org/Person"><a href="'.$the_family[$child]->url.'" itemprop="children" itemprop="sameAs"><span itemptop="name">'.$the_family[$child]->name.'</span></a></span> ';
			}
		} else {
			$html .= ' ';
		}
		$html .= '</td></tr>';
		$html .= '<tr><td colspan="4">Siblings: ';
		if (count($this->siblings) > 0) {
			$first = true; 
			foreach ($this->siblings as $sibling) {
				if (!$first) {
					$html .= ', ';
				} else {
					$first = false;
				}
				$html .= '<a href="'.$the_family[$sibling]->url.'" itemprop="sibling">'.$the_family[$sibling]->name.'</a>';
			}
		} else {
			$html .= ' ';
		}
		$html .= '</td></tr><tr><td colspan="4">Spouse: ';

		if (isset($this->name_spouse)) {
			$html .= '<span itemprop="spouse" itemscope="" itemtype="https://schema.org/Person"><a href="' . $this->url_spouse . '" itemprop="sameAs"><span itemptop="name">' . $this->name_spouse . '</span></a> </span> ';
		} else {
			$html .= 'Unspecified ';
		}


		$html .= '</td></tr>';

		// other partners - people you've had children with

		if ( is_array( $this->partners ) ) {
			// remove the one official spouse from partners array 
			$temp = array();
			$temp[] = $this->spouse;
			$other_partners = array_diff( $this->partners, $temp );
		}

		if ( count( $other_partners ) > 0 ) {
			$html .= '<tr><td colspan="4">Partners: ';
			$first = true; 
			foreach ( $other_partners as $partner ) {
				if ( !$first ) {
					$html .= ', ';
				} else {
					$first = false;
				}
				if ( isset( $the_family[$partner] ) ) {
					$html .= '<a href="'.$the_family[$partner]->url.'">'.$the_family[$partner]->name.'</a>';
				}

			}
			$html .= '</td></tr>';
		}

		$html .= '</table>';
		return $html;
	}
	function get_toolbar_div() {
		$plugloc = plugin_dir_url( __FILE__ );
		$ftlink = wpft_options::get_option('family_tree_link');

		if (strpos($ftlink, '?') === false) {
			$ftlink = $ftlink.'?ancestor='.$this->post_id;
		} else {
			$ftlink = $ftlink.'&ancestor='.$this->post_id;
		}
		$permalink = get_permalink($this->post_id);
		$html = '';

		if (wpft_options::get_option('bShowToolbar') == 'true') {
			$html .= '<div class="toolbar" id="toolbar'.$this->post_id.'">';
			if (wpft_options::get_option('family_tree_toolbar_blogpage') == 'Y') {
				$html .= '<a class="toolbar-blogpage" href="'.$permalink.'" title="View information about '.htmlspecialchars($this->name).'"><img border="0" class="toolbar-blogpage" src="'.$plugloc.'open-book.png"></a>';
			}
			if (wpft_options::get_option('family_tree_toolbar_treenav') == 'Y') {
				$html .= '<a class="toolbar-treenav" href="'.$ftlink.'" title="View the family of '.htmlspecialchars($this->name).'"><img border="0" class="toolbar-treenav" src="'.$plugloc.'tree.gif"></a>';
			}
			if (!empty($this->thumbsrc)) {

				// @todo remove this gif
				
				$html .= '<img border="0" class="toolbar-treenav" src="'.$plugloc.'camera.gif">';
			}
			$html .= '</div>';
		}
		return $html;
	}

	// @todo inspect: 

	function get_thumbnail_div() {
		$html = '';


		/****************************************************
		* @todo
		* the following div is getting style:
		
		visibility: hidden; left: 127px; top: 293px;

		****************************************************/
		



		$html .= '<div class="wpft_thumbnail" id="thumbnail'.$this->post_id.'">';



//		$html .= 'Thumbnail-'.$this->post_id;
		if (!empty($this->thumbsrc)) {
			$html .= '<img src="'.$this->thumbsrc.'">';
		}
		$html .= '</div>';
		return $html;
	}


	function get_box_html($the_family) {
		$html = '';
		$html .= '<a href="'.$this->url.'">'.$this->name.'</a>';
		$html .= '<br>Born: '.$this->born;
		if (!empty($this->died) && strlen($this->died) > 1) {
			$html .= '<br>Died: '.	$this->died;	
		}
		return $html;
	}
}
