<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once MODPATH.'core/controllers/nova_personnel.php';

class Personnel extends Nova_personnel {

	public function __construct()
	{
		parent::__construct();
	}
	

	/**********************/
	/**** MANIFEST MOD ****/
	/**********************/
	public function index()
	{
		// load the models
		$this->load->model('depts_model', 'dept');
		$this->load->model('ranks_model', 'ranks');
		$this->load->model('positions_model', 'pos');
		
		// get the variables
		$manifest = $this->uri->segment(3, $this->dept->get_default_manifest());
		
		// pull all the manifests
		$manifests = $this->dept->get_all_manifests();
		
		if ($manifests->num_rows() > 0)
		{
			if ($manifests->num_rows() > 1)
			{
				foreach ($manifests->result() as $m)
				{
					$data['manifests'][$m->manifest_id] = array(
						'id' => $m->manifest_id,
						'name' => $m->manifest_name,
						'desc' => $m->manifest_desc,
					);
				}
			}
			
			// get the manifest details (MD)
			$MD = $this->dept->get_manifest($manifest);
			
			// pull the content for the header
			$data['manifest_header'] = $MD->manifest_header_content;
			
			// run the methods
			$this->db->where('dept_manifest', $manifest);
			$depts = $this->dept->get_all_depts();
			$rank = $this->ranks->get_rankcat($this->rank);
			
			// build the blank image array
			$blank_img = array(
				'src' => Location::rank($this->rank, 'blank', $rank->rankcat_extension),
				'alt' => '',
				'class' => 'image');
			
			if ($depts->num_rows() > 0)
			{
				$a = 1;
				foreach ($depts->result() as $depts)
				{
					$name = '';
					// set the dept id as a variable
					$dept = $depts->dept_id;
					
					// set the dept name
					$data['depts'][$dept]['name'] = $depts->dept_name;
					$data['depts'][$dept]['type'] = $depts->dept_type;
					
					// get the sub depts
					$subdepts = $this->dept->get_sub_depts($dept);
					
					if ($subdepts->num_rows() > 0)
					{
						$a = 1;
						foreach ($subdepts->result() as $sub)
						{
							// set the name of the sub dept
							$data['depts'][$dept]['sub'][$a]['name'] = $sub->dept_name;
							$data['depts'][$dept]['sub'][$a]['type'] = $sub->dept_type;
							
							// grab the positions for the sub dept
							$positions = $this->pos->get_dept_positions($sub->dept_id);
					
							if ($positions->num_rows() > 0)
							{
								$b = 1;
								foreach ($positions->result() as $pos)
								{
									// set the sub dept position data
									$data['depts'][$dept]['sub'][$a]['pos'][$b]['name'] = $pos->pos_name;
									$data['depts'][$dept]['sub'][$a]['pos'][$b]['pos_id'] = $pos->pos_id;
									$data['depts'][$dept]['sub'][$a]['pos'][$b]['open'] = $pos->pos_open;
									$data['depts'][$dept]['sub'][$a]['pos'][$b]['blank_img'] = $blank_img;
									
									// get any characters in a position in a sub dept
									$characters = $this->char->get_characters_for_position($pos->pos_id, array('rank' => 'asc'));
							
									if ($characters->num_rows() > 0)
									{
										$c = 1;
										foreach ($characters->result() as $char)
										{
											// grab the rank data we need
											$rankdata = $this->ranks->get_rank($char->rank, array('rank_name', 'rank_image'));
											
											// get the character name and rank
											$name = $this->char->get_character_name($char->charid, true);

											// build the rank image array
											$rank_img = array(
												'src' => Location::rank(
													$this->rank, 
													$rankdata['rank_image'],
													$rank->rankcat_extension),
												'alt' => $rankdata['rank_name'],
												'class' => 'image');
											
											// get char image 
											$char_img = '';
											if ($char->images > '')
											{
												// get the images
												$images = explode(',', $char->images);
												$images_count = count($images);
												
												$src = (strstr($images[0], 'http://') !== false)
													? $images[0]
													: base_url().Location::asset('images/characters', trim($images[0]));

												list($imgwidth, $imgheight, $imgtype, $imgattr) = getimagesize($src);
												
												if ($imgwidth < $imgheight) {
													$factor = round($imgwidth/80, 2);
												} else {
													$factor = round($imgheight/80 ,2);
												}
												// set the image
												$char_img = array(
													'src' => $src,
													'alt' => $name,
													'class' => 'charimg',
													'height' => ($imgheight/$factor),
													'width' => ($imgwidth/$factor)
												);
											} else { //no image:
												// set the image
												list($imgwidth, $imgheight, $imgtype, $imgattr) = getimagesize(base_url().Location::img('no-avatar.png', $this->skin, 'main'));
												
												if ($imgwidth < $imgheight) {
													$factor = round($imgwidth/80, 2);
												} else {
													$factor = round($imgheight/80 ,2);
												}

												$char_img = array(
													'src' => Location::img('no-avatar.png', $this->skin, 'main'),
													'alt' => '',
													'class' => 'charimg',
													'height' => ($imgheight/$factor),
													'width' => ($imgwidth/$factor)
												);
											}
											
											
											// set the color
											$color = '';
											
											if ($char->user > 0)
											{
												$color = ($this->user->get_loa($char->user) == 'loa') ? '_loa' : $color;
												$color = ($this->user->get_loa($char->user) == 'eloa') ? '_eloa' : $color;
											}
											
											$color = ($char->crew_type == 'npc') ? '_npc' : $color;
									
											// build the combadge image array
											$cb_img = array(
												'src' => Location::cb('combadge'. $color .'.png', $this->skin, 'main'),
												'alt' => ucwords(lang('actions_view') 
													.' '. lang('labels_bio')),
												'class' => 'image'
											);
												
											
									$f_species = $this->char->get_field_data(2, $char->charid);
									if ($f_species->num_rows() > 0) {
										$fr_species = $f_species->result();
										$species = $fr_species[0]->data_value;
									}
									$f_gender = $this->char->get_field_data(1, $char->charid);
									if ($f_gender->num_rows() > 0) {
										$fr_gender = $f_gender->result();
										$gender = $fr_gender[0]->data_value;
									}

											if ($char->crew_type == 'active' and empty($char->user))
											{
												// don't do anything
											}
											else
											{
												// set the data for the characters in a position in a sub dept
												$data['depts'][$dept]['sub'][$a]['pos'][$b]['chars'][$c]['char_id'] = $char->charid;
												$data['depts'][$dept]['sub'][$a]['pos'][$b]['chars'][$c]['name'] = $name;
												$data['depts'][$dept]['sub'][$a]['pos'][$b]['chars'][$c]['rank_img'] = $rank_img;
												$data['depts'][$dept]['sub'][$a]['pos'][$b]['chars'][$c]['crew_type'] = $char->crew_type;
												$data['depts'][$dept]['sub'][$a]['pos'][$b]['chars'][$c]['combadge'] = $cb_img;
												
												$data['depts'][$dept]['sub'][$a]['pos'][$b]['chars'][$c]['char_gender'] = $gender; //1=gender
												$data['depts'][$dept]['sub'][$a]['pos'][$b]['chars'][$c]['char_species'] = $species; //2=species

												$data['depts'][$dept]['sub'][$a]['pos'][$b]['chars'][$c]['char_image'] = $char_img; 
												
												++$c;
											}
										}
									}
									
									++$b;
								}
							}
							
							++$a;
						}
					}
					
					// get the positions for the dept
					$positions = $this->pos->get_dept_positions($dept);
					
					if ($positions->num_rows() > 0)
					{
						$b = 1;
						foreach ($positions->result() as $pos)
						{
							// set the data for the dept positions
							$data['depts'][$dept]['pos'][$b]['name'] = $pos->pos_name;
							$data['depts'][$dept]['pos'][$b]['pos_id'] = $pos->pos_id;
							$data['depts'][$dept]['pos'][$b]['open'] = $pos->pos_open;
							$data['depts'][$dept]['pos'][$b]['blank_img'] = $blank_img;
							
							// get any characters in a position in the dept
							$characters = $this->char->get_characters_for_position($pos->pos_id, array('rank' => 'asc'));
							
							if ($characters->num_rows() > 0)
							{
								$c = 1;
								foreach ($characters->result() as $char)
								{
									// get the character name and rank
									$name = $this->char->get_character_name($char->charid, true);

									// get the rank data we need
									$ranksdata = $this->ranks->get_rank($char->rank, array('rank_name', 'rank_image'));
									
									// build the rank image array
									$rank_img = array(
										'src' => Location::rank(
											$this->rank,
											$ranksdata['rank_image'],
											$rank->rankcat_extension),
										'alt' => $ranksdata['rank_name'],
										'class' => 'image');
									
											// get char image 
											$char_img = '';
											if ($char->images > '')
											{
												// get the images
												$images = explode(',', $char->images);
												$images_count = count($images);
												
												$src = (strstr($images[0], 'http://') !== false)
													? $images[0]
													: base_url().Location::asset('images/characters', trim($images[0]));
												list($imgwidth, $imgheight, $imgtype, $imgattr) = getimagesize($src);

												if ($imgwidth < $imgheight) {
													$factor = round($imgwidth/80, 2);
												} else {
													$factor = round($imgheight/80 ,2);
												}
												
												// set the image
												$char_img = array(
													'src' => $src,
													'alt' => $name,
													'class' => 'charimg',
													'height' => ($imgheight/$factor),
													'width' => ($imgwidth/$factor)
												);
											} else { //no image:
												list($imgwidth, $imgheight, $imgtype, $imgattr) = getimagesize(base_url().Location::img('no-avatar.png', $this->skin, 'main'));
												if ($imgwidth < $imgheight) {
													$factor = round($imgwidth/80, 2);
												} else {
													$factor = round($imgheight/80 ,2);
												}
												// set the image
												$char_img = array(
													'src' => Location::img('no-avatar.png', $this->skin, 'main'),
													'alt' => '',
													'class' => 'charimg',
													'height' => ($imgheight/$factor),
													'width' => ($imgwidth/$factor)
												);
											}

											// set the color
									$color = '';
									
									if ($char->user > 0)
									{
										$color = ($this->user->get_loa($char->user) == 'loa') ? '_loa' : $color;
										$color = ($this->user->get_loa($char->user) == 'eloa') ? '_eloa' : $color;
									}
									
									$color = ($char->crew_type == 'inactive') ? '' : $color;
									$color = ($char->crew_type == 'npc') ? '_npc' : $color;
									
									// build the combadge image array
									$cb_img = array(
										'src' => Location::cb('combadge'. $color .'.png', $this->skin, 'main'),
										'alt' => ucwords(lang('actions_view') 
											.' '. lang('labels_bio')),
										'class' => 'image'
									);
									
									
									$f_species = $this->char->get_field_data(2, $char->charid);
									if ($f_species->num_rows() > 0) {
										$fr_species = $f_species->result();
										$species = $fr_species[0]->data_value;
									}
									$f_gender = $this->char->get_field_data(1, $char->charid);
									if ($f_gender->num_rows() > 0) {
										$fr_gender = $f_gender->result();
										$gender = $fr_gender[0]->data_value;
									}
									
									if ($char->crew_type == 'active' and empty($char->user))
									{
										// don't do anything
									}
									else
									{
										// set the data for characters in a position in the dept
										$data['depts'][$dept]['pos'][$b]['chars'][$c]['char_id'] = $char->charid;
										$data['depts'][$dept]['pos'][$b]['chars'][$c]['name'] = $name;
										$data['depts'][$dept]['pos'][$b]['chars'][$c]['rank_img'] = $rank_img;
										$data['depts'][$dept]['pos'][$b]['chars'][$c]['crew_type'] = $char->crew_type;
										$data['depts'][$dept]['pos'][$b]['chars'][$c]['combadge'] = $cb_img;

										$data['depts'][$dept]['pos'][$b]['chars'][$c]['char_gender'] = $gender; //1=gender
										$data['depts'][$dept]['pos'][$b]['chars'][$c]['char_species'] = $species; //2=species
										$data['depts'][$dept]['pos'][$b]['chars'][$c]['char_image'] = $char_img; 
										
										++$c;
									}
								}
							}
							
							++$b;
						}
					}
				}
			}
		}
		
		// pull the top open positions
		$top = $this->pos->get_open_positions('y', true);
		
		if ($top->num_rows() > 0)
		{
			foreach ($top->result() as $t)
			{
				$data['top'][$t->pos_id] = array(
					'name' => $t->pos_name,
					'id' => $t->pos_id,
					'dept' => $this->dept->get_dept($t->pos_dept, 'dept_name'),
					'blank_img' => $blank_img
				);
			}
		}
		
		// set the javascript data
		$js_data = array(
			'display' => $this->uri->rsegment(3),
			'manifest_defaults' => $MD->manifest_view);
		
		// set the data being sent to the view
		$data['display'] = $this->uri->rsegment(3, 'crew');
		$data['header'] = ucwords(lang('labels_crew') .' '. lang('labels_manifest'));
		$data['loader'] = array(
			'src' => Location::img('loader.gif', $this->skin, 'main'),
			'alt' => '',
			'class' => 'image');
			
		$data['label'] = array(
			'playing_chars' => ucwords(lang('status_playing') .' '. lang('global_characters')),
			'inactive_chars' => ucwords(lang('status_inactive') .' '. lang('global_characters')),
			'all_chars' => ucwords(lang('labels_all') .' '. lang('global_characters')),
			'open' => ucwords(lang('status_open') .' '. lang('global_positions')),
			'show' => ucfirst(lang('actions_show')),
			'toggle' => ucfirst(lang('actions_toggle')),
			'npcs' => lang('abbr_npcs'),
			'loading' => ucfirst(lang('actions_loading')),
			'inactive' => ucfirst(lang('status_inactive')),
			'apply' => ucwords(lang('global_position') .' '. lang('status_open') .' '. NDASH
				.' '. lang('actions_apply') .' '. lang('time_now')),
			'npc' => lang('abbr_npc'),
			'manifests' => ucwords(lang('labels_site').' '.lang('labels_manifests')),
			'top_positions' => ucwords(lang('labels_top').' '.lang('status_open').' '.lang('global_positions')),
		);
		
		$this->_regions['content'] = Location::view('personnel_index', $this->skin, 'main', $data);
		$this->_regions['javascript'] = Location::js('personnel_index_js', $this->skin, 'main', $js_data);
		$this->_regions['title'].= $data['header'];
		
		Template::assign($this->_regions);
		
		Template::render();
	}

}
