<?php
/**
 * @version		3.0.1
 * @package		Simple Image Gallery (plugin)
 * @author    	JoomlaWorks - http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2014 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

?>

<div id="rsigId<?php echo $gal_id; ?>" class="rsig-gallery<?php echo $extraWrapperClasses; ?>">
	<?php foreach($gallery as $count=>$photo): ?>
	<a href="<?php echo $photo->sourceImageFilePath; ?>" class="rsig-item<?php echo $extraClasses; ?>" data-size="<?php echo $photo->fullwidth; ?>x<?php echo $photo->fullheight; ?>"  rel="<?php echo $relName; ?>[gallery<?php echo $gal_id; ?>]" title="<?php echo $photo->filename; ?>" target="_blank"<?php echo $customLinkAttributes; ?>>
		<img class="rsig-img" src="<?php echo $photo->thumbImageFilePath; ?>" style="width:<?php echo $photo->thumb_width;?>px;height:<?php echo $photo->thumb_height; ?>px;"/>
	</a>
	<?php endforeach; ?>
</div>