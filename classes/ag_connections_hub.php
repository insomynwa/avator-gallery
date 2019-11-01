<?php
/*	CLASS MANAGING EXTERNAL CONNECTIONS REQUIRING COMPLEX / MULTI-STEP AUTHORIZATIONS
 *	html part showing wizard in magnificPopup through ajax + PHP logical part
 */

class ag_connection_hub {
	
	public $gid; // (int) gallery ID
	public $src; // (string) images source type
	public $to_consider = array( // types to consider for the hub
		'fb',
		//'picasa',
		'g_drive',
		'onedrive',
	); 
	
	private $tax_name = 'ag_connect_hub'; // connections taxonomy name
	private $main_term_id; // ID of specific source's main term
	
	public function __construct($gid, $src = false) {
		$this->gid = $gid;
		$this->src = (empty($src)) ? get_post_meta($gid, 'ag_type', true) : $src;
	}
	
	
	
	/*****************************************************************************************************************
	**************** WIZARD PART *****************
	**********************************************/
	
	// PRINT CODE
	// shows saved connections for selected source
	// JS code to add and call ajax script
	public function wizard() {
		include_once(AG_DIR .'/functions.php');
		$connections = $this->src_connections();

		// start code
		$code = '
		<h3>'. $this->source_name() .'</h3>';
		
		// list connections
		if(is_array($connections) && count($connections)) {
			$code .= '<table>';
			
			foreach($connections as $conn) {
				$term = get_term_by('id', $conn, $this->tax_name);
				
				$code .= '
				<tr rel="'. $conn .'">
					<td><span class="lcwp_del_row" title="'. __('delete connection', 'ag_ml') .'"></span></td>
					<td>'. $term->name .'</td>
				</tr>';	
			}
			
			$code .= '</table>';
		}
		else {
			$code .= '<p><em>'. __('no connections', 'ag_ml') .' ..</em><br/><br/></p>';	
		}
		
		$code .= '
		<div id="ag_add_conn_wrap">
			<span>'. __('add connection', 'ag_ml') .'</span>
			<form id="ag_add_conn_form" style="display: none;">'. $this->add_conn_form() .'</form>
		</div>';
		
		echo $code;
		?>
		<script type="text/javascript">
		
		// show add-conn form
		jQuery(document).delegate('#ag_add_conn_wrap > span', 'click', function()  {
			jQuery(this).slideUp();
			jQuery(this).parent().find('form').slideDown();
		});
		</script>
		<?php
	}
	
	
	/* form code with specific fields - source based */
	private function add_conn_form() {
		$form = '
		<p>
			<label>'. __('Connection Name', 'ag_ml') .'</label>
			<input type="text" name="conn_name" autocomplete="off" maxlength="250" />
		</p>';
		
		switch($this->src) {
			case 'fb' :
				$form .= '
				<p style="display: none;">
					<label>'. __('Select connetion type', 'ag_ml') .'<label>
					<select name="fb_src_switch" id="fb_src_switch" autocomplete="off">
						<option value="page">'. __("Facebook page", 'ag_ml') .'</option>
						<option value="profile">'. __("Facebook profile", 'ag_ml') .'</option>
					</select>
				</p>
				
				<p class="fbss_page">
					<label>'. __('Page URL', 'ag_ml') .'</label>
					<input name="fb_page_url" type="text" />
				</p>
				<p class="fbss_profile" style="display: none;">
					<label>'. __('Facebook Token', 'ag_ml') .'</label>
					<input name="fb_token" type="text" autocomplete="off" /> <br/>
					<a href="" target="_blank">'. __("Get your Facebook token", 'ag_ml') .' &raquo;</a>
				</p>
				<script type="text/javascript">
				jQuery(document).ready(function() {
					jQuery(document).delegate("#fb_src_switch", "change", function() {
						if(jQuery(this).val() == "page") {
							jQuery(".fbss_page").show();	
							jQuery(".fbss_profile").hide();		
						} else {
							jQuery(".fbss_page").hide();	
							jQuery(".fbss_profile").show();			
						}
					});
				});
				</script>';
				break;	
			
			
			
			case 'picasa' :
				include_once(AG_DIR . '/classes/source_helpers/gplus_integration.php');
				$gplus = new ag_gplus_integration($this->gid);
				
				$form .= '
				<p>
					<label>'. __('Google Username', 'ag_ml') .'</label>
					<input name="gplus_user" type="text" />
				</p>
				<p>
					<label>'. __('Google Token', 'ag_ml') .'</label>
					<input name="gplus_token" type="text" autocomplete="off" /> <br/>
					<a href="'. $gplus->accept_app() .'" target="_blank">'. __("Get your Google+ token", 'ag_ml') .' &raquo;</a>
				</p>';
				break;	
			
			
			
			case 'g_drive' :
				include_once(AG_DIR . '/classes/source_helpers/gdrive_integration.php');
				$gdrive = new ag_gdrive_integration($this->gid);
				
				$form .= '
				<p>
					<label>'. __('Google Username', 'ag_ml') .'</label>
					<input name="gdrive_user" type="text" />
				</p>
				<p>
					<label>'. __('Google Token', 'ag_ml') .'</label>
					<input name="gdrive_token" type="text" autocomplete="off" /> <br/>
					<a href="'. $gdrive->accept_app() .'" target="_blank">'. __("Get your Google Drive token", 'ag_ml') .' &raquo;</a>
				</p>';
				break;	
				
				
				
			case 'onedrive' :
				include_once(AG_DIR . '/classes/source_helpers/onedrive_integration.php');
				$onedrive = new ag_onedrive_integration($this->gid);
				
				$form .= '
				<p>
					<label>'. __('Microsoft Username', 'ag_ml') .'</label>
					<input name="onedrive_user" type="text" />
				</p>
				<p>
					<label>'. __('Microsoft Token', 'ag_ml') .'</label>
					<input name="onedrive_token" type="text" autocomplete="off" /> <br/>
					<a href="'. $onedrive->accept_app() .'" target="_blank">'. __("Get your OneDrive token", 'ag_ml') .' &raquo;</a>
				</p>';
				break;	
				
			
			default : return 'invalid source'; break;
		}

		
		$form .= '
		<section></section>
		<input type="button" value="'. __('submit', 'ag_ml') .'" name="ag_conn_hub_submit" id="ag_conn_hub_submit" class="button-primary" />';
		
		return $form;
	}
	
	
	/* be sure main term for this source exists - eventually create it - put it in class property */
	private function main_term_exists() {
		$slug = 'ag-'. $this->src;  
		$result = term_exists($slug, $this->tax_name);
		
		// create it
		if(empty($result)) {
			$inserted = wp_insert_term($slug, $this->tax_name, array('slug' => $slug));
			
			if(is_wp_error($inserted)) {
				trigger_error( $inserted->get_error_message(), E_USER_ERROR);
				return false;		
			}
			
			$this->main_term_id = $inserted->term_id;
		}
		else {
			$this->main_term_id = $result['term_id'];
		}
		
		return $this->main_term_id;
	}
	
	
	/* retrieve connections (terms) for a specific source */
	private function src_connections() {
		$this->main_term_exists();
		return get_term_children($this->main_term_id, $this->tax_name);	
	}
	
	
	/* source clear name */
	private function source_name() {
		switch($this->src) {
			case 'fb' 			: return 'Facebook'; 			break;	
			case 'picasa' 		: return 'Google+'; 			break;	
			case 'g_drive'		: return 'Google Drive';		break;
			case 'onedrive'		: return 'Microsoft OneDrive';	break;
		}
	}
	

