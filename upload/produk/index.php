<?php
session_start();

// Hapus session jika tombol "Reset URL" ditekan
if (isset($_GET['reset'])) {
    unset($_SESSION['json_url']);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Jika form disubmit, simpan URL ke session
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['json_url'])) {
    $_SESSION['json_url'] = $_POST['json_url'];
    header("Location: " . $_SERVER['PHP_SELF']); // Redirect agar tidak resubmit
    exit;
}

// Gunakan URL dari session jika tersedia, jika tidak pakai default
$url = isset($_SESSION['json_url']) ? $_SESSION['json_url'] : "https://okeconnect.com/harga/json?id=905ccd028329b0a";

// Ambil data dari okeconnect
$json = @file_get_contents($url);
$data = json_decode($json, true);

$kategoriProduk = [];
$produkGroupedByKategori = [];

if (is_array($data)) {
    foreach ($data as $item) {
        $kategori = $item['kategori'];
        $produk = $item['produk'];

        if (!isset($kategoriProduk[$kategori])) {
            $kategoriProduk[$kategori] = [];
        }
        if (!in_array($produk, $kategoriProduk[$kategori])) {
            $kategoriProduk[$kategori][] = $produk;
        }

        if (!isset($produkGroupedByKategori[$kategori][$produk])) {
            $produkGroupedByKategori[$kategori][$produk] = [];
        }
        $produkGroupedByKategori[$kategori][$produk][] = $item;
    }
} else {
    echo "Data tidak valid dari URL: $url";
    exit;
}

// Ambil data dari Bukaolshop
$token = "Y3p3WFhXaVRXN0ZPaVhJL3VtSzFnall4NEp4dUZPcFVUZnVkUXluVDhhM1l4UXpZSUd2RzRCTTg5TCtiR1JHRWwydjBLOTFsYXRwMkp2YitYS05SZTNIaVNUSHcyRElSVTM4ckxudnJPUXAwWUpleTRvNlFQQmFZNTAvaHFmYVh1ZW41NUFZd3V2RklUVmQwdVR5eHpXT0pjSnBPLzFwdk5uU2RKSXBCaFpWdjNlUXFNOFUxaVhsaXNCb21EYkdL";
$header = array("Authorization: Bearer ".$token);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://bukaolshop.net/api/v1/produk/id_upload");
curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$hasil = curl_exec($ch);
curl_close($ch);
$produkData = json_decode($hasil, true);

$kategoriBukaolshop = $produkData['kategori'];
$lokasiBukaolshop = $produkData['lokasi'];
?>

