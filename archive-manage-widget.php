<?php
/*
 * Plugin Name:   Archive Manage Widget
 * Version:       1.0
 * Plugin URI:    http://wordpress.org/extend/plugins/archive-manage-widget/
 * Description:   This plugin gives the flebility to effectively manage archives in a sidebar with multiple instances possible. Adjust your settings <a href="options-general.php?page=archive-manage-widget/archive-manage-widget.php">here</a>.
 * Author:        MaxBlogPress
 * Author URI:    http://www.maxblogpress.com
 *
 * License:       GNU General Public License
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * 
 * Copyright (C) 2007 www.maxblogpress.com
 *
 * This is the improved version of "Breukie's Archives Widget" plugin by Arnold Breukhoven
 *
 */
$mbpamw_path      = preg_replace('/^.*wp-content[\\\\\/]plugins[\\\\\/]/', '', __FILE__);
$mbpamw_path      = str_replace('\\','/',$mbpamw_path);
$mbpamw_dir       = substr($mban_path,0,strrpos($mbpamw_path,'/'));
$mbpamw_siteurl   = get_bloginfo('wpurl');
$mbpamw_siteurl   = (strpos($mbpamw_siteurl,'http://') === false) ? get_bloginfo('siteurl') : $mbpamw_siteurl;
$mbpamw_fullpath  = $mbpamw_siteurl.'/wp-content/plugins/'.$mbpamw_dir.'';
$mbpamw_fullpath  = $mbpamw_fullpath.'archive-manage-widget/';
$mbpamw_abspath   = str_replace("\\","/",ABSPATH); 

define('MBP_AMW_ABSPATH', $mbpamw_path);
define('MBP_AMW_LIBPATH', $mbpamw_fullpath);
define('MBP_AMW_SITEURL', $mbpamw_siteurl);
define('MBP_AMW_NAME', 'Archive Manage Widget');
define('MBP_AMW_VERSION', '1.0');  
define('MBP_AMW_LIBPATH', $mbpamw_fullpath);
global $wp_version;

