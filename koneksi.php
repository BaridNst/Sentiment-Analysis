<?php
$host = "localhost";
$user = "root";
$pass = ""; // sesuaikan dengan password db Anda
$db   = "db_sentiment_analysis";

// Konek ke MySQL tanpa spesifik database dulu
$conn = new mysqli($host, $user, $pass);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Buat database jika belum ada
$conn->query("CREATE DATABASE IF NOT EXISTS $db");

// Pilih database tersebut
$conn->select_db($db);

// Buat tabel jika belum ada
$sql_table = "CREATE TABLE IF NOT EXISTS berita (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul TEXT NOT NULL,
    link TEXT NOT NULL,
    deskripsi TEXT,
    tanggal_pub VARCHAR(100),
    sentimen VARCHAR(50),
    skor_sentimen FLOAT
)";
$conn->query($sql_table);

// Pastikan tipe kolom link dan judul sudah bertipe TEXT jika tabel sudah ada sebelumnya
$conn->query("ALTER TABLE berita MODIFY COLUMN link TEXT NOT NULL");
$conn->query("ALTER TABLE berita MODIFY COLUMN judul TEXT NOT NULL");
?>