<html lang="en">
<head>
    <title>Form Produk OkeConnect + Bukaolshop</title>
    <script>
        // Tampilkan popup hanya jika session URL belum diset
        window.onload = function () {
            <?php if (!isset($_SESSION['json_url'])): ?>
                setTimeout(function () {
                    document.getElementById("popupForm").style.display = "block";
                    document.getElementById("overlay").style.display = "block";
                }, 500);
            <?php endif; ?>
        };
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
    <style>
        body {
      font-family: "Poppins", sans-serif;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
    }
    /* Custom scrollbar for table container */
    .table-container::-webkit-scrollbar {
      height: 10px;
    }
    .table-container::-webkit-scrollbar-track {
      background: #e0e0e0;
      border-radius: 5px;
    }
    .table-container::-webkit-scrollbar-thumb {
      background: #764ba2;
      border-radius: 5px;
    }
    /* Larger row height and spacing */
    #produkInputTable tbody tr {
      height: 72px;
      transition: background-color 0.2s ease;
    }
    #produkInputTable tbody tr:hover {
      background-color: #f9f5ff;
    }
    /* Table header styling */
    #produkInputTable thead tr th {
      padding-top: 1rem;
      padding-bottom: 1rem;
      font-size: 0.875rem;
      letter-spacing: 0.03em;
      text-transform: uppercase;
    }
    /* Table cell vertical alignment and padding */
    #produkInputTable tbody tr td,
    #produkInputTable thead tr th {
      vertical-align: middle;
      padding-left: 0.75rem;
      padding-right: 0.75rem;
      white-space: nowrap;
    }
    /* Image cell styling */
    #produkInputTable tbody tr td.img-cell img {
      width: 48px;
      height: 48px;
      object-fit: cover;
      border-radius: 0.375rem;
      box-shadow: 0 0 6px rgba(118, 75, 162, 0.4);
    }
    /* Delete button styling */
    #produkInputTable tbody tr td.delete-cell button {
      background-color: #e0d7f7;
      color: #764ba2;
      padding: 0.4rem 0.6rem;
      border-radius: 0.375rem;
      font-weight: 600;
      transition: background-color 0.2s ease, color 0.2s ease;
    }
    #produkInputTable tbody tr td.delete-cell button:hover {
      background-color: #764ba2;
      color: white;
    }
    /* Responsive adjustments */
    @media (max-width: 640px) {
      #produkInputTable thead {
        display: none;
      }
      #produkInputTable tbody tr {
        display: block;
        margin-bottom: 1.5rem;
        border-radius: 1rem;
        box-shadow: 0 4px 12px rgb(118 75 162 / 0.3);
        background: white;
        padding: 1rem 1.5rem;
        height: auto !important;
      }
      #produkInputTable tbody tr td {
        display: flex;
        justify-content: space-between;
        padding: 0.5rem 0;
        white-space: normal;
        border-bottom: 1px solid #e9dfff;
        font-size: 0.9rem;
      }
      #produkInputTable tbody tr td:last-child {
        border-bottom: none;
      }
      #produkInputTable tbody tr td::before {
        content: attr(data-label);
        font-weight: 600;
        color: #764ba2;
        flex-basis: 45%;
        text-transform: capitalize;
      }
      #produkInputTable tbody tr td.img-cell {
        justify-content: flex-start;
      }
      #produkInputTable tbody tr td.img-cell img {
        margin-left: auto;
      }
      #popupForm {
            display: none;
            position: fixed;
            z-index: 1000;
            background: white;
            padding: 2rem 2.5rem;
            border-radius: 1rem;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            width: 90vw;
            max-width: 400px;
            text-align: center;
            font-weight: 600;
            color: #4c1d95;
        }
        #popupForm h3 {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
        }
        #popupForm input[type="text"] {
            width: 100%;
            padding: 0.75rem 1rem;
            border-radius: 0.75rem;
            border: 2px solid #a78bfa;
            font-size: 1rem;
            font-weight: 600;
            color: #5b21b6;
            transition: border-color 0.3s ease;
        }
        #popupForm input[type="text"]:focus {
            outline: none;
            border-color: #7c3aed;
            box-shadow: 0 0 8px #7c3aed;
        }
        #popupForm button {
            margin-top: 1.5rem;
            background: linear-gradient(90deg, #7c3aed, #5b21b6);
            color: white;
            font-weight: 700;
            padding: 0.75rem 2rem;
            border-radius: 1.5rem;
            font-size: 1.125rem;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(124, 58, 237, 0.6);
            transition: background 0.3s ease;
        }
        #popupForm button:hover {
            background: linear-gradient(90deg, #5b21b6, #7c3aed);
        }
        #overlay {
            position: fixed;
            top: 0; left: 0;
            width: 100vw; height: 100vh;
            background: rgba(0,0,0,0.5);
            z-index: 999;
            display: none;
        }
        .reset-btn {
            margin: 10px 0;
        }
        /* URL Saat Ini and Reset URL container */
        #currentUrlContainer {
            background: white;
            padding: 1rem 1.5rem;
            border-radius: 1rem;
            box-shadow: 0 4px 12px rgba(118, 75, 162, 0.3);
            max-width: 600px;
            margin: 0 auto 1.5rem auto;
            color: #4c1d95;
            font-weight: 600;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
        }
        #currentUrlContainer strong {
            font-weight: 700;
            margin-right: 0.5rem;
        }
        #currentUrlText {
            flex: 1 1 auto;
            overflow-wrap: anywhere;
        }
        #resetUrlButton {
            background: linear-gradient(90deg, #7c3aed, #5b21b6);
            color: white;
            font-weight: 700;
            padding: 0.5rem 1.5rem;
            border-radius: 1.5rem;
            font-size: 1rem;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(124, 58, 237, 0.6);
            transition: background 0.3s ease;
            flex-shrink: 0;
        }
        #resetUrlButton:hover {
            background: linear-gradient(90deg, #5b21b6, #7c3aed);
        }
    </style>
