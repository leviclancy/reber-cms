<? $entry_info = nesty_page($page_temp);
$entry_info = $entry_info[$page_temp];

$retrieve_page->execute(["page_id"=>$page_temp]);
$result = $retrieve_page->fetchAll();
foreach ($result as $row):
	$entry_info['name']		= json_decode($row['name'], true);
	$entry_info['summary']		= json_decode($row['summary'], true);
	$entry_info['body']		= json_decode($row['body'], true);
	$entry_info['studies']		= $row['studies'];
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
	$toggle_array[] = "wrapper-".$language_temp."-title";
	$toggle_array[] = "wrapper-".$language_temp."-headline";
	$toggle_array[] = "wrapper-".$language_temp."-body";
	endforeach;
$toggle_array[] = "wrapper-endnotes";
$toggle_array[] = "wrapper-more";

echo "<table>";
echo "<thead><tr><th>Language</th><th>Title</th><th>Headline</th><th>Body</th><th>More...</th></tr></thead>";
echo "<tbody>";
foreach ($languages_array as $language_temp):
	echo "<tr>";
	echo "<td>".ucfirst($language_temp)."</td>";
	echo "<td><span tabindex='0' role='button' on='tap:".implode(".hide;", $toggle_array).".hide;wrapper-".$language_temp."-title.show'>Open</span></td>";
	echo "<td><span>Open</span></td>";
	echo "<td><span>Open</span></td>";
	echo "<td></td>";
	echo "</tr>";
	endforeach;

	echo "<tr>";
	echo "<td colpan='4'></td>";
	echo "<td><span>Endnotes</span></td>";
	echo "</tr>";

	echo "<tr>";
	echo "<td colpan='4'></td>";
	echo "<td><span>More...</span></td>";
	echo "</tr>";

	echo "</tbody>";
	echo "</table>";

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
	
	if (in_array($input_type, ["amp-selector-single", "amp-selector-multiple"])):
		if (!(is_array($value_temp))): $value_temp = [ $value_temp ]; endif;
		$value_temp = array_unique($value_temp);
		$echo_temp .= "<input type='hidden' name='".$name_temp."' value=' '>";
		$echo_temp .= "<amp-selector layout='container' name='".$name_temp."' ".$multiple_temp.">";
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
		$echo_temp .= "<textarea	name='".$name_temp."' placeholder='". $placeholder_temp ."' id='".$id_temp."'>".$value_temp."</textarea>";
	elseif ($input_type == "textarea-small"):
		$echo_temp .= "<textarea	name='".$name_temp."' placeholder='". $placeholder_temp ."' id='".$id_temp."' class='textarea-small'>".$value_temp."</textarea>";
	elseif ($input_type == "input-date"):
		$echo_temp .= "<input		name='".$name_temp."' placeholder='". $placeholder_temp ."' id='".$id_temp."' type='date' value='".htmlspecialchars($value_temp, ENT_QUOTES)."'>";
	else:
		$echo_temp .= "<input		name='".$name_temp."' placeholder='". $placeholder_temp ."' id='".$id_temp."' type='text' value='".htmlspecialchars($value_temp, ENT_QUOTES)."' maxlength='150'>";
		endif;	

//	$echo_temp .= "<div class='input-button-wrapper'><span class='input-button' role='button' tabindex='0' on='tap:wrapper-".$id_temp.".hide,wrapper-".$id_temp."-button.show'>Hide: ".$placeholder_temp."</span></div>";

	$echo_temp .= "</div>";

//	$echo_temp .= "<div class='input-button-wrapper'><span class='input-button' role='button' tabindex='0' on='tap:wrapper-".$id_temp.".toggleVisibility'>Toggle: ".$placeholder_temp."</span></div>";
	
	echo $echo_temp; // Because it's stored as a string, we can also use this format to prepend or append onto $echo_section

	}

foreach ($languages_array as $language_temp):

	$hidden_temp = "hidden"; if (!(empty($entry_info['name'][$language_temp]))): $hidden_temp = null; endif;
	create_inputs($entry_info, "name", $language_temp, "title", "input-text", $hidden_temp);

	$hidden_temp = "hidden"; if (!(empty($entry_info['summary'][$language_temp]))): $hidden_temp = null; endif;
	create_inputs($entry_info, "summary", $language_temp, "headline", "textarea-small", $hidden_temp);
								 
	$hidden_temp = "hidden"; if (!(empty($entry_info['body'][$language_temp]))): $hidden_temp = null; endif;
	create_inputs($entry_info, "body", $language_temp, "body", "textarea-big", $hidden_temp);
	endforeach;

$hidden_temp = "hidden"; if (!(empty($entry_info['studies']))): $hidden_temp = null; endif;
create_inputs($entry_info, "studies", null, "endnotes", "textarea-big", $hidden_temp);

if (!(isset($site_info['appendix_array'][$entry_info['type']]))): $site_info['appendix_array'][$entry_info['type']] = []; endif;
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

echo "<div id='wrapper-more' hidden>";

	create_inputs($entry_info, "date_published", null, "Published date", "input-date");

	echo "<label for='entry-link'><a href='https://".$domain."/".$page_temp."/' target='_blank'>Entry URL ►</a></label>";
	echo "<input name='entry-link' type='text' value='".$domain."/".$page_temp."/' readonly>";
	echo "<div class='input-button-wrapper'>";
		echo "<div class='input-button' role='button' tabindex='0' on='tap:delete-popover'>&#x2B19; Delete entry</div>";
		echo "</div>";

	$possibilities_array = [];
	foreach ($information_array as $entry_id_temp => $entry_info_temp):
		$possibilities_array[$entry_id_temp] = $entry_info_temp['header'] . " • ". $site_info['category_array'][$entry_info_temp['type']];
		endforeach;
	create_inputs($entry_info, "parents", null, "parents", "amp-selector-multiple", $possibilities_array);
	create_inputs($entry_info, "children", null, "children", "amp-selector-multiple", $possibilities_array);

	create_inputs($entry_info, "type", null, "Type", "amp-selector-single", $site_info['category_array']);

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
