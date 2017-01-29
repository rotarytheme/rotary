<?php

function rotary_boardmembers_html( $atts ) {

		$args = array(
			'exclude' => array( 1 )				
		);
	
		$boardmembers = new WP_user_query( $args );
		//$boardmembers = new WP_user_query( array( 'ID' => 1 ) );
		
		$clubname = get_theme_mod( 'rotary_club_name', '' );
		$rotaryClubBefore = get_theme_mod( 'rotary_club_first', false);
		
	
		ob_start();
		?>
		
		<div class="boardmembers-container">
			<div id="bbrc-container">
			<h1><?php echo  rotary_club_header($clubname, $rotaryClubBefore ) . __( ' Board Members', 'Rotary' );?></h1>
			<?php 
			if( !empty( $boardmembers->results ) ) :
				foreach( $boardmembers->results as $boardmember ) :
					$usermeta = get_user_meta( $boardmember->ID );

			if (isset( $usermeta['profilepicture'] )) 
				$profilepic = $usermeta['profilepicture'][0];
			
					$roles = ( is_array( $usermeta['clubrole']) ? implode( $usermeta['clubrole'], '<br>') : '');
					if( $roles && 'Member' != $roles):
				?>
					<div class="bbrc-member-container" width="75">
						<div style="background-image: url( <?php echo $profilepic; ?> );" class="bbrc-picture-container"></div>
						<div class="bbrc-text-container">
							<div class="bbrc-username">
								<a href="mailto:<?php echo $boardmember->user_email; ?>"><?php echo $boardmember->first_name; ?><br><?php  echo $boardmember->last_name; ?></a>
							</div>
							<div class="bbrc-position"><?php echo $roles;?></div>
							<div class="bbrc-office">
								<p class="bbrc-office-label"><?php echo __( 'Office' );?>:</p>
								<p class="bbrc-office-phone"><?php echo $usermeta['busphone'][0];?></p>
							</div>
						</div>
					<div style="clear:both"></div>
					</div>
					<?php 
					endif;
					endforeach;
			else:
				?><p><?php echo __( 'Sorry - No Board Members have been identified.' );?></p>
				<?php 
			endif;
			?>
			<div style="clear:both"></div>
			</div>
		</div>
		<?php 
		$output = ob_get_clean();
		
		$output = apply_filters( 'rotary_boardmembers', $output, $atts );
	
return $output;
}


