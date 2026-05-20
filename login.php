<?php
require 'config.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['is_admin'] = $user['is_admin'];
        setcookie("user_id", $user['id'], time() + 7*24*3600, "/");
        setcookie("username", $user['username'], time() + 7*24*3600, "/");
        header("Location: welcome.php");
        exit;
    } else {
        $error = "账号或密码错误";
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用户登录</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Microsoft YaHei", "PingFang SC", sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #ffd1d7 0%, #ffedbb 50%, #a8d1ff 100%);
            overflow: hidden;
            position: relative;
        }

        /* 动态渐变背景 */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, 
                rgba(255, 209, 215, 0.3), 
                rgba(255, 237, 187, 0.3), 
                rgba(168, 209, 255, 0.3));
            animation: gradientShift 15s ease infinite;
            z-index: 0;
        }

        @keyframes gradientShift {
            0%, 100% { opacity: 0.5; }
            50% { opacity: 0.8; }
        }

        /* 浮动泡泡 */
        .bubble {
            position: absolute;
            border-radius: 50%;
            backdrop-filter: blur(8px);
            animation: float 15s infinite ease-in-out;
            z-index: 1;
            box-shadow: inset 0 0 20px rgba(255, 255, 255, 0.3);
        }

        .bubble:nth-child(1) {
            width: 180px;
            height: 180px;
            left: 10%;
            top: 15%;
            background: rgba(255, 255, 255, 0.15);
            animation-delay: 0s;
        }

        .bubble:nth-child(2) {
            width: 220px;
            height: 220px;
            right: 12%;
            top: 10%;
            background: rgba(255, 255, 255, 0.12);
            animation-delay: 2s;
        }

        .bubble:nth-child(3) {
            width: 150px;
            height: 150px;
            left: 20%;
            bottom: 20%;
            background: rgba(255, 255, 255, 0.18);
            animation-delay: 4s;
        }

        .bubble:nth-child(4) {
            width: 200px;
            height: 200px;
            right: 15%;
            bottom: 15%;
            background: rgba(255, 255, 255, 0.14);
            animation-delay: 6s;
        }

        .bubble:nth-child(5) {
            width: 130px;
            height: 130px;
            left: 50%;
            top: 8%;
            background: rgba(255, 255, 255, 0.2);
            animation-delay: 8s;
        }

        .bubble:nth-child(6) {
            width: 100px;
            height: 100px;
            left: 35%;
            bottom: 30%;
            background: rgba(255, 255, 255, 0.16);
            animation-delay: 3s;
        }

        .bubble:nth-child(7) {
            width: 160px;
            height: 160px;
            right: 30%;
            top: 40%;
            background: rgba(255, 255, 255, 0.13);
            animation-delay: 5s;
        }

        @keyframes float {
            0%, 100% { 
                transform: translateY(0) rotate(0deg); 
            }
            33% { 
                transform: translateY(-30px) rotate(5deg); 
            }
            66% { 
                transform: translateY(20px) rotate(-5deg); 
            }
        }

        /* 主容器 */
        .container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 440px;
            background: rgba(255, 255, 255, 0.98);
            padding: 50px 45px;
            border-radius: 24px;
            box-shadow: 
                0 25px 50px rgba(0, 0, 0, 0.15),
                0 0 0 1px rgba(255, 255, 255, 0.5);
            animation: slideUp 0.6s ease-out;
            backdrop-filter: blur(10px);
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Logo/图标区域 */
        .logo-area {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
            margin-bottom: 15px;
        }

        .logo-icon i {
            font-size: 36px;
            color: #fff;
        }

        h2 {
            text-align: center;
            margin-bottom: 10px;
            color: #2d3748;
            font-size: 28px;
            font-weight: 600;
            letter-spacing: 1px;
        }

        .subtitle {
            text-align: center;
            color: #718096;
            font-size: 14px;
            margin-bottom: 35px;
        }

        /* 表单样式 */
        .form-item {
            margin-bottom: 25px;
            position: relative;
        }

        .input-group {
            position: relative;
        }

        .input-group i {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #a0aec0;
            font-size: 18px;
            transition: all 0.3s ease;
            z-index: 2;
        }

        label {
            display: block;
            margin-bottom: 10px;
            color: #4a5568;
            font-size: 14px;
            font-weight: 500;
        }

        input {
            width: 100%;
            padding: 16px 16px 16px 50px;
            background: #f7fafc;
            border: 2px solid #e2e8f0;
            border-radius: 14px;
            font-size: 15px;
            color: #2d3748;
            outline: none;
            transition: all 0.3s ease;
        }

        input::placeholder {
            color: #cbd5e0;
        }

        input:focus {
            border-color: #667eea;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }

        input:focus + i,
        .input-group:has(input:focus) i {
            color: #667eea;
        }

        /* 记住我 */
        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            font-size: 14px;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            color: #718096;
        }

        .remember-me input[type="checkbox"] {
            width: 18px;
            height: 18px;
            padding: 0;
            accent-color: #667eea;
            cursor: pointer;
        }

        /* 登录按钮 */
        button {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            border: none;
            border-radius: 14px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            letter-spacing: 1px;
        }

        button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.5s ease;
        }

        button:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
        }

        button:hover::before {
            left: 100%;
        }

        button:active {
            transform: translateY(-1px);
        }

        /* 错误提示 */
        .error {
            background: linear-gradient(135deg, #fff5f5 0%, #fed7d7 100%);
            color: #c53030;
            padding: 14px 18px;
            border-radius: 12px;
            text-align: center;
            margin-bottom: 25px;
            font-size: 14px;
            border-left: 4px solid #fc8181;
            animation: shake 0.5s ease;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        /* 分割线 */
        .divider {
            display: flex;
            align-items: center;
            margin: 30px 0;
            color: #cbd5e0;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: linear-gradient(90deg, transparent, #e2e8f0, transparent);
        }

        .divider span {
            padding: 0 15px;
            font-size: 13px;
        }

        /* 链接区域 */
        .links {
            text-align: center;
        }

        .links a {
            color: #667eea;
            text-decoration: none;
            margin: 0 15px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
        }

        .links a::after {
            content: '';
            position: absolute;
            bottom: -3px;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, #667eea, #764ba2);
            transition: width 0.3s ease;
        }

        .links a:hover {
            color: #764ba2;
        }

        .links a:hover::after {
            width: 100%;
        }

        /* 底部文字 */
        .footer-text {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            color: #a0aec0;
            font-size: 13px;
        }

        /* 响应式设计 */
        @media (max-width: 480px) {
            .container {
                margin: 20px;
                padding: 35px 25px;
            }
            
            h2 {
                font-size: 24px;
            }
            
            .logo-icon {
                width: 65px;
                height: 65px;
            }
            
            .logo-icon i {
                font-size: 28px;
            }
        }
    </style>
</head>

<body>
<!-- 浮动泡泡背景 -->
<div class="bubble"></div>
<div class="bubble"></div>
<div class="bubble"></div>
<div class="bubble"></div>
<div class="bubble"></div>
<div class="bubble"></div>
<div class="bubble"></div>

<div class="container">
    <!-- Logo区域 -->
    <div class="logo-area">
        <div class="logo-icon">
            <i class="fas fa-user-shield"></i>
        </div>
    </div>
    
    <h2>欢迎回来</h2>
    <p class="subtitle">请登录您的账户</p>

    <?php if ($error): ?>
        <div class="error">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form method="post" autocomplete="off">
        <div class="form-item">
            <label>用户名/邮箱</label>
            <div class="input-group">
                <input type="text" name="username" placeholder="请输入用户名或邮箱" required>
                <i class="fas fa-user"></i>
            </div>
        </div>
        
        <div class="form-item">
            <label>密码</label>
            <div class="input-group">
                <input type="password" name="password" placeholder="请输入密码" required>
                <i class="fas fa-lock"></i>
            </div>
        </div>
        
        <div class="remember-forgot">
            <label class="remember-me">
                <input type="checkbox" name="remember">
                <span>记住我</span>
            </label>
        </div>
        
        <button type="submit">
            <i class="fas fa-sign-in-alt"></i> 登录
        </button>
    </form>

    <div class="divider">
        <span>或</span>
    </div>

    <div class="links">
        <a href="register.php"><i class="fas fa-user-plus"></i> 注册账号</a>
        <a href="reset_password.php"><i class="fas fa-key"></i> 重置密码</a>
    </div>
    
    <div class="footer-text">
        登录即表示您同意我们的服务条款
    </div>
</div>
</body>
</html>