	/* CONNECTIONS DROPDOWN for chosen type - used to connect */
	public function src_connections_dd() {
		$connections = $this->src_connections();
		$sel_opt = get_post_meta($this->gid, 'ag_connect_id', true);
		
		if(!is_array($connections) || !count($connections)) {	
			$dd = '<span style="display: block;">'. __('No connections for this source','ag_ml'). ' ..</span>';
		}
		else {
			$dd = '
			<label>'. __('Choose connection') .'</label>
			<select name="ag_connect_id" id="ag_connect_id" class="lcweb-chosen" autocomplete="off" data-placeholder="'. __('Select a connection', 'ag_ml') .' ..">';
			
			foreach($connections as $conn) {
				$term = get_term_by('id', $conn, $this->tax_name);
				
				$sel = ($sel_opt == $conn) ? 'selected="selected"' : '';	
				$dd .= '<option value="'.$conn.'" '.$sel.'>'. $term->name .'</option>';
			}
			
			$dd .= '</select>';
		}
		
		return $dd . 
				'<a href="#ag_conn_hub_wizard_wrap" id="ag_launch_conn_wizard">'. __("manage connections", 'ag_ml') .'</a>' .
				'<div id="ag_conn_hub_wizard_outer_wrap" style="display: none;">
					<div id="ag_conn_hub_wizard_wrap"></div>
				</div>';
	}



	
	/*****************************************************************************************************************
	**************** LOGICAL PART *****************
	**********************************************/
	
	
	// associative array containing fetched data for connection setup
	public $ajax_data; 
	
