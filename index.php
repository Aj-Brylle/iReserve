<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$base_url = '/IReserve'; 
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Management System</title>
    <!-- We keep the old style in case we need generic variables, but we redefine body below -->
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #0f172a !important;
            color: #f8fafc !important;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        
        /* Dynamic Gradient Background */
        .hero {
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            padding-top: 2rem;
        }
        .hero::before {
            content: '';
            position: absolute;
            top: -50%; left: -50%; width: 200%; height: 200%;
            background: radial-gradient(circle at 50% 50%, rgba(37, 99, 235, 0.15) 0%, rgba(15, 23, 42, 1) 50%);
            animation: rotateBG 20s linear infinite;
            z-index: 0;
        }
        @keyframes rotateBG {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .hero-content {
            position: relative;
            z-index: 10;
            text-align: center;
            max-width: 800px;
            padding: 2rem;
            animation: slideUp 1s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideUp {
            0% { transform: translateY(40px); opacity: 0; }
            100% { transform: translateY(0); opacity: 1; }
        }
        
        /* Glassmorphism Title */
        .glass-badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #38bdf8;
            padding: 0.5rem 1.25rem;
            border-radius: 9999px;
            font-weight: 600;
            font-size: 0.875rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .hero h1 {
            font-size: clamp(3rem, 8vw, 5.5rem);
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, #ffffff 0%, #94a3b8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -0.02em;
        }
        
        .hero p {
            font-size: clamp(1.1rem, 2vw, 1.35rem);
            color: #94a3b8;
            margin-bottom: 3rem;
            line-height: 1.6;
        }
        
        .action-buttons {
            display: flex;
            gap: 1.5rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn-modern {
            padding: 1rem 2.5rem !important;
            border-radius: 9999px !important;
            font-weight: 600 !important;
            font-size: 1.1rem !important;
            text-decoration: none !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 0.5rem !important;
        }
        
        .btn-glow {
            background: #2563eb !important;
            color: white !important;
            box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.5), 0 8px 10px -6px rgba(37, 99, 235, 0.1) !important;
            border: 1px solid rgba(255,255,255,0.1) !important;
        }
        .btn-glow:hover {
            transform: translateY(-3px) !important;
            box-shadow: 0 20px 25px -5px rgba(37, 99, 235, 0.6), 0 8px 10px -6px rgba(37, 99, 235, 0.2) !important;
            background: #1d4ed8 !important;
        }
        
        .btn-glass {
            background: rgba(255, 255, 255, 0.05) !important;
            backdrop-filter: blur(10px) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            color: #f8fafc !important;
        }
        .btn-glass:hover {
            background: rgba(255, 255, 255, 0.1) !important;
            transform: translateY(-3px) !important;
            color: white !important;
        }

        /* Features Section */
        .features {
            padding: 8rem 2rem;
            background-color: #0f172a;
            position: relative;
            z-index: 10;
        }
        
        .section-title {
            text-align: center;
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 700;
            margin-bottom: 4rem;
            color: #f8fafc;
            letter-spacing: -0.01em;
        }
        
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .feature-box {
            background: rgba(30, 41, 59, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.05);
            padding: 3rem 2rem;
            border-radius: 1.5rem;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
        .feature-box::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; height: 3px;
            background: linear-gradient(90deg, transparent, #38bdf8, transparent);
            transform: translateX(-100%);
            transition: transform 0.6s ease;
        }
        
        .feature-box:hover {
            transform: translateY(-10px);
            background: rgba(30, 41, 59, 0.8);
            box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.5), 0 0 20px rgba(56, 189, 248, 0.1);
            border-color: rgba(56, 189, 248, 0.2);
        }
        .feature-box:hover::before {
            transform: translateX(100%);
        }
        
        .icon-wrapper {
            background: linear-gradient(135deg, rgba(37,99,235,0.2) 0%, rgba(56,189,248,0.2) 100%);
            width: 72px; height: 72px;
            border-radius: 1.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 2rem;
            color: #38bdf8;
            border: 1px solid rgba(56,189,248,0.2);
            transition: transform 0.4s ease;
        }
        .feature-box:hover .icon-wrapper {
            transform: scale(1.1) rotate(-5deg);
        }
        
        .feature-box h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            font-weight: 600;
            color: #f8fafc;
        }
        
        .feature-box p {
            color: #94a3b8;
            line-height: 1.6;
            font-size: 1.05rem;
        }

        /* Abstract blobs */
        .blob {
            position: absolute;
            filter: blur(100px);
            z-index: 0;
            opacity: 0.3;
            border-radius: 50%;
            pointer-events: none;
        }
        .blob-1 { top: 10%; left: -10%; width: 50vw; height: 50vw; max-width: 500px; max-height: 500px; background: #2563eb; }
        .blob-2 { bottom: 20%; right: -5%; width: 40vw; height: 40vw; max-width: 400px; max-height: 400px; background: #10b981; }

    </style>
</head>
<body>

    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>

    <section class="hero">
        <div class="hero-content">
            <?php if(isset($_SESSION['success'])): ?>
                <div class="glass-badge" style="color: #34d399; border-color: rgba(52,211,153,0.3); margin-bottom: 1rem;">
                    <i data-lucide="check-circle" style="width:14px;height:14px;display:inline;vertical-align:middle;margin-right:6px;"></i> <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
            <?php if(isset($_SESSION['error'])): ?>
                <div class="glass-badge" style="color: #f87171; border-color: rgba(248,113,113,0.3); margin-bottom: 1rem;">
                    <i data-lucide="alert-circle" style="width:14px;height:14px;display:inline;vertical-align:middle;margin-right:6px;"></i> <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <div class="glass-badge">Next-Gen Barangay System</div>
            <h1>iReserve System</h1>
            <p>Experience seamless community governance. Request documents, reserve barangay equipment, and stay updated with the latest announcements entirely online.</p>
            
            <div class="action-buttons">
                <?php if(!isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])): ?>
                    <a href="login.php" class="btn-modern btn-glow">
                        Log In <i data-lucide="arrow-right"></i>
                    </a>
                    <a href="register.php" class="btn-modern btn-glass">
                        Create Account
                    </a>
                <?php else: ?>
                    <?php $dash = isset($_SESSION['admin_id']) || ($_SESSION['role'] ?? '') === 'Admin' ? 'admin/dashboard.php' : 'resident/dashboard.php'; ?>
                    <a href="<?php echo $dash; ?>" class="btn-modern btn-glow">
                        Access Dashboard <i data-lucide="arrow-right"></i>
                    </a>
                    <a href="logout.php" class="btn-modern btn-glass">
                        Logout
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="features">
        <h2 class="section-title">Digital Services at Your Fingertips</h2>
        <div class="feature-grid">
            

            <div class="feature-box">
                <div class="icon-wrapper">
                    <i data-lucide="boxes" style="width:36px;height:36px;"></i>
                </div>
                <h3>Equipment Reservation</h3>
                <p>Instantly check live stock of barangay hardware like chairs, tables, and tents. Secure your reservation with a single click.</p>
            </div>
            
            <div class="feature-box">
                <div class="icon-wrapper">
                    <i data-lucide="radio-tower" style="width:36px;height:36px;"></i>
                </div>
                <h3>Live Broadcasts</h3>
                <p>Stay informed with vital alerts, public service announcements, and community event scheduling directly from the barangay hall.</p>
            </div>

        </div>
    </section>

    <footer style="background: rgba(15,23,42,0.9); backdrop-filter: blur(10px); border-top: 1px solid rgba(255,255,255,0.05); text-align: center; padding: 3rem 1rem; color: #64748b; position: relative; z-index: 10;">
        <p style="font-family: 'Outfit', sans-serif;">&copy; <?php echo date('Y'); ?> iReserve Barangay Management System. All rights reserved.</p>
    </footer>

    <script>
      lucide.createIcons();
    </script>
</body>
</html>
