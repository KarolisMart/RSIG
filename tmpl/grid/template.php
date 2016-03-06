<?php
/**
 * @version		0.1
 * @package		Responsive Simple Image Gallery
 * @author		Karolis Martinkus https://martinkus.eu/
 * @copyright	Copyright (c) 2016 Karolis Martinkus
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

?>

<div id="rsigId<?php echo $gal_id; ?>" class="rsig-gallery<?php echo $extraWrapperClasses; ?>">
	<?php foreach($gallery as $count=>$photo): ?>
	<a href="<?php echo $photo->sourceImageFilePath; ?>" class="rsig-item<?php echo $extraClasses; ?>" data-size="<?php echo $photo->fullwidth; ?>x<?php echo $photo->fullheight; ?>"  target="_blank"<?php echo $customLinkAttributes; ?>>
		<img class="rsig-img" src="<?php echo $photo->thumbImageFilePath; ?>" />
	</a>
	<?php endforeach; ?>
</div>