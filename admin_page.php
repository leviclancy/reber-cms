<? $entry_info = nesty_page($page_temp);
$entry_info = $entry_info[$page_temp];

$retrieve_page->execute(["page_id"=>$page_temp]);
$result = $retrieve_page->fetchAll();
foreach ($result as $row):
	$entry_info['name']		= json_decode($row['name'], true);
	$entry_info['summary']		= json_decode($row['summary'], true);
	$entry_info['body']		= json_decode($row['body'], true);
	$entry_info['studies']		= $row['studies'];
	$entry_info['appendix'] = json_decode($row['appendix'],true);

	$empty_temp = 1;
	if (!(empty($row['name']))): $empty_temp = 0; endif;
	if (!(empty($row['summary']))): $empty_temp = 0; endif;
	if (!(empty($row['body']))): $empty_temp = 0; endif;
	if (!(empty($row['studies']))): $empty_temp = 0; endif;

	endforeach;

// Form list of languages
$languages_array = $site_info['languages'];

$languages_array_temp = array_keys($entry_info['name']);
if (empty($languages_array_temp)): $languages_array_temp = []; endif;
$languages_array = array_merge($languages_array_temp, $languages_array);

$languages_array_temp = array_keys($entry_info['summary']);
if (empty($languages_array_temp)): $languages_array_temp = []; endif;
$languages_array = array_merge($languages_array_temp, $languages_array);

$languages_array_temp = array_keys($entry_info['body']);
if (empty($languages_array_temp)): $languages_array_temp = []; endif;
$languages_array = array_merge($languages_array_temp, $languages_array);

$languages_array = array_unique($languages_array);

// Make toggles now
$toggle_array = [];
foreach ($languages_array as $language_temp):
	$hidden_temp = "hidden";
	if (!(empty($entry_info['name'][$language_temp]))): $hidden_temp = null; endif;

	// If it's an empty entry...
	if ( ($empty_temp == 1) && ($languages_array[0] == $language_temp)):
		$hidden_temp = null;
		endif;

	$toggle_array["wrapper-".$language_temp."-title"] = $hidden_temp;

	$hidden_temp = "hidden";
	if (!(empty($entry_info['summary'][$language_temp]))): $hidden_temp = null; endif;
	$toggle_array["wrapper-".$language_temp."-headline"] = $hidden_temp;

	$hidden_temp = "hidden";
	if (!(empty($entry_info['body'][$language_temp]))): $hidden_temp = null; endif;
	$toggle_array["wrapper-".$language_temp."-body"] = $hidden_temp;
	endforeach;

$hidden_temp = "hidden";
if (!(empty(trim($entry_info['studies'])))): $hidden_temp = null; endif;
$toggle_array["wrapper-endnotes"] = $hidden_temp;

if (isset($site_info['appendix_array'][$entry_info['type']])):
	$toggle_array["wrapper-appendices"] = "hidden";
	endif;

$toggle_array["wrapper-metadata"] = "hidden";

