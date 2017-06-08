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

jimport('joomla.plugin.plugin');
jimport('joomla.html.parameter');

class plgContentRSIG extends JPlugin {

  // Reference parameters
	var $plg_name					= "rsig";
	var $plg_tag					= "gallery";

	function onContentPrepare($context, &$row, &$params, $page = 0){
		// Define the DS constant under Joomla! 3.0+
		if (!defined('DS')){
			define('DS', DIRECTORY_SEPARATOR);
		}
		$this->renderGallery($row, $params, $page = 0);
		
	}
	
	// Main function
	function renderGallery(&$row, &$params, $page = 0){

		// API
		jimport('joomla.filesystem.file');
		$mainframe = JFactory::getApplication();
		$document  = JFactory::getDocument();

		// Assign paths
		$sitePath = JPATH_SITE;
		$siteUrl  = JURI::root(true);
		$pluginLivePath = $siteUrl.'/plugins/content/'.$this->plg_name;
		$defaultImagePath = 'images';

		// Check if plugin is enabled
		if (JPluginHelper::isEnabled('content', $this->plg_name) == false) return;

		// Bail out if the page format is not what we want
		$allowedFormats = array('', 'html', 'feed', 'json');
		if (!in_array(JRequest::getCmd('format'), $allowedFormats)) return;

		// Simple performance check to determine whether plugin should process further
		if (JString::strpos($row->text, $this->plg_tag) === false) return;

		// expression to search for
		$regex = "#{".$this->plg_tag."}(.*?){/".$this->plg_tag."}#is";

		// Find all instances of the plugin and put them in $matches
		preg_match_all($regex, $row->text, $matches);

		// Number of plugins
		$count = count($matches[0]);

		// Plugin only processes if there are any instances of the plugin in the text
		if (!$count) return;

		// Load the plugin language file the proper way
		JPlugin::loadLanguage('plg_content_'.$this->plg_name, JPATH_ADMINISTRATOR);

		// Check for basic requirements
		if (!extension_loaded('gd') && !function_exists('gd_info')){
			JError::raiseNotice('', JText::_('RSIG_NOTICE_01'));
			return;
		}
		if (!is_writable($sitePath.DS.'cache')){
			JError::raiseNotice('', JText::_('RSIG_NOTICE_02'));
			return;
		}


		// ----------------------------------- Get plugin parameters -----------------------------------

		// Get plugin info
		$plugin = JPluginHelper::getPlugin('content', $this->plg_name);

		// Control external parameters and set variable for controlling plugin layout within modules
		if (!$params) $params = class_exists('JParameter') ? new JParameter(null) : new JRegistry(null);
		$parsedInModule = $params->get('parsedInModule');

		$pluginParams = class_exists('JParameter') ? new JParameter($plugin->params) : new JRegistry($plugin->params);

		$galleries_rootfolder = ($params->get('galleries_rootfolder')) ? $params->get('galleries_rootfolder') : $pluginParams->get('galleries_rootfolder', $defaultImagePath);
		
		// Add masonry engine if using Masonry layout
		if ($pluginParams->get('layout', 'flow')=='masonry'){
			$engines = array('masonry', 'photoswipe');
		}
		else {
			$engines = array('photoswipe');
		}
		$static_thb = $pluginParams->get('STATIC_THB', 0);
		$thb_width = (!is_null($params->get('thb_width', null))) ? $params->get('thb_width') : $pluginParams->get('thb_width', 200);
		$thb_height = (!is_null($params->get('thb_height', null))) ? $params->get('thb_height') : $pluginParams->get('thb_height', 200);
		$crop = 0;
		$margin = $pluginParams->get('margin', 5);
		$pixel_density = $pluginParams->get('pixel_density', 1);
		$load_cdn = $pluginParams->get('load_cdn', 1);
		$overlay_captions = $pluginParams->get('overlay_captions', 0);
		$show_captions = 1;
		// Advanced
		$jpg_quality = $pluginParams->get('jpg_quality', 85);
		$cache_expire_time = $pluginParams->get('cache_expire_time', 1440) * 60; // Cache expiration time in minutes
		$memoryLimit = (int)$pluginParams->get('memoryLimit');
		$photoswipe_options = (stripos($pluginParams->get('photoswipe_options', 'shareEl: false'), ':')) ? $pluginParams->get('photoswipe_options', 'shareEl: false').',' : '' ; // Simplistic check to not brake js, propably should be improved
		if ($memoryLimit) ini_set("memory_limit", $memoryLimit."M");
		// Set different template and widths according to colum values if using Grid layout
		if ($pluginParams->get('layout', 'flow')=='grid'){
			$thb_template = 'grid';
			// Convert number of columns to appropriate widths
			$col_xs_width= 100/$pluginParams->get('columns_xs', 1) - 2*$margin;
			$col_sm_width= 100/$pluginParams->get('columns_sm', 3) - ceil(2*$margin/768);// margins of adjacent flex items do not collapse.
			$col_md_width= 100/$pluginParams->get('columns_md', 3) - ceil(2*$margin/992);
			$col_lg_width= 100/$pluginParams->get('columns_lg', 4) - ceil(2*$margin/1200);
			$document->addStyleDeclaration("@media (max-width: 767px) {.rsig-item {width: {$col_xs_width}%;}}");
			$document->addStyleDeclaration("@media (min-width: 768px) {.rsig-item {width: {$col_sm_width}%;}}");
			$document->addStyleDeclaration("@media (min-width: 992px) {.rsig-item {width: {$col_md_width}%;}}");
			$document->addStyleDeclaration("@media (min-width: 1200px) {.rsig-item {width: {$col_lg_width}%;}}");
		}
		else{
			$thb_template = 'default';
		}
		
		// Cleanups
		// Remove first and last slash if they exist
		if (substr($galleries_rootfolder, 0, 1) == '/') $galleries_rootfolder = substr($galleries_rootfolder, 1);
		if (substr($galleries_rootfolder, -1, 1) == '/') $galleries_rootfolder = substr($galleries_rootfolder, 0, -1);

		// Include image pharsing file
		require_once (dirname(__FILE__).DS.'images.php');

		// ----------------------------------- Prepare the output -----------------------------------

		// Process plugin tags
		if (preg_match_all($regex, $row->text, $matches, PREG_PATTERN_ORDER) > 0){

			// start the replace loop
			foreach ($matches[0] as $key => $match){

				$tagcontent = preg_replace("/{.+?}/", "", $match);

				if(strpos($tagcontent,':')!==false){
					$tagparams 			= explode(':',$tagcontent);
					$galleryFolder 	= $tagparams[0];
				} else {
					$galleryFolder 	= $tagcontent;
				}

				// Gallery folder and id
				$srcimgfolder = $galleries_rootfolder.'/'.$galleryFolder;
				$gal_id = substr(md5($key.$srcimgfolder), 1, 10);

				// Render the gallery
				$gallery = rsigImageGallery::renderGallery($srcimgfolder, $static_thb, $thb_width, $thb_height, $crop, $pixel_density, $jpg_quality, $cache_expire_time, $gal_id, $show_captions);

				if (!$gallery){
					JError::raiseNotice('', JText::_('RSIG_NOTICE_03').' '.$srcimgfolder);
					continue;
				}
				$enginehtml= '';
				// CSS & JS includes: Append head includes, but not when we're outputing raw content
				if (JRequest::getCmd('format') == '' || JRequest::getCmd('format') == 'html'){
					// Includes jquery if not already included
					if (version_compare(JVERSION, '3.0', 'ge')==false) {
						if(JFactory::getApplication()->get('jquery') !== true) {
							$document->addScript('//ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js');
							JFactory::getApplication()->set('jquery', true);
						}
					} else {
						JHtml::_('jquery.framework');
					}
					//Pharse js plugins like photoswipe and masonry
					foreach ($engines as $engine){
						// Initiate variables
						$relName = '';
						$extraClass = '';
						$extraWrapperClass = '';
						$legacyHeadIncludes = '';
						$customLinkAttribute = '';
						if(!isset($extraClasses))
							$extraClasses='';
						
						if (!isset($extraWrapperClasses))
							$extraWrapperClasses ='';

						if (!isset($customLinkAttributes))
							$customLinkAttributes = '';

						$enginePath = "{$pluginLivePath}/engines/{$engine}";
						$engineRequire = dirname(__FILE__).DS.'engines'.DS.$engine.DS.'engine.php';

						if (file_exists($engineRequire) && is_readable($engineRequire)){
							require ($engineRequire);
						}
						else{
							echo $engine.' engine file not found. ';
						}

						if (isset($stylesheets)){
							foreach ($stylesheets as $stylesheet){
								if (substr($stylesheet, 0, 4) == 'http' || substr($stylesheet, 0, 2) == '//'){
									$document->addStyleSheet($stylesheet);
								} else {
									$document->addStyleSheet($enginePath.'/'.$stylesheet);
								}
							}
						}
						if (isset($stylesheetDeclarations))
							foreach ($stylesheetDeclarations as $stylesheetDeclaration)
								$document->addStyleDeclaration($stylesheetDeclaration);

						if (isset($scripts)){
							foreach ($scripts as $script){
								if (substr($script, 0, 4) == 'http' || substr($script, 0, 2) == '//'){
									$document->addScript($script);
								} else {
									$document->addScript($enginePath.'/'.$script);
								}
							}
						}
						if (isset($scriptDeclarations))
							foreach ($scriptDeclarations as $scriptDeclaration)
								$document->addScriptDeclaration($scriptDeclaration);

						if ($legacyHeadIncludes)
							$document->addCustomTag($legacyHeadIncludes);

						if ($extraClass)
							$extraClasses .= ' '.$extraClass;

						if ($extraWrapperClass)
							$extraWrapperClasses .= ' '.$extraWrapperClass;

						if ($customLinkAttributes)
							$customLinkAttributes .= ' '.$customLinkAttribute;
					}
					$pluginCSS = $this->getTemplatePath($this->plg_name, 'css/template.css', $thb_template);
					$pluginCSS = $pluginCSS->http;
					$document->addStyleSheet($pluginCSS, 'text/css', 'screen');
					$document->addStyleDeclaration('.rsig-item {margin: '.$margin.'px}');
					// Hide overlay captions
					if (!$overlay_captions){
						$document->addStyleDeclaration('.rsig-gallery figure figcaption {display: none !important;}');
					}
				}

				// Fetch the template
				ob_start();
				$templatePath = $this->getTemplatePath($this->plg_name, 'template.php', $thb_template);
				$templatePath = $templatePath->file;
				include ($templatePath);
				$getTemplate = ob_get_contents().$enginehtml;
				ob_end_clean();

				// Output
				$plg_html = $getTemplate;

				// Do the replace
				$row->text = preg_replace("#{".$this->plg_tag."}".$tagcontent."{/".$this->plg_tag."}#s", $plg_html, $row->text);

			}// end the replace loop

		} // end if

	} // end of main function
	

