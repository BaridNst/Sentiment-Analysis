<?php
$hasil_analisis = null;
$error_msg = null;

// 1. Mengecek apakah tombol "Visualisasikan" sudah ditekan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['json_input'])) {
    $json_raw = $_POST['json_input'];
    
    // 2. Menerjemahkan JSON
    $hasil_analisis = json_decode($json_raw, true);
    
    // 3. Validasi apakah format JSON benar
    if (json_last_error() !== JSON_ERROR_NONE) {
        $error_msg = "Format JSON tidak valid! Pastikan kamu menyalin (copy) dari kurung kurawal pembuka { hingga penutup }.";
        $hasil_analisis = null;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SENTIMENT ANALITIK - Visualisasi Analisis Sentimen</title>
    <!-- Modern Typography & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

        .wrapper {
            width: 100%;
            max-width: 800px;
            display: flex;
            flex-direction: column;
            gap: 25px;
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

        /* Glassmorphism Card */
        .card {
            background: var(--bg-card);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--card-border);
            border-radius: 24px;
            padding: 30px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            transition: all 0.3s;
        }

        .card:hover {
            border-color: rgba(99, 102, 241, 0.25);
            box-shadow: 0 20px 40px rgba(99, 102, 241, 0.05);
        }

        textarea {
            width: 100%;
            height: 160px;
            padding: 16px;
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            color: var(--text-main);
            font-family: monospace;
            font-size: 0.9rem;
            resize: vertical;
            box-sizing: border-box;
            margin-bottom: 20px;
            transition: all 0.3s;
        }
        
        textarea:focus {
            outline: none;
            border-color: var(--primary-light);
            background: rgba(15, 23, 42, 0.9);
            box-shadow: 0 0 15px rgba(99, 102, 241, 0.25);
        }

        .btn {
            background: linear-gradient(135deg, var(--primary) 0%, #4f46e5 100%);
            color: white;
            text-decoration: none;
            padding: 14px 28px;
            border-radius: 16px;
            font-weight: 700;
            font-size: 0.95rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
            border: 1px solid rgba(255,255,255,0.1);
            cursor: pointer;
            width: 100%;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(99, 102, 241, 0.5);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: none;
            width: auto;
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.2);
            box-shadow: none;
        }

        .error {
            background: var(--negative-glow);
            color: var(--negative);
            border: 1px solid rgba(244, 63, 94, 0.3);
            padding: 16px 20px;
            border-radius: 16px;
            font-weight: 600;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        /* Visualization Result Styles */
        .result-title {
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }

        h2.news-headline {
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--text-bright);
            line-height: 1.4;
            margin-bottom: 20px;
        }

        .badge-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 20px;
            border-radius: 30px;
            font-weight: 700;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: white;
            margin-bottom: 25px;
        }

        .section-header {
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 25px;
            margin-bottom: 10px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            padding-bottom: 6px;
        }

        .reason-card {
            padding: 20px;
            border-radius: 16px;
            line-height: 1.6;
            font-size: 0.95rem;
        }

        /* Progress Bar */
        .progress-track {
            width: 100%;
            height: 12px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 30px;
            overflow: hidden;
            margin-top: 10px;
            border: 1px solid rgba(255, 255, 255, 0.03);
        }

        .progress-fill {
            height: 100%;
            border-radius: 30px;
            transition: width 1s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .progress-labels {
            display: flex;
            justify-content: space-between;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-muted);
            margin-top: 6px;
        }

        /* Keywords */
        .tags-container {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 10px;
        }

        .tag {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.06);
            color: var(--text-main);
            padding: 6px 14px;
            border-radius: 10px;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.2s;
        }

        .tag:hover {
            background: rgba(99, 102, 241, 0.1);
            border-color: rgba(99, 102, 241, 0.3);
            color: var(--primary-light);
        }
    </style>