</head>
<body class="p-4">
<div id="overlay" style="<?php if (!isset($_SESSION['json_url'])) echo 'display:block;'; ?>"></div>
    <div id="popupForm">
        <h3>Masukkan URL JSON OkeConnect</h3>
        <form method="POST" class="space-y-4">
            <input type="text" name="json_url" placeholder="https://..." required autocomplete="off" />
            <button type="submit">Simpan</button>
        </form>
    </div>

    <div id="currentUrlContainer">
        <div><strong>URL Saat Ini:</strong> <span id="currentUrlText"><?= htmlspecialchars($url) ?></span></div>
        <form method="get" action="" class="m-0">
            <button id="resetUrlButton" type="submit" name="reset" value="true">Reset URL</button>
        </form>
    </div>

    <main class=" bg-white bg-opacity-90 rounded-3xl shadow-2xl p-6 sm:p-8 ring-1 ring-white/20 backdrop-blur-md max-w-7xl mx-auto">
        <h1 class="text-3xl font-extrabold text-purple-900 mb-8 text-center drop-shadow-md">Form Produk OkeConnect + Bukaolshop</h1>

        <form method="post" id="produkForm" enctype="multipart/form-data" class="space-y-6">
            <!-- Kategori OkeConnect & Produk OkeConnect side by side on mobile -->
            <div class="grid grid-cols-2 gap-4">
    <div>
        <label for="kategoriOkeConnect" class="block text-purple-900 font-semibold mb-2">Kategori OkeConnect</label>
        <!-- Input pencarian kategori -->
        <input type="text" id="searchKategori" onkeyup="filterKategori()" placeholder="Cari kategori..." class="w-full rounded-xl border-2 border-purple-400 focus:border-purple-700 focus:ring-2 focus:ring-purple-300 px-4 py-3 text-purple-900 font-medium shadow-md transition duration-300 mb-2">

        <select id="kategoriOkeConnect" onchange="loadProdukOkeConnect()" class="w-full rounded-xl border-2 border-purple-400 focus:border-purple-700 focus:ring-2 focus:ring-purple-300 px-4 py-3 text-purple-900 font-medium shadow-md transition duration-300">
            <option value="">Pilih Kategori</option>
            <?php foreach ($kategoriProduk as $kategori => $produks): ?>
                <option value="<?= htmlspecialchars($kategori) ?>"><?= htmlspecialchars($kategori) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div>
        <label for="produkOkeConnect" class="block text-purple-900 font-semibold mb-2">Produk OkeConnect</label>
        <!-- Input pencarian produk -->
        <input type="text" id="searchProduk" onkeyup="filterProduk()" placeholder="Cari produk..." class="w-full rounded-xl border-2 border-purple-400 focus:border-purple-700 focus:ring-2 focus:ring-purple-300 px-4 py-3 text-purple-900 font-medium shadow-md transition duration-300 mb-2">

        <select id="produkOkeConnect" class="w-full rounded-xl border-2 border-purple-400 focus:border-purple-700 focus:ring-2 focus:ring-purple-300 px-4 py-3 text-purple-900 font-medium shadow-md transition duration-300">
            <!-- Produk akan dimuat berdasarkan kategori yang dipilih -->
        </select>
    </div>
