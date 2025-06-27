<?php

require_once('../../vendor/autoload.php');

Tigress\Core::settingUpRootMapping();

$directory = SYSTEM_ROOT . "/public/images/tinymce/" . $_GET['folder'] . "/";
$images = array_diff(scandir($directory), array('..', '.', '.htaccess'));
foreach ($images as &$image) {
    $image = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/public/images/tinymce/' . $_GET['folder'] . '/' . $image;
}
echo json_encode($images);
