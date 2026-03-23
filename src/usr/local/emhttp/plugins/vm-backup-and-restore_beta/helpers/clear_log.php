<?php
$debug   = !empty($_POST['debug']) && $_POST['debug'] === '1';
$logFile = $debug
    ? '/tmp/vm-backup-and-restore_beta/vm-backup-and-restore_beta-debug.log'
    : '/tmp/vm-backup-and-restore_beta/vm-backup-and-restore_beta.log';

header('Content-Type: application/json');
if (file_exists($logFile)) {
    file_put_contents($logFile, '');
}
echo json_encode(['ok' => true]);
