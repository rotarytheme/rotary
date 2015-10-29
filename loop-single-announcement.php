<?php
/**
 * The loop that displays an announcement.
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary HTML5 3.2
 */
?> 
<?php


	// Get all the data and metadata for the comment, ready to display.
	$id = $announcement->comment_ID;
	$posted_in_id = $announcement->comment_post_ID;
	
	$call_to_action = get_comment_meta( $id, 'call_to_action' ); //TODO: add call to action metadata field
	$announcement_title = get_comment_meta( $id, 'announcement_title', true );
	
	if( !get_comment_meta( $id, 'announcement_expiry_date', true ) ) :
		$expiry_date = new DateTime;
		$expiry_date->add(new DateInterval( 'P7D' ) ) ;
		$announcement_expiry_date = $expiry_date->format( 'Y-m-d' );
		update_comment_meta( $comment_id, 'announcement_expiry_date', $announcement_expiry_date );
	endif;
	
	$request_replies = get_comment_meta( $id, 'request_replies', true );
	$announcement_expiry_date = new DateTime( get_comment_meta( $id, 'announcement_expiry_date', true ));
	$announcement_text = apply_filters ("the_content", $announcement->comment_content);
	$announced_by = $announcement->comment_author;
	$call_to_action = get_comment_meta( $id, 'call_to_action', true );

	$date = new DateTime( $announcement->comment_date );

	if ( $context ) $extra_classes[] =  $context . '-announcement';


	?>
		<article id="comment-<?php echo $id ?>" <?php comment_class( $extra_classes ); ?>>
			
			<?php switch ( $context ) { 
			 case 'shortcode':
			 case 'speaker':  ?>
				<?php echo rotary_announcement_header( $posted_in_id, $announcement_title );?>
				<p class="announced-by"><?php 
				if ( $request_replies ) :
					$userdata = get_userdata( $announcement->user_id );
				?>
					<a class="rotarybutton-smallblue" href="mailto:<?php echo $userdata->user_email;?>?subject=Re: <?php echo $announcement_title;?>" ><?php echo sprintf( __ ('Reply to %s'), $announced_by ); ?></a>
				<?php 
				else:
					echo sprintf( 'by %s', $announced_by ); 
				endif;
				?></p>
				<?php if( $call_to_action ) : ?>
					<div class="calltoactioncontainer"><?php echo $call_to_action; ?></div>
				<?php endif;?>
				<?php if( 'shortcode' == $context ) : ?>
				<div class="announcement-date">
					<span class="day"><?php echo $date->format( 'd M Y') ; ?></span>
				</div>
				<div class="announcement-expiry-date">
					<span class="day"><?php echo $announcement_expiry_date->format( 'd M Y') ; ?></span>
				</div>
				<?php endif;?>					
				<div class="announcement-body">
					<?php echo $announcement_text; ?>			
				</div>
				<?php if( $allowedits ) :?>?>
				 <div class="editannouncementbutton-container">
				 	<a href="javascript:void(null)" data-comment-id="<?php echo $id; ?>" class="rotarybutton-smallwhite editannouncementbutton"><?php echo __( 'Edit Announcement' ); ?></a>
				 </div>
				<?php  endif; ?>
			<?php 
				break; 
			case 'project': 
			case 'committee':
			default: ?>
				<div class="announcement-date">
					<span class="day"><?php echo $date->format( 'd') ; ?></span>
					<span class="month"><?php  echo $date->format( 'M' ); ?></span>
					<span class="year"><?php echo $date->format( 'Y' ); ?></span>
				</div>
				<div class="announcement-expiry-date">
					<span class="day"><?php echo $announcement_expiry_date->format( 'd M Y') ; ?></span>
				</div>
				<div class="announcement-content">
					<div class="announcement-header">
						<h3><?php echo ( $announcement_title ) ? $announcement_title : _e( 'New Announcement!', 'Rotary' ); ?></h3>
						<p class="announced-by"><?php 
						if ( $request_replies ) :
							$userdata = get_userdata( $announcement->user_id );
						?>
							<a class="rotarybutton-smallblue" href="mailto:<?php echo $userdata->user_email;?>?subject=Re: <?php echo $announcement_title;?>" ><?php echo sprintf( __ ('Reply to %s'), $announced_by ); ?></a>
						<?php 
						else:
							echo sprintf( 'by ' );
							?><span><?php echo $announced_by; ?></span><?php  
						endif;
						?></p>
					</div>							
					<div class="announcement-body">
						<?php echo $announcement_text; ?>			
					</div>
				</div>		
			<?php }?>
			
			<div class="announcement-call-to-action"><?php $call_to_action; ?></div>
			<!-- <hr class="announcement-hr" /> -->
		</article>