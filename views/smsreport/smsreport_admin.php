<table style="width: 860px;" class="my_table">
	<tr>
		<td style="width:80px;">
			<span class="big_blue_span"><?php echo Kohana::lang('ui_main.example');?></span>
		</td>
		<td>
			<h4 class="fix">Format</h4>
			<p>
				According to your current settings, the plugin expects to receive SMS reports in this format and ordering :
			</p>	
			<div style="padding:10px;margin:20px; font-style:italic; border: 1px solid black;">
				<?php echo $example_format; ?>
			</div>
	         
			 <p><b>Format explanation :</b>
			<table>
                <tr><th>Code</th><td>A code word used to make sure that this SMS is a report (and it should be parsed by the plugin) and to guess that it comes from a trusted user</td></tr>
                <tr><th>Title</th><td>A title for your report</td></tr>
                <tr><th>Categories</th><td>Your report categories IDs <b><sup>1</sup></b> separated by commas ","</td></tr>
                <tr><th>Date</th><td>Date of your report</td></tr>
                <tr><th>Description</th><td>Your report description</td></tr>
                <tr><th>Location</th><td>The report location</td></tr>
                <tr><th>Latitude</th><td>Latitude of the report, in decimal degrees</td></tr>
                <tr><th>Longitude</th><td>Longitude of the report, in decimal degrees</td></tr>
                <tr><th>Form ID</th><td>The form ID <b><sup>2</sup></b> used by your report</td></tr>
                <tr><th>Field 1, Field 2, etc..</th><td>Your answers to custom fields, in the same order as they were specified in the form builder</td></tr>
            </table></p>
            
			<p>So, for example, if the plugin receive the following SMS</p>
		
			<div style="padding:10px;margin:20px; font-style:italic; border: 1px solid black;">
				<?php echo $example_sms; ?>
			</div>
			<p>
				<?php echo $example_txt; ?>
			</p><p><b><sup>1</sup></b>
				To figure out a category's ID number, in the Categories Manage Page (in the administrative interface) look at the status bar of your browser when mousing over the delete link : it's the last number.
            </p><p><b><sup>2</sup></b>
				To figure out a form's ID number, it's the same method as categories but in the Form Manage Page.
			</p><p>
				Please be careful with these settings, modifying some options during an active deployment will force you to inform your contributors that they have to change the way they send SMS reports !
			</p>


		</td>
	</tr>
	
	<tr>
		<td>
			<span class="big_blue_span">Basic Settings</span>
		</td>
		<td>
			<div class="row">
				<h4><a href="#" class="tooltip" title="What character should be the delimiter between fields in a text message ?">Delimiter</a></h4>
				<p>
					Don't use a comma, "," as this is the delimiter for category IDs and also a fairly commonly used punctuation mark. Use something more obscure like a sharp '#'".
				</p>
				
				<?php print form::input('delimiter', $form['delimiter'], ' class="text"'); ?>
			</div>
			<div class="row">
				<h4><a href="#" class="tooltip" title="What code word should be used to make sure that this SMS is a report and to guess that it comes from a trusted user ?">Code Word</a></h4>
				<p>
					This is case insensitive. For example "AbC" and "abc" will be treated as the same code word.
				</p>
				
				<?php print form::input('code_word', $form['code_word'], ' class="text"'); ?>
			</div>
			<div class="row">
				<h4><a href="#" class="tooltip" title="Enter a list of phone numbers, each number on a different line, that are allowed to send in SMS reports. 
					 Numbers must be in the exact same format as when they're received. If you want any number to be able to use this leave the list blank.">White listed phone numbers</a>
					 <br /><span>One number per line</span></h4>
				<?php print form::textarea('whitelist', $form['whitelist'], ' style="height:40px;"') ?>	
			</div>
	</tr>
	
	<tr>
		<td>
			<span class="big_blue_span">Auto features</span>
		</td>
		
		<td>
			<div class="row">
				<h4><a href="#" class="tooltip" title="The title parameter in the SMS becomes disabled. 
							The report will have an automatic title in the format: `SMS-Report by {sender} received on {date}`">Auto-Title</a></h4>
				<span class="sel-holder">
					<?php print form::dropdown('auto_title', $yesno_array, $form['auto_title']); ?>
				</span>
			</div>
			
			<div class="row">
				<h4><a href="#" class="tooltip" title="The description parameter in the SMS becomes disabled. 
							The report will have an automatic description which contains : the sender, the date and the original message">Auto-Description</a></h4>
				<span class="sel-holder">
					<?php print form::dropdown('auto_desc', $yesno_array, $form['auto_desc']); ?>
				</span>
			</div>
			
			<div class="row">
				<h4><a href="#" class="tooltip" title="The date parameter in the SMS becomes disabled.
                                                       The report will have the same date as when Ushahidi received the SMS">Auto-Date</a></h4>
				<span class="sel-holder">
					<?php print form::dropdown('auto_date', $yesno_array, $form['auto_date']); ?>
				</span>
			</div>

			<div class="row">
				<h4><a href="#" class="tooltip" title="Reports received by SMS will be automatically sets to `approved`">Auto-Approve</a></h4>
				<span class="sel-holder">
					<?php print form::dropdown('auto_approve', $yesno_array, $form['auto_approve']); ?>
				</span>
			</div>
			
			<div class="row">
				<h4><a href="#" class="tooltip" title="Reports received by SMS will be automatically sets to `verified`">Auto-Verify</a></h4>
				<span class="sel-holder">
					<?php print form::dropdown('auto_verify', $yesno_array, $form['auto_verify']); ?>
				</span>
			</div>

		</td>
	</tr>	
			
	<tr>
		<td>
			<span class="big_blue_span">Improvements</span>
		</td>
		<td>
        	<div class="row">
				<h4><a href="#" class="tooltip" title="Instead of answering to multi-value field (radio, checkbox & dropdown) in plain text,
                                                       you will be able to answer by an id. 
                                                       The first possible response is 0, next one is 1, etc... 
                                                       In same order as you specified in the form builder
                                                       Example : for answering `case1` and `case4` to a checkbox field, instead of typing `case1,case4` in the SMS 
                                                       you will have to simply type `0,3`">Multi-value fields by id</a></h4>
				<span class="sel-holder">
					<?php print form::dropdown('multival_resp_by_id', $yesno_array, $form['multival_resp_by_id']); ?>
				</span>
			</div>
            
			<div class="row">
				<h4><a href="#" class="tooltip" title="Should the plugin append the following text to report description ?">Append to Description</a></h4>
				<span class="sel-holder">
					<?php print form::dropdown('append_to_desc', $yesno_array, $form['append_to_desc']); ?>
				</span>
			</div>
			
			<div class="row">
				<h4><a href="#" class="tooltip" title="Text to append to report description">Text to append</a></h4>

					<?php print form::textarea('append_to_desc_txt', $form['append_to_desc_txt'], ' style="height:40px;"') ?>	

			</div>
			
			<div class="row">
				<h4><a href="#" class="tooltip" title="If the plugin fails to parse a SMS, the SMS message will be modified by adding
                                                        `ERROR` at his beginning and by appending the error message at the end.
                                                        With this option you will be able to analyse why parsing failed.">Append errors</a></h4>
				<span class="sel-holder">
					<?php print form::dropdown('append_errors', $yesno_array, $form['append_errors']); ?>
				</span>
			</div>
		</td>
	</tr>
	
	<tr>
		<td>
			<span class="big_blue_span">Location list</span>
		</td>
		<td>
        	<p>
				You <b>must</b> configure the location table name in the <i>plugin config file</i> (with a valid table) in order to activate these options.
			</p>
			<div class="row">
				<h4><a href="#" class="tooltip" title="Instead of sending by SMS the location in plain text, 
                                                you will be able to simply send an unique code. The plugin will look for this code in a table (in database),
                                                and replace this code by the location full name.">Get location by code</a></h4>
				<span class="sel-holder">
					<?php print form::dropdown('locate_from_list', $yesno_array, $form['locate_from_list'], $loc_table_dis); ?>
				</span>
			</div>
			<p><i>All the options below depends on <b>Get location by code</b></i></p>

			<div class="row">
				<h4><a href="#" class="tooltip" title="In which column in the table the plugin will find the unique code. 
													The `unique code` is what is sent in the sms.">Location code column in the table</a></h4>
				<span class="sel-holder">
					<?php print form::dropdown('locate_from_list_id', $columns_dropdown, $form['locate_from_list_id'], $loc_table_dis); ?>
				</span>
			</div>
			
			<div class="row">
				<h4><a href="#" class="tooltip" title="In which column in the table the plugin will find the name corresponding to the unique code.
														The `name` is what will be the location name of the report">Name column in the table</a></h4>
				<span class="sel-holder">
					<?php print form::dropdown('locate_from_list_name', $columns_dropdown, $form['locate_from_list_name'], $loc_table_dis); ?>
				</span>
			</div>
			<div class="row">
				<h4><a href="#" class="tooltip" title="If you want to save the original location code given in the SMS (before replacement), 
										put the exact field name where to save this information here.
										You MUST create a text field with this exact name in EACH form.
										If the field is not found when a SMS is received, no error will be raised.
										BE CAREFUL : This field will be treated like any other when parsing, 
										so put it in last position of your custom form in the form builder">Save location code in field</a>
					<br /><span>If you don't want to use this feature, let this box empty.</span></h4>
					<?php print form::input('loc_code_field', $form['loc_code_field'], ' class="text" '.$loc_table_dis); ?>
			</div>
			<br />
			<div class="row">
				<h4><a href="#" class="tooltip" title="The plugin will replace empty lat/lon coordinates in SMS
                                                       with a value in the table corresponding to the unique code">Replace empty lat/lon by the table</a></h4>
				<span class="sel-holder">
					<?php print form::dropdown('fill_empty_gps_by_loc', $yesno_array, $form['fill_empty_gps_by_loc'], $loc_table_dis); ?>
				</span>
			</div>
			
			<p><i>The next three options are for <b>Replace empty lat/lon by the table</b></i></p>
			
			<div class="row">
				<h4><a href="#" class="tooltip" title="In which column in the table the plugin will find the location latitude.">Latitude column in the table</a>
					<br /><span>Latitude must be in decimal</span></h4>
				<span class="sel-holder">
					<?php print form::dropdown('locate_from_list_lat', $columns_dropdown, $form['locate_from_list_lat'], $loc_table_dis); ?>
				</span>
			</div>
			
			<div class="row">
				<h4><a href="#" class="tooltip" title="In which column in the table the plugin will find the location longitude.">Longitude column in the table</a>
					<br /><span>Longitude must be in decimal</span></h4>
				<span class="sel-holder">
					<?php print form::dropdown('locate_from_list_lon', $columns_dropdown, $form['locate_from_list_lon'], $loc_table_dis); ?>
				</span>
			</div>
			
			<div class="row">
				<h4><a href="#" class="tooltip" title="If you want to save if lat/long comes from the SMS or from the location table, 
										put the exact field name where to save this information here.
										The field value will be 0 if coords comes from SMS, or 1 if coords were replaced by a location table value.
										You MUST create a text field with this exact name in EACH form. If the field is not found when a SMS is received, no error will be raised.
										BE CAREFUL : This field will be treated like any other when parsing, 
										so put it in last position of your custom form in the form builder">Save lat/lon type in field</a>
					<br /><span>If you don't want to use this feature, let this box empty.</span></h4>
					<?php print form::input('coord_type_field', $form['coord_type_field'], ' class="text" '.$loc_table_dis); ?>
			</div>
			
		</td>
	</tr>	
		
</table>

