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

class rsigImageGallery {

	public static function renderGallery($srcimgfolder, $static_thb, $thb_width, $thb_height, $crop, $pixel_density, $jpg_quality, $cache_expire_time, $gal_id, $show_captions)
	{

		// API
		jimport('joomla.filesystem.folder');

		// Path assignment
		$sitePath = JPATH_SITE.'/';
		if(JRequest::getCmd('format')=='feed')
		{
			$siteUrl = JURI::root(true).'';
		}
		else
		{
			$siteUrl = JURI::root(true).'/';
		}

		// Internal parameters
		$prefix = "rsig_cache_";


		// Check if the source folder exists and read it
		$srcFolder = JFolder::files($sitePath.$srcimgfolder);

		// Proceed if the folder is OK or fail silently
		if (!$srcFolder)
			return;

		// Set the cache folder, use thumbnails folder if static thumbnails are set to be used
		if ($static_thb){
			$cacheFolderPath = $srcimgfolder.DS.'thumbnails';
		}
		else {
			$cacheFolderPath = 'cache'.DS.'rsig';
		}
		if (file_exists($sitePath.$cacheFolderPath) && is_dir($sitePath.$cacheFolderPath))
		{
			// all OK
		}
		else
		{
			mkdir($sitePath.$cacheFolderPath);
		}
		
		// Loop through the source folder for images
		$fileTypes = array('jpg', 'jpeg', 'gif', 'png');
		// Create an array of file types
		$found = array();
		// Create an array for matching files
		foreach ($srcFolder as $srcImage)
		{
			$fileInfo = pathinfo($srcImage);
			if (array_key_exists('extension', $fileInfo) && in_array(strtolower($fileInfo['extension']), $fileTypes))
			{
				$found[] = $srcImage;
			}
		}

		// Bail out if there are no images found
		if (count($found) == 0)
			return;
		
		// Get captions
		$captions[] = false;
		if( $show_captions ){
			$langTag = JFactory::getLanguage()->getTag();
			$langCaptionFile = $srcimgfolder.'/'.$langTag.'.txt';
			$defaultCaptionFile = $srcimgfolder.'/captions.txt';
			if (file_exists($langCaptionFile)){
				$captionFile = array_map('trim', file($langCaptionFile));
				foreach($captionFile as $line)
				{
					if(!empty($line))
					{
						$lineSplit = explode('|', $line);
						$captions[$lineSplit[0]] = $lineSplit[1];
					}
				}
			}
			elseif (file_exists($defaultCaptionFile)){
				$captionFile = array_map('trim', file($defaultCaptionFile));
				foreach($captionFile as $line)
				{
					if(!empty($line))
					{
						$lineSplit = explode('|', $line);
						$captions[$lineSplit[0]] = $lineSplit[1];
					}
				}
			}
		}

		// Sort array
		sort($found);

		// Initiate array to hold gallery
		$gallery = array();

		// Loop through the image file list
		foreach ($found as $key => $filename)
		{

			// Determine thumb image filename
			if (strtolower(substr($filename, -4, 4)) == 'jpeg')
			{
				$thumbfilename = substr($filename, 0, -4).'jpg';
			}
			elseif (strtolower(substr($filename, -3, 3)) == 'gif' || strtolower(substr($filename, -3, 3)) == 'png' || strtolower(substr($filename, -3, 3)) == 'jpg')
			{
				$thumbfilename = substr($filename, 0, -3).'jpg';
			}

			// Object to hold each image elements
			$gallery[$key] = new JObject;

			// Assign source image and path to a variable
			$original = $sitePath.str_replace('/', DS, $srcimgfolder).DS.$filename;

			// Check if thumb image exists already
			if ($static_thb){
				$thumbimage = $cacheFolderPath.DS.$thumbfilename;
			}
			else{
				$thumbimage = $cacheFolderPath.DS.$prefix.$gal_id.'_'.strtolower(static::cleanThumbName($thumbfilename));
			}
			
			if (file_exists($thumbimage) && is_readable($thumbimage) && (((filemtime($thumbimage) + $cache_expire_time) > time()) || $static_thb))
			{
				// get width and height
				list($width, $height, $type) = getimagesize($original);
				list($thumb_width, $thumb_height) = getimagesize($thumbimage);
			}
			else
			{
				// Otherwise create the thumb image

				// begin by getting the details of the original
				list($width, $height, $type) = getimagesize($original);

				// create an image resource for the original
				switch($type)
				{
					case 1 :
						$source = @ imagecreatefromgif($original);
						if (!$source)
						{
							JError::raiseNotice('', JText::_('RSIG_ERROR_GIFS'));
							return;
						}
						break;
					case 2 :
						$source = imagecreatefromjpeg($original);
						break;
					case 3 :
						$source = imagecreatefrompng($original);
						break;
					default :
						$source = NULL;
				}

				// Bail out if the image resource is not OK
				if (!$source)
				{
					JError::raiseNotice('', JText::_('RSIG_ERROR_SRC_IMGS'));
					return;
				}

				// calculate thumbnails
				$thumbnail = static::thumbDimCalc($width, $height, $thb_width, $thb_height, $crop);

				$thumb_width = $thumbnail['width'] * $pixel_density; //width ajusted for pixel density setting
				$thumb_height = $thumbnail['height'] * $pixel_density; //height ajusted for pixel density setting
				
				
				// create an image resource for the thumbnail
				$thumb = imagecreatetruecolor($thumb_width, $thumb_height);

				// create the resized copy
				imagecopyresampled($thumb, $source, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height);

				// convert and save all thumbs to .jpg
				$success = imagejpeg($thumb, $thumbimage, $jpg_quality);

				// Bail out if there is a problem in the GD conversion
				if (!$success)
					return;

				// remove the image resources from memory
				imagedestroy($source);
				imagedestroy($thumb);

			}

			// Assemble the image elements
			$gallery[$key]->filename = $filename;
			$gallery[$key]->caption = array_key_exists ( $filename, $captions ) ? $captions[$filename] : "";
			$gallery[$key]->sourceImageFilePath = $siteUrl.$srcimgfolder.'/'.static::replaceWhiteSpace($filename);
			$gallery[$key]->thumbImageFilePath = $thumbimage; //$siteUrl.'cache/rsig/'.$prefix.$gal_id.'_'.strtolower(static::cleanThumbName($thumbfilename));
			$gallery[$key]->width = $thb_width;
			$gallery[$key]->height = $thb_height;
			$gallery[$key]->fullwidth = $width;
			$gallery[$key]->fullheight = $height;
			$gallery[$key]->thumb_width = $thumb_width / $pixel_density; // ajusted for pixel density setting, we want width that will be used for image displaying, not renderig
			$gallery[$key]->thumb_height = $thumb_height / $pixel_density; // ajusted for pixel density setting, we want height that will be used for image displaying, not renderig
			

		}// foreach loop

		// OUTPUT
		return $gallery;

	}

