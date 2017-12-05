<?php
require_once 'BackupTools.php';

$backup= new martyn911\BackupTools();
try {
    $backup->run();
} catch (\Exception $e) {
    echo $e->getMessage();
}