if ($wp_version > '2.3') {
	

	function mbp_amw_options() {
		add_options_page('Archive Manage Widget', 'Archive Manage Widget', 10, __FILE__, 'mbp_amw_activate');
	} 
	
	function mbp_amw_activate() {
		$mbp_amw_activate = get_option('mbp_amw_activate');
		$reg_msg = '';
		$mbp_amw_msg = '';
		$form_1 = 'mbp_amw_reg_form_1';
		$form_2 = 'mbp_amw_reg_form_2';
			// Activate the plugin if email already on list
		if ( trim($_GET['mbp_onlist']) == 1 ) {
			$mbp_amw_activate = 2;
			update_option('mbp_amw_activate', $mbp_amw_activate);
			$reg_msg = 'Thank you for registering the plugin. It has been activated'; 
		} 
		// If registration form is successfully submitted
		if ( ((trim($_GET['submit']) != '' && trim($_GET['from']) != '') || trim($_GET['submit_again']) != '') && $mbp_amw_activate != 2 ) { 
			update_option('mbp_amw_name', $_GET['name']);
			update_option('mbp_amw_email', $_GET['from']);
			$mbp_amw_activate = 1;
			update_option('mbp_amw_activate', $mbp_amw_activate);
		}
		if ( intval($mbp_amw_activate) == 0 ) { // First step of plugin registration
			global $userdata;
			mbp_amwRegisterStep1($form_1,$userdata);
		} else if ( intval($mbp_amw_activate) == 1 ) { // Second step of plugin registration
			$name  = get_option('mbp_amw_name');
			$email = get_option('mbp_amw_email');
			mbp_amwRegisterStep2($form_2,$name,$email);
		} else if ( intval($mbp_amw_activate) == 2 ) { // Options page
				if ( trim($reg_msg) != '' ) {
					echo '<div id="message" class="updated fade"><p><strong>'.$reg_msg.'</strong></p></div>';
				}			
			}
		
		if($mbp_amw_activate != '' && !$_GET['submit']) {
		?>
			
		<div class="wrap">
			<h2><?php echo MBP_AMW_NAME.' '.MBP_AMW_VERSION; ?></h2>
		<strong><img src="<?php echo MBP_AIT_LIBPATH;?>image/how.gif" border="0" align="absmiddle" /> <a href="http://wordpress.org/extend/plugins/archive-manage-widget/other_notes/" target="_blank">How to use it</a>&nbsp;&nbsp;&nbsp;
				<img src="<?php echo MBP_AIT_LIBPATH;?>image/comment.gif" border="0" align="absmiddle" /> <a href="http://www.maxblogpress.com/forum/forumdisplay.php?f=34" target="_blank">Community</a></strong>
		<br/><br/>				
				
				<div id="message" class="updated fade">
					<p>
						<strong>You have already registered. Please go to the <a href="<?php echo MBP_AMW_SITEURL;?>/wp-admin/widgets.php">Widgets</a> section to enable and configure the widget.</strong>
					</p>
				</div>
		</div>	
		<?php
		}	
	}
	function widget_amw( $args, $widget_args = 1 ) {
		extract( $args, EXTR_SKIP );
		if ( is_numeric($widget_args) )
			$widget_args = array( 'number' => $widget_args );
		$widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
		extract( $widget_args, EXTR_SKIP );
	
		$options 			= get_option('widget_amw');
		
		if ( !isset($options[$number]) )
			return;			
			
		//check if registered or not
		$mbp_amw_activate 	= get_option('mbp_amw_activate');				
		if ($mbp_amw_activate == '') {
			echo "Please register in the admin panel to activate the `Archive Manage Widget` widget";
		} else {			
			
	?>
		<?php echo $before_widget; ?>
		<div class="page_manage">
			
			<?php
			//for archive output
				$title 				= empty($options[$number]['title']) ? __('Archives') : $options[$number]['title'];
				$type 				= empty($options[$number]['type']) ? '' : $options[$number]['type'];
				$format 			= empty($options[$number]['format']) ? '' : '&format=' . $options[$number]['format'];
				$limit 				= empty($options[$number]['limit']) ? '' : '&limit=' . $options[$number]['limit'];

				$before 			= empty($options[$number]['before']) ? '' : '&before=' . $options[$number]['before'];

				$after 				= empty($options[$number]['after']) ? '' : '&after=' . $options[$number]['after'];		
				
				$show_post_count = empty($options[$number]['show_post_count']) ? '' : $options[$number]['show_post_count'];					

				echo "<div class='" . $title . "'>" .  $title . "</div>";
				echo '<ul>';
				
				if ($format == '&format=option') {
				?>
				
<select name="archive-dropdown" onChange='document.location.href=this.options[this.selectedIndex].value;'> 
  <option value="">
  		<?php echo attribute_escape(__('Select ' . ucfirst($type))); ?>
  </option> 
  <?php wp_get_archives('type=' . $type . $format . '&show_post_count=' . $show_post_count); ?> 
</select>
			
				<?php	
				} else {
				
				wp_get_archives("type=" 
								. $type 
								. "&show_post_count=" 
								. $show_post_count 
								. $limit 
								. $format 
								. $before 
								. $after);	
				}							
				echo '</ul>';	
			?>
		</div>
		<?php echo $after_widget; ?>
	<?php
		}//user registered or not
	}
	
	function widget_amw_control( $widget_args = 1 ) {
		global $wp_registered_widgets;
		static $updated = false; // Whether or not we have already updated the data after a POST submit
	
		if ( is_numeric($widget_args) )
			$widget_args = array( 'number' => $widget_args );
		$widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
		extract( $widget_args, EXTR_SKIP );
	
		// Data is stored as array:	 array( number => data for that instance of the widget, ... )
		$options = get_option('widget_amw');
		if ( !is_array($options) )
			$options = array();
	
		// We need to update the data
		if ( !$updated && !empty($_POST['sidebar']) ) {
			// Tells us what sidebar to put the data in
			$sidebar = (string) $_POST['sidebar'];
	
			$sidebars_widgets = wp_get_sidebars_widgets();
			if ( isset($sidebars_widgets[$sidebar]) )
				$this_sidebar =& $sidebars_widgets[$sidebar];
			else
				$this_sidebar = array();
	
			foreach ( $this_sidebar as $_widget_id ) {
				if ( 'widget_amw' == $wp_registered_widgets[$_widget_id]['callback'] && isset($wp_registered_widgets[$_widget_id]['params'][0]['number']) ) {
					$widget_number = $wp_registered_widgets[$_widget_id]['params'][0]['number'];
					if ( !in_array( "amw-$widget_number", $_POST['widget-id'] ) ) // the widget has been removed
						unset($options[$widget_number]);
				}
			}
	
			foreach ( (array) $_POST['widget-pmw'] as $widget_number => $widget_amw ) {
				if ( !isset($widget_amw['title']) && isset($options[$widget_number]) ) // user clicked cancel
					continue;
				
				$title 						= wp_specialchars( $widget_amw['title'] );
				$type 						= $widget_amw['type'] ;
				$format 					= $widget_amw['format'] ;
				$limit 						= $widget_amw['limit'];
				$before 					= $widget_amw['before'];
				$after						= $widget_amw['after'];
				$show_post_count			= $widget_amw['show_post_count'];
				
				$image 		= wp_specialchars( $widget_amw['image'] );
				$alt 		= wp_specialchars( $widget_amw['alt'] );
				$link 		= wp_specialchars( $widget_amw['link'] );
				$new_window = isset( $widget_amw['new_window'] );
				$options[$widget_number] 	= compact('image', 
														'alt', 
														'link', 
														'new_window',
														'title', 
														'type',
														'format', 
														'limit',
														'before',
														'after',
														'show_post_count'
														);			
			}
	
			update_option('widget_amw', $options);
			$updated = true; // So that we don't go through this more than once
		}
		
		//print_r($options);
		if ( -1 == $number ) { 
			$title 						= '';
			$type						= '';
			$format 					= '';
			$limit 						= '';
			$before 					= '';
			$after 						= '';
			$show_post_count			= '';
			$image = '';
			$alt = '';
			$link = '';
			$new_window = '';
			$number = '%i%';
		} else {
			$title 						= attribute_escape($options[$number]['title']);
			$type 						= attribute_escape($options[$number]['type']);
			$format 					= attribute_escape($options[$number]['format']);
			$limit 						= attribute_escape($options[$number]['limit']);
			$before 					= attribute_escape($options[$number]['before']);
			$after 						= attribute_escape($options[$number]['after']);		
			$show_post_count 			= attribute_escape($options[$number]['show_post_count']);
			
			$image 		= attribute_escape($options[$number]['image']);
			$alt 		= attribute_escape($options[$number]['alt']);
			$link 		= attribute_escape($options[$number]['link']);
			$new_window = attribute_escape($options[$number]['new_window']);
		}
	?>
			<p>
				<label for="amw-title-<?php echo $number; ?>">
					<?php _e('Title:'); ?>
					<input class="widefat" id="amw-title-<?php echo $number; ?>" name="widget-pmw[<?php echo $number; ?>][title]" type="text" value="<?php echo $title; ?>" />
				</label>
			</p>		
			
			<p>
				<label for="amw-type-<?php echo $number; ?>">
					<?php _e('Type:'); ?>
					<select id="widget-pmw-type-<?php echo $number; ?>" name="widget-pmw[<?php echo $number; ?>][type]">
					<option value="">Select</option>

					<option <?php if ($type == 'monthly') { echo 'selected';}?>  value="monthly">
						Monthly
					</option>
					
					<option <?php if ($type == 'daily') { echo 'selected';}?>  value="daily">
						Daily
					</option>
					
					<option <?php if ($type == 'weekly') { echo 'selected';}?>  value="weekly">
						Weekly
					</option>
					<option <?php if ($type == 'postbypost') { echo 'selected';}?> value="postbypost">
						Post By Post
					</option>					
					</select>
				</label>
			</p>						
			
			<p>
				<label for="amw-limit-<?php echo $number; ?>">
					<?php _e('Limit:'); ?>
					<input class="widefat" id="amw-limit-<?php echo $number; ?>" name="widget-pmw[<?php echo $number; ?>][limit]" type="text" value="<?php echo $limit; ?>" />
				</label>
			</p>			
			
			<p>
				<label for="amw-format-<?php echo $number; ?>">
					<?php _e('Format:'); ?>
					<select id="widget-pmw-format-<?php echo $number; ?>" name="widget-pmw[<?php echo $number; ?>][format]">
						<option value="">Select</option>
	
						<option <?php if ($format == 'html') { echo 'selected';}?>  value="html">
							HTML
						</option>
						
						<option <?php if ($format == 'option') { echo 'selected';}?>  value="option">
							Option
						</option>
						
						<option <?php if ($format == 'link') { echo 'selected';}?>  value="link">
							Link
						</option>
						<option <?php if ($format == 'custom') { echo 'selected';}?> value="custom">
							Custom
						</option>					
					</select>
				</label>
			</p>			
		
			<p>
				<label for="amw-before-<?php echo $number; ?>">
					<?php _e('Before:'); ?>
					<input class="widefat" id="amw-before-<?php echo $number; ?>" name="widget-pmw[<?php echo $number; ?>][before]" type="text" value="<?php echo $before; ?>" />
				</label>
			</p>	
			
			<p>
				<label for="amw-after-<?php echo $number; ?>">
					<?php _e('After:'); ?>
					<input class="widefat" id="amw-after-<?php echo $number; ?>" name="widget-pmw[<?php echo $number; ?>][after]" type="text" value="<?php echo $after; ?>" />
				</label>
			</p>					
			
<p>
				<label for="amw-show_post_count-<?php echo $number; ?>">
					<?php _e('Show Post Counts:'); ?>
					<select id="widget-pmw-show_post_count-<?php echo $number; ?>" name="widget-pmw[<?php echo $number; ?>][show_post_count]">
						<option value="">Select</option>
	
						<option <?php if ($show_post_count == 'yes') { echo 'selected';}?>  value="yes">
							Yes
						</option>
						
						<option <?php if ($show_post_count == 'no') { echo 'selected';}?>  value="no">
							No
						</option>
						
					</select>
				</label>
			</p>			
						
<style type="text/css">
<!--
#wpcontent select {
	height:auto;
}
-->
</style>		
				
			<input type="hidden" id="widget-amw-submit-<?php echo $number; ?>" name="widget-amw[<?php echo $number; ?>][submit]" value="1" />
	<?php
	}
	
	// Registers each instance of widget on startup
	function widget_amw_register() {
		if ( !$options = get_option('widget_amw') )
			$options = array();
	
		$widget_ops = array('classname' => 'widget_amw', 'description' => __('Archive Management'));
		$control_ops = array( 'id_base' => 'amw');
		$name = __(MBP_AMW_NAME);
	
		$registered = false;
		foreach ( array_keys($options) as $o ) {
			// Old widgets can have null values for some reason
			if ( !isset($options[$o]['image']) )
				continue;
	
			$id = "amw-$o"; // Never never never translate an id
			$registered = true;
			wp_register_sidebar_widget( $id, $name, 'widget_amw', $widget_ops, array( 'number' => $o ) );
			wp_register_widget_control( $id, $name, 'widget_amw_control', $control_ops, array( 'number' => $o ) );
		}
	
		// If there are none, we register the widget's existance with a generic template
		if ( !$registered ) {
			wp_register_sidebar_widget( 'amw-1', $name, 'widget_amw', $widget_ops, array( 'number' => -1 ) );
			wp_register_widget_control( 'amw-1', $name, 'widget_amw_control', $control_ops, array( 'number' => -1 ) );
		}
	}
	
	
