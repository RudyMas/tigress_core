<?php

require_once('../../vendor/autoload.php');

Tigress\Core::settingUpRootMapping();

$randomText = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 10);
$uploadedFile = $_FILES['file']['tmp_name'];
$destinationPath = SYSTEM_ROOT . '/public/images/tinymce/' . $_GET['folder'] . '/' . $randomText . '_' . $_FILES['file']['name'];

// check if the folder exists, if not, create it
if (!file_exists(SYSTEM_ROOT . '/public/images/tinymce/' . $_GET['folder'])) {
    mkdir(SYSTEM_ROOT . '/public/images/tinymce/' . $_GET['folder'], 0777, true);
}

//check if the file is an image and a JPG or PNG
if (exif_imagetype($uploadedFile) != IMAGETYPE_JPEG && exif_imagetype($uploadedFile) != IMAGETYPE_PNG) {
    header("HTTP/1.1 500 Internal Server Error");
    exit;
}

// Move the uploaded file to the destination path
if (move_uploaded_file($uploadedFile, $destinationPath)) {
    // Respond with the URL to the uploaded image
    echo json_encode(['location' => '/public/images/tinymce/' . $_GET['folder'] . '/' . $randomText . '_' . $_FILES['file']['name']]);
} else {
    // Handle errors
    header("HTTP/1.1 500 Internal Server Error");
}