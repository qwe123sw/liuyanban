<?php
require 'config.php';

$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $new_password = trim($_POST['new_password']);


    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND email = ?");
    $stmt->execute([$username, $email]);
    $user = $stmt->fetch();

    if (!$user) {
        $error = "用户名或邮箱不匹配";
    } elseif (strlen($new_password) < 6) {
        $error = "新密码长度至少6位";
    } else {

        $hashed_pwd = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hashed_pwd, $user['id']]);
        $success = "密码重置成功！<a href='login.php'>去登录</a>";
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>重置密码</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Microsoft YaHei", sans-serif;
        }

        /* 统一渐变背景：粉 + 黄 + 蓝 */
        body {
            background: linear-gradient(135deg, #ffd1d7 0%, #ffedbb 50%, #a8d1ff 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            overflow: hidden;
            position: relative;
            padding: 20px;
        }

        /* 统一彩色浮动泡泡 */
        .bubble {
            position: absolute;
            border-radius: 50%;
            backdrop-filter: blur(6px);
            animation: float 12s infinite ease-in-out;
            z-index: 1;
        }

        /* 淡粉色 */
        .bubble:nth-child(1) {
            width: 160px; height: 160px;
            left: 15%; top: 20%;
            background: rgba(255, 209, 215, 0.35);
            animation-delay: 0s;
        }
        /* 深粉色 */
        .bubble:nth-child(2) {
            width: 200px; height: 200px;
            right: 18%; top: 15%;
            background: rgba(255, 179, 186, 0.3);
            animation-delay: 2s;
        }
        /* 淡蓝色 */
        .bubble:nth-child(3) {
            width: 140px; height: 140px;
            left: 22%; bottom: 25%;
            background: rgba(168, 209, 255, 0.35);
            animation-delay: 4s;
        }
        /* 淡粉色 */
        .bubble:nth-child(4) {
            width: 170px; height: 170px;
            right: 20%; bottom: 20%;
            background: rgba(255, 209, 215, 0.3);
            animation-delay: 6s;
        }
        /* 深粉色小泡泡 */
        .bubble:nth-child(5) {
            width: 110px; height: 110px;
            left: 48%; top: 12%;
            background: rgba(255, 149, 159, 0.25);
            animation-delay: 8s;
        }

        @keyframes float {
            0% { transform: translateY(0); }
            50% { transform: translateY(-40px); }
            100% { transform: translateY(0); }
        }

        /* 统一白色卡片 */
        .container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 460px;
            background: rgba(255, 255, 255, 0.95);
            padding: 50px 40px;
            border-radius: 22px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
        }

        h2 {
            text-align: center;
            margin-bottom: 35px;
            color: #333;
            font-size: 28px;
            font-weight: 500;
        }

        .form-item {
            margin-bottom: 24px;
        }

        label {
            display: block;
            margin-bottom: 10px;
            color: #666;
            font-size: 15px;
        }

        input {
            width: 100%;
            padding: 14px 16px;
            background: #f9f9f9;
            border: 1px solid #eee;
            border-radius: 12px;
            font-size: 15px;
            color: #333;
            outline: none;
            transition: 0.3s;
        }

        input:focus {
            border-color: #a8d1ff;
            background: #fff;
        }

        /* 统一渐变按钮 */
        button {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #ffd1d7, #ffedbb, #a8d1ff);
            color: #444;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 10px;
        }

        button:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        .error {
            background: #fef0f0;
            color: #f56c6c;
            padding: 12px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 20px;
        }

        .success {
            background: #f0fff4;
            color: #36ac73;
            padding: 12px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 20px;
        }

        .links {
            margin-top: 30px;
            text-align: center;
        }

        .links a {
            color: #a8d1ff;
            text-decoration: none;
            font-size: 14px;
        }

        .links a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
<!-- 彩色浮动泡泡 -->
<div class="bubble"></div>
<div class="bubble"></div>
<div class="bubble"></div>
<div class="bubble"></div>
<div class="bubble"></div>

<div class="container">
    <h2>重置密码</h2>

    <?php if ($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="form-item">
            <label>用户名</label>
            <input type="text" name="username" required>
        </div>
        <div class="form-item">
            <label>注册邮箱</label>
            <input type="email" name="email" required>
        </div>
        <div class="form-item">
            <label>新密码</label>
            <input type="password" name="new_password" required>
        </div>
        <button type="submit">重置密码</button>
    </form>

    <div class="links">
        <a href="login.php">返回登录</a>
    </div>
</div>

</body>
</html>