</head>
<body>

    <div class="wrapper">
        
        <!-- Header -->
        <div class="header-section">
            <div class="brand">
                <div class="brand-icon">
                    <i class="fa-solid fa-brain"></i>
                </div>
                <div class="brand-text">
                    <h1>SENTIMENT ANALITIK</h1>
                    <p>Visualisasi Output AI & Sentimen Berita</p>
                </div>
            </div>
            <a href="dashboard.php" class="btn btn-secondary">
                <i class="fa-solid fa-chart-simple"></i> Dashboard Utama
            </a>
        </div>

        <!-- Input Card -->
        <div class="card">
            <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 10px; color: var(--text-bright);">Input Data JSON</h2>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 20px;">Tempelkan (paste) hasil respons JSON mentah dari Model AI Anda untuk melihat visualisasi indahnya.</p>
            
            <form method="POST" action="">
                <textarea name="json_input" placeholder='{
  "judul": "Ekonomi Indonesia Tumbuh Pesat Sebesar 5.5%",
  "sentimen": "Positif",
  "skor_keyakinan": 0.95,
  "alasan": "Teks menggunakan kata positif seperti tumbuh pesat dan kenaikan persentase yang tinggi.",
  "kata_kunci": ["ekonomi", "tumbuh pesat", "indonesia"]
}' required><?php echo isset($_POST['json_input']) ? htmlspecialchars($_POST['json_input']) : ''; ?></textarea>
                <button type="submit" class="btn">
                    <i class="fa-solid fa-wand-magic-sparkles"></i> Visualisasikan Hasil
                </button>
            </form>
        </div>

        <!-- Error Msg -->
        <?php if ($error_msg): ?>
            <div class="error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span><?php echo $error_msg; ?></span>
            </div>
        <?php endif; ?>

        <!-- Visualization Result -->
        <?php if ($hasil_analisis): 
            $warna = 'var(--neutral)';
            $glow = 'var(--neutral-glow)';
            $badge_icon = 'fa-circle-question';
            
            $sentimen_lower = strtolower($hasil_analisis['sentimen']);
            if ($sentimen_lower === 'positif') {
                $warna = 'var(--positive)';
                $glow = 'var(--positive-glow)';
                $badge_icon = 'fa-circle-check';
            } elseif ($sentimen_lower === 'negatif') {
                $warna = 'var(--negative)';
                $glow = 'var(--negative-glow)';
                $badge_icon = 'fa-circle-xmark';
            } elseif ($sentimen_lower === 'netral') {
                $warna = '#3b82f6';
                $glow = 'rgba(59, 130, 246, 0.15)';
                $badge_icon = 'fa-circle-minus';
            }

            $persentase = (isset($hasil_analisis['skor_keyakinan']) ? floatval($hasil_analisis['skor_keyakinan']) : 0) * 100;
        ?>
            <div class="card" style="border-color: rgba(255, 255, 255, 0.12);">
                <div class="result-title">Judul Berita yang Dianalisis</div>
                <h2 class="news-headline"><?php echo htmlspecialchars($hasil_analisis['judul']); ?></h2>

                <div class="badge-pill" style="background-color: <?php echo $glow; ?>; color: <?php echo $warna; ?>; border: 1px solid <?php echo $warna; ?>;">
                    <i class="fa-solid <?php echo $badge_icon; ?>"></i>
                    <span>Sentimen: <?php echo htmlspecialchars($hasil_analisis['sentimen']); ?></span>
                </div>

                <div class="section-header">Skor Keyakinan (Confidence Score)</div>
                <div class="progress-track">
                    <div class="progress-fill" style="width: <?php echo $persentase; ?>%; background-color: <?php echo $warna; ?>;"></div>
                </div>
                <div class="progress-labels">
                    <span>0.0 (Rendah)</span>
                    <strong style="color: <?php echo $warna; ?>; font-size: 0.95rem;"><?php echo $hasil_analisis['skor_keyakinan']; ?> (<?php echo $persentase; ?>%)</strong>
                    <span>1.0 (Mutlak)</span>
                </div>

                <div class="section-header">Alasan Analisis Sentimen</div>
                <div class="reason-card" style="background-color: <?php echo $glow; ?>; border-left: 4px solid <?php echo $warna; ?>;">
                    <?php echo htmlspecialchars($hasil_analisis['alasan']); ?>
                </div>

                <div class="section-header">Kata Kunci Utama</div>
                <div class="tags-container">
                    <?php 
                    if (isset($hasil_analisis['kata_kunci']) && is_array($hasil_analisis['kata_kunci'])) {
                        foreach ($hasil_analisis['kata_kunci'] as $kata) {
                            echo '<span class="tag">#' . htmlspecialchars($kata) . '</span>';
                        }
                    }
                    ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>