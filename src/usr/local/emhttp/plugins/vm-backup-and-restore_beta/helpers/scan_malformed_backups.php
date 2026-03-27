<?php

declare(strict_types=1);
header('Content-Type: application/json');

$restore_path_str = rtrim($_GET['restore_path'] ?? '', '/');

if ($restore_path_str === '' || !is_dir($restore_path_str) || strpos($restore_path_str, '/mnt') !== 0) {
    echo json_encode([]);
    exit;
}

function vmbr_is_vdisk(string $lower_str): bool
{
    if (str_ends_with($lower_str, '.img'))   return true;
    if (str_ends_with($lower_str, '.qcow2')) return true;
    if (str_ends_with($lower_str, '.raw'))   return true;
    if (str_contains($lower_str, 'qcow2'))   return true;
    if (str_contains($lower_str, '.img'))    return true;
    return false;
}

$result_arr = [];

foreach (scandir($restore_path_str) as $vm_folder_str) {
    if (in_array($vm_folder_str, ['.', '..', 'logs'], true)) {
        continue;
    }

    $vm_path_str = $restore_path_str . '/' . $vm_folder_str;
    if (!is_dir($vm_path_str)) {
        continue;
    }

    $groups_arr = [];

    foreach (scandir($vm_path_str) as $file_str) {
        if (!preg_match('/^(\d{8})_(\d{6})_/i', $file_str, $matches_arr)) {
            continue;
        }
        $timestamp_str = $matches_arr[1] . '-' . $matches_arr[2];
        $lower_str     = strtolower($file_str);

        if (!isset($groups_arr[$timestamp_str])) {
            $groups_arr[$timestamp_str] = ['disk' => false, 'xml' => false, 'fd' => false];
        }

        if (str_ends_with($lower_str, '.xml')) {
            $groups_arr[$timestamp_str]['xml'] = true;
        } elseif (str_ends_with($lower_str, '.fd')) {
            $groups_arr[$timestamp_str]['fd'] = true;
        } elseif (vmbr_is_vdisk($lower_str)) {
            $groups_arr[$timestamp_str]['disk'] = true;
        }
    }

    foreach ($groups_arr as $ts_str => $found_arr) {
        $missing_arr = [];
        if (!$found_arr['disk']) $missing_arr[] = 'vdisk';
        if (!$found_arr['xml'])  $missing_arr[] = 'xml';
        if (!$found_arr['fd'])   $missing_arr[] = 'nvram';

        if (!empty($missing_arr)) {
            $dt_obj      = DateTime::createFromFormat('Ymd-His', $ts_str);
            $display_str = $dt_obj ? $dt_obj->format('Y-m-d H:i:s') : $ts_str;

            $result_arr[] = [
                'vm'      => $vm_folder_str,
                'backup'  => $display_str,
                'missing' => $missing_arr,
            ];
        }
    }
}

echo json_encode($result_arr);