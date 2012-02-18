<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once MODPATH.'core/controllers/nova_admin.php';

class Admin extends Nova_admin {

	public function __construct()
	{
		parent::__construct();
	}
	
	/**********************/
	/**** MANIFEST MOD ****/
	/**********************/

	public function modmanifest() {
		// load the models
				
				
		if (isset($_POST['submit'])) {
			$showSpecies = $this->input->post('chkShowSpecies', true);
			$showGender = $this->input->post('chkShowGender', true);
			$showThumbnail = $this->input->post('chkShowThumbnail', true);
					
			$setting_data = array(
				'setting_value' => $showSpecies
			);
			$update_settings = $this->settings->update_setting('modManifest_show_species', $setting_data);					
			$setting_data = array(
				'setting_value' => $showGender
			);
			$update_settings = $this->settings->update_setting('modManifest_show_gender', $setting_data);					
			$setting_data = array(
				'setting_value' => $showThumbnail
			);

			$update_settings = $this->settings->update_setting('modManifest_show_thumbnail', $setting_data);					

			$message = "Settings updated sucessfully.";
			$flash['status'] = 'success';
			$flash['message'] = text_output($message);
		}

	
		$data['checkboxes']['show_species'] = array(
				'name'        => 'chkShowSpecies',
				'id'          => 'chkShowSpecies',
				'value'       => 'true');
		$data['checkboxes']['show_gender'] = array(
				'name'        => 'chkShowGender',
				'id'          => 'chkShowGender',
				'value'       => 'true');
		$data['checkboxes']['show_thumbnail'] = array(
				'name'        => 'chkShowThumbnail',
				'id'          => 'chkShowThumbnail',
				'value'       => 'true');

		
		$set_species = $this->settings->get_setting('modManifest_show_species');
		$set_gender = $this->settings->get_setting('modManifest_show_gender');
		$set_thumbnail = $this->settings->get_setting('modManifest_show_thumbnail');
		
		$data['temp'] = $set_species;
		
		$data['checkboxes']['show_species']['checked'] = $set_species;
		$data['checkboxes']['show_gender']['checked'] = $set_gender;
		$data['checkboxes']['show_thumbnail']['checked'] = $set_thumbnail;


	$data['submit'] = array(
				'type' => 'submit',
				'class' => 'button-main',
				'name' => 'submit',
				'value' => 'submit',
				'content' => ucwords(lang('actions_submit'))
		);

		

		
		$data['header'] = "Manifest Details Configuration";
		
		$view_loc = "admin_modmanifest";
		$this->_regions['content'] = Location::view($view_loc, $this->skin, 'admin', $data);
		$this->_regions['title'].= $data['header'];
		
		Template::assign($this->_regions);
		
		Template::render();
		
	}	

	/**********************/
	/**** MANIFEST MOD ****/
	/**********************/

}
