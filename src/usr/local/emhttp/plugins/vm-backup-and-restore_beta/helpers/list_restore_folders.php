<?php

declare(strict_types=1);
header('Content-Type: application/json');

$restore_path_str = rtrim($_GET['restore_path'] ?? '', '/');

if ($restore_path_str === '' || !is_dir($restore_path_str) || strpos($restore_path_str, '/mnt') !== 0) {
    echo json_encode(['folders' => []]);
    exit;
}

function vmbr_is_vdisk(string $lower_str): bool
{
    if (str_ends_with($lower_str, '.img'))   return true;
    if (str_ends_with($lower_str, '.qcow2')) return true;
    if (str_ends_with($lower_str, '.raw'))   return true;
    // compound names like vdisk1.2342342qcow2 — no dot before the token
    if (str_contains($lower_str, 'qcow2'))   return true;
    if (str_contains($lower_str, '.img'))    return true;
    return false;
}

$valid_folders_arr = array_values(array_filter(
    scandir($restore_path_str),
    static function (string $item_str) use ($restore_path_str): bool {
        if (in_array($item_str, ['.', '..', 'logs'], true)) {
            return false;
        }

        $full_path_str = $restore_path_str . '/' . $item_str;
        if (!is_dir($full_path_str)) {
            return false;
        }

        $groups_arr = [];

        foreach (scandir($full_path_str) as $file_str) {
            if (!preg_match('/^(\d{8}_\d{6})_/i', $file_str, $ts_matches_arr)) {
                continue;
            }
            $ts_str    = $ts_matches_arr[1];
            $lower_str = strtolower($file_str);

            if (!isset($groups_arr[$ts_str])) {
                $groups_arr[$ts_str] = ['disk' => false, 'xml' => false, 'fd' => false];
            }

            if (str_ends_with($lower_str, '.xml')) {
                $groups_arr[$ts_str]['xml'] = true;
            } elseif (str_ends_with($lower_str, '.fd')) {
                $groups_arr[$ts_str]['fd'] = true;
            } elseif (vmbr_is_vdisk($lower_str)) {
                $groups_arr[$ts_str]['disk'] = true;
            }
        }

        foreach ($groups_arr as $found_arr) {
            if ($found_arr['disk'] && $found_arr['xml'] && $found_arr['fd']) {
                return true;
            }
        }

        return false;
    }
));

echo json_encode([
    'folders' => $valid_folders_arr,
]);