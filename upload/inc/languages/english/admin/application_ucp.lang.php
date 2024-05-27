<?php
//Infos
$l['application_ucp_info_name'] = 'personal description in the UCP (Risuena)';
$l['application_ucp_info_descr'] = 'Option to have a custom fillable section for profiles/character information in the UCP';

$l['application_ucp_permission'] = 'Can create/manage profile fields?';
$l['application_ucp_name'] = 'Profile in the UCP';
$l['application_ucp_menu'] = 'Profile Settings';

$l['application_ucp_overview'] = 'Overview';
$l['application_ucp_overview_appl'] = 'An overview of all profile fields';
$l['application_ucp_overview_sort'] = 'Sorting';
$l['application_ucp_overview_opt'] = 'Options';

$l['application_ucp_delete'] = 'delete';
$l['application_ucp_deactivate'] = 'deactivated';
$l['application_ucp_activate'] = 'activated';

$l['application_ucp_delete_ask'] = "Should the field really be deleted? Warning, the users' content will also be deleted! Otherwise, just deactivate the field.";
$l['application_ucp_deactivate_ask'] = "Should the field really be deactivated? The content will not be deleted and the data will be retained.";
$l['application_ucp_activate_ask'] = "Should the field really be activated? The previously hidden content will be displayed again.";

$l['application_ucp_manageusers'] = 'Manage Profiles';
$l['application_ucp_manageusers_dscr'] = 'Manage and edit user profiles';
$l['application_ucp_manageusers_all'] = 'Overview of all users and the option to manage their profiles.';

$l['application_ucp_manageusers_manage'] = 'Manage';
$l['application_ucp_manageusers_application'] = 'Manage Profile';
$l['application_ucp_manageusers_profile'] = 'View Profile';

$l['application_ucp_edituser'] = 'Edit Profile.';

$l['application_ucp_createfieldtype'] = 'Create Profile Field';
$l['application_ucp_createfieldtype_dscr'] = 'Here you can create a profile field and configure all settings.';
$l['application_ucp_editfieldtype'] = 'Edit Profile Field';
$l['application_ucp_editfieldtype_dscr'] = 'Here you can edit a profile field and change all settings.';

$l['application_ucp_formname'] = 'Create Profile Field';
$l['application_ucp_formname_edit'] = 'Edit Profile Field';

$l['application_ucp_add_name'] = 'Name/Identifier (unique!) of the field';
$l['application_ucp_add_name_descr'] = 'The designation for the field, no special characters, no spaces. Serves as an identifier.';

$l['application_ucp_add_descr'] = 'Field Description';
$l['application_ucp_add_descr_descr'] = 'The description for the field.';

$l['application_ucp_add_fieldlabel'] = 'Field Label';
$l['application_ucp_add_fieldlabel_descr'] = 'What should be displayed before the field? For example, First Name';

$l['application_ucp_add_fieldtyp'] = 'Field Type';
$l['application_ucp_add_fieldtyp_descr'] = 'What type should the field have?';

$l['application_ucp_add_fieldoptions'] = 'Field Options';
$l['application_ucp_add_fieldoptions_descr'] = 'The possible response options for select, select-multiple, checkbox, and radio buttons. Leave empty otherwise.<br/>Separated by \',\'. For example: Ravenclaw, Hufflepuff, Gryffindor, Slytherin';

$l['application_ucp_add_fieldeditable'] = 'Editable?';
$l['application_ucp_add_fieldeditable_descr'] = 'Is the field editable after the profile is accepted?';

$l['application_ucp_add_fieldmandatory'] = 'Mandatory Field?';
$l['application_ucp_add_fieldmandatory_descr'] = 'Is the field mandatory?';

$l['application_ucp_add_fielddependency'] = 'Dependency?';
$l['application_ucp_add_fielddependency_descr'] = 'Is the field only visible if it depends on the value of another field? If yes, select it here.';

