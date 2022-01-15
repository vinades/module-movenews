<?php

/**
 * @Project NUKEVIET 4.x
 * @Author VINADES.,JSC (contact@vinades.vn)
 * @Copyright (C) 2014 VINADES.,JSC. All rights reserved
 * @License GNU/GPL version 2 or any later version
 * @Createdate 2-10-2010 20:59
 */

if (!defined('NV_IS_FILE_ADMIN')) {
    die('Stop!!!');
}

$page_title = $lang_module['main'];
$base_url = NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name;

$array_module_news = [];

foreach ($site_mods as $mod_title => $mod) {
    if ($mod['module_file'] == 'news') {
        $array_module_news[$mod_title] = $mod['custom_title'];
    }
}

$error = [];

$request = [];
$request['from_module'] = $nv_Request->get_title('fm', 'get', '');
$request['to_module'] = $nv_Request->get_title('tm', 'get', '');
if (!isset($array_module_news[$request['from_module']])) {
    $request['from_module'] = '';
}
if (!isset($array_module_news[$request['to_module']])) {
    $request['to_module'] = '';
}

$is_submit = $nv_Request->isset_request('fm', 'get');
$data_success = false;
if ($is_submit) {
    if (empty($is_submit)) {
        ;
    }
}

$xtpl = new XTemplate('main.tpl', NV_ROOTDIR . '/themes/' . $global_config['module_theme'] . '/modules/' . $module_file);
$xtpl->assign('LANG', $lang_module);
$xtpl->assign('GLANG', $lang_global);
$xtpl->assign('NV_BASE_ADMINURL', NV_BASE_ADMINURL);
$xtpl->assign('NV_NAME_VARIABLE', NV_NAME_VARIABLE);
$xtpl->assign('NV_OP_VARIABLE', NV_OP_VARIABLE);
$xtpl->assign('NV_LANG_VARIABLE', NV_LANG_VARIABLE);
$xtpl->assign('NV_LANG_DATA', NV_LANG_DATA);
$xtpl->assign('MODULE_NAME', $module_name);
$xtpl->assign('OP', $op);
$xtpl->assign('NV_CHECK_SESSION', NV_CHECK_SESSION);

foreach ($array_module_news as $mod => $title) {
    $mod = [
        'key' => $mod,
        'title' => $title,
        'selected_from' => $request['from_module'] == $mod ? ' selected="selected"' : '',
        'selected_to' => $request['to_module'] == $mod ? ' selected="selected"' : ''
    ];
    $xtpl->assign('MOD', $mod);
    $xtpl->parse('main.mod1');
    $xtpl->parse('main.mod2');
}

$xtpl->parse('main');
$contents = $xtpl->text('main');

include NV_ROOTDIR . '/includes/header.php';
echo nv_admin_theme($contents);
include NV_ROOTDIR . '/includes/footer.php';
