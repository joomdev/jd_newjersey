<?php
defined('_JEXEC') or die('Restricted access');


if ( $this->virtuemartFeed) {
	?> <ul class="newsfeed" id="newsfeed"> <?php
	foreach ($this->virtuemartFeed as $item) {
		if (!empty($item->link)) {
			$description=strip_tags($item->description);
			$description=substr($description, 0,200)."...";
			?>
			<li class="newsfeed-item">
				<a href="<?php echo $item->link; ?>" target="_blank" title=" <?php echo $description; ?>"> <?php echo $item->title; ?></a>
			</li>
		<?php
		}
	}
	?></ul><?php
}


if ( $this->extensionsFeed ) {
	$j=0;
	foreach ($this->extensionsFeed as $item){
		// This is directly related to extensions.virtuemart.net
		if (($j / 5) == 0) { ?>
			<div class="clear"></div>

			<h2 class="cpanel"><?php echo vmText::_('COM_VIRTUEMART_FEED_LATEST_EXTENSION')?></h2>
			<?php
		} elseif (($j / 5) == 1) { ?>
			<div class="clear"></div>

			<h2 class="cpanel"><?php echo vmText::_('COM_VIRTUEMART_FEED_FEATURED_EXTENSION')?></h2>
		<?php
		} elseif (($j / 5) == 2) { ?>
			<div class="clear"></div>
			<h2 class="cpanel"><?php echo vmText::_('COM_VIRTUEMART_FEED_POPULAR_EXTENSION')?></h2>
		<?php
		}
		$image="";
		if (!empty($item->link)) {
			 $description = $item->description;
			preg_match('/<img[^>]+>/i',$description, $result);
			if (is_array($result) and isset($result[0])){
				$image=$result[0];
				$description=str_replace($image,"",$description);
				$description=strip_tags($description);
				$description=str_replace(vmText::_ ('COM_VIRTUEMART_FEED_READMORE') ,"",$description);
			} else {
				$description="";
			}
			?>
			<div class="icon vmextimg" >
				<a href="<?php echo $item->link; ?>" target="_blank" title="<?php echo $description ?>">
					<?php
					if ($image){
						echo  $image."<br />" ;
					}
					echo $item->title;
					?>
				</a>
			</div>
		<?php
		}
		$j++;
	}
}
?>