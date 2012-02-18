<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');?>

<br />
	<?php echo text_output($header, 'h1', 'page-head');?>
<br />

	<?php echo form_open('admin/modmanifest/settings');?>
	
	<div class="indent-left">
		<p>
			<?php echo form_checkbox($checkboxes['show_species']) .' Show Character Species';?>
		</p>
		<p>
			<?php echo form_checkbox($checkboxes['show_gender']) .' Show Character Gender';?>
		</p>
		<p>
			<?php echo form_checkbox($checkboxes['show_thumbnail']) .' Show Character Thumbnail';?>
		</p>
	</div> 
	
		<p><?php echo form_button($submit);?></p>
	
	<?php echo form_close();?>
