<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
class PlgSystemChronoengine_Gcore2 extends JPlugin{
	var $output = '';
	var $active = true;
	
	public function onAfterRoute(){
		$app = JFactory::getApplication();
		
		if($app->isAdmin()){
			defined('GCORE_SITE') or define('GCORE_SITE', 'admin');
		}else{
			defined('GCORE_SITE') or define('GCORE_SITE', 'front');
		}
		
		jimport('cegcore2.gcloader');
		if(!class_exists('\G2\Loader')){
			JError::raiseWarning(100, 'The CEGCore2 library could not be found.');
			$this->active = false;
		}
		
		if($this->active){
			if(!$app->isAdmin()){
				\G2\L\AppLoader::initialize();
				
				$social = \GApp::extension('chronosocial')->path();
				
				if(file_exists($social)){
					if(
						!empty($_REQUEST['option']) AND $_REQUEST['option'] == 'com_users'
						AND
						!empty($_REQUEST['view']) AND $_REQUEST['view'] == 'registration'
					){
						$_REQUEST['option'] = 'com_chronosocial';
						$_REQUEST['cont'] = 'users';
						$_REQUEST['act'] = 'register';
					}
					
				}
			}
		}
	}
	
	public function onAfterDispatch(){
		$app = JFactory::getApplication();
		$doc = JFactory::getDocument();
		$buffer = $doc->getBuffer('component');
		
		if($this->active){
			\G2\L\AppLoader::initialize();
			
			if(!$app->isAdmin()){
				$director = \GApp::extension('chronodirector')->path();
				if(file_exists($director)){
					//require($director);
					
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