// Srart Registration.

/**
 * Plugin registration form
 */
function mbp_amwRegistrationForm($form_name, $submit_btn_txt='Register', $name, $email, $hide=0, $submit_again='') {
	$wp_url = get_bloginfo('wpurl');
	$wp_url = (strpos($wp_url,'http://') === false) ? get_bloginfo('siteurl') : $wp_url;
	$plugin_pg    = 'options-general.php';
	$thankyou_url = $wp_url.'/wp-admin/'.$plugin_pg.'?page='.$_GET['page'];
	$onlist_url   = $wp_url.'/wp-admin/'.$plugin_pg.'?page='.$_GET['page'].'&amp;mbp_onlist=1';
	if ( $hide == 1 ) $align_tbl = 'left';
	else $align_tbl = 'center';
	?>
	
	<?php if ( $submit_again != 1 ) { ?>
	<script><!--
	function trim(str){
		var n = str;
		while ( n.length>0 && n.charAt(0)==' ' ) 
			n = n.substring(1,n.length);
		while( n.length>0 && n.charAt(n.length-1)==' ' )	
			n = n.substring(0,n.length-1);
		return n;
	}
	function mbp_amwValidateForm_0() {
		var name = document.<?php echo $form_name;?>.name;
		var email = document.<?php echo $form_name;?>.from;
		var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
		var err = ''
		if ( trim(name.value) == '' )
			err += '- Name Required\n';
		if ( reg.test(email.value) == false )
			err += '- Valid Email Required\n';
		if ( err != '' ) {
			alert(err);
			return false;
		}
		return true;
	}
	//-->
	</script>
	<?php } ?>
	<table align="<?php echo $align_tbl;?>">
	<form name="<?php echo $form_name;?>" method="post" action="http://www.aweber.com/scripts/addlead.pl" <?php if($submit_again!=1){;?>onsubmit="return mbp_amwValidateForm_0()"<?php }?>>
	 <input type="hidden" name="unit" value="maxbp-activate">
	 <input type="hidden" name="redirect" value="<?php echo $thankyou_url;?>">
	 <input type="hidden" name="meta_redirect_onlist" value="<?php echo $onlist_url;?>">
	 <input type="hidden" name="meta_adtracking" value="mr-posr-ordering">
	 <input type="hidden" name="meta_message" value="1">
	 <input type="hidden" name="meta_required" value="from,name">
	 <input type="hidden" name="meta_forward_vars" value="1">	
	 <?php if ( $submit_again == 1 ) { ?> 	
	 <input type="hidden" name="submit_again" value="1">
	 <?php } ?>		 
	 <?php if ( $hide == 1 ) { ?> 
	 <input type="hidden" name="name" value="<?php echo $name;?>">
	 <input type="hidden" name="from" value="<?php echo $email;?>">
	 <?php } else { ?>
	 <tr><td>Name: </td><td><input type="text" name="name" value="<?php echo $name;?>" size="25" maxlength="150" /></td></tr>
	 <tr><td>Email: </td><td><input type="text" name="from" value="<?php echo $email;?>" size="25" maxlength="150" /></td></tr>
	 <?php } ?>
	 <tr><td>&nbsp;</td><td><input type="submit" name="submit" value="<?php echo $submit_btn_txt;?>" class="button" /></td></tr>
	 </form>
	</table>
	<?php
}