</div>
<!-- Kategori Bukaolshop & Lokasi Pengiriman side by side on mobile -->
            <div class="grid grid-cols-2 gap-4">
    <div>
        <label for="kategoriBukaolshop" class="block text-purple-900 font-semibold mb-2">Kategori Bukaolshop</label>
        <!-- Input pencarian kategori -->
        <input type="text" id="searchKategoriBukaolshop" onkeyup="filterKategoriBukaolshop()" placeholder="Cari kategori..." class="w-full rounded-xl border-2 border-purple-400 focus:border-purple-700 focus:ring-2 focus:ring-purple-300 px-4 py-3 text-purple-900 font-medium shadow-md transition duration-300 mb-2">

        <select id="kategoriBukaolshop" class="w-full rounded-xl border-2 border-purple-400 focus:border-purple-700 focus:ring-2 focus:ring-purple-300 px-4 py-3 text-purple-900 font-medium shadow-md transition duration-300">
            <option value="">Pilih Kategori</option>
            <?php foreach ($kategoriBukaolshop as $k): ?>
                <option value="<?= $k['id_kategori'] ?>"><?= $k['nama_kategori'] ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div>
        <label for="lokasi" class="block text-purple-900 font-semibold mb-2">Lokasi Pengiriman</label>
        <!-- Input pencarian lokasi -->
        <input type="text" id="searchLokasi" onkeyup="filterLokasi()" placeholder="Cari lokasi..." class="w-full rounded-xl border-2 border-purple-400 focus:border-purple-700 focus:ring-2 focus:ring-purple-300 px-4 py-3 text-purple-900 font-medium shadow-md transition duration-300 mb-2">

        <select id="lokasi" class="w-full rounded-xl border-2 border-purple-400 focus:border-purple-700 focus:ring-2 focus:ring-purple-300 px-4 py-3 text-purple-900 font-medium shadow-md transition duration-300">
            <option value="">Pilih Lokasi</option>
            <?php foreach ($lokasiBukaolshop as $l): ?>
                <option value="<?= $l['id_lokasi'] ?>"><?= $l['nama_pengirim'] ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>


            <div>
                <label for="gambarUpload" class="block text-purple-900 font-semibold mb-2">Upload Gambar</label>
                <input type="file" id="gambarUpload" name="gambarUpload" onchange="uploadGambar()" accept="image/*" class="w-full rounded-xl border-2 border-purple-400 focus:border-purple-700 focus:ring-2 focus:ring-purple-300 px-4 py-2 text-purple-900 font-medium shadow-md cursor-pointer transition duration-300" />
                <div id="gambarPreview" class="mt-2 text-green-700 font-semibold text-sm"></div>
            </div>

            <div>
                <label for="produkKhusus" class="block text-purple-900 font-semibold mb-2">Produk Digital Khusus</label>
                <select id="produkKhusus" class="w-full rounded-xl border-2 border-purple-400 focus:border-purple-700 focus:ring-2 focus:ring-purple-300 px-4 py-3 text-purple-900 font-medium shadow-md transition duration-300">
                    <option value="none">none</option>
                    <option value="pascabayar">pascabayar</option>
                    <option value="pln_prabayar">pln_prabayar</option>
                    <option value="bebas_nominal">bebas_nominal</option>
                    <option value="bulk">bulk</option>
                </select>
            </div>

            <div>
                <label for="tipeInput" class="block text-purple-900 font-semibold mb-2">Tipe Input</label>
                <select id="tipeInput" class="w-full rounded-xl border-2 border-purple-400 focus:border-purple-700 focus:ring-2 focus:ring-purple-300 px-4 py-3 text-purple-900 font-medium shadow-md transition duration-300">
                    <option value="angka_only">angka_only</option>
                    <option value="angka_huruf">angka_huruf</option>
                </select>
            </div>

            <div id="priceChoiceContainer" class="mt-2">
                <label>
                    <input type="radio" name="hargaOption" value="default" checked>
                    Gunakan harga dari produk
                    <span id="defaultHargaText" class="font-semibold text-purple-900 ml-1">-</span>
                </label>
                <label>
                    <input type="radio" name="hargaOption" value="manual">
                    Input harga manual:
                    <input type="number" id="manualHargaInput" min="0" step="0.01" class="border-2 border-purple-300 rounded-xl px-3 py-1 ml-2 w-36 text-purple-900 font-semibold" disabled />
                </label>
            </div>

            <div class="flex flex-col sm:flex-row gap-4 mt-6">
                <button type="button" onclick="tambahSemuaProduk()" class="flex-1 bg-gradient-to-r from-purple-600 to-purple-800 hover:from-purple-700 hover:to-purple-900 text-white font-bold rounded-2xl py-3 shadow-lg transition duration-300 justify-center items-center flex gap-2">
                    <i class="fas fa-list-alt text-lg"></i> Buat Daftar Produk
                </button>
                <button type="button" onclick="uploadProduk()" class="flex-1 bg-gradient-to-r from-green-500 to-green-700 hover:from-green-600 hover:to-green-800 text-white font-bold rounded-2xl py-3 shadow-lg transition duration-300 justify-center items-center flex gap-2">
                    <i class="fas fa-upload text-lg"></i> Upload Produk
                </button>
            </div>

            <div id="uploadStatusPopup" role="dialog" aria-modal="true" aria-labelledby="uploadStatusTitle" tabindex="-1">
                <button class="close-btn" aria-label="Close upload status popup" onclick="closeUploadStatusPopup()">&times;</button>
                <h3 id="uploadStatusTitle">Status Upload Produk</h3>
                <div id="uploadStatusContent" class="mt-2"></div>
            </div>

            <div class="table-container overflow-x-auto mt-8 rounded-2xl border-2 border-purple-300 shadow-lg bg-white">
                <table id="produkInputTable" class="min-w-full divide-y divide-purple-200 text-sm rounded-2xl">
                    <thead class="bg-gradient-to-r from-purple-300 to-purple-400 text-purple-900 sticky top-0 z-20">
                        <tr>
                            <th class="px-3 py-2 text-center font-semibold whitespace-nowrap">Hapus</th>
                            <th class="px-3 py-2 text-left font-semibold whitespace-nowrap">Nama Barang</th>
                            <th class="px-3 py-2 text-left font-semibold whitespace-nowrap">Kategori ID</th>
                            <th class="px-3 py-2 text-left font-semibold whitespace-nowrap">Lokasi ID</th>
                            <th class="px-3 py-2 text-left font-semibold whitespace-nowrap">Jenis</th>
                            <th class="px-3 py-2 text-left font-semibold whitespace-nowrap">Deskripsi</th>
                            <th class="px-3 py-2 text-left font-semibold whitespace-nowrap">Singkat</th>
                            <th class="px-3 py-2 text-left font-semibold whitespace-nowrap">Harga</th>
                            <th class="px-3 py-2 text-left font-semibold whitespace-nowrap">Berat</th>
                            <th class="px-3 py-2 text-left font-semibold whitespace-nowrap">Stok</th>
                            <th class="px-3 py-2 text-left font-semibold whitespace-nowrap">Gambar</th>
                            <th class="px-3 py-2 text-left font-semibold whitespace-nowrap">Harga Modal</th>
                            <th class="px-3 py-2 text-left font-semibold whitespace-nowrap">Harga Asli</th>
                            <th class="px-3 py-2 text-left font-semibold whitespace-nowrap">Wajib Isi</th>
                            <th class="px-3 py-2 text-left font-semibold whitespace-nowrap">Batasi Max</th>
                            <th class="px-3 py-2 text-left font-semibold whitespace-nowrap">Khusus</th>
                            <th class="px-3 py-2 text-left font-semibold whitespace-nowrap">Tipe Input</th>
                            
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-purple-200 bg-white"></tbody>
                </table>
            </div>

            
        </form>
    </main>
