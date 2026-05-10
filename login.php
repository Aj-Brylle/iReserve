<?php
session_start();
require_once 'config/database.php';

// If already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'Admin') {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: resident/dashboard.php");
    }
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (empty($identifier) || empty($password)) {
        $error = 'Please enter your Gmail/username and password.';
    } else {
        $authenticated = false;

        // If looks like an email, check admins table first
        if (strpos($identifier, '@') !== false) {
            $email = strtolower($identifier);
            $aStmt = $pdo->prepare('SELECT * FROM admins WHERE LOWER(email) = ?');
            $aStmt->execute([$email]);
            $admin = $aStmt->fetch();
            if ($admin && password_verify($password, $admin['password_hash'])) {
                // admin login
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['email'] = $email;
                $_SESSION['role'] = 'Admin';
                header("Location: admin/dashboard.php");
                exit;
            }
        }

        // Not an admin or admin auth failed: continue with existing user/resident logic
        $user = null;
        // If input looks like an email, try to resolve to a user via residents.email
        if (strpos($identifier, '@') !== false) {
            $email = strtolower($identifier);
            $rStmt = $pdo->prepare('SELECT user_id FROM residents WHERE LOWER(email) = ?');
            $rStmt->execute([$email]);
            $res = $rStmt->fetch();
            if ($res && $res['user_id']) {
                $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
                $stmt->execute([$res['user_id']]);
                $user = $stmt->fetch();
            }
        }

        // Fallback: treat input as username (legacy)
        if (!$user) {
            $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
            $stmt->execute([$identifier]);
            $user = $stmt->fetch();
        }

        if ($user && password_verify($password, $user['password_hash'])) {
            if ($user['status'] === 'Pending') {
                $error = 'Your account is pending admin approval.';
            } elseif ($user['status'] === 'Rejected') {
                $error = 'Your account application was rejected.';
            } else {
                // Login success for regular user
                $_SESSION['user_id'] = $user['id'];
                // populate email session for app use
                $_SESSION['email'] = $identifier;
                $_SESSION['role'] = $user['role'];

                // if Resident, fetch resident_id and store in session for quick access
                if ($user['role'] === 'Resident') {
                    $rStmt = $pdo->prepare('SELECT id FROM residents WHERE user_id = ?');
                    $rStmt->execute([$user['id']]);
                    $resident = $rStmt->fetch();
                    if ($resident) {
                        $_SESSION['resident_id'] = $resident['id'];
                    }
                    header("Location: resident/dashboard.php");
                } else {
                    header("Location: admin/dashboard.php");
                }
                exit;
            }
        } else {
            $error = 'Invalid credentials. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In - iReserve Portal</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #0f172a;
            color: #f8fafc;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        
        body::before {
            content: '';
            position: fixed;
            top: -50%; left: -50%; width: 200%; height: 200%;
            background: radial-gradient(circle at 50% 50%, rgba(37, 99, 235, 0.1) 0%, rgba(15, 23, 42, 1) 50%);
            animation: rotateBG 20s linear infinite;
            z-index: 0;
        }
        @keyframes rotateBG {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .blob {
            position: fixed;
            filter: blur(100px);
            z-index: 0;
            opacity: 0.3;
            border-radius: 50%;
            pointer-events: none;
        }
        .blob-1 { top: 10%; right: -10%; width: 50vw; height: 50vw; max-width: 500px; max-height: 500px; background: #2563eb; }
        .blob-2 { bottom: -10%; left: -5%; width: 40vw; height: 40vw; max-width: 400px; max-height: 400px; background: #10b981; }

        .auth-container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 450px;
            padding: 2rem;
            animation: slideUp 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes slideUp {
            0% { transform: translateY(30px); opacity: 0; }
            100% { transform: translateY(0); opacity: 1; }
        }

        .glass-card {
            background: rgba(30, 41, 59, 0.6);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 1.5rem;
            padding: 3rem 2.5rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .auth-header { text-align: center; margin-bottom: 2.5rem; }
        .auth-header h2 {
            font-size: 2.25rem; font-weight: 700; margin: 0 0 0.5rem;
            background: linear-gradient(135deg, #ffffff 0%, #94a3b8 100%);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }
        .auth-header p { color: #94a3b8; font-size: 1.05rem; margin: 0; }

        .form-group { margin-bottom: 1.5rem; }
        .form-label { display: block; font-size: 0.9rem; font-weight: 500; color: #cbd5e1; margin-bottom: 0.5rem; }
        .form-control {
            width: 100%; box-sizing: border-box; padding: 0.85rem 1.25rem;
            font-family: inherit; font-size: 1rem;
            background: rgba(15, 23, 42, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 0.75rem; color: #f8fafc;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            outline: none; background: rgba(15, 23, 42, 0.8);
            border-color: #38bdf8; box-shadow: 0 0 0 4px rgba(56, 189, 248, 0.1);
        }

        .btn-glow {
            width: 100%; padding: 1rem 1.5rem; border: none; border-radius: 0.75rem;
            font-family: inherit; font-size: 1.1rem; font-weight: 600;
            background: #2563eb; color: white; cursor: pointer;
            box-shadow: 0 10px 20px -5px rgba(37, 99, 235, 0.4);
            transition: all 0.3s ease; display: flex; justify-content: center; align-items: center; gap: 0.5rem;
        }
        .btn-glow:hover {
            background: #1d4ed8; transform: translateY(-2px);
            box-shadow: 0 15px 25px -5px rgba(37, 99, 235, 0.5);
        }

        .alert-error {
            background: rgba(248, 113, 113, 0.1);
            border: 1px solid rgba(248, 113, 113, 0.3);
            color: #fca5a5; padding: 1rem 1.25rem; border-radius: 0.75rem;
            font-size: 0.9rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;
        }

        .auth-footer { text-align: center; margin-top: 2rem; font-size: 0.95rem; color: #94a3b8; }
        .auth-footer a { color: #38bdf8; text-decoration: none; font-weight: 600; transition: color 0.2s; }
        .auth-footer a:hover { color: #7dd3fc; }

        .back-home {
            display: inline-flex; align-items: center; gap: 0.5rem;
            color: #94a3b8; text-decoration: none; font-size: 0.9rem;
            position: absolute; top: 2rem; left: 2rem; z-index: 20; transition: color 0.2s;
        }
        .back-home:hover { color: white; }
    </style>
</head>
<body>
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>

    <a href="index.php" class="back-home"><i data-lucide="arrow-left" style="width:16px;height:16px;"></i> Return to Homepage</a>

    <div class="auth-container">
        <div class="glass-card">
            <div class="auth-header">
                <h2>Welcome Back</h2>
                <p>Sign in to access digital services</p>
            </div>

            <?php if ($error): ?>
                <div class="alert-error"><i data-lucide="alert-circle" style="width:18px;height:18px;"></i> <?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert-error" style="background: rgba(52, 211, 153, 0.1); border-color: rgba(52, 211, 153, 0.3); color: #6ee7b7;">
                    <i data-lucide="check-circle" style="width:18px;height:18px;"></i> <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <div class="form-group">
            <label class="form-label">Gmail</label>
            <input type="text" name="email" class="form-control" placeholder="Enter your Gmail" required autofocus>
                </div>
                <div class="form-group" style="margin-bottom: 2rem;">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>
                
                <button type="submit" class="btn-glow">
                    Sign In <i data-lucide="log-in" style="width:20px;height:20px;"></i>
                </button>
            </form>

            <div class="auth-footer">
                Don't have an account? <a href="register.php">Register now</a>
            </div>
        </div>
    </div>

    <script>lucide.createIcons();</script>
</body>
</html>
