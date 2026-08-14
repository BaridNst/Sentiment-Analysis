<?php
session_start();
require_once 'koneksi.php';

$alert_msg = "";
$alert_type = "";

if (isset($_SESSION['alert_msg'])) {
    $alert_msg = $_SESSION['alert_msg'];
    $alert_type = $_SESSION['alert_type'];
    unset($_SESSION['alert_msg']);
    unset($_SESSION['alert_type']);
}

// Proses input manual jika ada
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_manual'])) {
    $judul_manual = $_POST['judul'];
    $deskripsi_manual = $_POST['deskripsi'];
    
    $kata_positif = ['baik', 'bagus', 'meningkat', 'untung', 'berhasil', 'juara', 'positif', 'naik', 'unggul', 'aman', 'laba', 'sukses', 'bantuan', 'apresiasi'];
    $kata_negatif = ['buruk', 'gagal', 'rugi', 'turun', 'krisis', 'bencana', 'negatif', 'kasus', 'ancaman', 'bahaya', 'korupsi', 'skandal', 'tewas', 'kecelakaan', 'tersangka'];

    $teks = strtolower($judul_manual . " " . $deskripsi_manual);
    $skor = 0;
    
    foreach ($kata_positif as $kp) { $skor += substr_count($teks, $kp); }
    foreach ($kata_negatif as $kn) { $skor -= substr_count($teks, $kn); }
    
    if ($skor > 0) { $label_sentimen = "positif"; } 
    elseif ($skor < 0) { $label_sentimen = "negatif"; } 
    else { $label_sentimen = "netral"; }
    
    $judul_clean = $conn->real_escape_string($judul_manual);
    $deskripsi_clean = $conn->real_escape_string($deskripsi_manual);
    $tgl = date("r"); // Format tanggal standar RSS
    $link_manual = "manual-input-" . time(); // Link dummy agar unik
    
    $sql = "INSERT INTO berita (judul, link, deskripsi, tanggal_pub, sentimen, skor_sentimen) 
            VALUES ('$judul_clean', '$link_manual', '$deskripsi_clean', '$tgl', '$label_sentimen', '$skor')";
    
    if($conn->query($sql)) {
        $_SESSION['alert_msg'] = "Berhasil! Teks dianalisis sebagai sentimen <strong>" . strtoupper($label_sentimen) . "</strong>";
        $_SESSION['alert_type'] = $label_sentimen; 
    } else {
        $_SESSION['alert_msg'] = "Gagal menyimpan data!";
        $_SESSION['alert_type'] = "negatif";
    }
    
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SENTIMENT ANALITIK - Dashboard Analisis Sentimen</title>
    <!-- Modern Typography & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #6366f1;
            --primary-light: #818cf8;
            --primary-glow: rgba(99, 102, 241, 0.15);
            --positive: #10b981;
            --positive-glow: rgba(16, 185, 129, 0.15);
            --negative: #f43f5e;
            --negative-glow: rgba(244, 63, 94, 0.15);
            --neutral: #64748b;
            --neutral-glow: rgba(100, 116, 139, 0.15);
            
            --bg-dark: #090d16;
            --bg-card: rgba(17, 24, 39, 0.7);
            --card-border: rgba(255, 255, 255, 0.08);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --text-bright: #ffffff;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-dark);
            background-image: 
                radial-gradient(at 10% 20%, rgba(99, 102, 241, 0.15) 0px, transparent 50%),
                radial-gradient(at 90% 80%, rgba(244, 63, 94, 0.1) 0px, transparent 50%);
            background-attachment: fixed;
            color: var(--text-main);
            min-height: 100vh;
            padding: 40px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* Container Layout */
        .wrapper {
            width: 100%;
            max-width: 1200px;
            display: flex;
            flex-direction: column;
            gap: 30px;
        }

        /* Glassmorphism Header */
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
            padding: 24px 32px;
            background: var(--bg-card);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid var(--card-border);
            border-radius: 24px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand-icon {
            font-size: 2rem;
            background: linear-gradient(135deg, var(--primary) 0%, #a855f7 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .brand-text h1 {
            font-size: 1.75rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            background: linear-gradient(to right, #ffffff, #c084fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .brand-text p {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-top: 2px;
        }

        .nav-links {
            display: flex;
            gap: 12px;
        }

        /* Interactive Grid Dashboard */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            gap: 24px;
        }

        .col-4 { grid-column: span 4; }
        .col-8 { grid-column: span 8; }
        .col-6 { grid-column: span 6; }
        .col-12 { grid-column: span 12; }

        @media (max-width: 1024px) {
            .col-4, .col-8, .col-6 {
                grid-column: span 12;
            }
        }

        /* Glassmorphism Card */
        .card {
            background: var(--bg-card);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--card-border);
            border-radius: 24px;
            padding: 30px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.3s ease, border-color 0.3s;
            position: relative;
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 30px 60px rgba(99, 102, 241, 0.1);
            border-color: rgba(99, 102, 241, 0.3);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .card-title {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--text-bright);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-title i {
            color: var(--primary-light);
        }

        /* Metrics / Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            width: 100%;
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .stat-card {
            background: rgba(30, 41, 59, 0.4);
            border: 1px solid var(--card-border);
            border-radius: 20px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            transition: all 0.3s;
        }

        .stat-card:hover {
            background: rgba(30, 41, 59, 0.6);
            transform: translateY(-2px);
        }

        .stat-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
        }

        .stat-content {
            display: flex;
            flex-direction: column;
        }

        .stat-value {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--text-bright);
            line-height: 1.2;
        }

        .stat-label {
            font-size: 0.8rem;
            color: var(--text-muted);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 2px;
        }

        /* Stat Themes */
        .stat-tot .stat-icon { background: var(--primary-glow); color: var(--primary-light); }
        .stat-pos .stat-icon { background: var(--positive-glow); color: var(--positive); }
        .stat-neg .stat-icon { background: var(--negative-glow); color: var(--negative); }
        .stat-neu .stat-icon { background: var(--neutral-glow); color: var(--neutral); }

        /* Chart Wrapper with Side-by-side or Tab layouts */
        .charts-container {
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            gap: 24px;
            align-items: center;
        }

        @media (max-width: 768px) {
            .charts-container {
                grid-template-columns: 1fr;
            }
        }

        .chart-box {
            position: relative;
            height: 280px;
            width: 100%;
        }

        .chart-legend-custom {
            display: flex;
            flex-direction: column;
            gap: 12px;
            justify-content: center;
        }

        .legend-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(255, 255, 255, 0.02);
            padding: 12px 16px;
            border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.03);
            font-size: 0.9rem;
        }

        .legend-label-col {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
        }

        .legend-bullet {
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }

        .legend-bullet.pos { background: var(--positive); }
        .legend-bullet.neg { background: var(--negative); }
        .legend-bullet.neu { background: var(--neutral); }

        .legend-value-col {
            font-weight: 700;
            color: var(--text-bright);
        }

        /* Form Design */
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .form-control {
            width: 100%;
            padding: 14px 16px;
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid var(--card-border);
            border-radius: 14px;
            color: var(--text-main);
            font-family: inherit;
            font-size: 0.95rem;
            box-sizing: border-box;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary-light);
            background: rgba(15, 23, 42, 0.9);
            box-shadow: 0 0 15px rgba(99, 102, 241, 0.25);
        }

        /* Table Design */
        .table-responsive {
            overflow-x: auto;
            width: 100%;
            margin-top: 10px;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 8px;
        }

        th {
            color: var(--text-muted);
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1px;
            padding: 12px 16px;
            border-bottom: 2px solid rgba(255, 255, 255, 0.05);
        }

        td {
            padding: 16px;
            background: rgba(255, 255, 255, 0.02);
            border-top: 1px solid rgba(255, 255, 255, 0.03);
            border-bottom: 1px solid rgba(255, 255, 255, 0.03);
            font-size: 0.9rem;
            transition: background 0.2s;
        }

        td:first-child {
            border-left: 1px solid rgba(255, 255, 255, 0.03);
            border-radius: 12px 0 0 12px;
        }

        td:last-child {
            border-right: 1px solid rgba(255, 255, 255, 0.03);
            border-radius: 0 12px 12px 0;
        }

        tr:hover td {
            background: rgba(255, 255, 255, 0.05);
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge i {
            font-size: 0.7rem;
        }

        .badge-positif { background: var(--positive-glow); color: var(--positive); border: 1px solid rgba(16, 185, 129, 0.3); }
        .badge-negatif { background: var(--negative-glow); color: var(--negative); border: 1px solid rgba(244, 63, 94, 0.3); }
        .badge-netral { background: var(--neutral-glow); color: #cbd5e1; border: 1px solid rgba(148, 163, 184, 0.3); }

        /* Buttons & Alerts */
        .btn {
            background: linear-gradient(135deg, var(--primary) 0%, #4f46e5 100%);
            color: white;
            text-decoration: none;
            padding: 14px 28px;
            border-radius: 16px;
            font-weight: 700;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
            border: 1px solid rgba(255,255,255,0.1);
            cursor: pointer;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(99, 102, 241, 0.5);
            background: linear-gradient(135deg, var(--primary-light) 0%, #5850ec 100%);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: none;
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.2);
            box-shadow: none;
        }

        .btn-action-group {
            display: flex;
            gap: 12px;
        }

        .alert {
            padding: 16px 20px;
            border-radius: 16px;
            font-weight: 600;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: fadeInDown 0.5s ease;
        }
        .alert-positif { background: var(--positive-glow); border: 1px solid rgba(16, 185, 129, 0.3); color: var(--positive); }
        .alert-negatif { background: var(--negative-glow); border: 1px solid rgba(244, 63, 94, 0.3); color: var(--negative); }
        .alert-netral { background: var(--neutral-glow); border: 1px solid rgba(148, 163, 184, 0.3); color: #e2e8f0; }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: var(--bg-dark);
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.1);
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(255,255,255,0.2);
        }
    </style>
</head>
<body>

    <div class="wrapper">
        
        <!-- Header -->
        <div class="header-section">
            <div class="brand">
                <div class="brand-icon">
                    <i class="fa-solid fa-chart-line"></i>
                </div>
                <div class="brand-text">
                    <h1>SENTIMENT ANALITIK</h1>
                    <p>Visualisasi Real-time & Analisis Sentimen Berita Kecerdasan Buatan</p>
                </div>
            </div>
            <div class="nav-links">
                <a href="analisis.php" class="btn" style="background: linear-gradient(135deg, var(--positive) 0%, #059669 100%); box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);">
                    <i class="fa-solid fa-arrows-rotate"></i> Update RSS & Analisis
                </a>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fa-solid fa-code"></i> Visualisasi JSON
                </a>
            </div>
        </div>

        <?php if($alert_msg != ""): ?>
            <div class="alert alert-<?php echo $alert_type; ?>">
                <i class="fa-solid <?php 
                    echo $alert_type === 'positif' ? 'fa-circle-check' : ($alert_type === 'negatif' ? 'fa-circle-xmark' : 'fa-circle-info'); 
                ?>"></i>
                <div><?php echo $alert_msg; ?></div>
            </div>
        <?php endif; ?>

        <!-- Filter Bar -->
        <div class="filter-section card" style="padding: 15px 30px; display: flex; flex-direction: row; justify-content: space-between; align-items: center; gap: 15px; margin-bottom: -5px; border-radius: 20px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-filter" style="color: var(--primary-light); font-size: 1.1rem;"></i>
                <span style="font-weight: 700; font-size: 0.95rem; letter-spacing: 0.5px;">Wilayah Analisis:</span>
            </div>
            <div style="display: flex; gap: 8px;">
                <button class="btn btn-sm filter-btn active" id="btnFilterAll" data-filter="all" style="padding: 8px 16px; font-size: 0.85rem; border-radius: 12px;">Semua Berita Aceh</button>
                <button class="btn btn-sm btn-secondary filter-btn" id="btnFilterSingkil" data-filter="singkil" style="padding: 8px 16px; font-size: 0.85rem; border-radius: 12px; background: rgba(255, 255, 255, 0.05); box-shadow: none;">Khusus Aceh Singkil</button>
            </div>
        </div>

        <!-- Stats Cards Row -->
        <div class="stats-grid">
            <div class="stat-card stat-tot">
                <div class="stat-icon"><i class="fa-solid fa-newspaper"></i></div>
                <div class="stat-content">
                    <div class="stat-value" id="valTotal">0</div>
                    <div class="stat-label">Total Berita</div>
                </div>
            </div>
            <div class="stat-card stat-pos">
                <div class="stat-icon"><i class="fa-solid fa-face-smile"></i></div>
                <div class="stat-content">
                    <div class="stat-value" id="valPos">0</div>
                    <div class="stat-label">Positif</div>
                </div>
            </div>
            <div class="stat-card stat-neg">
                <div class="stat-icon"><i class="fa-solid fa-face-frown"></i></div>
                <div class="stat-content">
                    <div class="stat-value" id="valNeg">0</div>
                    <div class="stat-label">Negatif</div>
                </div>
            </div>
            <div class="stat-card stat-neu">
                <div class="stat-icon"><i class="fa-solid fa-face-meh"></i></div>
                <div class="stat-content">
                    <div class="stat-value" id="valNeu">0</div>
                    <div class="stat-label">Netral</div>
                </div>
            </div>
        </div>

        <!-- Main Dashboard Grid -->
        <div class="dashboard-grid">
            
            <!-- Left Chart Card (Now is a dynamic side-by-side view featuring a Polar Area Chart + Custom Stats) -->
            <div class="col-8 card">
                <div class="card-header">
                    <div class="card-title">
                        <i class="fa-solid fa-chart-pie"></i>
                        <span>Pembagian Sentimen (Polar Area Chart)</span>
                    </div>
                </div>
                <div class="charts-container">
                    <div class="chart-box">
                        <canvas id="sentimenChart"></canvas>
                    </div>
                    <div class="chart-legend-custom">
                        <div class="legend-row">
                            <div class="legend-label-col">
                                <span class="legend-bullet pos"></span>
                                <span>Sentimen Positif</span>
                            </div>
                            <div class="legend-value-col" id="pctPos">0%</div>
                        </div>
                        <div class="legend-row">
                            <div class="legend-label-col">
                                <span class="legend-bullet neg"></span>
                                <span>Sentimen Negatif</span>
                            </div>
                            <div class="legend-value-col" id="pctNeg">0%</div>
                        </div>
                        <div class="legend-row">
                            <div class="legend-label-col">
                                <span class="legend-bullet neu"></span>
                                <span>Sentimen Netral</span>
                            </div>
                            <div class="legend-value-col" id="pctNeu">0%</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Form Card -->
            <div class="col-4 card">
                <div class="card-header">
                    <div class="card-title">
                        <i class="fa-solid fa-pen-to-square"></i>
                        <span>Input Analisis Manual</span>
                    </div>
                </div>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="judul">Judul Berita / Teks</label>
                        <input type="text" id="judul" name="judul" class="form-control" required placeholder="Masukkan judul teks...">
                    </div>
                    <div class="form-group">
                        <label for="deskripsi">Isi Teks / Deskripsi</label>
                        <textarea id="deskripsi" name="deskripsi" class="form-control" rows="4" required placeholder="Tulis deskripsi atau konten lengkap..."></textarea>
                    </div>
                    <button type="submit" name="submit_manual" class="btn" style="width: 100%;">
                        <i class="fa-solid fa-brain"></i> Analisis & Simpan
                    </button>
                </form>
            </div>

            <!-- Table Card -->
            <div class="col-12 card">
                <div class="card-header">
                    <div class="card-title">
                        <i class="fa-solid fa-list-check"></i>
                        <span>Data Analisis Berita Terbaru</span>
                    </div>
                    <div class="btn-action-group">
                        <a href="fetch_rss.php" class="btn btn-secondary btn-sm" style="padding: 8px 16px; font-size: 0.8rem;">
                            <i class="fa-solid fa-download"></i> 1. Tarik RSS
                        </a>
                        <a href="analisis.php" class="btn btn-secondary btn-sm" style="padding: 8px 16px; font-size: 0.8rem;">
                            <i class="fa-solid fa-rotate"></i> 2. Proses Analisis
                        </a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="newsTable">
                        <thead>
                            <tr>
                                <th>Judul Berita</th>
                                <th>Tanggal Publikasi</th>
                                <th style="width: 140px;">Sentimen</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="3" style="text-align: center; color: var(--text-muted);"><i class="fa-solid fa-spinner fa-spin"></i> Memuat data analisis...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </div>
    
    <script>
        let globalData = [];
        let activeFilter = 'all';

        function updateDashboard(filterType) {
            activeFilter = filterType;
            let filtered = globalData;
            
            if (filterType === 'singkil') {
                filtered = globalData.filter(item => {
                    const text = (item.judul + " " + item.deskripsi).toLowerCase();
                    // Cocok dengan "singkil", atau kecamatan-kecamatan di Aceh Singkil
                    return text.includes('singkil') || 
                           text.includes('gunung meriah') || 
                           text.includes('simpang kanan') || 
                           text.includes('singkohor') || 
                           text.includes('kuala baru') || 
                           text.includes('danau paris') || 
                           text.includes('suro makmur') || 
                           text.includes('kota baharu') || 
                           text.includes('pulau banyak');
                });
            }

            let positif = 0, negatif = 0, netral = 0;
            filtered.forEach(item => {
                if (item.sentimen === 'positif') positif++;
                else if (item.sentimen === 'negatif') negatif++;
                else netral++;
            });

            const total = filtered.length || 1; // avoid division by 0
            const pctPosVal = Math.round((positif / total) * 100);
            const pctNegVal = Math.round((negatif / total) * 100);
            const pctNeuVal = Math.round((netral / total) * 100);

            // Update Stats
            document.getElementById('valTotal').innerText = filtered.length;
            document.getElementById('valPos').innerText = positif;
            document.getElementById('valNeg').innerText = negatif;
            document.getElementById('valNeu').innerText = netral;

            // Update Percentage Text
            document.getElementById('pctPos').innerText = pctPosVal + '%';
            document.getElementById('pctNeg').innerText = pctNegVal + '%';
            document.getElementById('pctNeu').innerText = pctNeuVal + '%';

            // Render/Update Chart
            const ctx = document.getElementById('sentimenChart').getContext('2d');
            if (window.myChart) {
                window.myChart.destroy();
            }
            window.myChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Positif', 'Netral', 'Negatif'],
                    datasets: [{
                        data: [positif, netral, negatif],
                        backgroundColor: [
                            'rgba(16, 185, 129, 0.75)', 
                            'rgba(100, 116, 139, 0.75)',
                            'rgba(244, 63, 94, 0.75)'
                        ],
                        borderColor: [
                            '#10b981', 
                            '#64748b',
                            '#f43f5e'
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            grid: {
                                color: 'rgba(255, 255, 255, 0.05)'
                            },
                            ticks: {
                                color: '#94a3b8',
                                font: { family: 'Plus Jakarta Sans', size: 11 }
                            }
                        },
                        y: {
                            grid: {
                                color: 'rgba(255, 255, 255, 0.05)'
                            },
                            ticks: {
                                color: '#94a3b8',
                                font: { family: 'Plus Jakarta Sans', size: 11 },
                                stepSize: 1
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            titleFont: { family: 'Plus Jakarta Sans', size: 13, weight: '600' },
                            bodyFont: { family: 'Plus Jakarta Sans', size: 13 },
                            padding: 12,
                            cornerRadius: 10,
                            borderColor: 'rgba(255,255,255,0.08)',
                            borderWidth: 1
                        }
                    }
                }
            });

            // Populate Table
            const tbody = document.querySelector('#newsTable tbody');
            tbody.innerHTML = ''; 
            
            if(filtered.length === 0) {
                tbody.innerHTML = '<tr><td colspan="3" style="text-align: center; color: var(--text-muted); padding: 30px;">Belum ada data berita khusus Aceh Singkil. Silakan Tarik/Proses Data.</td></tr>';
                return;
            }
            
            filtered.forEach(item => {
                const tr = document.createElement('tr');
                const dateObj = new Date(item.tanggal_pub);
                const formattedDate = isNaN(dateObj) ? item.tanggal_pub : dateObj.toLocaleDateString('id-ID', {day: 'numeric', month: 'short', year: 'numeric'});
                
                let linkDisplay = item.link.startsWith('manual-') ? '#' : item.link;
                let targetAttr = item.link.startsWith('manual-') ? '' : 'target="_blank"';
                
                let icon = '';
                if (item.sentimen === 'positif') icon = '<i class="fa-solid fa-circle-check"></i> ';
                else if (item.sentimen === 'negatif') icon = '<i class="fa-solid fa-circle-xmark"></i> ';
                else icon = '<i class="fa-solid fa-circle-minus"></i> ';

                tr.innerHTML = `
                    <td><a href="${linkDisplay}" ${targetAttr} style="color: var(--text-main); text-decoration: none; font-weight: 600; transition: color 0.2s;" onmouseover="this.style.color='#818cf8'" onmouseout="this.style.color='var(--text-main)'">${item.judul}</a></td>
                    <td style="color: var(--text-muted); font-size: 0.85rem; font-weight: 500;">${formattedDate}</td>
                    <td><span class="badge badge-${item.sentimen}">${icon}${item.sentimen}</span></td>
                `;
                tbody.appendChild(tr);
            });
        }

        // Fetch Data Awal
        fetch('api_hasil.php?_=' + Date.now())
            .then(response => response.json())
            .then(data => {
                globalData = data.data || [];
                updateDashboard('all');
            })
            .catch(error => {
                console.error('Gagal mengambil data:', error);
                document.querySelector('#newsTable tbody').innerHTML = '<tr><td colspan="3" style="text-align: center; color: var(--negative); padding: 30px;"><i class="fa-solid fa-circle-exclamation"></i> Gagal memuat data dari API. Pastikan database dan koneksi sudah benar.</td></tr>';
            });

        // Filter button click event listeners
        document.getElementById('btnFilterAll').addEventListener('click', function() {
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.add('btn-secondary');
                btn.style.background = 'rgba(255, 255, 255, 0.05)';
                btn.style.boxShadow = 'none';
            });
            this.classList.remove('btn-secondary');
            this.style.background = 'linear-gradient(135deg, var(--primary) 0%, #4f46e5 100%)';
            this.style.boxShadow = '0 4px 15px rgba(99, 102, 241, 0.3)';
            updateDashboard('all');
        });

        document.getElementById('btnFilterSingkil').addEventListener('click', function() {
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.add('btn-secondary');
                btn.style.background = 'rgba(255, 255, 255, 0.05)';
                btn.style.boxShadow = 'none';
            });
            this.classList.remove('btn-secondary');
            this.style.background = 'linear-gradient(135deg, var(--primary) 0%, #4f46e5 100%)';
            this.style.boxShadow = '0 4px 15px rgba(99, 102, 241, 0.3)';
            updateDashboard('singkil');
        });
    </script>

</body>
</html>