function wrapper_buttons ($switch_array, $descriptor_temp) {

	global $toggle_array;
	
	global $languages_array;

	if ( !(is_int($colspan_temp)) || ($colspan_temp < 0) ): $colspan_temp = 0; endif;
	
	if (empty($descriptor_temp)): $descriptor_temp = ucwords(str_replace("-", " • ", str_replace("wrapper-", null, $wrapper_temp))); endif;

	// We will assume that it is hidden
	$hidden_check = 1;
	$found_temp = null;
	$found_counter = 0;
	
	$toggle_others_temp = $toggle_show_temp = $toggle_hide_temp = [];
	
	foreach ($languages_array as $language_temp):

		if ([$language_temp] !== $switch_array): 
//			$toggle_others_temp[] = "wrapper-".$language_temp.".hide";
			$toggle_others_temp[] = "wrapper-".$language_temp."-show.show";
			$toggle_others_temp[] = "wrapper-".$language_temp."-hide.hide";
			continue; endif;
	
		endforeach;
	
	foreach ($toggle_array as $toggle_temp => $discard_temp):

		foreach ($switch_array as $switch_temp):
			if (strpos($toggle_temp, $switch_temp) === FALSE):
				$toggle_others_temp[] = $toggle_temp.".hide";
				$toggle_others_temp[] = $toggle_temp."-show.show";
				$toggle_others_temp[] = $toggle_temp."-hide.hide";
				continue 2; endif;
			endforeach;
	
		$found_counter++;
	
		$found_temp = $toggle_temp;
	
		$toggle_show_temp[] = $toggle_temp.".show";
		$toggle_show_temp[] = $toggle_temp."-show.hide";
		$toggle_show_temp[] = $toggle_temp."-hide.show";

		$toggle_hide_temp[] = $toggle_temp.".hide";
		$toggle_hide_temp[] = $toggle_temp."-show.show";
		$toggle_hide_temp[] = $toggle_temp."-hide.hide";
	
		if ($toggle_array[$toggle_temp] == "hidden"): $hidden_check = 0; endif;
	
		endforeach;
	
	$hide_hidden_temp = "hidden";
	$show_hidden_temp = null;
	if ($hidden_check == 1):
		$hide_hidden_temp = null;
		$show_hidden_temp = "hidden";
		endif;
	
	if ($found_count == 1): $wrapper_temp = $found_temp;
	else: $wrapper_temp = "wrapper-".implode("-", $switch_array); endif;
	
	$echo_temp = null;
	
	$echo_temp .= "<span id='".$wrapper_temp."-toggle' class='sidebar-inputs-toggle-button' tabindex='0' role='button' on='tap:". implode(",", $toggle_others_temp) .",". implode(",", $toggle_show_temp) .",". $wrapper_temp ."-hide.show,". $wrapper_temp ."-show.hide'>Ѫ</span>";
	$echo_temp .= "<span id='".$wrapper_temp."-hide' class='sidebar-inputs-hide-button' tabindex='0' role='button' on='tap:". implode(",", $toggle_hide_temp) .",". $wrapper_temp ."-hide.hide,". $wrapper_temp ."-show.show' ".$hide_hidden_temp.">Hide ".$descriptor_temp."</span>";
	$echo_temp .= "<span id='".$wrapper_temp."-show' class='sidebar-inputs-show-button' tabindex='0' role='button' on='tap:". implode(",", $toggle_show_temp) .",". $wrapper_temp ."-hide.show,". $wrapper_temp ."-show.hide' ".$show_hidden_temp.">Show ".$descriptor_temp."</span>";
	
	return $echo_temp; }

echo "<amp-sidebar id='sidebar-inputs' layout='nodisplay' side='right' on='sidebarOpen:login-popover.close,settings-popover.close,new-popover.close,search-popover.close,delete-popover.close' open>";

	echo "<div class='sidebar-back' on='tap:sidebar-inputs.close' role='button' tabindex='0'>Close</div>";

	echo "<div class='navigation-list'>";

	$list_temp = null;

	foreach ($languages_array as $language_temp):
		$list_temp .= "+++" . wrapper_buttons([$language_temp], ucfirst($language_temp));
			$list_temp .= "++++++" . wrapper_buttons([$language_temp, "title"], "Title");
			$list_temp .= "++++++" . wrapper_buttons([$language_temp, "headline"], "Headline");
			$list_temp .= "++++++" . wrapper_buttons([$language_temp, "body"], "Body");
		endforeach;

	$list_temp .= "+++" . wrapper_buttons([ "endnotes" ], "Endnotes");

	if (isset($site_info['appendix_array'][$entry_info['type']])):
		$list_temp .= "+++" . wrapper_buttons([ "appendices"], "Appendices");
		endif;

	$list_temp .= "+++" . wrapper_buttons([ "metadata"], "Metadata");

	echo body_process("+-+-+".$list_temp."+-+-+");

	echo "</div>";

	echo "</amp-sidebar>";

// Add a reset button that shows only if there is content inside

// Do a delete popover ... redirect if deletion works ...
echo "<amp-lightbox id='delete-popover' layout='nodisplay'>";

	echo "<form action-xhr='/delete-xhr/' method='post' id='delete' target='_top' class='admin-page-form' on=\"
		submit:
			delete-popover-submit.hide;
		submit-error:
			delete-popover-submit.show
		\">";

	echo "<input type='hidden' name='entry_id' value='".$page_temp."'>";

	echo "<p>Do you really want to delete this entry?</p>";

	// Submit button ...
	echo "<br><span id='delete-popover-submit' role='button' tabindex='0' on='tap:delete.submit'>Delete</span>";

	echo "<div class='form-feedback' submitting>Submitting...</div>";
	echo "<div class='form-feedback' submit-error><template type='amp-mustache'>Error. {{{message}}}</template></div>";
	echo "<div class='form-feedback' submit-success><template type='amp-mustache'>{{{message}}}</template></div>";

	echo "</form>";

	echo "</amp-lightbox>";

