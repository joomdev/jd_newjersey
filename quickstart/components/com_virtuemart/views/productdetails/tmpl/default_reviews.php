<?php
/**
 *
 * Show the product details page
 *
 * @package    VirtueMart
 * @subpackage
 * @author Max Milbers, Valerie Isaksen
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: default_reviews.php 9766 2018-02-21 11:24:10Z Milbo $
 */

// Check to ensure this file is included in Joomla!
defined ('_JEXEC') or die ('Restricted access');

// Customer Reviews
$review_editable = true;
if ($this->allowRating || $this->allowReview || $this->showRating || $this->showReview) {

	$maxrating = VmConfig::get( 'vm_maximum_rating_scale', 5 );
	$ratingsShow = VmConfig::get( 'vm_num_ratings_show', 3 ); // TODO add  vm_num_ratings_show in vmConfig
	$stars = array();
	//$showall = vRequest::getBool( 'showall', FALSE );
	$ratingWidth = $maxrating*24;
	for( $num = 0; $num<=$maxrating; $num++ ) {
		$stars[] = '
				<span title="'.(vmText::_( "COM_VIRTUEMART_RATING_TITLE" ).$num.'/'.$maxrating).'" class="vmicon ratingbox" style="display:inline-block;width:'. 24*$maxrating.'px;">
					<span class="stars-orange" style="width:'.(24*$num).'px">
					</span>
				</span>';
	}

	echo '<div class="customer-reviews">';

	if ($this->rating_reviews) {
		foreach( $this->rating_reviews as $review ) {
			/* Check if user already commented */
			// if ($review->virtuemart_userid == $this->user->id ) {
			if ($review->created_by == $this->user->id && !$review->review_editable) {
				$review_editable = false;
			}
		}
	}
}

if ($this->allowRating or $this->allowReview) {



	if ($review_editable) {
		?>

		<form method="post"
			  action="<?php echo JRoute::_( 'index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id='.$this->product->virtuemart_product_id.'&virtuemart_category_id='.$this->product->virtuemart_category_id, FALSE ); ?>"
			  name="reviewForm" id="reviewform">

			<?php if($this->allowRating and $review_editable) { ?>

				<h4><?php echo vmText::_( 'COM_VIRTUEMART_WRITE_REVIEW' );
					if(count( $this->rating_reviews ) == 0) {
						?><span><?php echo vmText::_( 'COM_VIRTUEMART_WRITE_FIRST_REVIEW' ) ?></span><?php
					} ?>
				</h4>
				<span class="step"><?php echo vmText::_( 'COM_VIRTUEMART_RATING_FIRST_RATE' ) ?></span>
				<div class="rating">
					<label for="vote"><?php echo $stars[$maxrating]; ?></label>
					<input type="hidden" id="vote" value="<?php echo $maxrating ?>" name="vote">
				</div>

				<?php

				$reviewJavascript = "
		jQuery(function($) {
			var steps = ".$maxrating.";
			var parentPos= $('.rating .ratingbox').position();
			var boxWidth = $('.rating .ratingbox').width();// nbr of total pixels
			var starSize = (boxWidth/steps);
			var ratingboxPos= $('.rating .ratingbox').offset();

			jQuery('.rating .ratingbox').mousemove( function(e){
				var span = jQuery(this).children();
				var dif = e.pageX-ratingboxPos.left; // nbr of pixels
				difRatio = Math.floor(dif/boxWidth* steps )+1; //step
				span.width(difRatio*starSize);
				$('#vote').val(difRatio);
				//console.log('note = ',parentPos, boxWidth, ratingboxPos);
			});
		});
		";
				vmJsApi::addJScript( 'rating_stars', $reviewJavascript );

			}

			// Writing A Review
			if ($this->allowReview and $review_editable) {
			?>
			<div class="write-reviews">

				<?php // Show Review Length While Your Are Writing
				$reviewJavascript = "
function check_reviewform() {

var form = document.getElementById('reviewform');
var ausgewaehlt = false;

	if (form.comment.value.length < ".VmConfig::get( 'reviews_minimum_comment_length', 100 ).") {
		alert('".addslashes( vmText::sprintf( 'COM_VIRTUEMART_REVIEW_ERR_COMMENT1_JS', VmConfig::get( 'reviews_minimum_comment_length', 100 ) ) )."');
		return false;
	}
	else if (form.comment.value.length > ".VmConfig::get( 'reviews_maximum_comment_length', 2000 ).") {
		alert('".addslashes( vmText::sprintf( 'COM_VIRTUEMART_REVIEW_ERR_COMMENT2_JS', VmConfig::get( 'reviews_maximum_comment_length', 2000 ) ) )."');
		return false;
	}
	else {
		return true;
	}
}

function refresh_counter() {
	var form = document.getElementById('reviewform');
	form.counter.value= form.comment.value.length;
}
";
				vmJsApi::addJScript( 'check_reviewform', $reviewJavascript ); ?>
				<span
					class="step"><?php echo vmText::sprintf( 'COM_VIRTUEMART_REVIEW_COMMENT', VmConfig::get( 'reviews_minimum_comment_length', 100 ), VmConfig::get( 'reviews_maximum_comment_length', 2000 ) ); ?></span>
				<br/>
				<textarea class="virtuemart" title="<?php echo vmText::_( 'COM_VIRTUEMART_WRITE_REVIEW' ) ?>"
						  class="inputbox" id="comment" onblur="refresh_counter();" onfocus="refresh_counter();"
						  onkeyup="refresh_counter();" name="comment" rows="5"
						  cols="60"><?php if(!empty($this->review->comment)) {
						echo $this->review->comment;
					} ?></textarea>
				<br/>
		<span><?php echo vmText::_( 'COM_VIRTUEMART_REVIEW_COUNT' ) ?>
			<input type="text" value="0" size="4" name="counter" maxlength="4" readonly="readonly"/>
				</span>
                <br/><br/>
                <input class="highlight-button" type="submit" onclick="return( check_reviewform());"
                       name="submit_review" title="<?php echo vmText::_( 'COM_VIRTUEMART_REVIEW_SUBMIT' ) ?>"
                       value="<?php echo vmText::_( 'COM_VIRTUEMART_REVIEW_SUBMIT' ) ?>"/>
            </div>
				<?php } else if($review_editable and $this->allowRating) { ?>
                    <input class="highlight-button" type="submit" name="submit_review"
                           title="<?php echo vmText::_( 'COM_VIRTUEMART_REVIEW_SUBMIT' ) ?>"
                           value="<?php echo vmText::_( 'COM_VIRTUEMART_REVIEW_SUBMIT' ) ?>"/>


					<?php
				}

				/*if($review_editable and $this->allowReview) {
					?>
					<br/><br/>
					<input class="highlight-button" type="submit" onclick="return( check_reviewform());"
						   name="submit_review" title="<?php echo vmText::_( 'COM_VIRTUEMART_REVIEW_SUBMIT' ) ?>"
						   value="<?php echo vmText::_( 'COM_VIRTUEMART_REVIEW_SUBMIT' ) ?>"/>
				<?php } else if($review_editable and $this->allowRating) { ?>
					<input class="highlight-button" type="submit" name="submit_review"
						   title="<?php echo vmText::_( 'COM_VIRTUEMART_REVIEW_SUBMIT' ) ?>"
						   value="<?php echo vmText::_( 'COM_VIRTUEMART_REVIEW_SUBMIT' ) ?>"/>
				<?php
				}*/

				?>
			<input type="hidden" name="virtuemart_product_id"
				   value="<?php echo $this->product->virtuemart_product_id; ?>"/>
			<input type="hidden" name="option" value="com_virtuemart"/>
			<input type="hidden" name="virtuemart_category_id"
				   value="<?php echo vRequest::getInt( 'virtuemart_category_id' ); ?>"/>
			<input type="hidden" name="virtuemart_rating_review_id" value="0"/>
			<input type="hidden" name="task" value="review"/>
		</form>
	<?php
	} else if(!$review_editable) {
		echo '<strong>'.vmText::_( 'COM_VIRTUEMART_DEAR' ).$this->user->name.',</strong><br>';
		echo vmText::_( 'COM_VIRTUEMART_REVIEW_ALREADYDONE' );
	}
}


