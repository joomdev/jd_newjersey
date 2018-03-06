<?php
defined ('_JEXEC') or die('Restricted access');

/**
 *
 * VirtueMart script file
 *
 * This file is executed during install/upgrade and uninstall
 *
 * @author Patrick Kohl, Max Milbers, Valérie Isaksen
 * @package VirtueMart
 */

defined ('DS') or define('DS', DIRECTORY_SEPARATOR);


$max_execution_time = ini_get ('max_execution_time');
if ((int)$max_execution_time < 120) {
	@ini_set ('max_execution_time', '120');
}


// hack to prevent defining these twice in 1.6 installation
if (!defined ('_VM_AIO_SCRIPT_INCLUDED')) {

	define('_VM_AIO_SCRIPT_INCLUDED', TRUE);

	class com_tcpdfInstallerScript {

		public function preflight () {
			$mL = ini_get('memory_limit');
			$mLimit = 0;
			if(!empty($mL)){
				$u = strtoupper(substr($mL,-1));
				$mLimit = (int)substr($mL,0,-1);
				if($mLimit>0){

					if($u == 'M'){
						//$mLimit = $mLimit * 1048576;
					} else if($u == 'G'){
						$mLimit = $mLimit * 1024;
					} else if($u == 'K'){
						$mLimit = $mLimit / 1024.0;
					} else {
						$mLimit = $mLimit / 1048576.0;
					}
					$mLimit = (int) $mLimit - 5; // 5 MB reserve
					if($mLimit<=0){
						$mLimit = 1;
						$m = 'Increase your php memory limit, which is must too low to run VM, your current memory limit is set as '.$mL.' ='.$mLimit.'MB';
						vmError($m,$m);
					}
				}
			}
			if ($mLimit < 128) {
				@ini_set ('memory_limit', '128M');
			}
		}

		public function install () {
			//$this->vmInstall();
		}

		public function discover_install () {
			$this->tcpdfInstall ();
		}

		public function postflight () {

			$this->tcpdfInstall ();
		}

		public function tcpdfInstall () {


			jimport ('joomla.filesystem.file');
			jimport ('joomla.installer.installer');

			$this->path = JInstaller::getInstance ()->getPath ('extension_administrator');

			// libraries auto move
			$src = $this->path . DS . "libraries";
			$dst = JPATH_ROOT . DS . "libraries";
			$this->recurse_copy ($src, $dst);

			echo '<a
					href="http://virtuemart.net"
					target="_blank"> <img
						border="0"
						align="left" style="margin-right: 20px"
						src="components/com_virtuemart/assets/images/vm_menulogo.png"
						alt="Cart" /> </a>';
			echo '<h3 style="clear: both;">TcPdf moved to the joomla libraries folder</h3>';
			echo "<h3>Installation Successful.</h3>";
			return TRUE;

		}



		/**
		 * copy all $src to $dst folder and remove it
		 *
		 * @author Max Milbers
		 * @param String $src path
		 * @param String $dst path
		 * @param String $type modulesBE, modules, plugins, languageBE, languageFE
		 */
		private function recurse_copy ($src, $dst) {

			static $failed = false;
			$dir = opendir ($src);

			if (is_resource ($dir)) {
				while (FALSE !== ($file = readdir ($dir))) {
					if (($file != '.') && ($file != '..')) {
						if (is_dir ($src . DS . $file)) {
							if(!JFolder::create($dst . DS . $file)){
								$app = JFactory::getApplication ();
								$app->enqueueMessage ('Couldnt create folder ' . $dst . DS . $file);
							}
							$this->recurse_copy ($src . DS . $file, $dst . DS . $file);
						} else {
							if (JFile::exists ($dst . DS . $file)) {
								if (!JFile::delete ($dst . DS . $file)) {
									$app = JFactory::getApplication ();
									$app->enqueueMessage ('Couldnt delete ' . $dst . DS . $file);
									//return false;
								}
							}
							if (!JFile::move ($src . DS . $file, $dst . DS . $file)) {
								$app = JFactory::getApplication ();
								$app->enqueueMessage ('Couldnt move ' . $src . DS . $file . ' to ' . $dst . DS . $file);
								$failed = true;
								//return false;
							}
						}
					}
				}
				closedir ($dir);
				if (is_dir ($src) and !$failed) {
					JFolder::delete ($src);
				}
			} else {
				$app = JFactory::getApplication ();
				$app->enqueueMessage ('Couldnt read dir ' . $dir . ' source ' . $src);
				return false;
			}
			return true;
		}


		public function uninstall () {

			return TRUE;
		}

		/**
		 * creates a folder with empty html file
		 *
		 * @author Max Milbers
		 *
		 */
		public function createIndexFolder ($path) {

			if (JFolder::create ($path)) {
				/*if (!JFile::exists ($path . DS . 'index.html')) {
					JFile::copy (JPATH_ROOT . DS . 'components' . DS . 'index.html', $path . DS . 'index.html');
				}*/
				return TRUE;
			}
			return FALSE;
		}

	}

	if (!defined ('_VM_SCRIPT_INCLUDED')) {
		// PLZ look in #vminstall.php# to add your plugin and module
		function com_install () {

			if (!version_compare (JVERSION, '1.6.0', 'ge')) {
				$vmInstall = new com_virtuemart_allinoneInstallerScript();
				$vmInstall->tcpdfInstall ();
			}
			return TRUE;
		}

		function com_uninstall () {

			return TRUE;
		}
	}
} //if defined
// pure php no tag
