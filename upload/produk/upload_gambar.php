<?php
if (isset($_FILES['gambar'])) {
    $file = $_FILES['gambar'];
    $uploadDir = __DIR__ . '/assets/';
    $filename = basename($file['name']);
    $targetPath = $uploadDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        echo "https://{$_SERVER['HTTP_HOST']}/upload/produk/assets/" . $filename;
    } else {
        echo "Gagal upload";
    }
} else {
    echo "Tidak ada file";
}
?>
