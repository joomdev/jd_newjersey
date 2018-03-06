<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<div class="ui menu inverted">
	<a class="item icon blue <?php if($this->action == 'index' AND $this->controller == ''): ?>active<?php endif; ?>" href="<?php echo r2('index.php?ext='.$this->extension); ?>">
		<div class="ui blue inverted compact small active header large"><?php echo $etitle; ?></div>
	</a>
	<?php
		$menuitems = array_merge($menuitems, [
			['act' => 'clear_cache', 'title' => rl('Clear cache')],
			['cont' => 'languages', 'title' => rl('Languages')],
			['act' => 'validateinstall', 'title' => rl('Validate')],
		]);
	?>
	<?php foreach($menuitems as $k => $amdata): ?>
		<?php
			$active = '';
			$icon = '';
			
			if(!empty($amdata['cont'])){
				if($this->controller == $amdata['cont']){
					$active = 'active';
				}
				
				if($amdata['cont'] == 'languages'){
					$icon = 'translate';
				}
				
				if($amdata['cont'] == 'tags'){
					$icon = 'tag';
				}
			}
			if(!empty($amdata['act'])){
				if($this->action == $amdata['act']){
					$active = 'active';
				}
				
				if($amdata['act'] == 'install_feature'){
					$icon = 'magic';
				}
				
				if($amdata['act'] == 'clear_cache'){
					$icon = 'refresh';
				}
				
				if($amdata['act'] == 'validateinstall'){
					$icon = 'checkmark green';
				}
				
				if($amdata['act'] == 'info'){
					$icon = 'question';
				}
				
				if($amdata['act'] == 'settings'){
					$icon = 'settings';
				}
				
				if($amdata['act'] == 'permissions'){
					$icon = 'key';
				}
			}
			
			if(!empty($amdata['icon'])){
				$icon = $amdata['icon'];
			}
			
			$url = 'index.php?ext='.$this->extension.(!empty($amdata['cont']) ? '&cont='.$amdata['cont'] : '').(!empty($amdata['act']) ? '&act='.$amdata['act'] : '');
		?>
		<a class="item blue <?php echo $active; ?>" href="<?php echo r2($url); ?>">
			<?php if(!empty($icon)): ?>
				<i class="<?php echo $icon; ?> icon"></i>
			<?php endif; ?>
			<?php echo $amdata['title']; ?>
		</a>
	<?php endforeach; ?>
</div>