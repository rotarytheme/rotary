<?php
/*
Rotary User Profiles*/
class RotaryProfiles {
	 private $rotaryMemberData;
	 public $member_profile = array();
	 public $members = array();
	 
	function __construct() {
		add_action( 'show_user_profile', array( $this, 'show_membership_profile_fields'));
		add_action( 'edit_user_profile', array( $this, 'show_membership_profile_fields'));
		add_action( 'personal_options_update', array( $this, 'update_membership_profile_fields'));
		add_action( 'edit_user_profile_update', array( $this, 'update_membership_profile_fields'));
		add_filter( 'get_avatar', array( $this, 'get_rotary_member_avatar'), 10, 5);
	}	 
	/*
	 * get all members
	 */
	function get_members() {
		$args = array(
				'meta_key'     => 'memberyesno',
				'meta_value'   => '1',
				'meta_compare' => '=',
				'orderby'      => 'last_name',
				'order'        => 'ASC',
		);
		$this->members = get_users( $args );
		return $this->members;
	}

	/*
	this function will display the profile picture intead of the avatar
	*/
	function get_rotary_member_avatar($avatar, $id_or_email, $size = '96', $default='' , $alt = false) {
		$usermeta = array();
		if (is_numeric($id_or_email)){
			$usermeta = get_user_meta($id_or_email);
		}
		elseif ( is_object($id_or_email)) {
			$email = $id_or_email->comment_author_email;
			$user = get_user_by('email', $email);
			$id =  $user->ID;
			$usermeta = get_user_meta($id);
		}

		else {
			$email = $id_or_email;
			$user = get_user_by('email', $email);
			$id = (int) $user->ID;
			$usermeta = get_user_meta($id);
		}
		if (isset($usermeta['profilepicture'])) {
			$profilepic = $usermeta['profilepicture'][0];
			return '<img class="avatar" src="'.$profilepic.'" height="'.$size. '" width="'.$size.'"/>';
		}
		else { 
			 return $avatar;
		}
	}
	/*
	this function shows the added custom profile fields whenever a user profile is viewed or edited.
	*/
	function show_membership_profile_fields($user) { 
	    $options = get_option('rotary_dacdb');
		$useDacDbOption = $options['rotary_use_dacdb'];
		$disabled = '';
		if ('yes' == $useDacDbOption) {
			$disabled = ' disabled="disabled"';
		}
	?>
		<h3>Rotary Membership</h3>

	<table class="form-table">
	<!--Classification-->
		<tr>
			<th><label for="classification">Classification</label></th>

			<td>
            
				<input type="text" name="classification" id="classification" value="<?php echo esc_attr( get_user_meta( $user->ID, 'classification', true ) ); ?>" class="regular-text" <?php echo $disabled;?>/><br />

			</td>
		</tr>
        <!--Club Role-->
        <tr>
			<th><label for="clubrole">Club Role</label></th>

			<td>
				<input type="text" name="clubrole" id="clubrole"  value="<?php echo esc_attr( get_user_meta( $user->ID, 'clubrole', true ) ); ?>" class="regular-text"  <?php echo $disabled;?>/><br />

			</td>
        </tr> 
        <!--Partner Name-->
        <tr>
			<th><label for="partnername">Partner Name</label></th>

			<td>
				<input type="text" name="partnername" id="partnername"  value="<?php echo esc_attr( get_user_meta($user->ID, 'partnername', true ) ); ?>" class="regular-text"  <?php echo $disabled;?>/><br />

			</td>
        </tr> 
        <!--Anniversary Date-->   
        <tr>
			<th><label for="anniversarydate">Anniversary Date</label></th>

			<td>
				<input type="text" name="anniversarydate" id="anniversarydate" class="datepicker" value="<?php echo esc_attr( get_user_meta( $user->ID, 'anniversarydate', true ) ); ?>"  <?php echo $disabled;?> /><br />

			</td>
		</tr>
        <!--Home Phone-->
        <tr>
			<th><label for="homephone">Home Phone</label></th>

			<td>
				<input type="text" name="homephone" id="homephone"  value="<?php echo esc_attr( get_user_meta(  $user->ID, 'homephone', true ) ); ?>" class="regular-text"  <?php echo $disabled;?>/><br />

			</td>
        </tr> 
		<!--Business Phone-->
        <tr>
			<th><label for="businessphone">Business Phone</label></th>

			<td>
				<input type="text" name="businessphone" id="businessphone"  value="<?php echo esc_attr( get_user_meta( $user->ID, 'businessphone', true ) ); ?>" class="regular-text"  <?php echo $disabled;?>/><br />

			</td>
        </tr> 
        <!--Cell Phone-->
        <tr>
			<th><label for="cellphone">Cell Phone</label></th>

			<td>
				<input type="text" name="cellphone" id="cellphone"  value="<?php echo esc_attr( get_user_meta(  $user->ID, 'cellphone', true ) ); ?>" class="regular-text"  <?php echo $disabled;?>/><br />

			</td>
        </tr> 
        
        <!--Street Address 1-->
        <tr>
			<th><label for="streetaddress1">Street Address 1</label></th>

			<td>
				<input type="text" name="streetaddress1" id="streetaddress1"  value="<?php echo esc_attr( get_user_meta( $user->ID, 'streetaddress1', true ) ); ?>" class="regular-text"  <?php echo $disabled;?>/><br />

			</td>
        </tr> 
        <!--Street Address 2-->
        <tr>
			<th><label for="streetaddress2">Street Address 2</label></th>

			<td>
				<input type="text" name="streetaddress2" id="streetaddress2"  value="<?php echo esc_attr( get_user_meta( $user->ID, 'streetaddress2', true ) ); ?>" class="regular-text"  <?php echo $disabled;?>/><br />

			</td>
        </tr> 
        <!--City-->
        <tr>
			<th><label for="city">City</label></th>

			<td>
				<input type="text" name="city" id="city"  value="<?php echo esc_attr( get_user_meta( $user->ID, 'city', true ) ); ?>" class="regular-text"  <?php echo $disabled;?>/><br />

			</td>
        </tr> 
        <!--State / Province-->
        <tr>
			<th><label for="state">State / Province</label></th>

			<td>
				<input type="text" name="state" id="state"  value="<?php echo esc_attr( get_user_meta( $user->ID, 'state', true ) ); ?>" class="regular-text"  <?php echo $disabled;?>/><br />

			</td>
        </tr> 
        <!--County-->
        <tr>
			<th><label for="county">County</label></th>

			<td>
				<input type="text" name="county" id="county"  value="<?php echo esc_attr( get_user_meta( $user->ID, 'county',  true ) ); ?>" class="regular-text"  <?php echo $disabled;?>/><br />

			</td>
        </tr> 
        <!--Postal / Zip-->
        <tr>
			<th><label for="zip">Postal / Zip</label></th>

			<td>
				<input type="text" name="zip" id="zip"  value="<?php echo esc_attr( get_user_meta( $user->ID, 'zip', true ) ); ?>" class="regular-text"  <?php echo $disabled;?>/><br />

			</td>
        </tr> 
        <!--Country-->
        <tr>
			<th><label for="country">Country</label></th>

			<td>
				<input type="text" name="country" id="country"  value="<?php echo esc_attr( get_user_meta(  $user->ID, 'country',true ) ); ?>" class="regular-text"  <?php echo $disabled;?>/><br />

			</td>
        </tr> 
       <!-- Profile Picture -->
       <tr>
			<th><label for="profilepicture">Profile Picture<br/><small>(Clearing this field will cause the profile picture to be refreshed from DaCDb on next update)</small></label></th>
            <td>
        		<div class="uploader">
  					<input type="text" name="profilepicture" id="profilepicture" value="<?php echo esc_attr( get_user_meta( $user->ID, 'profilepicture', true ) ); ?>"  />
  					<input class="button" name="profilepicture_button" id="profilepicture_button" value="Upload"  <?php echo $disabled;?>/>
				</div>
             </td>
            </tr>    
        <!--Company-->
        <tr>
			<th><label for="company">Company</label></th>

			<td>
				<input type="text" name="company" id="company"  value="<?php echo esc_attr( get_user_meta( $user->ID, 'company', true ) ); ?>" class="regular-text"  <?php echo $disabled;?>/><br />

			</td>
        </tr> 
        <!--Job Title-->
        <tr>
			<th><label for="jobtitle">Job Title</label></th>

			<td>
				<input type="text" name="jobtitle" id="jobtitle"  value="<?php echo esc_attr( get_user_meta( $user->ID, 'jobtitle', true ) ); ?>" class="regular-text"  <?php echo $disabled;?>/><br />

			</td>
        </tr> 
        <!--Business Website-->
        <tr>
			<th><label for="jobtitle">Business Website</label></th>

			<td>
				<input type="text" name="busweb" id="busweb"  value="<?php echo esc_attr( get_user_meta( $user->ID, 'busweb', true ) ); ?>" class="regular-text"  <?php echo $disabled;?>/><br />

			</td>
        </tr> 
        <!--Birthday-->   
        <tr>
			<th><label for="birthday">Birthday</label></th>

			<td>
				<input type="text" name="birthday" id="birthday" class="datepicker" value="<?php echo esc_attr( get_user_meta( $user->ID, 'birthday', true ) ); ?>"   <?php echo $disabled;?>/><br />

			</td>
		</tr>
        <!--Member Since-->   
        <tr>
			<th><label for="membersince">Member Since <br/><small>(required for member to appear in member directory)</small></label></th>

			<td>
				<input type="text" name="membersince" id="membersince" class="datepicker" value="<?php echo esc_attr( get_user_meta( $user->ID, 'membersince', true ) ); ?>"  /><br />

			</td>
		</tr>
       
	</table>
	<?php }
	/*
	function to update the custom user profile fields. Verifies user capabilites prior to update.
	*/
	function update_membership_profile_fields( $user_id ) {
			if ( !current_user_can( 'edit_user', $user_id ) ) {
				return FALSE;
			}
			
			$options = get_option('rotary_dacdb');
			if ('yes' != $options['rotary_use_dacdb']) {	
				update_user_meta( $user_id, 'classification', $_POST['classification'] );
				update_user_meta( $user_id, 'clubrole', $_POST['clubrole'] );
				update_user_meta( $user_id, 'anniversarydate', $_POST['anniversarydate'] );
				update_user_meta( $user_id, 'partnername', $_POST['partnername'] );
				update_user_meta( $user_id, 'homephone', $_POST['homephone'] );
				update_user_meta( $user_id, 'businessphone', $_POST['businessphone'] );
				update_user_meta( $user_id, 'cellphone', $_POST['cellphone'] );
				update_user_meta( $user_id, 'streetaddress1', $_POST['streetaddress1'] );
				update_user_meta( $user_id, 'streetaddress2', $_POST['streetaddress2'] );
				update_user_meta( $user_id, 'city', $_POST['city'] );
				update_user_meta( $user_id, 'state', $_POST['state'] );
				update_user_meta( $user_id, 'county', $_POST['county'] );
				update_user_meta( $user_id, 'zip', $_POST['zip'] );
				update_user_meta( $user_id, 'country', $_POST['country'] );
				update_user_meta( $user_id, 'profilepicture', $_POST['profilepicture'] );
				update_user_meta( $user_id, 'company', $_POST['company'] );
				update_user_meta( $user_id, 'jobtitle', $_POST['jobtitle'] );
				update_user_meta( $user_id, 'birthday', $_POST['birthday'] );
				update_user_meta( $user_id, 'membersince', $_POST['membersince'] );
				update_user_meta( $user_id, 'memberyesno', $_POST['memberyesno'] );
				update_user_meta( $user_id, 'busweb', $_POST['busweb'] );
				
			}
			else {
				update_user_meta( $user_id, 'profilepicture', $_POST['profilepicture'] );
				update_user_meta( $user_id, 'membersince', $_POST['membersince'] );
			}
		
	}
	
