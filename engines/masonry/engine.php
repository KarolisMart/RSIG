<?php
/**
 * @version		0.3
 * @package		Responsive Simple Image Gallery
 * @author		Karolis Martinkus https://martinkus.eu/
 * @copyright	Copyright (c) 2016 Karolis Martinkus
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$relName = '';
$extraClass = 'grid-item';
$extraWrapperClass='grid';

$stylesheets = array();
$stylesheetDeclarations = array('.grid{margin: 0 auto}');
$scripts = $load_cdn ? array('https://npmcdn.com/masonry-layout@4.0/dist/masonry.pkgd.min.js') : array('masonry.pkgd.min.js');

$mansonryColWidth = ($thb_height==0) ? ($thb_width + 2 * $margin) : 1; //colum width if images are the same width (height=0 setting is used)

if(!defined('MASONRY_LOADED')){
	define('MASONRY_LOADED', true);
	$scriptDeclarations = array("
		jQuery(document).ready(function() {
			jQuery('.grid').masonry({
				  // options
				  itemSelector: '.grid-item',
				  columnWidth: {$mansonryColWidth},
				  isFitWidth: true
				});
		});
	");
	$enginehtml ='';
} else {
	$scriptDeclarations = array();
}
