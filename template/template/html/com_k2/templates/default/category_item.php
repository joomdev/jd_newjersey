<?php
/**
 * @version    2.8.x
 * @package    K2
 * @author     JoomlaWorks http://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2017 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

// Define default image size (do not change)
K2HelperUtilities::setDefaultImage($this->item, 'itemlist', $this->params);

$has_image = '';
if($this->item->params->get('catItemImage') && !empty($this->item->image)) {
	$has_image = 'hasItemImage';
}
?>
<div class="catItemView wow fadeIn <?php echo $has_image; ?> group<?php echo ucfirst($this->item->itemGroup); ?><?php echo ($this->item->featured) ? ' catItemIsFeatured' : ''; ?><?php if($this->item->params->get('pageclass_sfx')) echo ' '.$this->item->params->get('pageclass_sfx'); ?>"><!-- Start K2 Item Layout -->

	<?php echo $this->item->event->BeforeDisplay; ?><!-- Plugins: BeforeDisplay -->
	<?php echo $this->item->event->K2BeforeDisplay; ?><!-- K2 Plugins: K2BeforeDisplay -->

	<div class="catItemBody">

		<?php echo $this->item->event->BeforeDisplayContent; ?><!-- Plugins: BeforeDisplayContent -->

		<?php echo $this->item->event->K2BeforeDisplayContent; ?><!-- K2 Plugins: K2BeforeDisplayContent -->
		
		<?php if($this->item->params->get('catItemTitle')){ ?>
		<h3 class="catItemTitle itemTitle"><!-- Item title -->
			<?php if(isset($this->item->editLink)){ ?>
			<span class="catItemEditLink"><!-- Item edit link -->
				<a class="modal" rel="{handler:'iframe',size:{x:990,y:550}}" href="<?php echo $this->item->editLink; ?>">
					<?php echo JText::_('K2_EDIT_ITEM'); ?>
				</a>
			</span>
			<?php } ?>

			<?php if ($this->item->params->get('catItemTitleLinked')){ ?>
			<a href="<?php echo $this->item->link; ?>">
				<?php echo $this->item->title; ?>
			</a>
			<?php } else { ?>
			<?php echo $this->item->title; ?>
			<?php } ?>

			<?php if($this->item->params->get('catItemFeaturedNotice') && $this->item->featured){ ?>
			<!-- Featured flag -->
			<sup>
				<?php echo JText::_('K2_FEATURED'); ?>
			</sup>
			<?php } ?>
		</h3>
		<?php } ?>

			<?php if (
				$this->item->params->get('catItemCategory') || 
				$this->item->params->get('catItemAuthor') ||
				$this->item->params->get('catItemCommentsAnchor') ||
				$this->item->params->get('catItemTags')
				) {?>
			<div class="catItemHeader itemHeader">

				<?php if($this->item->params->get('catItemDateCreated',1)) { ?>
				<!-- Date created -->
				<span class="catItemDateCreated">
					<i class="icon-time"></i> <?php echo JHTML::_('date', $this->item->created , JText::_('DATE_FORMAT_LC3')); ?>
				</span>
				<?php } ?>

				<?php if($this->item->params->get('catItemAuthor')){ ?>
				<span class="catItemAuthor"><!-- Item Author -->
					by <?php if(isset($this->item->author->link) && $this->item->author->link){ ?>
					<a rel="author" href="<?php echo $this->item->author->link; ?>"><?php echo $this->item->author->name; ?></a>
					<?php } else { ?>
					<?php echo $this->item->author->name; ?>
					<?php } ?>
				</span>
				<?php } ?>		

				<?php if($this->item->params->get('catItemCommentsAnchor') && ( ($this->item->params->get('comments') == '2' && !$this->user->guest) || ($this->item->params->get('comments') == '1')) ){ ?>
					<span class="catItemComment comments"><!-- Anchor link to comments below -->
						<?php if(!empty($this->item->event->K2CommentsCounter)){ ?>
						<?php echo $this->item->event->K2CommentsCounter; ?><!-- K2 Plugins: K2CommentsCounter -->
						<?php } else { ?>
						<a href="<?php echo $this->item->link; ?>#itemCommentsAnchor">
							 <?php echo $this->item->numOfComments; ?> Comment
						</a>
						<?php } ?>
					</span>
				<?php } ?>						
				<?php if($this->item->params->get('catItemCategory')){ ?>
				<span class="catItemCategory"><!-- Item category name -->
				<a href="<?php echo $this->item->category->link; ?>"><?php echo $this->item->category->name; ?></a>
				</span>
				<?php } ?>
			<?php if($this->item->params->get('itemHits')) { ?>
				<!-- Item Hits -->
				<span class="itemHits">
					<?php echo $this->item->hits; ?> Views
				</span>
				<?php } ?>
				<div class="clr"></div>
			</div>
			<?php } ?> <!-- END:: catItemHeader condition -->

		<?php if($this->item->params->get('catItemImage') && !empty($this->item->image)){ ?>
		<div class="catItemImageBlock"><!-- Item Image -->
			<span class="catItemImage">
				<a class="itemImage" href="<?php echo $this->item->link; ?>" title="<?php if(!empty($this->item->image_caption)) echo K2HelperUtilities::cleanHtml($this->item->image_caption); else echo K2HelperUtilities::cleanHtml($this->item->title); ?>">
					<img src="<?php echo $this->item->image; ?>" alt="<?php if(!empty($this->item->image_caption)) echo K2HelperUtilities::cleanHtml($this->item->image_caption); else echo K2HelperUtilities::cleanHtml($this->item->title); ?>" style="width:<?php echo $this->item->imageWidth; ?>px; height:auto;" />
				</a>
			</span>
			<div class="clr"></div>
		</div>
		<?php }?>

		<?php if($this->item->params->get('itemAuthorBlock') && empty($this->item->created_by_alias)) { ?>
		<!-- Author Block -->
		<div class="itemAuthorBlock itemAuthorBlock-user">

			<?php if($this->item->params->get('itemAuthorImage') && !empty($this->item->author->avatar)) { ?>
			<img class="itemAuthorAvatar" src="<?php echo $this->item->author->avatar; ?>" alt="<?php echo K2HelperUtilities::cleanHtml($this->item->author->name); ?>" />
			<?php } ?>

			<div class="itemAuthorDetails">
				<h3 class="itemAuthorName">Written by
					<a rel="author" href="<?php echo $this->item->author->link; ?>"><?php echo $this->item->author->name; ?></a>
				</h3>
				<!-- K2 Plugins: K2UserDisplay -->
			</div>
			<div class="clr"></div>
		</div>
		<?php } ?>
		
		<?php echo $this->item->event->AfterDisplayTitle; ?><!-- Plugins: AfterDisplayTitle -->
		<?php echo $this->item->event->K2AfterDisplayTitle; ?><!-- K2 Plugins: K2AfterDisplayTitle -->		

		<div class="catItemContentWrapper">
		
			<div class="itemInner">

				<?php if($this->item->params->get('catItemIntroText')){ ?>
				<div class="catItemIntroText"><!-- Item introtext -->
					<?php echo $this->item->introtext; ?>
				</div>
				<?php } ?>
				<div class="clr"></div>

				<?php if($this->item->params->get('catItemExtraFields') && count($this->item->extra_fields)){ ?>
				<div class="catItemExtraFields"><!-- Item extra fields -->
					<h4><?php echo JText::_('K2_ADDITIONAL_INFO'); ?></h4>
					<ul>
						<?php foreach ($this->item->extra_fields as $key=>$extraField){ ?>
						<?php if($extraField->value != ''){ ?>
						<li class="<?php echo ($key%2) ? "odd" : "even"; ?> type<?php echo ucfirst($extraField->type); ?> group<?php echo $extraField->group; ?>">
							<?php if($extraField->type == 'header'){ ?>
							<h4 class="catItemExtraFieldsHeader"><?php echo $extraField->name; ?></h4>
							<?php } else { ?>
							<span class="catItemExtraFieldsLabel"><?php echo $extraField->name; ?></span>
							<span class="catItemExtraFieldsValue"><?php echo $extraField->value; ?></span>
							<?php } ?>
						</li>
						<?php } ?>
						<?php } ?>
					</ul>
					<div class="clr"></div>
				</div>
				<?php } ?>

				<?php echo $this->item->event->AfterDisplayContent; ?><!-- Plugins: AfterDisplayContent -->
				<?php echo $this->item->event->K2AfterDisplayContent; ?><!-- K2 Plugins: K2AfterDisplayContent -->

				<div class="clr"></div>

				<?php if($this->item->params->get('catItemAttachments') && count($this->item->attachments)){ ?>

				<div class="catItemAttachmentsBlock"><!-- Item attachments -->
					<span><?php echo JText::_('K2_DOWNLOAD_ATTACHMENTS'); ?></span>
					<ul class="catItemAttachments">
						<?php foreach ($this->item->attachments as $attachment){ ?>
						<li>
							<a title="<?php echo K2HelperUtilities::cleanHtml($attachment->titleAttribute); ?>" href="<?php echo $attachment->link; ?>">
								<?php echo $attachment->title ; ?>
							</a>
							<?php if($this->item->params->get('catItemAttachmentsCounter')){ ?>
							<span>(<?php echo $attachment->hits; ?> <?php echo ($attachment->hits==1) ? JText::_('K2_DOWNLOAD') : JText::_('K2_DOWNLOADS'); ?>)</span>
							<?php } ?>
						</li>
						<?php } ?>
					</ul>
				</div>
				<?php } ?>

				<div class="clr"></div>

				<?php if($this->item->params->get('catItemVideo') && !empty($this->item->video)){ ?>

				<div class="catItemVideoBlock"><!-- Item video -->
					<h3><?php echo JText::_('K2_RELATED_VIDEO'); ?></h3>
					<?php if($this->item->videoType=='embedded'){ ?>
					<div class="catItemVideoEmbedded embed-responsive embed-responsive-16by9">
						<?php echo $this->item->video; ?>
					</div>
					<?php } else { ?>
					<span class="catItemVideo embed-responsive embed-responsive-16by9"><?php echo $this->item->video; ?></span>
					<?php } ?>
				</div>
				<?php } ?>

				<?php if($this->item->params->get('catItemImageGallery') && !empty($this->item->gallery)){ ?>

				<div class="catItemImageGallery"><!-- Item image gallery -->
					<h4><?php echo JText::_('K2_IMAGE_GALLERY'); ?></h4>
					<?php echo $this->item->gallery; ?>
				</div>
				<?php } ?>
				<div class="clr"></div>

				<?php if ($this->item->params->get('catItemReadMore')){ ?>

				<a class="k2ReadMore" href="<?php echo $this->item->link; ?>"><!-- Item "read more..." link -->
					<?php echo JText::_('K2_READ_MORE'); ?>
				</a>
				<?php } ?>

			<?php if($this->item->params->get('catItemTags') && count($this->item->tags)){ ?>
			<span class="catItemTagsBlock"><!-- Item tags -->
				<ul class="itemTags">
					<?php foreach ($this->item->tags as $tag){ ?>
					<li><a href="<?php echo $tag->link; ?>"># <?php echo $tag->name; ?></a></li>
					<?php } ?>
				</ul>
			</span>
			<?php } ?>

				<?php
				if(
					$this->item->params->get('catItemHits') ||
					$this->item->params->get('catItemRating') || 
					$this->item->params->get('catItemDateModified')){?>

						<div class="IndexToolbar clearfix">

							<!-- item hits -->
							<?php if($this->item->params->get('catItemHits')){ ?>
							<div class="catItemHits"><!-- Item Hits -->
								<?php echo JText::_('K2_READ'); ?> <b><?php echo $this->item->hits; ?></b> <?php echo JText::_('K2_TIMES'); ?>
							</div>
							<?php } ?>
							<!-- END:: item hits -->
							<!-- item rating -->
							<?php if($this->item->params->get('catItemRating')){ ?>
							<div class="catItemRatingBlock"><!-- Item Rating -->
								<span><?php echo JText::_('K2_RATE_THIS_ITEM'); ?></span>
								<div class="itemRatingForm">
									<ul class="itemRatingList">
										<li class="itemCurrentRating" id="itemCurrentRating<?php echo $this->item->id; ?>" style="width:<?php echo $this->item->votingPercentage; ?>%;"></li>
										<li><a href="#" data-id="<?php echo $this->item->id; ?>" title="<?php echo JText::_('K2_1_STAR_OUT_OF_5'); ?>" class="one-star">1</a></li>
										<li><a href="#" data-id="<?php echo $this->item->id; ?>" title="<?php echo JText::_('K2_2_STARS_OUT_OF_5'); ?>" class="two-stars">2</a></li>
										<li><a href="#" data-id="<?php echo $this->item->id; ?>" title="<?php echo JText::_('K2_3_STARS_OUT_OF_5'); ?>" class="three-stars">3</a></li>
										<li><a href="#" data-id="<?php echo $this->item->id; ?>" title="<?php echo JText::_('K2_4_STARS_OUT_OF_5'); ?>" class="four-stars">4</a></li>
										<li><a href="#" data-id="<?php echo $this->item->id; ?>" title="<?php echo JText::_('K2_5_STARS_OUT_OF_5'); ?>" class="five-stars">5</a></li>
									</ul>
									<div id="itemRatingLog<?php echo $this->item->id; ?>" class="itemRatingLog"><?php echo $this->item->numOfvotes; ?></div>
									<div class="clr"></div>
								</div>
								<div class="clr"></div>
							</div>
							<?php } ?>
							<!-- END:: item rating -->

							<!-- item modified date -->
							<?php if($this->item->params->get('catItemDateModified')){ ?>
							<?php if($this->item->modified != $this->nullDate && $this->item->modified != $this->item->created ){ ?>
							<span class="itemDateModified"><!-- Item date modified -->
								<?php echo JText::_('K2_LAST_MODIFIED_ON'); ?> <?php echo JHTML::_('date', $this->item->modified, JText::_('DATE_FORMAT_LC3')); ?>
							</span>
							<?php } ?>
							<?php } ?>
							<!-- END:: item modified date -->

						</div>
						<?php } ?>

						<?php echo $this->item->event->AfterDisplay; ?><!-- Plugins: AfterDisplay -->
						<?php echo $this->item->event->K2AfterDisplay; ?><!-- K2 Plugins: K2AfterDisplay -->

					</div><!--/.catItemInner-->	

				</div>
			</div>
		</div><!-- End K2 Item Layout -->
