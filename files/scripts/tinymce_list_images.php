<?php

require_once('../../vendor/autoload.php');

Tigress\Core::settingUpRootMapping();

$directory = SYSTEM_ROOT . "/public/images/tinymce/" . $_GET['folder'] . "/";
$images = array_diff(scandir($directory), array('..', '.', '.htaccess'));
echo json_encode($images);
