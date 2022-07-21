<?php
//Infos
$l['application_ucp_info_name'] = 'Application in th UCP by risuena';
$l['application_ucp_info_descr'] = 'Creates an own Site in UCP for Applications';


$l['application_ucp_permission'] = 'Application Alerts';
$l['application_ucp_name'] = 'application in UCP';
$l['application_ucp_menu'] = 'application settings';

$l['application_ucp_overview'] = 'Overview';
$l['application_ucp_overview_appl'] = 'overview of all application fields';
$l['application_ucp_overview_sort'] = 'sort';
$l['application_ucp_overview_opt'] = 'settings';

$l['application_ucp_delete'] = 'delete';
$l['application_ucp_deactivate'] = 'deactive';
$l['application_ucp_activate'] = 'active';

$l['application_ucp_delete_ask'] = "Do you really want to delete this field? Careful, all the content of users will be deleted! Otherwise, just deactivate this field.";
$l['application_ucp_deactivate_ask'] = "Do you really want to deactivate this field? The content won't be deleted, but will be hidden and the data is kept.";
$l['application_ucp_activate_ask'] = "Do you really want to activate this field? The hidden content will be shown again.";

$l['application_ucp_manageusers'] = 'Manage applications';
$l['application_ucp_manageusers_dscr'] = 'manage and edit applications of characters.';
$l['application_ucp_manageusers_all'] = 'Overview of all characters, gives you the possibility to manage them.';

$l['application_ucp_manageusers_manage'] = 'Manage';
$l['application_ucp_manageusers_application'] = 'manage applications';


$l['application_ucp_edituser'] = 'edit application';


$l['application_ucp_createfieldtype'] = 'create applicationfield';
$l['application_ucp_createfieldtype_dscr'] = 'This is whre you can create a applicationfield and make all settings';
$l['application_ucp_editfieldtype'] = 'edit applicationfield';
$l['application_ucp_editfieldtype_dscr'] = 'you can edit this applicationfield and change settings.';

$l['application_ucp_formname'] = 'create applicationfield';
$l['application_ucp_formname_edit'] = 'edit applicationfield';

$l['application_ucp_add_name'] = 'name of field';
$l['application_ucp_add_name_descr'] = 'the term for field, no special characters. Important for identification.';

$l['application_ucp_add_fieldlabel'] = 'label of field';
$l['application_ucp_add_fieldlabel_descr'] = 'Display in front of input? For example surname';

$l['application_ucp_add_fieldtyp'] = 'type of field';
$l['application_ucp_add_fieldtyp_descr'] = 'what type this field should be?';

$l['application_ucp_add_fieldoptions'] = 'options of field';
$l['application_ucp_add_fieldoptions_descr'] = 'options of field when select, select-multiple, checkbox and radiobuttons 
Leave empty if not needed.<br/>seperate options with \',\'. Example: Ravenclaw, Hufflepuff, Gryffindor, Slytherin';

$l['application_ucp_add_fieldeditable'] = 'Editable?';
$l['application_ucp_add_fieldeditable_descr'] = 'can a user edit this field after his wob?';

$l['application_ucp_add_fieldmandatory'] = 'Mandatory?';
$l['application_ucp_add_fieldmandatory_descr'] = 'is this field mandatory?';

$l['application_ucp_add_fielddependency'] = 'Dependency?';
$l['application_ucp_add_fielddependency_descr'] = 'is this field only visible, when it depends on an other? If yes, choose it in the list.';

$l['application_ucp_add_fielddependencyval'] = 'Value of Dependency?';
$l['application_ucp_add_fielddependencyval_descr'] = 'From which value the field depends? Careful: It has to be exactly written like the answeroption of field.';

$l['application_ucp_add_fieldpostbit'] = 'display in postbit?';
$l['application_ucp_add_fieldpostbit_descr'] = 'is this field visible in postbit?';

$l['application_ucp_add_fieldprofile'] = 'display in profile?';
$l['application_ucp_add_fieldprofile_descr'] = 'is this field visible in profile?';

$l['application_ucp_add_fieldtemplate'] = 'Template?';
$l['application_ucp_add_fieldtemplate_descr'] = 'is there a template for this field?';

$l['application_ucp_add_fieldhtml'] = 'enable HTML?';
$l['application_ucp_add_fieldhtml_descr'] = 'is html allowed?';

$l['application_ucp_add_fieldmybb'] = 'enable MyBB-Code?';
$l['application_ucp_add_fieldmybb_descr'] = 'is MyBB-Code allowed?';

$l['application_ucp_add_fieldimg'] = 'enable IMG-Tag?';
$l['application_ucp_add_fieldimg_descr'] = 'are images allowed?';

$l['application_ucp_add_fieldvideo'] = 'enable Videos?';
$l['application_ucp_add_fieldvideo_descr'] = 'are videos allowed?';

$l['application_ucp_add_fieldsort'] = 'Displayorder?';
$l['application_ucp_add_fieldsort_descr'] = 'sets the order of display?';

$l['application_ucp_save'] = 'save';

// Errors
$l['application_ucp_err_name'] = 'add a term.';
$l['application_ucp_err_name_sonder'] = 'no specialchars in term.';
$l['application_ucp_err_label'] = 'add a label.';
$l['application_ucp_err_fieldtyp'] = 'choose a fieldtype.';
$l['application_ucp_err_fieldoptions'] = 'you select a fieldtyp which needed options, please add some.';
$l['application_ucp_err_dependency_value_empty'] = 'you\'ve choose a dependency but forgot to add a value for the dependency.';
$l['application_ucp_err_dependency_value_wrong'] = 'the choosen dependency value doesn\'t exist. Typo? Also consider large and lower case.';

$l['application_ucp_err_delete'] = "The field could not be deleted.";

$l['application_ucp_success'] = 'saved successfully';