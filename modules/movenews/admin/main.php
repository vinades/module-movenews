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
$base_url = NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name;

$array_module_news = [];

foreach ($site_mods as $mod_title => $mod) {
    if ($mod['module_file'] == 'news') {
        $array_module_news[$mod_title] = $mod['custom_title'];
    }
}

$error = [];

$request = [];
$request['fm'] = $nv_Request->get_title('fm', 'get', '');
$request['tm'] = $nv_Request->get_title('tm', 'get', '');
$request['fc'] = $nv_Request->get_typed_array('fc', 'get', 'int', []);
$request['tc'] = $nv_Request->get_typed_array('tc', 'get', 'int', []);
$request['c2'] = $nv_Request->get_absint('c2', 'get', 0);
if (!isset($array_module_news[$request['fm']])) {
    $request['fm'] = '';
}
if (!isset($array_module_news[$request['tm']])) {
    $request['tm'] = '';
}

// Lấy chuyên mục
$array_from_cats = $array_to_cats = [];
if ($request['fm']) {
    $sql = 'SELECT * FROM ' . NV_PREFIXLANG . '_' . $site_mods[$request['fm']]['module_data'] . '_cat ORDER BY sort ASC';
    $array_from_cats = $nv_Cache->db($sql, 'catid', $request['fm']);
}
if ($request['tm']) {
    $sql = 'SELECT * FROM ' . NV_PREFIXLANG . '_' . $site_mods[$request['tm']]['module_data'] . '_cat ORDER BY sort ASC';
    $array_to_cats = $nv_Cache->db($sql, 'catid', $request['tm']);
}
$request['fc'] = array_intersect($request['fc'], array_keys($array_from_cats));
$request['tc'] = array_intersect($request['tc'], array_keys($array_to_cats));

if (!in_array($request['c2'], $request['tc'])) {
    $request['c2'] = 0;
}

$is_submit = $nv_Request->isset_request('fm', 'get');
$is_complete = false;
$array = [];

