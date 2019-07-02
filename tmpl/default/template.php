<?php
/**
 * @version		0.3
 * @package		Responsive Simple Image Gallery
 * @author		Karolis Martinkus https://martinkus.eu/
 * @copyright	Copyright (c) 2016 Karolis Martinkus
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
$mansonryColWidth = ($thb_height==0) ? ($thb_width + 2 * $margin) : 1; //colum width if images are the same width (height=0 setting is used)
?>

<div id="rsigId<?php echo $gal_id; ?>" class="rsig-gallery<?php echo $extraWrapperClasses; ?>">
	<div class="rsig-sizer" style="width:<?php echo $mansonryColWidth;?>px;"></div>
	<?php foreach($gallery as $count=>$photo): ?>
	<figure class="rsig-item<?php echo $extraClasses; ?>" style="width:<?php echo $photo->thumb_width;?>px;" itemprop="associatedMedia" itemscope itemtype="http://schema.org/ImageObject">
		<a href="<?php echo $photo->sourceImageFilePath; ?>" data-size="<?php echo $photo->fullwidth; ?>x<?php echo $photo->fullheight; ?>" target="_blank"<?php echo $customLinkAttributes; ?>>
			<img class="rsig-img" src="<?php echo $photo->thumbImageFilePath; ?>" data-caption="<?php echo $photo->caption; ?>" />
		</a>
		<?php if(!empty($photo->caption)): ?>
			<figcaption itemprop="caption description"><?php echo $photo->caption; ?></figcaption>
		<?php endif ?>
	</figure>
	<?php endforeach; ?>
</div>