echo "<span id='sidebar-inputs-button' role='button' tabindex='0' on='tap:sidebar-inputs.open'>Inputs</span>";

echo "<form action-xhr='/edit-xhr/' method='post' class='admin-page-form' id='save' on=\"
		submit:
			admin-page-form-snackbar-ready.hide,
			admin-page-form-save.hide;
		submit-error:
			admin-page-form-save.show;
		submit-success:
			admin-page-form-save.show
		\">";

echo "<input type='hidden' name='entry_id' value='$page_temp'>";

// function create_inputs($entry_info, $input_backend, $input_descriptor, $input_type = "input-text", $language_toggle = "on", $visibility_manual = null, $possibilities_array = []) {

function create_inputs($entry_info, $input_backend, $language_temp, $input_descriptor, $input_type, $hidden_temp = null, $possibilities_array = []) {

	global $site_info;
	
//	$echo_section = null;

	if (!(empty($language_temp))):
		$placeholder_temp = ucfirst($input_descriptor)." / ". ucfirst($language_temp);
		$id_temp = $language_temp."-".$input_descriptor;
		$name_temp = $input_backend."[".$language_temp."]";
		if (isset($entry_info[$input_backend][$language_temp])): $value_temp = trim($entry_info[$input_backend][$language_temp]); endif;
	else:
		$placeholder_temp = ucfirst($input_descriptor);
		$id_temp = $input_descriptor;
		$name_temp = $input_backend;
		if (isset($entry_info[$input_backend])):
			$value_temp = $entry_info[$input_backend];
		elseif (isset($entry_info['appendix'][$input_backend])):
//			$name_temp = "appendix[".$name_temp."]";
			$value_temp = $entry_info['appendix'][$input_backend]; endif;
		if (!(is_array($value_temp))): $value_temp = trim($value_temp); endif;
		endif;
	
	$multiple_temp = null;
	if ($input_type == "amp-selector-single"):
		endif;
	if ($input_type == "amp-selector-multiple"):
		$name_temp .= "[]";
		$multiple_temp = "multiple";
		endif;

//	$echo_temp .= "<div class='input-button-wrapper'><span role='button' tabindex='0' class='input-button' id='wrapper-".$id_temp."-button' on='tap:wrapper-".$id_temp.".show,wrapper-".$id_temp."-button.hide' ".$button_hidden_temp.">Show:  ".$placeholder_temp."</span></div>";

	$echo_temp .= "<div class='wrapper-input' id='wrapper-".$id_temp."' ".$hidden_temp.">";
		
	$echo_temp .= "<label for='".$name_temp."'>". $placeholder_temp ."</label>";
	
	$properties_temp = "name='".$name_temp."' placeholder='". $placeholder_temp ."'";
	
	if (in_array($input_type, ["amp-selector-single", "amp-selector-multiple"])):
		if (!(is_array($value_temp))): $value_temp = [ $value_temp ]; endif;
		$value_temp = array_unique($value_temp);
		$echo_temp .= "<input type='hidden' name='".$name_temp."' value=' '>";
		$echo_temp .= "<amp-selector layout='container' ".$properties_temp." ".$multiple_temp.">";
		foreach ($value_temp as $value_temp_temp):
			if (empty(trim($value_temp_temp))): continue; endif;
			if (!(isset($possibilities_array[$value_temp_temp]))): continue; endif;
			$echo_temp .= "<span option='".$value_temp_temp."' selected>".$possibilities_array[$value_temp_temp]."</span>";
			endforeach;
		foreach ($possibilities_array as $value_temp_temp => $frontend_temp_temp):
			if (in_array($value_temp_temp, $value_temp)): continue; endif;
			$echo_temp .= "<span option='".$value_temp_temp."'>".$frontend_temp_temp."</span>";
			endforeach;
		$echo_temp .= "</amp-selector>";
	elseif ($input_type == "textarea-big"):
		$echo_temp .= "<textarea ".$properties_temp.">".$value_temp."</textarea>";
	elseif ($input_type == "textarea-small"):
		$echo_temp .= "<textarea ".$properties_temp." class='textarea-small'>".$value_temp."</textarea>";
	elseif ($input_type == "input-date"):
		$echo_temp .= "<input ".$properties_temp." type='date' value='".htmlspecialchars($value_temp, ENT_QUOTES)."'>";
	else:
		$echo_temp .= "<input ".$properties_temp." type='text' value='".htmlspecialchars($value_temp, ENT_QUOTES)."' maxlength='150'>";
		endif;	

//	$echo_temp .= "<div class='input-button-wrapper'><span class='input-button' role='button' tabindex='0' on='tap:wrapper-".$id_temp.".hide,wrapper-".$id_temp."-button.show'>Hide: ".$placeholder_temp."</span></div>";

	$echo_temp .= "</div>";

//	$echo_temp .= "<div class='input-button-wrapper'><span class='input-button' role='button' tabindex='0' on='tap:wrapper-".$id_temp.".toggleVisibility'>Toggle: ".$placeholder_temp."</span></div>";
	
	echo $echo_temp; // Because it's stored as a string, we can also use this format to prepend or append onto $echo_section

	}