	// variable containing data retrieved from test_connection()
	private $connect_data;
	
	// created (term) connection ID
	public $connect_id;
	 
	
	
	/* 
	 * WRAP-UP function to be used in AJAX - validate / tries connection / saves data 
	 * @return (string) succcess if everything is ok - otherwise the error message
	 */
	public function setup_connection() {
		/* debug
		ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
		error_reporting(E_ALL);*/
		
		$validation = $this->handle_data();
		if($validation !== true) {return $validation;}
		
		$conn_check = $this->test_connection();
		if($conn_check !== true) {return $conn_check;}
		
		//
		$saving = $this->save_connection();
		if($saving !== true) {return $saving;}
		
		return 'success';
	}
	
	
	
	/* 
	 * HANDLE DATA - passed via ajax and different for every source 
	 * @return (true/string) true if data has been handled successfully - otherwise error validation message
	 */
	private function handle_data() {
		include_once('simple_form_validator.php');
		
		$validator = new simple_fv;
		$indexes = array();
		$indexes[] = array('index'=>'conn_name', 'label'=>__('Connection Name', 'ag_ml'), 'max_len'=>250, 'required'=>true);
		
		switch($this->src) {
			case 'fb' :
				$indexes[] = array('index'=>'fb_src_switch', 'label'=>__('Connection type', 'ag_ml'), 'required'=>true);
				
				if(isset($_POST['fb_src_switch']) && $_POST['fb_src_switch'] == 'page') {
					$indexes[] = array('index'=>'fb_page_url', 'label'=>__('Page URL', 'ag_ml'), 'type'=>'url', 'required'=>true);	
				} else {
					$indexes[] = array('index'=>'fb_token', 'label'=>__('Connection type', 'ag_ml'), 'required'=>true);
				}
				break;
			
			case 'picasa' :
				$indexes[] = array('index'=>'gplus_user', 'label'=>__('Google Username', 'ag_ml'), 'required'=>true);
				$indexes[] = array('index'=>'gplus_token', 'label'=>__('Google Token', 'ag_ml'), 'required'=>true);
				break;	
			
			case 'g_drive' :
				$indexes[] = array('index'=>'gdrive_user', 'label'=>__('Google Username', 'ag_ml'), 'required'=>true);
				$indexes[] = array('index'=>'gdrive_token', 'label'=>__('Google Token', 'ag_ml'), 'required'=>true);
				break;	
				
			case 'onedrive' :
				$indexes[] = array('index'=>'onedrive_user', 'label'=>__('Microsoft Username', 'ag_ml'), 'required'=>true);
				$indexes[] = array('index'=>'onedrive_token', 'label'=>__('Microsoft Token', 'ag_ml'), 'required'=>true);
				break;	
		}

		$validator->formHandle($indexes);
		$error = $validator->getErrors();
		
		if(!empty($error)) {
			return $error;	
		} 
		else {
			$this->ajax_data = $validator->form_val;	
			return true;
		}
	}
	
	
	