<script>
    const dataProduk = <?= json_encode($produkGroupedByKategori); ?>;
    let uploadedImageUrl = "";
    let currentHarga = null;

    function loadProdukOkeConnect() {
        const kategori = document.getElementById("kategoriOkeConnect").value;
        const produkSelect = document.getElementById("produkOkeConnect");

        produkSelect.innerHTML = '<option value="">Pilih Produk</option>';
        if (kategori && dataProduk[kategori]) {
            Object.keys(dataProduk[kategori]).forEach(produk => {
                produkSelect.innerHTML += `<option value="${produk}">${produk}</option>`;
            });
        }
        updateDefaultHargaText(null);
    }

    // Update default harga text below tipeInput
    function updateDefaultHargaText(harga) {
        const hargaText = document.getElementById("defaultHargaText");
        if (harga !== null && harga !== undefined) {
            hargaText.textContent = harga;
            currentHarga = harga;
        } else {
            hargaText.textContent = "-";
            currentHarga = null;
        }
    }

    // Filter kategori berdasarkan input pencarian
function filterKategori() {
    const searchKategori = document.getElementById("searchKategori").value.toLowerCase();
    const kategoriOptions = document.querySelectorAll("#kategoriOkeConnect option");

    kategoriOptions.forEach(option => {
        const kategoriText = option.textContent.toLowerCase();
        if (kategoriText.includes(searchKategori)) {
            option.style.display = "block"; // Tampilkan kategori yang cocok
        } else {
            option.style.display = "none"; // Sembunyikan kategori yang tidak cocok
        }
    });
}

