<?php
$opml_file = 'feeds.opml';
if (!file_exists($opml_file)) {
    die("File feeds.opml tidak ditemukan. Silakan buat atau ekspor dari Feedreader terlebih dahulu.");
}

$opml_content = file_get_contents($opml_file);
$xml_opml = simplexml_load_string($opml_content);
$berita_list = [];

// Mengambil semua outline yang memiliki xmlUrl (termasuk dari dalam folder)
$outlines = $xml_opml->xpath('//outline[@xmlUrl]');

foreach ($outlines as $outline) {
    $rss_url = (string)$outline['xmlUrl'];
    $sumber = isset($outline['title']) ? (string)$outline['title'] : 'Feed Lainnya';
    
    // Konteks untuk mengabaikan error jika RSS gagal ditarik
    $context = stream_context_create(['http' => ['ignore_errors' => true]]);
    $rss_content = @file_get_contents($rss_url, false, $context);
    
    if ($rss_content) {
        $xml_rss = @simplexml_load_string($rss_content);
        if ($xml_rss && isset($xml_rss->channel->item)) {
            foreach ($xml_rss->channel->item as $item) {
                $berita_list[] = [
                    "sumber" => $sumber,
                    "judul" => (string)$item->title,
                    "link" => (string)$item->link,
                    "deskripsi" => strip_tags((string)$item->description), // Menghilangkan tag HTML dari deskripsi
                    "tanggal_pub" => (string)$item->pubDate
                ];
            }
        }
    }
}

$json_berita = json_encode($berita_list, JSON_PRETTY_PRINT);
file_put_contents('data_berita.json', $json_berita);

// Hanya tampilkan output visual jika diakses langsung lewat browser
if (basename($_SERVER['PHP_SELF']) == 'fetch_rss.php') {
    echo "<div style='font-family: sans-serif; padding: 20px;'>";
    echo "<h2>Berhasil memproses " . count($outlines) . " sumber feed dari Feedreader.</h2>";
    echo "<p>Total berita: <strong>" . count($berita_list) . "</strong> berita disimpan ke <em>data_berita.json</em></p>";
    echo "<br><a href='analisis.php' style='padding: 10px 20px; background: #4f46e5; color: white; text-decoration: none; border-radius: 5px;'>Lanjut ke Proses Analisis</a>";
    echo "</div>";
}
?>