	// ajax function to return data in form for the participation table (projects)
	function get_form_entries_json( $gf_form_id, $post_id ) {

		$gf_fields = array();
		$gf_headers = array();
		$gf_field_ids = array();
		$i = 0;
		while ( have_rows( 'field_column_display_repeater', $post_id )) : the_row();
			$gf_headers[$i] = get_sub_field( 'form_field_column_header' );
			$gf_field_ids[$i] = get_sub_field( 'form_field_column_selector' );
			$i++;
		endwhile;
		
		$search_criteria = array( 'status' => 'active');
		$sorting         = array( 'key' => $gf_fields[1], 'direction' => 'ASC' );
		$paging 		 = array( 'offset' => 0, 'page_size' => 200 );
		$gf_entries      = GFAPI::get_entries( $gf_form_id, $search_criteria, $sorting, $paging );
		$output = array(
				'sColumns' => implode ( ',', $gf_headers ),
				'sEcho' => isset( $_GET['sEcho'] ) ? intval($_GET['sEcho']) : null,
				'iTotalRecords' => isset( $gf_form_id ) ? GFAPI::count_entries( $gf_form_id ): 0,
				'iTotalDisplayRecords' => 10,
				'aaData' => array()
		);
	
		foreach ( $gf_entries as $gf_entry ) {
			$columnvalue = $this->get_columnvalues( $gf_entry, $gf_field_ids );
			$row = array();
			for ( $i = 0; $i < count( $gf_field_ids ); $i++ ) {
				$row[$i] = $columnvalue[$i];
			};
			$output['aaData'][] = $row;
		}
		return $output;
	}
	
	
	function get_columnvalues( $gf_entry, $gf_field_ids  ) {
		
	$entry_id = $gf_entry["id"];
	$gf_form_id = $gf_entry['form_id'];
	$gf_form = GFAPI::get_form( $gf_form_id );
	// Loop trough all the fields
	foreach( $gf_form["fields"] as $field ) {
		// If the field is one we want to display (array search should return the order (array key)
		$columnnumber = array_search( $field['id'], $gf_field_ids );
		if ( false !== $columnnumber ) :
			// ...we get the value for it
			$field_value = RGFormsModel::get_lead_field_value( $gf_entry, $field );
			
			// If nowrap is set for this field we add a class to it
			// If the field is a product field
			if ($field["type"] == "product" || $field["type"] == "shipping" || $field["type"] == "option") {
				// ...and the value is an array
				if( is_array($field_value) ) {
					// If this product field is an option field
					if( $field["type"] == "option" ) {
						// Remove the price from the string
						foreach ($field_value as &$option) {
							$option = substr($option, 0, strpos($option, "|"));
						}
						// Remove empty values and implode the array
						$field_value = array_filter( $field_value );
						$field_value = implode(", ", $field_value);
					}else{
						// Get the total number of products (last item in array)
						$field_value = end($field_value);
					}
				}else{
					// Remove the price from the string
					$field_value = substr($field_value, 0, strpos($field_value, "|"));
				}
			}
			elseif( $field["type"] == "name" ) {
				// Sort the array by key so that the fields are shown in the correct order
				// Concatenate field values into string separated by a space
                ksort( $field_value );
                $field_values = "";
                 // Concatenate field values into string separated by a space
                foreach ($field_value as $field => $value) {
                      $field_values .= $value . " ";
                }
                $field_value = trim( $field_values );
			}
			// If the value is an array (i.e. address field, name field, etc)
			elseif( is_array($field_value) ) {
				// Sort the array by key so that the fields are shown in the correct order
				// Concatenate field values into string separated by a space
                ksort( $field_value );
                $field_values = "";
                 // Concatenate field values into string separated by a space
                foreach ($field_value as $field => $value) {
                      $field_values .= ( $value) ? $value . ", " : '';
                }
                $field_value =  substr(trim($field_values), 0, strlen(trim($field_values))-1);
			}
			// If the field is a date field we need to format it
			elseif ($field["type"] == "date" && $field_value != "") {
				$field_value = date( 'm/d/Y', strtotime($field_value )); 
			}
	
			// If the field is an URL we need to format it
			elseif ($field["type"] == "website" && $field_value != "") {
				$field_value = "<a href='$field_value'>$field_value</a></td>";
			}
	
			// If the field is a post category we need to remove the ID from the string
			elseif ($field["type"] == "post_category" && $field_value != "") {
				$tdClass = "stickylist-category";
				$field_value = strtok($field_value, ":");
			}
	
			// If the field is a list field we need to unserialize it, flatten the array and implode it into a string
			elseif ($field["type"] == "list" && $field_value != "") {
				if( is_array(maybe_unserialize($field_value ))) {
					$list = maybe_unserialize($field_value);
					$field_value = iterator_to_array(new RecursiveIteratorIterator(new RecursiveArrayIterator( $list )), FALSE);
					$field_value = implode(", ", $field_value);
				}
			}
			$columnvalue[ $columnnumber ] = $field_value;
		endif;
		}
	return $columnvalue;
	}
	