// Filter kategori berdasarkan input pencarian
function filterKategoriBukaolshop() {
    const searchKategori = document.getElementById("searchKategoriBukaolshop").value.toLowerCase();
    const kategoriOptions = document.querySelectorAll("#kategoriBukaolshop option");

    kategoriOptions.forEach(option => {
        const kategoriText = option.textContent.toLowerCase();
        if (kategoriText.includes(searchKategori)) {
            option.style.display = "block"; // Tampilkan kategori yang cocok
        } else {
            option.style.display = "none"; // Sembunyikan kategori yang tidak cocok
        }
    });
}

// Filter lokasi berdasarkan input pencarian
function filterLokasi() {
    const searchLokasi = document.getElementById("searchLokasi").value.toLowerCase();
    const lokasiOptions = document.querySelectorAll("#lokasi option");

    lokasiOptions.forEach(option => {
        const lokasiText = option.textContent.toLowerCase();
        if (lokasiText.includes(searchLokasi)) {
            option.style.display = "block"; // Tampilkan lokasi yang cocok
        } else {
            option.style.display = "none"; // Sembunyikan lokasi yang tidak cocok
        }
    });
}


// Filter produk berdasarkan input pencarian
function filterProduk() {
    const searchProduk = document.getElementById("searchProduk").value.toLowerCase();
    const produkOptions = document.querySelectorAll("#produkOkeConnect option");

    produkOptions.forEach(option => {
        const produkText = option.textContent.toLowerCase();
        if (produkText.includes(searchProduk)) {
            option.style.display = "block"; // Tampilkan produk yang cocok
        } else {
            option.style.display = "none"; // Sembunyikan produk yang tidak cocok
        }
    });
}

    function uploadGambar() {
        const fileInput = document.getElementById("gambarUpload");
        const file = fileInput.files[0];
        const formData = new FormData();
        formData.append("gambar", file);

        fetch("upload_gambar.php", {
            method: "POST",
            body: formData,
        })
        .then(response => response.text())
        .then(url => {
            uploadedImageUrl = url;
            document.getElementById("gambarPreview").innerText = "Gambar Telah Di Upload";
        })
        .catch(error => {
            console.error("Upload gagal:", error);
        });
    }

    // Update harga choice container when produkOkeConnect changes
    document.getElementById("produkOkeConnect").addEventListener("change", function() {
        const kategori = document.getElementById("kategoriOkeConnect").value;
        const produk = this.value;
        if (kategori && produk && dataProduk[kategori] && dataProduk[kategori][produk] && dataProduk[kategori][produk].length > 0) {
            const harga = dataProduk[kategori][produk][0].harga;
            updateDefaultHargaText(harga);
            // Reset radio and manual input
            document.querySelector('input[name="hargaOption"][value="default"]').checked = true;
            document.getElementById("manualHargaInput").value = "";
            document.getElementById("manualHargaInput").disabled = true;
        } else {
            updateDefaultHargaText(null);
        }
    });

    // Handle radio buttons for harga option
    document.querySelectorAll('input[name="hargaOption"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const manualInput = document.getElementById("manualHargaInput");
            if (this.value === "manual") {
                manualInput.disabled = false;
                manualInput.focus();
            } else {
                manualInput.disabled = true;
                manualInput.value = "";
            }
        });
    });

    function tambahSemuaProduk() {
        const kategori = document.getElementById("kategoriOkeConnect").value;
        const produk = document.getElementById("produkOkeConnect").value;
        const kategoriID = document.getElementById("kategoriBukaolshop").value;
        const lokasiID = document.getElementById("lokasi").value;
        const produkKhusus = document.getElementById("produkKhusus").value;
        const tipeInput = document.getElementById("tipeInput").value;

        if (!kategori || !produk || !kategoriID || !lokasiID || !uploadedImageUrl) {
            alert("Lengkapi semua pilihan dan upload gambar terlebih dahulu.");
            return;
        }

        const produkList = dataProduk[kategori][produk] || [];
        const tbody = document.querySelector("#produkInputTable tbody");
        tbody.innerHTML = "";

        // Determine harga to use
        const hargaOption = document.querySelector('input[name="hargaOption"]:checked').value;
        let hargaManualValue = null;
        if (hargaOption === "manual") {
            const manualInput = document.getElementById("manualHargaInput");
            hargaManualValue = manualInput.value.trim();
            if (hargaManualValue === "" || isNaN(hargaManualValue) || Number(hargaManualValue) < 0) {
                alert("Masukkan harga manual yang valid.");
                return;
            }
        }

        produkList.forEach(p => {
            const hargaDipakai = (hargaOption === "manual" && hargaManualValue !== null) ? hargaManualValue : p.harga;
            const row = document.createElement("tr");
            row.innerHTML = `
                <td class="delete-cell text-center" data-label="Hapus"><button type="button" onclick="this.closest('tr').remove()">Hapus</button></td>
                <td class="font-medium text-purple-900" data-label="Nama Barang"><input type="text" name="nama_barang[]" value="${p.keterangan}" /></td>
                <td data-label="Kategori"><input type="text" name="id_kategori[]" value="${kategoriID}" /></td>
                <td data-label="Lokasi"><input type="text" name="id_lokasi[]" value="${lokasiID}" /></td>
                <td data-label="Jenis"><input type="text" name="jenis_barang[]" value="digital" /></td>
                <td data-label="Desk panjang"><input type="text" name="deskripsi_panjang[]" value="${p.keterangan}" /></td>
                <td data-label="Desk Singkat"><input type="text" name="deskripsi_singkat[]" value="${p.kode}" /></td>
                <td data-label="Harga"><input type="text" name="harga_barang[]" value="${hargaDipakai}" /></td>
                <td data-label="Berat"><input type="number" name="berat[]" value="0" /></td>
                <td data-label="Stok"><input type="number" name="stok_barang[]" value="9998888888" /></td>
                <td data-label="Gambar"><input type="text" name="url_gambar_barang_1[]" value="${uploadedImageUrl}" /></td>
                <td data-label="Harga modal"><input type="text" name="harga_modal[]" value="${hargaDipakai}" /></td>
                <td data-label="Harga asli"><input type="text" name="harga_barang_asli[]" value="${hargaDipakai}" /></td>
                <td data-label="Catatan wajib"><input type="text" name="catatan_wajib_isi[]" value="true" /></td>
                <td data-label="Batas Max"><input type="text" name="batasi_max[]" value="true" /></td>
                <td data-label="Khusus"><input type="text" name="produk_digital_khusus[]" value="${produkKhusus}" /></td>
                <td data-label="Tipe Input"><input type="text" name="tipe_input[]" value="${tipeInput}" /></td>
                
            `;
            tbody.appendChild(row);
        });
    }

    function uploadProduk() {
    const rows = document.querySelectorAll("#produkInputTable tbody tr");
    let currentIndex = 0;
    let totalRows = rows.length;
    let allResponses = [];

    // Show popup
    const popup = document.getElementById("uploadStatusPopup");
    const content = document.getElementById("uploadStatusContent");
    popup.style.display = "block";
    content.innerHTML = "<p>Memulai upload produk...</p>";

    function uploadNextProduct() {
        if (currentIndex >= totalRows) {
            // Tampilkan semua hasil respon setelah semua produk diupload
            let html = "<h3>Semua produk berhasil di-upload!</h3><ul>";
            allResponses.forEach((res, i) => {
                html += `<li><strong>Produk ${i + 1}</strong>: ${res.nama_barang || '-'} (ID Antrian: ${res.id_antrian || '-'})</li>`;
            });
            html += "</ul>";
            content.innerHTML = html;
            return;
        }

        const row = rows[currentIndex];
        const formData = new FormData();

        row.querySelectorAll("input").forEach(input => {
            formData.append(input.name, input.value);
        });

        fetch("upload_produknya.php", {
            method: "POST",
            body: formData,
        })
        .then(response => response.json())
        .then(response => {
            allResponses.push(response);
            content.innerHTML = `Mengunggah produk ${currentIndex + 1} dari ${totalRows}: ${response.message}`;
            currentIndex++;
            uploadNextProduct();
        })
        .catch(error => {
            console.error("Upload error:", error);
            allResponses.push({ nama_barang: 'Gagal', id_antrian: '-', message: error.toString() });
            currentIndex++;
            uploadNextProduct();
        });
    }

    uploadNextProduct();
}

function closeUploadStatusPopup() {
    const popup = document.getElementById("uploadStatusPopup");
    popup.style.display = "none";
}

</script>

</body>
</html>