	// Template overides
	static function getTemplatePath($pluginName, $file, $tmpl)
	{

		$mainframe = JFactory::getApplication();
		$p = new JObject;
		$pluginGroup = 'content';

		$jTemplate = $mainframe->getTemplate();

		if($mainframe->isAdmin()){
			$db = JFactory::getDBO();
			$query = "SELECT template FROM #__template_styles WHERE client_id = 0 AND home = 1";
			$db->setQuery($query);
			$jTemplate = $db->loadResult();
		}

		if (file_exists(JPATH_SITE.DS.'templates'.DS.$jTemplate.DS.'html'.DS.$pluginName.DS.$tmpl.DS.str_replace('/', DS, $file)))
		{
			$p->file = JPATH_SITE.DS.'templates'.DS.$jTemplate.DS.'html'.DS.$pluginName.DS.$tmpl.DS.$file;
			$p->http = JURI::root(true)."/templates/".$jTemplate."/html/{$pluginName}/{$tmpl}/{$file}";
		}
		else
		{
			$p->file = JPATH_SITE.DS.'plugins'.DS.$pluginGroup.DS.$pluginName.DS.'tmpl'.DS.$tmpl.DS.$file;
			$p->http = JURI::root(true)."/plugins/{$pluginGroup}/{$pluginName}/tmpl/{$tmpl}/{$file}";

		}
		return $p;
	}
	
} // end of class
