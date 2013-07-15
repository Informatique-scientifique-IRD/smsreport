<table style="width: 860px;" class="my_table">
	<tr>
		<td style="width:80px;">
			<span class="big_blue_span"><?php echo Kohana::lang('ui_main.example');?></span>
		</td>
		<td>
			<h4 class="fix">Format</h4>
			<p>
				For incoming SMS messages to work with this plugin the following format and ordering must be used.
			</p>	
			<div style="padding:10px;margin:20px; font-style:italic; border: 1px solid black;">
				 &lt;Code Word&gt;&lt;delimiter&gt;
				&lt;Decimal Degree Latitude&gt;&lt;delimiter&gt;&lt;Decimal Degree Longitude&gt;&lt;delimiter&gt;
				&lt;Title&gt;&lt;delimiter&gt;&lt;Location Description&gt;&lt;delimiter&gt;
				&lt;Event Description&gt;&lt;delimiter&gt;&lt;Category Codes seperated by commas&gt;
			</div>
			
			<p>So for example if we use ';' as our delimiter and "abc" as our code word then the following:</p>
		
			<div style="padding:10px;margin:20px; font-style:italic; border: 1px solid black;">
				abc;7.77;-9.42;My Title;Zorzor, Liberia;The description of the event;1,3,4
			</div>
			<p>
				This would be converted into a report at latitude 7.77 and longitude -9.42, calling this location "Zorzor Liberia", with a title of "My Title", a description of 
				"The description of the event", and tagged under catgories 1, 3 and 4. 
			</p><p>
				To figure out a category's ID number look at the status bar when mousing over the edit or delete link in the Catgories Manage Page in the
				administrative interface. This should be located in admin/manage on your Ushahidi site.
			</p><p>
				The Location Description, Event Description and Category fields are optional. A message must have a code word, lat, lon, and title to be parsed.
			</p><p>
				Please be careful with these settings, choosing an easy to guess code word 
				will make your site an easy target for malicious groups wishing to spread mis-information. Also by choosing a delimiter 
				that may be used in the message you run the risk of having malformed SMS messages that can't be properly read.
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
					Don't use a comma, "," as this is the delimiter for category IDs, and also a fairly commonly used punctionation mark. Use something more obscure like a semi-colon, ";" or an ampersand. "&amp;".
				</p>
				
				<?php print form::input('delimiter', $form['delimiter'], ' class="text"'); ?>
			</div>
			<div class="row">
				<h4><a href="#" class="tooltip" title="What code word should be used to make sure that the SMS is from a trusted user?">Code Word</a></h4>
				<p>
					This is case insensative. For example "AbC" and "abc" will be treated as the same code word.
				</p>
				
				<?php print form::input('code_word', $form['code_word'], ' class="text"'); ?>
			</div>
			<div class="row">
				<h4><a href="#" class="tooltip" title="Enter a list of phone numbers, each number on a different line, that are allowed to send in SMSs that are automatically made into reports. 
					 Numbers must be in the exact same format as when they're recieved. If you want any number to be able to use this leave the list blank.">White listed phone numbers</a></h4>
				<?php print form::textarea('whitelist', $form['whitelist'], ' style="height:40px;"') ?>	
			</div>
	</tr>
	<tr>
		<td>
			<span class="big_blue_span">Experimental</span>
		</td>
		<td>
			<div class="row">
				<h4><a href="#" class="tooltip" title="[Explanation here]">append_to_desc</a></h4>
					<?php print form::dropdown('append_to_desc', $yesno_array, $form['append_to_desc']); ?>
			</div>
			
			<div class="row">
				<h4><a href="#" class="tooltip" title="[Explanation here]">append_to_desc_txt</a></h4>
				<?php print form::textarea('append_to_desc_txt', $form['append_to_desc_txt'], ' style="height:40px;"') ?>	
			</div>

			<div class="row">
				<h4><a href="#" class="tooltip" title="If this option is set, the date of the report will be the date when the SMS was received">Auto-date</a></h4>
					<?php print form::dropdown('auto_date', $yesno_array, $form['auto_date']); ?>
			</div>

			<div class="row">
				<h4><a href="#" class="tooltip" title="[Explanation here]Replace multi-value by list of id">EXPERIMENTAL : Multi-value by id</a></h4>
					<?php print form::dropdown('multival_resp_by_id', $yesno_array, $form['multival_resp_by_id']); ?>
			</div>
		</td>
	</tr>	
			
	<tr>
		<td>
			<span class="big_blue_span">ToDO</span>
		</td>
		<td>
			<div class="row">
				<h4><a href="#" class="tooltip" title="[Explanation here]">TODO : Auto-title</a></h4>
					<?php print form::dropdown('auto_title', $yesno_array, $form['auto_title']); ?>
			</div>
			
			<div class="row">
				<h4><a href="#" class="tooltip" title="[Explanation here]">TODO : auto_desc</a></h4>
					<?php print form::dropdown('auto_desc', $yesno_array, $form['auto_desc']); ?>
			</div>
			
			<div class="row">
				<h4><a href="#" class="tooltip" title="[Explanation here]">TODO : auto_approve</a></h4>
					<?php print form::dropdown('auto_approve', $yesno_array, $form['auto_approve']); ?>
			</div>
			
			<div class="row">
				<h4><a href="#" class="tooltip" title="[Explanation here]">TODO : auto_verify</a></h4>
					<?php print form::dropdown('auto_verify', $yesno_array, $form['auto_verify']); ?>
			</div>
			
			<div class="row">
				<h4><a href="#" class="tooltip" title="[Explanation here]">TODO : append_errors</a></h4>
					<?php print form::dropdown('append_errors', $yesno_array, $form['append_errors']); ?>
			</div>
			
			<div class="row">
				<h4><a href="#" class="tooltip" title="[Explanation here]">TODO : locate_from_list</a></h4>
					<?php print form::dropdown('locate_from_list', $yesno_array, $form['locate_from_list']); ?>
			</div>
			
			<div class="row">
				<h4><a href="#" class="tooltip" title="[Explanation here]">TODO : fill_empty_gps_by_loc</a></h4>
					<?php print form::dropdown('fill_empty_gps_by_loc', $yesno_array, $form['fill_empty_gps_by_loc']); ?>
			</div>
			<div class="row">
				<h4><a href="#" class="tooltip" title="[Explanation here]">TODO : locate_from_list_table</a></h4>
					<?php print form::input('locate_from_list_table', $form['locate_from_list_table'], ' class="text"'); ?>
			</div>
			
			<div class="row">
				<h4><a href="#" class="tooltip" title="[Explanation here]">TODO : locate_from_list_id</a></h4>
					<?php print form::input('locate_from_list_id', $form['locate_from_list_id'], ' class="text"'); ?>
			</div>
			
			<div class="row">
				<h4><a href="#" class="tooltip" title="[Explanation here]">TODO : locate_from_list_name</a></h4>
					<?php print form::input('locate_from_list_name', $form['locate_from_list_name'], ' class="text"'); ?>
			</div>
			
			<div class="row">
				<h4><a href="#" class="tooltip" title="[Explanation here]">TODO : locate_from_list_lat</a></h4>
					<?php print form::input('locate_from_list_lat', $form['locate_from_list_lat'], ' class="text"'); ?>
			</div>
			
			<div class="row">
				<h4><a href="#" class="tooltip" title="[Explanation here]">TODO : locate_from_list_lon</a></h4>
					<?php print form::input('locate_from_list_lon', $form['locate_from_list_lon'], ' class="text"'); ?>
			</div>

		</td>
	</tr>	
		
</table>