if ($is_submit) {
    if (empty($request['fm'])) {
        $error[] = $lang_module['err1'];
    } else {
        if (empty($request['fc'])) {
            $error[] = $lang_module['err3'];
        }
    }
    if (empty($request['tm'])) {
        $error[] = $lang_module['err2'];
    } else {
        if (empty($request['tc'])) {
            $error[] = $lang_module['err4'];
        }
        if (empty($request['c2'])) {
            $error[] = $lang_module['err5'];
        }
    }

    if (empty($error)) {
        $offset = $nv_Request->get_absint('o', 'get', 0);
        $num_updated = $nv_Request->get_absint('n', 'get', 0);

        $where = [];
        $where[] = 'catid IN(' . implode(',', $request['fc']) . ')';
        foreach ($request['fc'] as $catid) {
            $where[] = 'FIND_IN_SET(' . $catid . ', listcatid)';
        }

        // Lấy nhóm tin mặc định để đưa vào
        $sql = "SELECT bid FROM " . NV_PREFIXLANG . "_" . $site_mods[$request['tm']]['module_data'] . "_block_cat WHERE adddefault=1";
        $blocks = $db->query($sql)->fetchAll(PDO::FETCH_COLUMN);

        $sql = 'SELECT * FROM ' . NV_PREFIXLANG . '_' . $site_mods[$request['fm']]['module_data'] . '_rows
        WHERE (' . implode(' OR ', $where) . ') ORDER BY id ASC LIMIT 100 OFFSET ' . $offset;
        $result = $db->query($sql);
        while ($row = $result->fetch()) {
            $array[$row['id']] = [
                'title' => $row['title'],
                'status' => '--'
            ];

            // Kiểm tra trùng
            $sql = "SELECT id FROM " . NV_PREFIXLANG . "_" . $site_mods[$request['tm']]['module_data'] . "_rows
            WHERE title=" . $db->quote($row['title']) . " AND addtime=" . $row['addtime'];
            if ($db->query($sql)->fetchColumn()) {
                $array[$row['id']]['status'] = $lang_module['isexists'];
            } else {
                $num_updated++;
                $array[$row['id']]['status'] = $lang_module['iscopyed'];

                // Lấy và tạo dòng sự kiện
                $topicid = 0;
                if (!empty($row['topicid'])) {
                    $sql = "SELECT * FROM " . NV_PREFIXLANG . "_" . $site_mods[$request['fm']]['module_data'] . "_topics
                    WHERE topicid=" . $row['topicid'];
                    $from_topic = $db->query($sql)->fetch();

                    if (!empty($from_topic)) {
                        // Tìm xem có dòng sự kiện nào trùng không
                        $sql = "SELECT * FROM " . NV_PREFIXLANG . "_" . $site_mods[$request['tm']]['module_data'] . "_topics
                        WHERE alias=" . $db->quote($from_topic['alias']);
                        $to_topic = $db->query($sql)->fetch();

                        if (!empty($to_topic)) {
                            $topicid = $to_topic['topicid'];
                        } else {
                            $sql = "SELECT MAX(weight) FROM " . NV_PREFIXLANG . "_" . $site_mods[$request['tm']]['module_data'] . "_topics";
                            $weight = $db->query($sql)->fetchColumn() + 1;

                            $sql = "INSERT INTO " . NV_PREFIXLANG . "_" . $site_mods[$request['tm']]['module_data'] . "_topics (
                                title, alias, image, description, weight, keywords, add_time, edit_time
                            ) VALUES (
                                :title, :alias, :image, :description, :weight, :keywords, :add_time, :edit_time
                            )";
                            $array_insert = [];
                            $array_insert['title'] = $from_topic['title'];
                            $array_insert['alias'] = $from_topic['alias'];
                            $array_insert['image'] = '';
                            $array_insert['description'] = $from_topic['description'];
                            $array_insert['weight'] = $weight;
                            $array_insert['keywords'] = $from_topic['keywords'];
                            $array_insert['add_time'] = $from_topic['add_time'];
                            $array_insert['edit_time'] = $from_topic['edit_time'];

                            $tmp = $db->insert_id($sql, 'topicid', $array_insert);
                            if ($tmp) {
                                $topicid = $tmp;
                            }
                        }
                    }
                }

                // Lấy và tạo nguồn tin
                $sourceid = 0;
                if (!empty($row['sourceid'])) {
                    $sql = "SELECT * FROM " . NV_PREFIXLANG . "_" . $site_mods[$request['fm']]['module_data'] . "_sources
                    WHERE sourceid=" . $row['sourceid'];
                    $from_source = $db->query($sql)->fetch();

                    if (!empty($from_source)) {
                        // Tìm xem có nguồn tin nào trùng không
                        $sql = "SELECT * FROM " . NV_PREFIXLANG . "_" . $site_mods[$request['tm']]['module_data'] . "_sources
                        WHERE title=" . $db->quote($from_source['title']);
                        $to_source = $db->query($sql)->fetch();

                        if (!empty($to_source)) {
                            $sourceid = $to_source['sourceid'];
                        } else {
                            $sql = "SELECT MAX(weight) FROM " . NV_PREFIXLANG . "_" . $site_mods[$request['tm']]['module_data'] . "_sources";
                            $weight = $db->query($sql)->fetchColumn() + 1;

                            $sql = "INSERT INTO " . NV_PREFIXLANG . "_" . $site_mods[$request['tm']]['module_data'] . "_sources (
                                title, link, logo, weight, add_time, edit_time
                            ) VALUES (
                                :title, :link, :logo, :weight, :add_time, :edit_time
                            )";
                            $array_insert = [];
                            $array_insert['title'] = $from_source['title'];
                            $array_insert['link'] = $from_source['link'];
                            $array_insert['logo'] = '';
                            $array_insert['weight'] = $weight;
                            $array_insert['add_time'] = $from_source['add_time'];
                            $array_insert['edit_time'] = $from_source['edit_time'];

                            $tmp = $db->insert_id($sql, 'sourceid', $array_insert);
                            if ($tmp) {
                                $sourceid = $tmp;
                            }
                        }
                    }
                }

                // Xử lý ảnh đại diện nếu là ảnh local
                if (($row['homeimgthumb'] == 1 or $row['homeimgthumb'] == 2) and !empty($row['homeimgfile'])) {
                    $image = $site_mods[$request['fm']]['module_upload'] . '/' . $row['homeimgfile'];
                    // Ảnh thumb
                    $image1 = NV_ROOTDIR . '/' . NV_FILES_DIR . '/' . $image;
                    // Ảnh gốc
                    $image2 = NV_ROOTDIR . '/' . NV_UPLOADS_DIR . '/' . $image;
                    // Thư mục
                    $folder = explode('/', $image);
                    if (sizeof($folder) > 2) {
                        unset($folder[sizeof($folder) - 1], $folder[0]);
                        $folder = array_values($folder);
                    } else {
                        $folder = [];
                    }

                    $copy1 = copyImagesNews($image1, NV_ROOTDIR . '/' . NV_FILES_DIR . '/' . $site_mods[$request['tm']]['module_upload'], $folder);
                    $copy2 = copyImagesNews($image2, NV_ROOTDIR . '/' . NV_UPLOADS_DIR . '/' . $site_mods[$request['tm']]['module_upload'], $folder);

                    if ($copy1 > 0 and $copy2 > 0) {
                        $homeimgthumb = 1;
                        $homeimgfile = $row['homeimgfile'];
                    } elseif ($copy2 > 0) {
                        $homeimgthumb = 2;
                        $homeimgfile = $row['homeimgfile'];
                    } else {
                        $homeimgthumb = 0;
                        $homeimgfile = '';
                    }
                } else {
                    $homeimgthumb = $row['homeimgthumb'];
                    $homeimgfile = $row['homeimgfile'];
                }

                // Lưu vào bảng rows
                $sql = "INSERT INTO " . NV_PREFIXLANG . "_" . $site_mods[$request['tm']]['module_data'] . "_rows (
                    catid, listcatid, topicid, admin_id, author, sourceid,
                    addtime, edittime, status, weight, publtime, exptime,
                    archive, title, alias, hometext, homeimgfile, homeimgalt,
                    homeimgthumb, inhome, allowed_comm, allowed_rating, external_link,
                    hitstotal, hitscm, total_rating, click_rating, instant_active,
                    instant_template, instant_creatauto
                ) VALUES (
                    " . $request['c2'] . ", " . $db->quote(implode(',', $request['tc'])) . ",
                    " . $topicid . ", :admin_id, :author, " . $sourceid . ",
                    :addtime, :edittime, :status, :weight, :publtime, :exptime,
                    :archive, :title, :alias, :hometext, :homeimgfile, :homeimgalt,
                    :homeimgthumb, :inhome, :allowed_comm, :allowed_rating, :external_link,
                    :hitstotal, :hitscm, :total_rating, :click_rating, :instant_active,
                    :instant_template, :instant_creatauto
                )";
                $array_insert = [];
                $array_insert['admin_id'] = $row['admin_id'];
                $array_insert['author'] = $row['author'];
                $array_insert['addtime'] = $row['addtime'];
                $array_insert['edittime'] = $row['edittime'];
                $array_insert['status'] = $row['status'];
                $array_insert['weight'] = $row['weight'];
                $array_insert['publtime'] = $row['publtime'];
                $array_insert['exptime'] = $row['exptime'];
                $array_insert['archive'] = $row['archive'];
                $array_insert['title'] = $row['title'];
                $array_insert['alias'] = $row['alias'];
                $array_insert['hometext'] = $row['hometext'];
                $array_insert['homeimgfile'] = $homeimgfile;
                $array_insert['homeimgalt'] = $row['homeimgalt'];
                $array_insert['homeimgthumb'] = $homeimgthumb;
                $array_insert['inhome'] = $row['inhome'];
                $array_insert['allowed_comm'] = $row['allowed_comm'];
                $array_insert['allowed_rating'] = $row['allowed_rating'];
                $array_insert['external_link'] = $row['external_link'];
                $array_insert['hitstotal'] = $row['hitstotal'];
                $array_insert['hitscm'] = $row['hitscm'];
                $array_insert['total_rating'] = $row['total_rating'];
                $array_insert['click_rating'] = $row['click_rating'];
                $array_insert['instant_active'] = $row['instant_active'];
                $array_insert['instant_template'] = $row['instant_template'];
                $array_insert['instant_creatauto'] = $row['instant_creatauto'];

                $new_id = $db->insert_id($sql, 'id', $array_insert);
                if (!$new_id) {
                    continue;
                }

                // Chép vào bảng cat
                foreach ($request['tc'] as $catid) {
                    $sql = "INSERT IGNORE INTO " . NV_PREFIXLANG . "_" . $site_mods[$request['tm']]['module_data'] . "_" . $catid . "
                    SELECT * FROM " . NV_PREFIXLANG . "_" . $site_mods[$request['tm']]['module_data'] . "_rows WHERE id=" . $new_id;
                    $db->query($sql);
                }

                // Lấy detail để xử lý
                $sql = "SELECT * FROM " . NV_PREFIXLANG . "_" . $site_mods[$request['fm']]['module_data'] . "_detail WHERE id=" . $row['id'];
                $detail = $db->query($sql)->fetch();
                if (!empty($detail)) {
                    $files = [];
                    if (!empty($detail['files'])) {
                        $detail['files'] = array_filter(array_map('trim', explode(',', $detail['files'])));
                        foreach ($detail['files'] as $file) {
                            $file_path = $site_mods[$request['fm']]['module_upload'] . '/' . $file;

                            // File gốc
                            $file1 = NV_ROOTDIR . '/' . NV_UPLOADS_DIR . '/' . $file_path;

                            // Thư mục
                            $folder = explode('/', $file_path);
                            if (sizeof($folder) > 2) {
                                unset($folder[sizeof($folder) - 1], $folder[0]);
                                $folder = array_values($folder);
                            } else {
                                $folder = [];
                            }

                            $copy1 = copyImagesNews($file1, NV_ROOTDIR . '/' . NV_UPLOADS_DIR . '/' . $site_mods[$request['tm']]['module_upload'], $folder);
                            if ($copy1 > 0) {
                                $files[] = $file;
                            }
                        }
                    }
                    $files = empty($files) ? '' : implode(',', $files);

                    // Lưu detail
                    $sql = "INSERT IGNORE INTO " . NV_PREFIXLANG . "_" . $site_mods[$request['tm']]['module_data'] . "_detail (
                        id, titlesite, description, bodyhtml, keywords, sourcetext,
                        files, imgposition, layout_func, copyright, allowed_send, allowed_print, allowed_save
                    ) VALUES (
                        " . $new_id . ", " . $db->quote($detail['titlesite']) . ",
                        " . $db->quote($detail['description']) . ",
                        " . $db->quote($detail['bodyhtml']) . ",
                        " . $db->quote($detail['keywords']) . ",
                        " . $db->quote($detail['sourcetext']) . ",
                        " . $db->quote($files) . ",
                        " . $detail['imgposition'] . ",
                        " . $db->quote($detail['layout_func']) . ",
                        " . $detail['copyright'] . ",
                        " . $detail['allowed_send'] . ",
                        " . $detail['allowed_print'] . ",
                        " . $detail['allowed_save'] . "
                    )";
                    $db->query($sql);
                }

                // Lấy, tạo từ khóa và lưu từ khóa
                $sql = "SELECT tb1.keyword, tb2.* FROM
                " . NV_PREFIXLANG . "_" . $site_mods[$request['fm']]['module_data'] . "_tags_id tb1
                INNER JOIN " . NV_PREFIXLANG . "_" . $site_mods[$request['fm']]['module_data'] . "_tags tb2 ON tb1.tid=tb2.tid
                WHERE tb1.id=" . $row['id'];
                $result_tag = $db->query($sql);

                while ($tag = $result_tag->fetch()) {
                    $tag_id = 0;

                    // Kiểm tra trùng
                    $sql = "SELECT tid FROM " . NV_PREFIXLANG . "_" . $site_mods[$request['tm']]['module_data'] . "_tags
                    WHERE alias=" . $db->quote($tag['alias']);
                    $exits_tag = $db->query($sql)->fetchColumn();
                    if ($exits_tag) {
                        $tag_id = $exits_tag;
                    } else {
                        // Thêm mới
                        $sql = "INSERT INTO " . NV_PREFIXLANG . "_" . $site_mods[$request['tm']]['module_data'] . "_tags (
                            numnews, alias, image, description, keywords
                        ) VALUES (
                            0, :alias, '', :description, :keywords
                        )";
                        $array_insert = [];
                        $array_insert['alias'] = $tag['alias'];
                        $array_insert['description'] = $tag['description'];
                        $array_insert['keywords'] = $tag['keywords'];

                        $new_tag = $db->insert_id($sql, 'tid', $array_insert);
                        if ($new_tag) {
                            $tag_id = $new_tag;
                        }
                    }
                    if ($tag_id) {
                        // Gắn tag với bài viết
                        $sql = "INSERT IGNORE INTO " . NV_PREFIXLANG . "_" . $site_mods[$request['tm']]['module_data'] . "_tags_id (
                            id, tid, keyword
                        ) VALUES (
                            " . $new_id . ", " . $tag_id . ", " . $db->quote($tag['keywords']) . "
                        )";
                        $db->query($sql);

                        // Cập nhật số tin của tag
                        $sql = "UPDATE " . NV_PREFIXLANG . "_" . $site_mods[$request['tm']]['module_data'] . "_tags
                        SET numnews=(
                            SELECT COUNT(id) FROM " . NV_PREFIXLANG . "_" . $site_mods[$request['tm']]['module_data'] . "_tags_id
                            WHERE id=" . $new_id . "
                        ) WHERE tid=" . $tag_id;
                        $db->query($sql);
                    }
                }

                // Lưu nhóm tin mặc đinh
                if (!empty($blocks)) {
                    foreach ($blocks as $bid) {
                        // Set block mặc định
                        $sql = "INSERT IGNORE INTO " . NV_PREFIXLANG . "_" . $site_mods[$request['tm']]['module_data'] . "_block (
                            bid, id, weight
                        ) VALUES (
                            " . $bid . ", " . $new_id . ", 0
                        )";
                        if ($db->exec($sql)) {
                            // Update lại thứ tự
                            $sql = "UPDATE " . NV_PREFIXLANG . "_" . $site_mods[$request['tm']]['module_data'] . "_block
                            SET weight=weight+1 WHERE bid=" . $bid;
                            $db->query($sql);

                            // Bỏ row quá 100
                            $sql = "DELETE FROM " . NV_PREFIXLANG . "_" . $site_mods[$request['tm']]['module_data'] . "_block
                            WHERE weight>100 AND bid=" . $bid;
                            $db->query($sql);
                        }
                    }
                }
            }
        }

        $offset += 100;
        if (empty($array)) {
            $is_complete = true;
        }

        $base_url .= '&' . http_build_query($request, '', '&') . '&o=' . $offset . '&n=' . $num_updated;
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

