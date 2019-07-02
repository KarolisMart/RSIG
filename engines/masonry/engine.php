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
$scripts = $load_cdn ? array('https://unpkg.com/masonry-layout@4/dist/masonry.pkgd.min.js') : array('masonry.pkgd.min.js');

if(!defined('MASONRY_LOADED')){
	define('MASONRY_LOADED', true);
	$scriptDeclarations = array("
		jQuery(window).load(function() {
			jQuery('.grid').masonry({
				itemSelector: '.grid-item',
				columnWidth: '.rsig-sizer'
				});
		});
	");
	$enginehtml ='';
} else {
	$scriptDeclarations = array();
}
