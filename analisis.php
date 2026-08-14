<?php
require_once 'koneksi.php';


// Kamus kata sederhana
$kata_positif = ['baik', 'bagus', 'meningkat', 'untung', 'berhasil', 'juara', 'positif', 'naik', 'unggul', 'aman', 'laba', 'sukses', 'bantuan', 'apresiasi'];
$kata_negatif = ['buruk', 'gagal', 'rugi', 'turun', 'krisis', 'bencana', 'negatif', 'kasus', 'ancaman', 'bahaya', 'korupsi', 'skandal', 'tewas', 'kecelakaan', 'tersangka'];

if (!file_exists('data_berita.json')) {
    die("File data_berita.json belum ada.");
}

$json_data = file_get_contents('data_berita.json');
$berita_list = json_decode($json_data, true);

// Mulai perulangan analisis berita
$inserted = 0;
$filtered_count = 0;

// Mulai perulangan analisis berita
foreach ($berita_list as $berita) {
    $teks = strtolower($berita['judul'] . " " . $berita['deskripsi']);
    
    
    $skor = 0;
    
    // Perhitungan skor
    foreach ($kata_positif as $kp) { $skor += substr_count($teks, $kp); }
    foreach ($kata_negatif as $kn) { $skor -= substr_count($teks, $kn); }
    
    // Menentukan label
    if ($skor > 0) { $label_sentimen = "positif"; } 
    elseif ($skor < 0) { $label_sentimen = "negatif"; } 
    else { $label_sentimen = "netral"; }
    
    // Escape string untuk MySQL
    $judul = $conn->real_escape_string($berita['judul']);
    $link = $conn->real_escape_string($berita['link']);
    $deskripsi = $conn->real_escape_string($berita['deskripsi']);
    $tgl = $conn->real_escape_string($berita['tanggal_pub']);
    
    // Cek duplikasi berdasar link
    $cek = $conn->query("SELECT id FROM berita WHERE link = '$link'");
    if ($cek->num_rows == 0) {
        $sql = "INSERT INTO berita (judul, link, deskripsi, tanggal_pub, sentimen, skor_sentimen) 
                VALUES ('$judul', '$link', '$deskripsi', '$tgl', '$label_sentimen', '$skor')";
        if($conn->query($sql)){
            $inserted++;
        }
    }
}

// Hanya tampilkan output visual jika diakses langsung lewat browser
if (basename($_SERVER['PHP_SELF']) == 'analisis.php') {
    echo "<div style='font-family: sans-serif; padding: 20px;'>";
    echo "<h2>Analisis sentimen (PROSES) selesai.</h2>";
    echo "<p><strong>$inserted</strong> berita baru berhasil dianalisis dan tersimpan di database MySQL.</p>";
    echo "<br><a href='dashboard.php' style='padding: 10px 20px; background: #10b981; color: white; text-decoration: none; border-radius: 5px;'>Lihat Dashboard Hasil</a>";
    echo "</div>";
}
?>
