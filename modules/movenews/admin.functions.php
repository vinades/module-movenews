<?php

/**
 * @Project NUKEVIET 4.x
 * @Author VINADES.,JSC (contact@vinades.vn)
 * @Copyright (C) 2014 VINADES.,JSC. All rights reserved
 * @License GNU/GPL version 2 or any later version
 * @Createdate 2-10-2010 20:59
 */

if (!defined('NV_ADMIN') or !defined('NV_MAINFILE') or !defined('NV_IS_MODADMIN')) {
    die('Stop!!!');
}

define('NV_IS_FILE_ADMIN', true);

/**
 * @param string $file
 * @param string $to_path
 * @param array $folder
 * @return number
 */
function copyImagesNews($file, $to_path, $folder)
{
    global $db;

    // File nguồn không tồn tại
    if (!file_exists($file)) {
        return 0;
    }
    if (!empty($folder)) {
        $base_path = $to_path;
        // Tạo thư mục đích đến
        foreach ($folder as $folder_i) {
            if (!file_exists($base_path . '/' . $folder_i)) {
                $check = nv_mkdir($base_path, $folder_i);
                if ($check[0] < 1) {
                    return 0;
                }
                if ($check[0] == 1) {
                    $f = substr($base_path . '/' . $folder_i, strlen(NV_ROOTDIR . '/'));
                    if (strpos($f, NV_UPLOADS_DIR) === 0) {
                        // Thư mục tạo mới thì lưu vào CSDL
                        try {
                            $db->query("INSERT INTO " . NV_UPLOAD_GLOBALTABLE . "_dir (dirname, time) VALUES ('" . $f . "', 0)");
                        } catch (PDOException $e) {
                        }
                    }
                }
            }
            $base_path .= '/' . $folder_i;
        }
    }
    // Thư mục copy đến
    $full_path = $to_path . (empty($folder) ? '' : ('/' . implode('/', $folder)));
    if (!file_exists($full_path)) {
        return 0;
    }
    $full_path .= '/' . basename($file);
    // File tồn tại rồi thì thôi
    if (file_exists($full_path)) {
        return 1;
    }
    copy($file, $full_path);
    if (!file_exists($full_path)) {
        return 0;
    }
    return 1;
}
