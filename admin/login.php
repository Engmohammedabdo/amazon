<?php
/**
 * تسجيل الدخول - لوحة التحكم
 */

session_start();

// إذا كان مسجل دخول بالفعل، إعادة التوجيه للوحة التحكم
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: /admin/index.php');
    exit();
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrfToken = $_POST['csrf_token'] ?? '';

    // التحقق من CSRF Token
    if (!verifyCSRFToken($csrfToken)) {
        $error = 'خطأ في التحقق من الأمان. الرجاء المحاولة مرة أخرى.';
    } elseif (empty($username) || empty($password)) {
        $error = 'الرجاء إدخال اسم المستخدم وكلمة المرور';
    } else {
        try {
            $db = getDB();
            $stmt = $db->prepare("SELECT * FROM admin_users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                // تسجيل الدخول ناجح
                session_regenerate_id(true);
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_username'] = $user['username'];

                header('Location: /admin/index.php');
                exit();
            } else {
                $error = 'اسم المستخدم أو كلمة المرور غير صحيحة';
            }
        } catch (Exception $e) {
            error_log("Login Error: " . $e->getMessage());
            $error = 'حدث خطأ. الرجاء المحاولة لاحقاً';
        }
    }
}

$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - لوحة التحكم</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Cairo', sans-serif;
            background: linear-gradient(135deg, #FF6B35 0%, #F7931E 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 450px;
            width: 100%;
            overflow: hidden;
        }
        .login-header {
            background: #1A1A1A;
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .login-header h1 {
            font-size: 2rem;
            color: #FF6B35;
            margin-bottom: 0.5rem;
        }
        .login-header p {
            color: #ccc;
            font-size: 0.9rem;
        }
        .login-form {
            padding: 2rem;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #1A1A1A;
            font-weight: 600;
        }
        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            font-family: 'Cairo', sans-serif;
            transition: border-color 0.3s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #FF6B35;
        }
        .error-message {
            background: #fee;
            color: #c33;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            border: 2px solid #fcc;
        }
        .btn-login {
            width: 100%;
            padding: 1rem;
            background: #FF6B35;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            font-family: 'Cairo', sans-serif;
        }
        .btn-login:hover {
            background: #E55A2B;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 53, 0.3);
        }
        .back-link {
            text-align: center;
            margin-top: 1rem;
        }
        .back-link a {
            color: #666;
            text-decoration: none;
            transition: color 0.3s;
        }
        .back-link a:hover {
            color: #FF6B35;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>🔐 PYRASTORE</h1>
            <p>لوحة التحكم - تسجيل الدخول</p>
        </div>

        <div class="login-form">
            <?php if ($error): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">

                <div class="form-group">
                    <label>اسم المستخدم</label>
                    <input type="text" name="username" required autofocus autocomplete="username">
                </div>

                <div class="form-group">
                    <label>كلمة المرور</label>
                    <input type="password" name="password" required autocomplete="current-password">
                </div>

                <button type="submit" class="btn-login">تسجيل الدخول</button>
            </form>

            <div class="back-link">
                <a href="/">← العودة للموقع</a>
            </div>
        </div>
    </div>
</body>
</html>