	/* 
	 * TEST SOURCE CONNECTION 
	 * @return (true/string) true if data has been handled successfully - otherwise error validation message
	 */
	public function test_connection() {
		$data = $this->ajax_data;

		switch($this->src) {
			
			case 'fb' :
				include_once(AG_DIR . '/classes/source_helpers/fb_integration.php');

				// facebook PAGE
				if($data['fb_src_switch'] == 'page') {
					$fb = new ag_facebook_integration(false, $data);	
					
					$page_id = $fb->page_url_to_id($data['fb_page_url']);
					if(!$page_id) {return __('Page not found - check the URL', 'ag_ml');}
					
					$this->ajax_data['fb_page_id'] = $page_id;
					return true;
				} 
				
				// facebook PROFILE
				else {return false;}
				break;	
			
			
			
			case 'picasa' :
				// check if username already exists in stored ones
				$stored = get_option('ag_gplus_base_tokens_db', array());
				if(isset($stored[ $data['gplus_user'] ])) {
					return __('Username already used in another connection', 'ag_ml');		
				}
				
				// test connection (already storing tokens in case of success)
				include_once(AG_DIR .'/classes/source_helpers/gplus_integration.php');
				
				$gplus = new ag_gplus_integration(false, $data['gplus_user']);
				$connection = $gplus->get_access_token($data['gplus_token']);
				
				return ($connection) ? true : __('Connection failed - Check username and access token', 'ag_ml');
				break;	
			
			
			
			case 'g_drive' :
				// check if username already exists in stored ones
				$stored = get_option('ag_gdrive_base_tokens_db', array());
				if(isset($stored[ $data['gdrive_user'] ])) {
					return __('Username already used in another connection', 'ag_ml');		
				}
				
				// test connection (already storing tokens in case of success)
				include_once(AG_DIR .'/classes/source_helpers/gdrive_integration.php');
				
				$gdrive = new ag_gdrive_integration(false, $data['gdrive_user']);
				$connection = $gdrive->get_access_token($data['gdrive_token']);
				
				return ($connection) ? true : __('Connection failed - Check username and access token', 'ag_ml');
				break;	
			
			
			
			case 'onedrive' :
				// check if username already exists in stored ones
				$stored = get_option('ag_onedrive_base_tokens_db', array());
				if(isset($stored[ $data['onedrive_user'] ])) {
					return __('Username already used in another connection', 'ag_ml');		
				}
				
				// test connection (already storing tokens in case of success)
				include_once(AG_DIR .'/classes/source_helpers/onedrive_integration.php');
				
				$onedrive = new ag_onedrive_integration(false, $data['onedrive_user']);
				$connection = $onedrive->setup_first_access_token($data['onedrive_token']);
				
				return ($connection) ? true : __('Connection failed - Check username and authorization token', 'ag_ml');
				break;	
			
			
			default : return 'invalid source'; break;
		}
	}
	
	
	
	/* SAVE CONNECTION as term */
	public function save_connection() {
		
		// specific data to store for each source
		switch($this->src) {
			case 'fb' :
				if($this->ajax_data['fb_src_switch'] == 'page') {
					$data = array(
						'fb_page_url' => $this->ajax_data['fb_page_url'],
						'fb_page_id' => $this->ajax_data['fb_page_id']
					);
				} 
				else {/*TODO*/}
				
				$data['fb_src_switch'] = $this->ajax_data['fb_src_switch']; 
				break;	
			
			
			case 'picasa' :
				$data = array(
					'gplus_user' => $this->ajax_data['gplus_user'] // dynamic token stored in ag_gplus_base_tokens_db option
				);
				break;	
			
			
			case 'g_drive' :
				$data = array(
					'gdrive_user' => $this->ajax_data['gdrive_user'] // dynamic token stored in ag_gdrive_base_tokens_db option
				);
				break;	
			

			case 'onedrive' :
				$data = array(
					'onedrive_user' => $this->ajax_data['onedrive_user'] // dynamic token stored in ag_onedrive_base_tokens_db option
				);
				break;	

			default : $data = $this->ajax_data; break;
		}
		
		
		// be sure main term and data exists 
		$parent = $this->main_term_exists();
		if(empty($data)) {return __('no data to store', 'ag_ml');}
		
		// create term	
		$args = array(
			'description' => base64_encode(serialize($data)),
			'slug' => uniqid(),
    		'parent'=> $parent
		);
		$result = wp_insert_term($this->ajax_data['conn_name'], $this->tax_name, $args);
		
		if(is_wp_error($result)) {
			//echo $result->get_error_message(); // debug
			return 'error creating connection term';
		} else {
			$this->connect_id = $result['term_id'];
			return true;
		}
	}
}
