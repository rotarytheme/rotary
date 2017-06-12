<?php
	function get_membership_layout( $rotarymembership, $projects, $id ) {
		 
		global  $RegistrationNoun, $RegistrationCTA;
		
		//Participation Table - must be explicitly a project table, and have an id or will default ot a membership table
		if ( 'projects' == $projects && strlen( $id ) > 1 ) : 
			$divID = 'rotaryprojects';
			$title = __( 'Participants', 'Rotary' );
			$dataID = ' data-id="'.$id.'"';
			$hideClass = ' hide';
			$deleteCol = '<th class="deletecol">' . _x( 'Delete', 'Remove from project participation' , 'rotary' ) . '</th>';
			$select = '
				<div class="usercontainer"><select id="newparticipants">'.$rotarymembership->get_users_for_membertable_select().'</select></div>
				<div class="sendemail">' . __( 'Bulk Actions', 'Rotary' ) . '&nbsp;
					<select> <option value="email">' . _x( 'Email', 'Bulk action', 'rotary' ) . '</option></select> 
					<input id="sendmailbutton" class="rotarybutton-smallwhite" type="button" value="' . _x( 'Go', 'Bulk actions', 'rotary' ) . '"/>
				</div>';
		
		// Gravity Form
		elseif ( 'form' == $projects ) :
			$gf_form_id = get_field( 'field_gravity_form_id', $id);
			$gf_fields = array();
			$i = 1;
			while ( have_rows( 'field_column_display_repeater', $id )) : the_row();
				$gf_fields[$i]['field_id'] = get_sub_field( 'form_field_column_selector' );
				$gf_fields[$i]['header'] = get_sub_field( 'form_field_column_header' );
				$gf_fields[$i]['width'] = get_sub_field( 'form_field_column_width' );
				$i++;
			endwhile;
			$gf_form = GFAPI::get_form( $gf_form_id );
			$divID = 'rotaryform';
			if( $gf_form ) :
				$divID = 'rotaryform';
			
				$action = get_field( 'field_button_label', $id );
				$title = $RegistrationNoun[$action];
				$button_label =  $RegistrationCTA[$action];
				
				$dataID = ' data-id="'.$id.'"';
				$hideClass = $deleteCol = '';
				$select = '<div class="usercontainer">
								<a id="showregistrationform" class="rotarybutton-largeblue">'. $button_label . '</a>
								<a id="cancelregistrationform" class="rotarybutton-largewhite"  style="display:none;">'. __( 'Cancel' ) . '</a>
							</div>';
			endif;
				
		//Membership Directory
		else: 
			$divID = 'rotarymembers';
			$title = __( 'Membership Directory', 'Rotary' );
			$dataID = $hideClass = $deleteCol = '';
			$select = '
				<div id="rotarymembershipoptions">
					<div class="sendemail">' . __( 'Bulk Actions' , 'Rotary' ) . '&nbsp;
						<select><option value="email">' . _x( 'Email', 'Bulk action', 'Rotary' ) . '</option></select> 
						<input id="sendmailbutton" class="rotarybutton-smallwhite" type="button" value="' . _x( 'Go', 'Bulk actions', 'rotary' ) . '"/>
					</div>
					<div id="namedisplayorder">
						<input type="radio" id="nameorder1" name="nameorder" value="firstname"/><span>' . _x( 'First Last', 'Name display order', 'rotary' ) . '</span>
						<input type="radio" id="nameorder2" name="nameorder" value="lastname" checked="checked"/><span>' . _x( 'Last, First', 'Name display order', 'rotary' ) . '</span>
					</div>
				</div>
				<div class="committeecontainer">
					<select id="committees">' . $rotarymembership->get_committees_for_membertable() . '</select>
				</div>';
		endif;
	
		/********Now produce the table ***/
		
		if( 'form' == $projects ) :
		$memberTable = '
				<div class="rotarymembershipcontainer">
				<input type="hidden" id="form_id" value="' . $gf_form_id . '" />
				<input type="hidden" id="post_id" value="' . $id . '" />
					<div class="rotarymembershipheader">
						<h2>' . $title . '</h2>
						<div class="rotaryselections">' . $select . '</div>
						<div id="gravityform" style="display: none;">' . do_shortcode( '[gravityform id="' . $gf_form_id . '" title="false" description="false" ajax=true]' ) . '</div>';

			if (is_user_logged_in() ) :
				$memberTable .= 	'<table id="' . $divID . '" cellspacing="0" cellpadding="0" border="0" class="display"' . $dataID . '>
								<thead>
									<tr>';
	
			
					$i=1;
				foreach ( $gf_fields as $gf_field ) {
					$memberTable .= '<th class="formcolumnheader col' . $i . '" id ="col' . $i . '" width="' . trim( $gf_field['width'] ) . '%">' . $gf_field['header'] . '</th>';
					$i++;
				} 
				$memberTable .= '
								</thead>
								<tbody></tbody>
				        	</table>';
			endif;
			$memberTable .=		'</div><!-- end rotarymembershipheader -->
       			</div><!-- end rotarymembershipcontainer -->
			';
		elseif ( 'form' != $projects) :
		$memberTable = '
				<div class="rotarymembershipcontainer">
					<div class="rotarymembershipheader">
						<h2>' . $title . '</h2>
						<div class="rotaryselections">' . $select . '</div>
						<table id="' . $divID . '" style="width:97%;" cellspacing="0" cellpadding="0" border="0" class="display"' . $dataID . ' >	
							<thead>
								<tr>	
									<th class="selectorcol"><input type="checkbox" id="selectallcheckbox" /></th>
					        		<th class="fullnamecol">'. _x( 'Name', 'Directory table label', 'rotary') . '</th>		         
					        		<th class="classificationcol' . $hideClass . '">'. _x( 'Classification', 'Directory table label', 'rotary') . '</th>		         
					        		<th class="partnercol ' . $hideClass . '">'. _x( 'Partner', 'Directory table label', 'rotary') . '</th>                         
					        		<th class="homephonecol">'. _x( 'Cell/Home Phone', 'Directory table label', 'rotary') . '</th>                         
					        		<th class="businessphonecol">'. _x( 'Business Phone', 'Directory table label', 'rotary') . '</th>                      
					        		<th class="phonecol">'. _x( 'Contact Info', 'Directory table label', 'rotary') . '</th>                         
					        		<th class="emailcol">'. _x( 'Email', 'Directory table label', 'rotary') . '</th>
					        		<th class="idcol hide">ID</th>
					        		'.$deleteCol.
					        	'</tr>
							</thead>
							<tbody></tbody>
			        	</table>
						<div id="rotarymemberdialog">
							<div class="dialogtop">
							   <div class="namearea">
									<h2 class="membername"></h2>
									<p class="classification"></p>
								</div>
								<div class="addressarea">
									<p class="addressdetails"></p>
								</div>	
							</div>
							<div class="dialogmain">
								<div class="personalinfoarea">
									<div class="profilepicture"></div>
									<div class="profilepicturebottom"></div>
									<h4>Birthday</h4>
									<p class="birthday"></p>
									<h4>Anniversary</h4>
									<p class="anniversarydate"></p>	
									<h4>Member Since</h4>
									<p class="membersince"></p>
								</div>
								<div class="memberdetailsarea">
									<div class="company">
										<h3>Company</h3>
										<div class="clearleft">	
											<h4>Name</h4>
											<p class="busname"></p>
										</div>
										<div class="clearleft">
											<h4>Title</h4>
											<p class="jobtitle"></p>
										</div>
										<div class="clearleft">
											<h4>Web</h4>	
											<p class="busweb"></p>
										</div>	
									</div>
									<div class="contact">
										<h3>Contact</h3>
										<div class="clearleft">
											<h4>Cell</h4>
											<p class="cellphone"></p>
											<h4>Home</h4>
											<p class="homephone"></p>
										</div>
										<div class="clearleft larger">
											<h4>Business</h4>
											<p class="officephone"></p>
											<h4>Email</h4>
											<p class="email"></p>
										</div>
									</div>
									<div class="partner">
										<h3>Partner</h3>
										<div class="clearleft">
											<h4>Spouse</h4>
											<p class="partnername"></p>
										</div>	
									</div>
								</div><!-- end personalinfoarea -->
							</div><!-- end dialogmain -->
							<div class="dialogbottom"></div>
						</div><!-- end rotarymemberdialog -->
					</div><!-- end rotarymembershipheader -->
       			</div><!-- end rotarymembershipcontainer -->
			';
		endif;
		return $memberTable;
	}
?>