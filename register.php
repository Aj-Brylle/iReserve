<?php
session_start();
require_once 'config/database.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    // we'll store the email as the account identifier in users.username
    $username = $email;
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];
    
    // Additional Resident fields
    $first_name = trim($_POST['first_name']);
    $last_name  = trim($_POST['last_name']);
    $dob        = $_POST['dob'];
    $gender     = $_POST['gender'];
    $contact    = trim($_POST['contact']);
    $address    = trim($_POST['address']);

    if ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($contact) !== 11 || !is_numeric($contact)) {
        $error = 'Contact number must be exactly 11 digits.';
    } elseif (empty($email)) {
        $error = 'Please enter your Gmail address.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strtolower(substr($email, -10)) !== '@gmail.com') {
        $error = 'Please enter a valid Gmail address (example@gmail.com).';
    } else {
        // Check uniqueness: ensure the email isn't already used as an account
        $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $error = 'This Gmail address is already registered.';
        } else {
            // Check if email already used
            $eStmt = $pdo->prepare('SELECT id FROM residents WHERE email = ?');
            $eStmt->execute([$email]);
            if ($eStmt->fetch()) {
                $error = 'This Gmail address is already registered.';
            } else {
                // Transaction
                try {
                // Determine file upload validity
                $uploadDir = 'uploads/valid_ids/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                if (!isset($_FILES['valid_id']) || $_FILES['valid_id']['error'] !== UPLOAD_ERR_OK) {
                    throw new Exception('Please upload a valid ID image.');
                }

                $file = $_FILES['valid_id'];
                
                // Validate size (5MB max)
                if ($file['size'] > 5 * 1024 * 1024) {
                    throw new Exception('Image size too large. Maximum 5MB allowed.');
                }

                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);

                $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
                if (!in_array($mime, $allowedMimes)) {
                    throw new Exception('Invalid file type. Only JPG, PNG, and WEBP images are allowed.');
                }

                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $uniqueName = uniqid('id_') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $destPath = $uploadDir . $uniqueName;

                if (!move_uploaded_file($file['tmp_name'], $destPath)) {
                    throw new Exception('Failed to save the uploaded image to the server.');
                }

                $pdo->beginTransaction();

                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmtUser = $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, 'Resident')");
                $stmtUser->execute([$username, $hash]);
                $user_id = $pdo->lastInsertId();

                $stmtRes = $pdo->prepare("INSERT INTO residents (user_id, first_name, last_name, date_of_birth, gender, contact_number, email, address, valid_id_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmtRes->execute([$user_id, $first_name, $last_name, $dob, $gender, $contact, $email, $address, $destPath]);

                $pdo->commit();
                $_SESSION['success'] = 'Registration successful! Please wait for admin approval before logging in.';
                header("Location: login.php");
                exit;
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = 'Registration failed: ' . $e->getMessage();
            }
        }
    }
}
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - iReserve Portal</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #0f172a;
            color: #f8fafc;
            margin: 0;
            padding: 2rem 1rem;
            overflow-x: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        
        body::before {
            content: ''; position: fixed; top: -50%; left: -50%; width: 200%; height: 200%;
            background: radial-gradient(circle at 50% 50%, rgba(16, 185, 129, 0.1) 0%, rgba(15, 23, 42, 1) 50%);
            animation: rotateBG 20s linear infinite; z-index: 0;
        }
        @keyframes rotateBG { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

        .blob {
            position: fixed; filter: blur(100px); z-index: 0; opacity: 0.25; border-radius: 50%; pointer-events: none;
        }
        .blob-1 { top: 10%; right: -10%; width: 50vw; height: 50vw; max-width: 500px; max-height: 500px; background: #2563eb; }
        .blob-2 { bottom: -10%; left: -5%; width: 40vw; height: 40vw; max-width: 400px; max-height: 400px; background: #10b981; }

        .auth-container {
            position: relative; z-index: 10; width: 100%; max-width: 700px;
            animation: slideUp 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes slideUp { 0% { transform: translateY(30px); opacity: 0; } 100% { transform: translateY(0); opacity: 1; } }

        .glass-card {
            background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 1.5rem;
            padding: 3rem; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .auth-header { text-align: center; margin-bottom: 2.5rem; }
        .auth-header h2 {
            font-size: 2.25rem; font-weight: 700; margin: 0 0 0.5rem;
            background: linear-gradient(135deg, #ffffff 0%, #94a3b8 100%);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }
        .auth-header p { color: #94a3b8; font-size: 1.05rem; margin: 0; }

        .section-split { color: #38bdf8; font-size: 1.1rem; font-weight: 600; margin: 2rem 0 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 0.5rem; }

        .grid-2 { display: grid; grid-template-columns: 1fr; gap: 1.25rem; }
        @media (min-width: 600px) { .grid-2 { grid-template-columns: repeat(2, 1fr); } }

        .form-group { margin-bottom: 1.25rem; }
        .form-label { display: block; font-size: 0.9rem; font-weight: 500; color: #cbd5e1; margin-bottom: 0.5rem; }
        .form-control {
            width: 100%; box-sizing: border-box; padding: 0.85rem 1.25rem;
            font-family: inherit; font-size: 1rem;
            background: rgba(15, 23, 42, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 0.75rem; color: #f8fafc;
            transition: all 0.3s ease;
        }
        .form-control:focus { outline: none; background: rgba(15, 23, 42, 0.8); border-color: #38bdf8; box-shadow: 0 0 0 4px rgba(56, 189, 248, 0.1); }
        select.form-control option { background: #1e293b; color: white; }

        .btn-glow {
            width: 100%; padding: 1rem 1.5rem; border: none; border-radius: 0.75rem;
            font-family: inherit; font-size: 1.1rem; font-weight: 600;
            background: #10b981; color: white; cursor: pointer;
            box-shadow: 0 10px 20px -5px rgba(16, 185, 129, 0.4);
            transition: all 0.3s ease; display: flex; justify-content: center; align-items: center; gap: 0.5rem;
            margin-top: 1.5rem;
        }
        .btn-glow:hover {
            background: #059669; transform: translateY(-2px);
            box-shadow: 0 15px 25px -5px rgba(16, 185, 129, 0.5);
        }

        .alert-error {
            background: rgba(248, 113, 113, 0.1); border: 1px solid rgba(248, 113, 113, 0.3);
            color: #fca5a5; padding: 1rem 1.25rem; border-radius: 0.75rem;
            font-size: 0.9rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;
        }

        .auth-footer { text-align: center; margin-top: 2rem; font-size: 0.95rem; color: #94a3b8; }
        .auth-footer a { color: #34d399; text-decoration: none; font-weight: 600; transition: color 0.2s; }
        .auth-footer a:hover { color: #6ee7b7; }

        .back-home {
            display: inline-flex; align-items: center; gap: 0.5rem;
            color: #94a3b8; text-decoration: none; font-size: 0.9rem;
            position: fixed; top: 2rem; left: 2rem; z-index: 20; transition: color 0.2s;
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
                <h2>Create Account</h2>
                <p>Register as a resident to access exclusive digital services</p>
            </div>

            <?php if ($error): ?>
                <div class="alert-error"><i data-lucide="alert-circle" style="width:18px;height:18px;"></i> <?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="register.php" enctype="multipart/form-data">
                <div class="section-split" style="margin-top: 0;">Account Setup</div>
                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">Gmail Address</label>
                        <input type="email" name="email" class="form-control" required placeholder="example@gmail.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    </div>
                </div>
                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <div style="position: relative;">
                            <input type="password" name="password" id="reg_password" class="form-control" required style="padding-right: 2.75rem;">
                            <i data-lucide="eye" class="toggle-pwd" data-target="reg_password" style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); cursor: pointer; color: #94a3b8; width: 20px; height: 20px;"></i>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirm Password</label>
                        <div style="position: relative;">
                            <input type="password" name="confirm_password" id="reg_confirm" class="form-control" required style="padding-right: 2.75rem;">
                            <i data-lucide="eye" class="toggle-pwd" data-target="reg_confirm" style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); cursor: pointer; color: #94a3b8; width: 20px; height: 20px;"></i>
                        </div>
                    </div>
                </div>

                <div class="section-split">Personal Details</div>
                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">First Name</label>
                        <input type="text" name="first_name" class="form-control" required value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Last Name</label>
                        <input type="text" name="last_name" class="form-control" required value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>">
                    </div>
                </div>
                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">Date of Birth</label>
                        <input type="date" name="dob" class="form-control" required value="<?php echo htmlspecialchars($_POST['dob'] ?? ''); ?>" style="color-scheme: dark;">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Gender</label>
                        <select name="gender" class="form-control" required>
                            <option value="">Select...</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Contact Number</label>
                    <input type="text" name="contact" class="form-control" maxlength="11" minlength="11" pattern="\d{11}" title="Contact number must be exactly 11 digits" required value="<?php echo htmlspecialchars($_POST['contact'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control" rows="2" required><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                </div>
                
                <div class="section-split">Verification</div>
                <div class="form-group">
                    <label class="form-label">Valid ID Image <span style="color:#ef4444">*</span></label>
                    <input type="file" name="valid_id" class="form-control" accept="image/png, image/jpeg, image/webp" required style="padding: 0.6rem 1.25rem;">
                    <p style="font-size: 0.8rem; color: #94a3b8; margin-top: 0.5rem;">Accepted formats: JPG, PNG, WEBP (Max size: 5MB)</p>
                </div>

                <button type="submit" class="btn-glow">
                    Register Account <i data-lucide="user-plus" style="width:20px;height:20px;"></i>
                </button>
            </form>
            
            <div class="auth-footer">
                Already have an account? <a href="login.php">Log in</a>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();
        
        // Toggle password visibility
        document.querySelectorAll('.toggle-pwd').forEach(icon => {
            icon.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const input = document.getElementById(targetId);
                if (input.type === 'password') {
                    input.type = 'text';
                    this.setAttribute('data-lucide', 'eye-off');
                } else {
                    input.type = 'password';
                    this.setAttribute('data-lucide', 'eye');
                }
                // Re-render the specific icon or all lucide icons to show eye-off
                lucide.createIcons();
            });
        });
    </script>
</body>
</html>
