<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
class PlgSystemChronoengine_Gcore2 extends JPlugin{
	var $output = '';
	
	public function onAfterRoute(){
		$app = JFactory::getApplication();
		
		if($app->isAdmin()){
			defined('GCORE_SITE') or define('GCORE_SITE', 'admin');
		}else{
			defined('GCORE_SITE') or define('GCORE_SITE', 'front');
		}
		
		jimport('cegcore2.joomla_gcloader');
		if(!class_exists('JoomlaGCLoader2')){
			JError::raiseWarning(100, 'Please download the CEGCore2 framework from www.chronoengine.com then install it under Extensions > Install under the Joomla admin area.');
			return;
		}
	}
	
	public function onAfterDispatch(){
		$app = JFactory::getApplication();
		$doc = JFactory::getDocument();
		$buffer = $doc->getBuffer('component');
		
		if(!$app->isAdmin()){
			//check director filters
			//$c = new \G2\E\Chronodirector\C\Manager();
			$director = JPATH_SITE.DS.'components'.DS.'com_chronodirector'.DS.'chronodirector.php';
			if(file_exists($director)){
				require($director);
				
			}
			//match shortcodes
			$regexes = [
				'chronomarket' => '#{chronomarket}(.*?){/chronomarket}#s',
			];
			
			foreach($regexes as $ext => $regex){
				preg_match_all($regex, $buffer, $matches);
				if(!empty($matches[0])){
					foreach($matches[0] as $k => $match){
						ob_start();
						$ext_path = JPATH_SITE.DS.'components'.DS.'com_'.$ext.DS.$ext.'.php';
						if(file_exists($ext_path)){
							require($ext_path);
							$result = ob_get_clean();
							$buffer = str_replace($match, $result, $buffer);
						}
					}
				}
			}
			
			$doc->setBuffer($buffer, 'component');
		}
	}
	
	public function onBeforeCompileHead(){
		if(class_exists('GApp')){
			$doc = \GApp::document();
			$doc->buildHeader();
		}
		if(class_exists('SemanticTheme')){
			if(!empty(SemanticTheme::$packassets) AND !empty(SemanticTheme::$template->params->get('assetsPath'))){
				SemanticTheme::package(SemanticTheme::$template, SemanticTheme::$template->params->get('assetsPath'), 'js');
				SemanticTheme::package(SemanticTheme::$template, SemanticTheme::$template->params->get('assetsPath'), 'css');
			}
		}
	}
}