	// Calculate thumbnail dimensions
	public static function thumbDimCalc($width, $height, $thb_width, $thb_height, $crop)
	{
		if ($thb_width==0){
			// Force fit height
			$thumb_height = $thb_height;
			$thumb_width = floor($width * $thb_height / $height);
		}
		elseif($thb_height==0){
			// Force fit width
			$thumb_width = $thb_width;
			$thumb_height = floor($height * $thb_width / $width);
		}
		else {
		$ratio_thumb = $thb_width/$thb_height;
		$ratio_orig = $width/$height;
			if($crop){
				// Crop image to fit dimensions, feature currently unimplemented
			}
			else {
				// Resize with fitting larger dimension, do not crop image
				if ($ratio_thumb > $ratio_orig)
				{
					//Fit height
					$thumb_height = $thb_height;
					$thumb_width = floor($width * $thb_height / $height);
				}
				else
				{
					// Fit width
					$thumb_width = $thb_width;
					$thumb_height = floor($height * $thb_width / $width);
				}
			}
		}
		$thumbnail = array();
		$thumbnail['width'] = $thumb_width;
		$thumbnail['height'] = $thumb_height;

		return $thumbnail;

	}

	// Replace white space
	static function replaceWhiteSpace($text_to_parse)
	{
		$source_html = array(" ");
		$replacement_html = array("%20");
		return str_replace($source_html, $replacement_html, $text_to_parse);
	}

	// Cleanup thumbnail filenames
	static function cleanThumbName($text_to_parse)
	{
		$source_html = array(' ', ',');
		$replacement_html = array('_', '_');
		return str_replace($source_html, $replacement_html, $text_to_parse);
	}

} // End class