if ($this->showReview) {

	?>
	<h4><?php echo vmText::_ ('COM_VIRTUEMART_REVIEWS') ?></h4>

	<div class="list-reviews">
		<?php
		$i = 0;
		//$review_editable = TRUE;
		$reviews_published = 0;
		if ($this->rating_reviews) {
			foreach ($this->rating_reviews as $review) {
				if ($i % 2 == 0) {
					$color = 'normal';
				} else {
					$color = 'highlight';
				}


				?>

				<?php // Loop through all reviews
				if (!empty($this->rating_reviews) && $review->published) {
					$reviews_published++;
					?>
					<div class="<?php echo $color ?>">
						<span class="date"><?php echo JHtml::date ($review->created_on, vmText::_ ('DATE_FORMAT_LC')); ?></span>
						<span class="vote"><?php echo $stars[(int)$review->review_rating] ?></span>
						<blockquote><?php echo $review->comment; ?></blockquote>
						<span class="bold"><?php echo $review->customer ?></span>
					</div>
					<?php
				}
				$i++;
				if ($i == $ratingsShow && !$this->showall) {
					/* Show all reviews ? */
					if ($reviews_published >= $ratingsShow) {
						$attribute = array('class'=> 'details', 'title'=> vmText::_ ('COM_VIRTUEMART_MORE_REVIEWS'));
						echo JHtml::link ($this->more_reviews, vmText::_ ('COM_VIRTUEMART_MORE_REVIEWS'), $attribute);
					}
					break;
				}
			}

		} else {
			// "There are no reviews for this product"
			?>
			<span class="step"><?php echo vmText::_ ('COM_VIRTUEMART_NO_REVIEWS') ?></span>
			<?php
		}  ?>
		<div class="clear"></div>
	</div>
<?php
}

if ($this->allowRating || $this->allowReview || $this->showRating || $this->showReview) {
	echo '</div> ';
}
?>