	function get_users_json( $nameorder ) {
		$output = array(
        	'sColumns' => 'Name, Classification, Cell/Home Phone, Business Phone, Phone, Email',
        	'sEcho' => isset( $_GET['sEcho'] ) ? intval($_GET['sEcho']) : null,
			'iTotalRecords' => isset( $rotaryclubmembers ) ? count( $rotaryclubmembers->MEMBER ) : 0,
			'iTotalDisplayRecords' => 10,
			'aaData' => array()
		);
		if (isset( $_GET['id'] ) ) {
			$users = get_users( array(
  				'connected_type' => 'projects_to_users',
  				'connected_items' => $_GET['id'],
				'connected_direction' => 'from',
			));
		}
		elseif ( ! isset($_GET['committees'] ) || $_GET['committees'] == "all" ) {

			$args = array(
				'exclude' => array(
				 1
				)
			);
			$users = get_users( $args );
		}
		else {
			$args = array(
			  	'posts_per_page' => 1,
			 	'post_type' 	 => 'rotary-committees',
			 	'p'              => $_GET['committees']
			);
			$query = new WP_Query( $args );	
				while ( $query->have_posts() ) : $query->the_post();
					$users = get_users( array(
  						'connected_type' => 'committees_to_users',
  						'connected_items' => $query->post->ID,
					    'connected_direction' => 'from',
					));
				
				endwhile;	
				wp_reset_postdata();
			
		}
		
		foreach ( $users as $user ) {
		    
			$usermeta = get_user_meta($user->ID);
			if ( !isset($usermeta['membersince'][0] ) || '' == trim( $usermeta['membersince'][0] )) {
				continue;
			}
			
			if ($nameorder == 'firstname') {
				$memberName = $usermeta['first_name'][0]. ' ' .$usermeta['last_name'][0];
			}
			else {
				$memberName = $usermeta['last_name'][0]. ', ' .$usermeta['first_name'][0];
			}
			$emailname = $usermeta['email'][0];
			$email = count($usermeta['email']) > 0 ? '<a href="mailto:' . antispambot($emailname, 1) .'">Email</a>': '';
			$groupemail = count($usermeta['email']) > 0 ? '<input class="emailselect" type="checkbox"/><span class="emailaddress">'. antispambot($emailname, 1) .'</span>': '';
			
			$cell_home_array = array();
			if( $usermeta['cellphone'][0] ) $cell_home_array[] = $usermeta['cellphone'][0] . '&nbsp;(' . _x( 'c', 'Home/Cellphone Abbreviation', 'rotary' ) .')';
			if( $usermeta['homephone'][0] ) $cell_home_array[] = $usermeta['homephone'][0] . '&nbsp;(' . _x( 'h', 'Home/Cellphone Abbreviation', 'rotary' ) .')';

			$contact_info_array = $cell_home_array;
		//	if( $usermeta['busphone'][0]  ) $contact_info_array[] = $usermeta['busphone'][0] . '&nbsp;(' . _x( 'w', 'Home/Cellphone Abbreviation', 'rotary' ) .')';
			if( $usermeta['email'][0]  ) $contact_info_array[] = '<a class="email" href="mailto:' . antispambot( $emailname, 1 ) .'">' . _x( 'Send Email', 'Email Address Abbreviation', 'rotary' ) .'</a>';			
			
			$cell_home = implode( '<br>', $cell_home_array);
			$contact_info = implode( '<br>', $contact_info_array);
			
			$row =array(
						$groupemail, 
						$memberName, 
						$usermeta['classification'], 
						$usermeta['partnername'], 
						$cell_home, 
						$usermeta['busphone'], 
						$contact_info,
						$email, 
						$user->ID
				);
			if (isset( $_GET['id'] ) ) {
				array_push( $row, 'X');
			}	
			$output['aaData'][] = $row;
			}
		return $output;
	}
	function get_users_details_json( $memberID ) {
		$output = array();
		$user = get_user_by('id', $memberID);
		if (!$user) {
			$output['first_name'] = 'Member is not found';
			return $output;
		}
		$usermeta = get_user_meta($user->ID);
		$memberName = $usermeta['first_name'][0]. ' ' .$usermeta['last_name'][0];
		//$memberAddress = $usermeta['streetaddress1'][0] . ' ' . $usermeta['streetaddress2'][0] . ' ' . $usermeta['city'][0] . ' ' . $usermeta['state'][0]. ' ' . $usermeta['zip'][0];
		$memberAddress = $usermeta['streetaddress1'][0];
		if ($usermeta['streetaddress2'][0] ) {
			$memberAddress .= '<br/>'.$usermeta['streetaddress2'][0];
		}
		if($usermeta['city'][0]) {
			$memberAddress .= '<br/>'. $usermeta['city'][0] . ' ' . $usermeta['state'][0]. ' ' . $usermeta['zip'][0];
		}
		
		$output['ID'] = $memberID;
		$output['memberName'] = $memberName;
		$output['memberAddress'] = $memberAddress;
		$output['classification'] = ($usermeta['classification'])  ? $usermeta['classification'] : '&nbsp;';
		$output['company'] = ($usermeta['company']) ? $usermeta['company'] : '&nbsp;';
		$output['jobTitle'] = ($usermeta['jobtitle']) ? $usermeta['jobtitle'] : '&nbsp;';
		$output['homephone'] = ($usermeta['homephone']) ? $usermeta['homephone'] : '&nbsp;';
		$output['businessphone'] = ($usermeta['busphone'][0]) ? $usermeta['busphone'] : '&nbsp;';
		$output['cellphone'] = (trim($usermeta['cellphone'][0])) ? $usermeta['cellphone'] : '&nbsp;';
		$email = ($usermeta['email']) ? $usermeta['email'][0] : '&nbsp;';
		if ('&nbsp;' == $email) {
			$output['email'] = $email;
		}
		else {
			$output['email'] = '<a href="mailto:' .antispambot($email, 1) .'">'.$email.'</a>';
		}

		$output['anniversarydate_datetime'] = ( $usermeta['anniversarydate'][0] ) ? strtotime( $usermeta['anniversarydate'][0] ): null;
		$output['membersince_datetime'] 	= ( $usermeta['membersince'][0] ) ? strtotime( $usermeta['membersince'][0] ) : null ;
		$output['birthday_datetime'] 		= ( $usermeta['birthday'][0] ) ? strtotime($usermeta['birthday'][0]) : null ;
		
		$output['partnername'] = ($usermeta['partnername'][0]) ? $usermeta['partnername'][0] : '&nbsp;';
		$output['anniversarydate'] = ($usermeta['anniversarydate'][0]) ? date('F d', strtotime($usermeta['anniversarydate'][0])): '&nbsp;';
		$output['membersince'] = ($usermeta['membersince']) ? $usermeta['membersince'] : '&nbsp;';
		$output['profilepicture'] = $usermeta['profilepicture'];
		$output['birthday'] = ($usermeta['birthday'][0]) ? date('F d', strtotime($usermeta['birthday'][0])) : '&nbsp;';
		$output['busweb'] = ($usermeta['busweb']) ? $usermeta['busweb'] : '&nbsp;';
		$output['membersince'] = ($usermeta['membersince'][0]) ?  date('F d Y', strtotime($usermeta['membersince'][0])) : '&nbsp;';

		
		$options = get_option('rotary_dacdb');
		if ('yes' == $options['rotary_use_dacdb']) {
			$output['clubname'] = $usermeta['clubname'];
		}
		else {
			$output['clubname'] = $options['rotary_dacdb_club_name'];
		}
		
		$this->member_profile = $output;
		return $this->member_profile;
	}
	   
		

}//end class
?>