$l['application_ucp_add_fielddependencyval'] = 'Dependency Value?';
$l['application_ucp_add_fielddependencyval_descr'] = 'Which selectable option should the field depend on? For example, "Hogwarts".<br/>For multiple options, separate with commas but <b>without</b> spaces: For example, "Ravenclaw,Hufflepuff".<br/>Leave empty if no dependency.<br/>
 <b>Note:</b> exactly as written in the options of the field selected for dependency!';

$l['application_ucp_add_fieldpostbit'] = 'Display in Postbit?';
$l['application_ucp_add_fieldpostbit_descr'] = 'Should the field be displayed in the postbit?';

$l['application_ucp_add_fieldprofile'] = 'Display in Profile?';
$l['application_ucp_add_fieldprofile_descr'] = 'Should the field be displayed in the profile?';

$l['application_ucp_add_fieldmember'] = 'Display in Memberlist?';
$l['application_ucp_add_fieldmember_descr'] = 'Should the field be displayed in the memberlist?';

$l['application_ucp_add_fieldtemplate'] = 'Template?';
$l['application_ucp_add_fieldtemplate_descr'] = 'Here you can create a template for the field. For example, the code for a timeline.';

$l['application_ucp_add_fieldhtml'] = 'Allow HTML?';
$l['application_ucp_add_fieldhtml_descr'] = 'Should HTML be allowed in the field?';

$l['application_ucp_add_fieldmybb'] = 'Allow MyBB Code?';
$l['application_ucp_add_fieldmybb_descr'] = 'Should MyBB code be allowed in the field?';

$l['application_ucp_add_fieldimg'] = 'Allow IMG Tag?';
$l['application_ucp_add_fieldimg_descr'] = 'Can images be embedded in the field using the IMG tag?';

$l['application_ucp_add_searchable'] = 'Searchable?';
$l['application_ucp_add_searchable_descr'] = 'Should the field be searchable in the memberlist?';

$l['application_ucp_add_suggestion'] = 'Search Suggestions?';
$l['application_ucp_add_suggestion_descr'] = 'Should suggestions for the search be made based on already entered values?';

$l['application_ucp_add_guest'] = 'Visible to Guests?';
$l['application_ucp_add_guest_descr'] = 'Can guests see the field?';

$l['application_ucp_add_guest_content'] = 'Alternative Content for Guests';
$l['application_ucp_add_guest_content_descr'] = 'What should guests see instead of the actual content? Leave empty if it should just be hidden.
For the theme folder, specify $themepath, e.g., $themepath/default.png';

$l['application_ucp_add_container'] = 'HTML Element around Value?';
$l['application_ucp_add_container_descr'] = 'Should an HTML element be placed around the variable that outputs the plain text value? This also contains the class "is_empty", which can be hidden in CSS with display:none;';

$l['application_ucp_add_active'] = 'Active?';
$l['application_ucp_add_active_descr'] = 'If No: User data is not deleted, the field is just hidden, no longer editable, and not displayed anywhere.';

$l['application_ucp_add_fieldvideo'] = 'Allow Videos?';
$l['application_ucp_add_fieldvideo_descr'] = 'Can videos be embedded in the field?';

$l['application_ucp_add_fieldsort'] = 'Display Order?';
$l['application_ucp_add_fieldsort_descr'] = 'At what position should the field be displayed?';

$l['application_ucp_save'] = 'Save';

// Errors
$l['application_ucp_err_name'] = 'Please provide a name.';
$l['application_ucp_err_name_exists'] = 'The name already exists. Please use a unique identifier.';

$l['application_ucp_err_name_sonder'] = 'The name must not contain special characters or spaces.';
$l['application_ucp_err_label'] = 'Please provide a label.';
$l['application_ucp_err_fieldtyp'] = 'Please select a field type.';
$l['application_ucp_err_fieldoptions'] = 'You have selected a field type that requires options. Please fill in.';
$l['application_ucp_err_dependency_value_empty'] = 'You selected a dependency but did not specify which value your created field should depend on.';
$l['application_ucp_err_dependency_value_wrong'] = 'The specified option does not exist for the selected field. Typo? Please also check for correct case.';

$l['application_ucp_err_delete'] = "The field could not be deleted.";

$l['application_ucp_success'] = 'Successfully saved.';