/**
 * Register Plugin - Step 2
 */
function mbp_amwRegisterStep2($form_name='frm2',$name,$email) {
	$msg = 'You have not clicked on the confirmation link yet. A confirmation email has been sent to you again. Please check your email and click on the confirmation link to activate the plugin.';
	if ( trim($_GET['submit_again']) != '' && $msg != '' ) {
		echo '<div id="message" class="updated fade"><p><strong>'.$msg.'</strong></p></div>';
	}
	?>
	<style type="text/css">
	table, tbody, tfoot, thead {
		padding: 8px;
	}
	tr, th, td {
		padding: 0 8px 0 8px;
	}
	</style>
	<div class="wrap"><h2> <?php echo MBP_AMW_NAME.' '.MBP_AMW_VERSION; ?></h2>
	 <center>
	 <table width="100%" cellpadding="3" cellspacing="1" style="border:1px solid #e3e3e3; padding: 8px; background-color:#f1f1f1;">
	 <tr><td align="center">
	 <table width="650" cellpadding="5" cellspacing="1" style="border:1px solid #e9e9e9; padding: 8px; background-color:#ffffff; text-align:left;">
	  <tr><td align="center"><h3>Almost Done....</h3></td></tr>
	  <tr><td><h3>Step 1:</h3></td></tr>
	  <tr><td>A confirmation email has been sent to your email "<?php echo $email;?>". You must click on the link inside the email to activate the plugin.</td></tr>
	  <tr><td><strong>The confirmation email will look like:</strong><br /><img src="http://www.maxblogpress.com/images/activate-plugin-email.jpg" vspace="4" border="0" /></td></tr>
	  <tr><td>&nbsp;</td></tr>
	  <tr><td><h3>Step 2:</h3></td></tr>
	  <tr><td>Click on the button below to Verify and Activate the plugin.</td></tr>
	  <tr><td><?php mbp_amwRegistrationForm($form_name.'_0','Verifyg and Activate',$name,$email,$hide=1,$submit_again=1);?></td></tr>
	 </table>
	 </td></tr></table><br />
	 <table width="100%" cellpadding="3" cellspacing="1" style="border:1px solid #e3e3e3; padding:8px; background-color:#f1f1f1;">
	 <tr><td align="center">
	 <table width="650" cellpadding="5" cellspacing="1" style="border:1px solid #e9e9e9; padding:8px; background-color:#ffffff; text-align:left;">
	   <tr><td><h3>Troubleshooting</h3></td></tr>
	   <tr><td><strong>The confirmation email is not there in my inbox!</strong></td></tr>
	   <tr><td>Dont panic! CHECK THE JUNK, spam or bulk folder of your email.</td></tr>
	   <tr><td>&nbsp;</td></tr>
	   <tr><td><strong>It's not there in the junk folder either.</strong></td></tr>
	   <tr><td>Sometimes the confirmation email takes time to arrive. Please be patient. WAIT FOR 6 HOURS AT MOST. The confirmation email should be there by then.</td></tr>
	   <tr><td>&nbsp;</td></tr>
	   <tr><td><strong>6 hours and yet no sign of a confirmation email!</strong></td></tr>
	   <tr><td>Please register again from below:</td></tr>
	   <tr><td><?php mbp_amwRegistrationForm($form_name,'Register Again',$name,$email,$hide=0,$submit_again=2);?></td></tr>
	   <tr><td><strong>Help! Still no confirmation email and I have already registered twice</strong></td></tr>
	   <tr><td>Okay, please register again from the form above using a DIFFERENT EMAIL ADDRESS this time.</td></tr>
	   <tr><td>&nbsp;</td></tr>
	   <tr>
		 <td><strong>Why am I receiving an error similar to the one shown below?</strong><br />
			 <img src="http://www.maxblogpress.com/images/no-verification-error.jpg" border="0" vspace="8" /><br />
		   You get that kind of error when you click on &quot;Verify and Activate&quot; button or try to register again.<br />
		   <br />
		   This error means that you have already subscribed but have not yet clicked on the link inside confirmation email. In order to  avoid any spam complain we don't send repeated confirmation emails. If you have not recieved the confirmation email then you need to wait for 12 hours at least before requesting another confirmation email. </td>
	   </tr>
	   <tr><td>&nbsp;</td></tr>
	   <tr><td><strong>But I've still got problems.</strong></td></tr>
	   <tr><td>Stay calm. <strong><a href="http://www.maxblogpress.com/contact-us/" target="_blank">Contact us</a></strong> about it and we will get to you ASAP.</td></tr>
	 </table>
	 </td></tr></table>
	 </center>		
	<p style="text-align:center;margin-top:3em;"><strong><?php echo MBP_AMW_NAME.' '.MBP_AMW_VERSION; ?> by <a href="http://www.maxblogpress.com/" target="_blank" >MaxBlogPress</a></strong></p>
	</div>
	<?php
}