foreach ($languages_array as $language_temp):
	create_inputs($entry_info, "name", $language_temp, "title", "input-text", $toggle_array["wrapper-".$language_temp."-title"]);
	endforeach;

foreach ($languages_array as $language_temp):
	create_inputs($entry_info, "summary", $language_temp, "headline", "textarea-small", $toggle_array["wrapper-".$language_temp."-headline"]);
	endforeach;

foreach ($languages_array as $language_temp):
	create_inputs($entry_info, "body", $language_temp, "body", "textarea-big", $toggle_array["wrapper-".$language_temp."-body"]);
	endforeach;

create_inputs($entry_info, "studies", null, "endnotes", "textarea-big", $toggle_array["wrapper-endnotes"]);

if (isset($site_info['appendix_array'][$entry_info['type']])):
	echo "<div class='wrapper-input' id='wrapper-appendices' ".$toggle_array["wrapper-appendices"].">";
	foreach ($site_info['appendix_array'][$entry_info['type']] as $appendix_key => $appendix_type):

		$possibilities_array = [];

		// For a "unit" only give it offices and units
		if ($appendix_key == "unit"):
			foreach ($information_array as $entry_id_temp => $entry_info_temp):
				if ($entry_info_temp['type'] !== "offices-units"): continue; endif;
				$possibilities_array[$entry_id_temp] = $entry_info_temp['header'] . " • ". $site_info['category_array'][$entry_info_temp['type']];
				endforeach;
			endif;

		create_inputs($entry_info, $appendix_key, null, str_replace("_", " ", $appendix_key), $appendix_type, null, $possibilities_array);
		endforeach;
	echo "</div>";
	endif;

echo "<div id='wrapper-metadata' hidden>";

	echo "<label for='entry-link'><a href='https://".$domain."/".$page_temp."/' target='_blank'>Entry URL ►</a></label>";
	echo "<input name='entry-link' type='text' value='".$domain."/".$page_temp."/' readonly>";
	echo "<div class='input-button-wrapper'>";
		echo "<div class='input-button' role='button' tabindex='0' on='tap:delete-popover'>&#x2B19; Delete entry</div>";
		echo "</div>";

	create_inputs($entry_info, "date_published", null, "Published date", "input-date");

	$possibilities_array = [];
	foreach ($information_array as $entry_id_temp => $entry_info_temp):
		$possibilities_array[$entry_id_temp] = $entry_info_temp['header'] . " • ". $site_info['category_array'][$entry_info_temp['type']];
		endforeach;
	create_inputs($entry_info, "parents", null, "parents", "amp-selector-multiple", null, $possibilities_array);
	create_inputs($entry_info, "children", null, "children", "amp-selector-multiple", null, $possibilities_array);

	create_inputs($entry_info, "type", null, "Type", "amp-selector-single", null, $site_info['category_array']);

	echo "</div>";

echo "<br><br><br><br><br>";

echo "<div id='admin-page-form-snackbar'>";
	echo "<div id='admin-page-form-snackbar-ready'>Ready...</div>";
	echo "<div submitting>Submitting...</div>";
	echo "<div submit-error><template type='amp-mustache'>Error. {{{message}}}</template></div>";
	echo "<div submit-success><template type='amp-mustache'>{{{message}}}</template></div>";
	echo "</div>";

echo "<div id='admin-page-form-save' role='button' tabindex='0' on='tap:pageState.refresh,save.submit'>Save</div>";

echo "</form>"; ?>
