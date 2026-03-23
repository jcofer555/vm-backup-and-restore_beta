<?php
$debug   = !empty($_GET['debug']) && $_GET['debug'] === '1';
$logFile = $debug
    ? '/tmp/vm-backup-and-restore_beta/vm-backup-and-restore_beta-debug.log'
    : '/tmp/vm-backup-and-restore_beta/vm-backup-and-restore_beta.log';

header('Content-Type: text/plain; charset=utf-8');
if (file_exists($logFile)) {
    readfile($logFile);
} else {
    echo '';
}
