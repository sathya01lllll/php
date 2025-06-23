<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $data = [
        "nama_barang" => $_POST['nama_barang'],
        "id_kategori" => $_POST['id_kategori'],
        "id_lokasi" => $_POST['id_lokasi'],
        "jenis_barang" => $_POST['jenis_barang'],
        "deskripsi_panjang" => $_POST['deskripsi_panjang'],
        "deskripsi_singkat" => $_POST['deskripsi_singkat'],
        "harga_barang" => $_POST['harga_barang'],
        "berat" => $_POST['berat'],
        "stok_barang" => $_POST['stok_barang'],
        "url_gambar_barang_1" => $_POST['url_gambar_barang_1'],
        "harga_modal" => $_POST['harga_modal'],
        "harga_barang_asli" => $_POST['harga_barang_asli'],
        "catatan_wajib_isi" => $_POST['catatan_wajib_isi'],
        "batasi_max" => $_POST['batasi_max'],
        "produk_digital_khusus" => $_POST['produk_digital_khusus'],
        "tipe_input" => $_POST['tipe_input'],
    ];

    $token = "Y3p3WFhXaVRXN0ZPaVhJL3VtSzFnall4NEp4dUZPcFVUZnVkUXluVDhhM1l4UXpZSUd2RzRCTTg5TCtiR1JHRWwydjBLOTFsYXRwMkp2YitYS05SZTNIaVNUSHcyRElSVTM4ckxudnJPUXAwWUpleTRvNlFQQmFZNTAvaHFmYVh1ZW41NUFZd3V2RklUVmQwdVR5eHpXT0pjSnBPLzFwdk5uU2RKSXBCaFpWdjNlUXFNOFUxaVhsaXNCb21EYkdL";
    $header = ["Authorization: Bearer " . $token];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://bukaolshop.net/api/v1/produk/create");
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $result = curl_exec($ch);
    curl_close($ch);

    $response = json_decode($result, true);

    if (isset($response['code']) && $response['code'] == 200) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Produk berhasil dikirim ke antrian upload!',
            'id_antrian' => $response['id_antrian'] ?? '',
            'nama_barang' => $response['nama_barang'] ?? '',
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Gagal mengunggah produk.',
            'response' => $response
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Metode tidak valid.'
    ]);
}
?>
