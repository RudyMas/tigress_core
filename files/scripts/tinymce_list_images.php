<?php
$directory = "../../public/images/tinymce/" . $_GET['folder'] . "/";
$images = array_diff(scandir($directory), array('..', '.', '.htaccess'));
echo json_encode($images);