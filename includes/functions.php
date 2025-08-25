<?php
function uploadFile($file, $targetDir)
{
    // Validasi file
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    // Generate nama file unik
    $fileExt = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = uniqid() . '.' . $fileExt;
    $targetPath = $targetDir . $fileName;

    // Validasi ekstensi file
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
    if (!in_array(strtolower($fileExt), $allowedExtensions)) {
        return false;
    }

    // Pindahkan file ke direktori target
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return $fileName;
    }
    return false;
}

function redirect($location)
{
    header("Location: $location");
    exit();
}

function sanitizeInput($data)
{
    return htmlspecialchars(strip_tags(trim($data)));
}

function displayError($message)
{
    echo '<div class="alert alert-danger">' . $message . '</div>';
}

function displaySuccess($message)
{
    echo '<div class="alert alert-success">' . $message . '</div>';
}
