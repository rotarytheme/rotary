<?php
/**
 * The Sidebar containing the primary and secondary widget areas.
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary 1.0
 * sidebar used on blog and interior pages
 */
?>

	<aside id="committee-sidebar" role="complementary">
		<ul>
			<li>
				<?php if(get_field('committeemission')) { ?>
					<h3>Mission</h3>
					<ul>
					<li><?php the_field('committeemission'); ?></li>
					</ul>

				<?php } ?>
				
				<?php if(get_field('committee_chair_email')) { ?>
					<h3>Chair</h3>
					<ul>
					<?php
	$user = get_user_by( 'email', get_field('committee_chair_email') );
	$usermeta = get_user_meta($user->ID); ?>
					<li><?php echo $usermeta['first_name'][0]. ' ' .$usermeta['last_name'][0]; ?></li>
					<li><a href="mailto:<?php antispambot( the_field('committee_chair_email') ); ?>"> <?php antispambot( the_field('committee_chair_email') ); ?></a>
</li>
					</ul>
			 <?php } ?>
			 	<?php if(get_field('committee_cochair_email_1')) { ?>
					<h3>Co Chair</h3>
					<ul>
					<?php
	$user = get_user_by( 'email', get_field('committee_cochair_email_1') );
	$usermeta = get_user_meta($user->ID); ?>
					<li><?php echo $usermeta['first_name'][0]. ' ' .$usermeta['last_name'][0]; ?></li>
					<?php if(get_field('committee_cochair_email_2')) {
		$user = get_user_by( 'email', get_field('committee_cochair_email_2') );
		$usermeta = get_user_meta($user->ID); ?>
						<li><?php echo $usermeta['first_name'][0]. ' ' .$usermeta['last_name'][0]; ?></li>
					<?php } ?>
					</ul>
			 <?php } ?>
			 <?php if(get_field('committee_featured_image')) { ?>
					<ul class="avatarcontainer">				
					<?php $image = wp_get_attachment_image_src( get_field('committee_featured_image'), 'medium' ); ?>
					<li><img src="<?php echo  $image[0]?>" alt="Committee Featured Image" title=""/> </li>
					</ul>
				<?php } ?>
				<ul class="committeemembers">
				<?php
$users = get_users( array(
		'connected_type' => 'committees_to_users',
		'connected_items' => get_the_id(),
		'connected_direction' => 'from',
	)); ?>
<h3>Committee Members</h3>
<?php
foreach ($users as $user) :
	$usermeta = get_user_meta($user->ID);
	$memberName = $usermeta['first_name'][0]. ' ' .$usermeta['last_name'][0]; ?>

					<li><?php echo get_avatar( $user->user_email, '16', '',  $memberName). ' ' .$memberName;?></li>
			<?php
endforeach;
?>
				</ul>
			</li>
		</ul>
	</aside>