if (!empty($error)) {
    $xtpl->assign('ERROR', implode('<br />', $error));
    $xtpl->parse('main.error');
}

foreach ($array_module_news as $mod => $title) {
    $mod = [
        'key' => $mod,
        'title' => $title,
        'selected_from' => $request['fm'] == $mod ? ' selected="selected"' : '',
        'selected_to' => $request['tm'] == $mod ? ' selected="selected"' : ''
    ];
    $xtpl->assign('MOD', $mod);
    $xtpl->parse('main.mod1');
    $xtpl->parse('main.mod2');
}

foreach ($array_from_cats as $cat) {
    $cat['space'] = '';
    for ($i = 0; $i < $cat['lev']; $i++) {
        $cat['space'] .= '&nbsp;&nbsp;&nbsp;&nbsp;';
    }
    $cat['checked'] = in_array($cat['catid'], $request['fc']) ? ' checked="checked"' : '';

    $xtpl->assign('CAT1', $cat);

    if (!$cat['checked']) {
        $xtpl->parse('main.cat1.hide');
    }

    $xtpl->parse('main.cat1');
}

foreach ($array_to_cats as $cat) {
    $cat['space'] = '';
    for ($i = 0; $i < $cat['lev']; $i++) {
        $cat['space'] .= '&nbsp;&nbsp;&nbsp;&nbsp;';
    }
    $cat['checked'] = in_array($cat['catid'], $request['tc']) ? ' checked="checked"' : '';

    $xtpl->assign('CAT2', $cat);
    $xtpl->assign('CAT2_CHECK', $request['c2'] == $cat['catid'] ? ' checked="checked"' : '');

    if (!$cat['checked']) {
        $xtpl->parse('main.cat2.hide');
    }

    $xtpl->parse('main.cat2');
}

// Xuất các bài viết quét qua
if (!empty($array)) {
    $stt = $offset - 100;
    foreach ($array as $row) {
        $stt++;
        $xtpl->assign('STT', number_format($stt, 0, ',', '.'));
        $xtpl->assign('ROW', $row);
        $xtpl->parse('main.data.loop');
    }
    $xtpl->parse('main.data');
}

if ($is_submit and empty($error)) {
    if ($is_complete) {
        $xtpl->assign('MESAGE', sprintf($lang_module['complete'], number_format($num_updated, 0, ',', '.')));
        $xtpl->parse('main.complete');
    } else {
        $xtpl->assign('LINK', $base_url);
        $xtpl->parse('main.continue');
    }
}

$xtpl->parse('main');
$contents = $xtpl->text('main');

include NV_ROOTDIR . '/includes/header.php';
echo nv_admin_theme($contents);
include NV_ROOTDIR . '/includes/footer.php';