/**
 * Register Plugin - Step 1
 */
function mbp_amwRegisterStep1($form_name='frm1',$userdata) {
	$name  = trim($userdata->first_name.' '.$userdata->last_name);
	$email = trim($userdata->user_email);
	?>
	<style type="text/css">
	tabled , tbody, tfoot, thead {
		padding: 8px;
	}
	tr, th, td {
		padding: 0 8px 0 8px;
	}
	</style>
	<div class="wrap"><h2> <?php echo MBP_AMW_NAME.' '.MBP_AMW_VERSION; ?></h2>
	 <center>
	 <table width="100%" cellpadding="3" cellspacing="1" style="border:2px solid #e3e3e3; padding: 8px; background-color:#f1f1f1;">
	  <tr><td align="center">
		<table width="548" align="center" cellpadding="3" cellspacing="1" style="border:1px solid #e9e9e9; padding: 8px; background-color:#ffffff;">
		  <tr><td align="center"><h3>Please register the plugin to activate it. (Registration is free)</h3></td></tr>
		  <tr><td align="left">In addition you'll receive complimentary subscription to MaxBlogPress Newsletter which will give you many tips and tricks to attract lots of visitors to your blog.</td></tr>
		  <tr><td align="center"><strong>Fill the form below to register the plugin:</strong></td></tr>
		  <tr><td align="center"><?php mbp_amwRegistrationForm($form_name,'Register',$name,$email);?></td></tr>
		  <tr><td align="center"><font size="1">[ Your contact information will be handled with the strictest confidence <br />and will never be sold or shared with third parties ]</font></td></tr>
		</table>
	  </td></tr></table>
	 </center>
	<p style="text-align:center;margin-top:3em;"><strong><?php echo MBP_AMW_NAME.' '.MBP_AMW_VERSION; ?> by <a href="http://www.maxblogpress.com/" target="_blank" >MaxBlogPress</a></strong></p>
	</div>
	<?php
}	
	
	// add a option page
	add_action('admin_menu', 'mbp_amw_options');
	// Hook for the registration
	add_action( 'widgets_init', 'widget_amw_register' );
} else if ($wp_version < '2.5') {
function widget_amw_init()
{
	// Check for the required API functions
	if ( !function_exists('register_sidebar_widget') || !function_exists('register_widget_control') )
		return;

function widget_amw($args, $number = 1) {
	extract($args);
	$options = get_option('widget_amw');
	$aantalarchief = empty($options[$number]['aantalarchief']) ? '' : $options[$number]['aantalarchief'];
	$title = empty($options[$number]['title']) ? __('Archives') : $options[$number]['title'];
// Extraatjes
	$type = empty($options[$number]['type']) ? '' : $options[$number]['type'];
	$limit = empty($options[$number]['limit']) ? '' : '&limit=' . $options[$number]['limit'];
	$format = empty($options[$number]['format']) ? '' : '&format=' . $options[$number]['format'];
	$before = empty($options[$number]['before']) ? '' : '&before=' . $options[$number]['before'];
	$after = empty($options[$number]['after']) ? '' : '&after=' . $options[$number]['after'];

	echo $before_widget . $title;
	
	if ($format == '&format=option') {
		?>
<br/>		
<select name="archive-dropdown" onChange='document.location.href=this.options[this.selectedIndex].value;'> 
<option value="">
<?php echo attribute_escape(__('Select ' . ucfirst($type))); ?>
</option> 
<?php wp_get_archives('type=' . $type . $format . '&show_post_count=' . $show_post_count); ?> 
</select>
	
		<?php	
		} else { 	
	
		wp_get_archives("type=" . $type . "&show_post_count=" . $aantalarchief . $limit . $format . $before . $after);
	
		echo $after_widget;
	}
}

function widget_amw_control($number) {
	$options = $newoptions = get_option('widget_amw');
	if ( $_POST["amw-submit-$number"] ) {
		$newoptions[$number]['aantalarchief'] = strip_tags(stripslashes($_POST["amw-aantalarchief-$number"]));
		$newoptions[$number]['title'] = stripslashes($_POST["amw-title-$number"]);
// Extraatjes
		$newoptions[$number]['type'] = strip_tags(stripslashes($_POST["amw-type-$number"]));
		$newoptions[$number]['limit'] = strip_tags(stripslashes($_POST["amw-limit-$number"]));
		$newoptions[$number]['format'] = stripslashes($_POST["amw-format-$number"]);
		$newoptions[$number]['before'] = strip_tags(stripslashes($_POST["amw-before-$number"]));
		$newoptions[$number]['after'] = strip_tags(stripslashes($_POST["amw-after-$number"]));
	}
	if ( $options != $newoptions ) {
		$options = $newoptions;
		update_option('widget_amw', $options);
	}
	$aantalarchief = htmlspecialchars($options[$number]['aantalarchief'], ENT_QUOTES);
	$title = htmlspecialchars($options[$number]['title'], ENT_QUOTES);
// Extraatjes
	$type = htmlspecialchars($options[$number]['type'], ENT_QUOTES);
	$limit = htmlspecialchars($options[$number]['limit'], ENT_QUOTES);
	$format = htmlspecialchars($options[$number]['format'], ENT_QUOTES);
	$before = htmlspecialchars($options[$number]['before'], ENT_QUOTES);
	$after = htmlspecialchars($options[$number]['after'], ENT_QUOTES);

?>
<center>Check <a href="http://codex.wordpress.org/Template_Tags/wp_get_archives" target="_blank">wp_get_archives</a> for help with these parameters.</center>
<br />
<table align="center" cellpadding="1" cellspacing="1" width="400">
<tr>
<td align="left" valign="middle" width="90" nowrap="nowrap">
Title Widget:
</td>
<td align="left" valign="middle">
<input style="width: 300px;" id="amw-title-<?php echo "$number"; ?>" name="amw-title-<?php echo "$number"; ?>" type="text" value="<?php echo $title; ?>" />
</td>
</tr>
<tr>
<td align="left" valign="middle" width="90" nowrap="nowrap">
Type:
</td>
<td align="left" valign="middle">
<select id="amw-type-<?php echo "$number"; ?>" name="amw-type-<?php echo "$number"; ?>" value="<?php echo $options[$number]['type']; ?>">
<?php echo "<option value=\"\">Select</option>"; ?>
<?php echo "<option value=\"monthly\"" . ($options[$number]['type']=='monthly' ? " selected='selected'" : '') .">Monthly</option>"; ?>
<?php echo "<option value=\"daily\"" . ($options[$number]['type']=='daily' ? " selected='selected'" : '') .">Daily</option>"; ?>
<?php echo "<option value=\"weekly\"" . ($options[$number]['type']=='weekly' ? " selected='selected'" : '') .">Weekly</option>"; ?>
<?php echo "<option value=\"postbypost\"" . ($options[$number]['type']=='postbypost' ? " selected='selected'" : '') .">Post by Post</option>"; ?>
</select>
</td>
</tr>
<tr>
<td align="left" valign="middle" width="90" nowrap="nowrap">
Limit:
</td>
<td align="left" valign="middle">
<input style="width: 300px;" id="amw-limit-<?php echo "$number"; ?>" name="amw-limit-<?php echo "$number"; ?>" type="text" value="<?php echo $limit; ?>" />
</td>
</tr>
<tr>
<td align="left" valign="middle" width="90" nowrap="nowrap">
Format:
</td>
<td align="left" valign="middle">
<select id="amw-format-<?php echo "$number"; ?>" name="amw-format-<?php echo "$number"; ?>" value="<?php echo $options[$number]['format']; ?>">
<?php echo "<option value=\"\">Select</option>"; ?>
<?php echo "<option value=\"html\"" . ($options[$number]['format']=='html' ? " selected='selected'" : '') .">HTML</option>"; ?>
<?php echo "<option value=\"option\"" . ($options[$number]['format']=='option' ? " selected='selected'" : '') .">Option</option>"; ?>
<?php echo "<option value=\"link\"" . ($options[$number]['format']=='link' ? " selected='selected'" : '') .">Link</option>"; ?>
<?php echo "<option value=\"custom\"" . ($options[$number]['format']=='custom' ? " selected='selected'" : '') .">Custom</option>"; ?>
</select>
</td>
</tr>
<tr>
<td align="left" valign="middle" width="90" nowrap="nowrap">
Before:
</td>
<td align="left" valign="middle">
<input style="width: 300px;" id="amw-before-<?php echo "$number"; ?>" name="amw-before-<?php echo "$number"; ?>" type="text" value="<?php echo $before; ?>" />
</td>
</tr>
<tr>
<td align="left" valign="middle" width="90" nowrap="nowrap">
After:
</td>
<td align="left" valign="middle">
<input style="width: 300px;" id="amw-after-<?php echo "$number"; ?>" name="amw-after-<?php echo "$number"; ?>" type="text" value="<?php echo $after; ?>" />
</td>
</tr>
<tr>
<td align="left" valign="middle" width="90" nowrap="nowrap">
Show Post Counts:
</td>
<td align="left" valign="middle">
<select id="amw-aantalarchief-<?php echo "$number"; ?>" name="amw-aantalarchief-<?php echo "$number"; ?>" value="<?php echo $options[$number]['aantalarchief']; ?>">
<?php echo "<option value=\"\">Select</option>"; ?>
<?php echo "<option value=\"1\"" . ($options[$number]['aantalarchief']=='1' ? " selected='selected'" : '') .">YES</option>"; ?>
<?php echo "<option value=\"0\"" . ($options[$number]['aantalarchief']=='0' ? " selected='selected'" : '') .">NO</option>"; ?>
</select>
<input type="hidden" id="amw-submit-<?php echo "$number"; ?>" name="amw-submit-<?php echo "$number"; ?>" value="1" />
</td>
</tr>
</table>
<br />

<?php
}

function widget_amw_setup() {
	$options = $newoptions = get_option('widget_amw');
	if ( isset($_POST['amw-number-submit']) ) {
		$number = (int) $_POST['amw-number'];
		if ( $number > 9 ) $number = 9;
		if ( $number < 1 ) $number = 1;
		$newoptions['number'] = $number;
	}
	if ( $options != $newoptions ) {
		$options = $newoptions;
		update_option('widget_amw', $options);
		widget_amw_register($options['number']);
	}
}

function widget_amw_page() {
	$options = $newoptions = get_option('widget_amw');
?>
	<div class="wrap">
		<form method="POST">
			<h2>Archive Manage Widgets</h2>
			<p style="line-height: 30px;"><?php _e('How many Archive widgets would you like?'); ?>
			<select id="amw-number" name="amw-number" value="<?php echo $options['number']; ?>">
<?php for ( $i = 1; $i < 10; ++$i ) echo "<option value='$i' ".($options['number']==$i ? "selected='selected'" : '').">$i</option>"; ?>
			</select>
			<span class="submit"><input type="submit" name="amw-number-submit" id="amw-number-submit" value="<?php _e('Save'); ?>" /></span></p>
		</form>
	</div>
<?php
}

function widget_amw_register() {
	$options = get_option('widget_amw');
	$number = $options['number'];
	if ( $number < 1 ) $number = 1;
	if ( $number > 9 ) $number = 9;
	for ($i = 1; $i <= 9; $i++) {
		$name = array('Archive Manage Widget%s', null, $i);
		
		if ($wp_version == '2.2') {
			register_sidebar_widget($name, $i <= $number ? 'widget_amw' : /* unregister */ '','', $i);
		} else if ($wp_version == '2.3') {
			register_sidebar_widget($name, $i <= $number ? 'widget_amw' : /* unregister */ '', $i);
		} else {
			register_sidebar_widget($name, $i <= $number ? 'widget_amw' : /* unregister */ '','', $i);				
		}			
		
		register_widget_control($name, $i <= $number ? 'widget_amw_control' : /* unregister */ '', 460, 260, $i);
	}
	add_action('sidebar_admin_setup', 'widget_amw_setup');
	add_action('sidebar_admin_page', 'widget_amw_page');
}
// Delay plugin execution to ensure Dynamic Sidebar has a chance to load first
widget_amw_register();
}

// Tell Dynamic Sidebar about our new widget and its control
add_action('plugins_loaded', 'widget_amw_init');
}
?>