<?php

function vmfds_events_cat_submission() {
	// extract form data from POST
	$data = $_POST["koi"]['ko_event_categories'];
	kota_process_data('ko_event_categories', $data, "post");
	return $data;
}


function my_action_handler_vmfds_events_cat_list() {
	$_SESSION['show'] = 'vmfds_events_cat_list';
}

function my_action_handler_vmfds_events_cat_add() {
	$_SESSION['show'] = 'vmfds_events_cat_add';
}

function my_action_handler_vmfds_events_cat_edit() {
	$_SESSION['show'] = 'vmfds_events_cat_edit';
}

function my_action_handler_vmfds_events_cat_del() {
	global $info;
	
	db_delete_data('ko_event_categories', 'WHERE (id='.$_POST['id'].')');
	$info = 'vmfds_events_cat_deleted';
	$_SESSION['show'] = 'vmfds_events_cat_list';
}


function my_action_handler_vmfds_events_cat_submit_a() {
	global $info;

	$cat = vmfds_events_cat_submission();
	db_insert_data('ko_event_categories', $cat);

	// show list:
	$info = 'vmfds_events_cat_added';
	$_SESSION['show'] = 'vmfds_events_cat_list';
}

function my_action_handler_vmfds_events_cat_submit_e() {
	global $info; 
	
	//Check for edit rights
	//if($access['daten']['MAX'] < 3) return;

	list($table, $columns, $id, $hash) = explode("@", $_POST["id"]);
	if(FALSE === ($id = format_userinput($id, "uint", TRUE))) return;

	$cat = vmfds_events_cat_submission();
	db_update_data('ko_event_categories', 'WHERE (id='.$id.')', $cat);

	// show list:
	$info = 'vmfds_events_cat_edited';
	$_SESSION['show'] = 'vmfds_events_cat_list';
}


function vmfds_events_category_form($id) {
	global $smarty, $KOTA;
	global $access;

	if($access['daten']['MAX'] < 3) return;
	
	if (!$id) {
		// new publisher
		$mode = 'neu';
		$id = 0;
	} else {
		// editing, so: preload data
		$mode = 'edit';
	}

	
	$form_data['title'] = $mode == 'neu' ? getLL('my_vmfds_events_add_category') : getLL('my_vmfds_events_edit_category');
	$form_data['submit_value'] = getLL('save');
	$form_data['action'] = ($mode == 'neu' ? 'vmfds_events_cat_submit_a' : 'vmfds_events_cat_submit_e');
	$form_data['cancel'] = 'vmfds_events_cat_list';

	ko_multiedit_formular('ko_event_categories', '', $id, '', $form_data);
}

function my_show_case_vmfds_events_cat_add() {
	vmfds_events_category_form();
}

function my_show_case_vmfds_events_cat_edit() {
	vmfds_events_category_form($_POST['id']);
}


function my_show_case_vmfds_events_cat_list() {
	global $ko_path;
	global $access;

	if($access['daten']['MAX'] < 3) return;

	$list = new kOOL_listview();

	// no filter!
	$z_where = '';

	// set limits and order
	$z_limit = 'LIMIT ' . ($_SESSION['show_start']-1) . ', ' . $_SESSION['show_limit'];
	$rows = db_get_count('ko_event_categories', 'id', $z_where);
	$order = ($_SESSION['sort_eventscats']) ? ' ORDER BY '.$_SESSION['sort_eventscats'].' '.$_SESSION['sort_eventscats_order'] : '';

	// get data
	//var_dump($z_where, $order, $z_limit);
	$data = db_select_data('ko_event_categories', 'WHERE 1=1 '.$z_where, '*', $order, $z_limit);
	
	$list->init('daten', 'ko_event_categories', array("chk", "edit", "delete"), $_SESSION["show_start"], $_SESSION["show_limit"]);
	$list->setTitle(getLL('my_event_publisher_list_title'));
	$list->setAccessRights(array('edit' => 3, 'delete' => 3), $access['daten']);
	$list->setActions(array("edit" 		=> array("action" => "vmfds_events_cat_edit"),
							"delete" 	=> array("action" => "vmfds_events_cat_del", "confirm" => TRUE))
										);
	$list->setStats($rows);
	$list->setSort(TRUE, "setsorteventscats", $_SESSION["sort_eventscats"], $_SESSION["sort_eventscats_order"]);

	if($output) {
		$list->render($data);
	} else {
		print $list->render($data);
	}
}

