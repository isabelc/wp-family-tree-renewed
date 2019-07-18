=== WP Family Tree Renewed ===
Contributors: isabel104
Requires at least: 3.7
Tested up to: 5.3
Requires PHP: 7.0
Stable tag: 2.2
License: GNU GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

WP Family Tree Renewed is a family tree generator for WordPress.

== Changelog ==

= 2.2 =

* New - Requires PHP 7.0 or higher.
* New - Show images on family tree. Images for each person are now displayed by default, instead of the previous way, which was only on hover.
* Fix - Fixed opacity on hover over the tree nodes. Previously, the opacity only increased if you hovered over a blank spot on the node. It did not work if you hovered over any text in the node.
* Fix - Removed several PHP error notices.
* Tweak - Show a blank instead of NONE for Children or Siblings biodata in case children are siblings do exists but are not listed yet because they are alive. NONE is wrong in those cases.

= 2.1 =

* Fix - Removed some PHP warnings.

= 1.1.3 =

* Release date: April 13, 2015
* New - Family tree diagrams can now be dragged on touch or mobile devices.
* New - Mobile repsonsive styles for family bio data on family member pages and family directory list.
* Tweak - Use wp_enqueue_scripts to load scripts and styles.

= 1.1.2 =

* Tweak - Rework the family_tree_update_post function to not accidentally delete meta on update.

= 1.1 =

* Fix - Removed PHP Error notices which appeared while editing posts without Family Tree meta.
* Fix - Removed deprecated wp_specialchars.

= 1.0.9 =

* Fix: Family tree post meta was not able to be erased.
* New - Show the spouse name and link with the other bio data for each member. Also added the spouse microdata item property.
* New - In addition to spouse, show Partners with the bio data. Partners are people who the person has common children with.
* New - The family list is now in alphabetical order by first name.

= 1.0.8 =

* Fix - Removed PHP error notice for deprecated use of User Levels instead of capabilites.
* Fix - Removed several other PHP warnings.

= 1.0.7 =

* New - Added schema.org microdata for person to the single family member pages, and on the family members directory list page. Person properties include name, birth date, death date, parent, children, sibling, and image.
* Tweak - Added spaces between names and dates in tables for better description snippets in search results.
