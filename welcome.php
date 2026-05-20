<?php
require 'config.php';
no_logged_in();


if (isset($_GET['del_id'])) {
    $delId = $_GET['del_id'];
    $stmtCheck = $pdo->prepare("SELECT user_id FROM messages WHERE id = ?");
    $stmtCheck->execute([$delId]);
    $msgInfo = $stmtCheck->fetch();

    if ($msgInfo && (is_admin() || $msgInfo['user_id'] == $_SESSION['user_id'])) {
        $stmtDel = $pdo->prepare("DELETE FROM messages WHERE id = ?");
        $stmtDel->execute([$delId]);
    }
    header("Location: welcome.php");
    exit;
}


$error = '';
$success = '';
$msg_success = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_pwd'])) {
    $old_pwd = trim($_POST['old_pwd']);
    $new_pwd = trim($_POST['new_pwd']);

    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (password_verify($old_pwd, $user['password'])) {
        if (strlen($new_pwd) >= 6) {
            $hashed_pwd = password_hash($new_pwd, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed_pwd, $_SESSION['user_id']]);
            $success = "密码修改成功 ✅";
        } else {
            $error = "新密码长度至少6位";
        }
    } else {
        $error = "原密码错误";
    }
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message'])) {
    $content = trim($_POST['content']);
    if (!empty($content)) {
        $stmt = $pdo->prepare("INSERT INTO messages (user_id, content) VALUES (?, ?)");
        $stmt->execute([$_SESSION['user_id'], $content]);
        $msg_success = "留言发布成功 ✅";
    }
}


$stmt = $pdo->prepare("SELECT m.*, u.username FROM messages m JOIN users u ON m.user_id = u.id ORDER BY m.created_at DESC");
$stmt->execute();
$messages = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>用户中心</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Microsoft YaHei", sans-serif;
        }

        /* 🔥 统一渐变背景：粉 + 黄 + 蓝 */
        body {
            background: linear-gradient(135deg, #ffd1d7 0%, #ffedbb 50%, #a8d1ff 100%);
            min-height: 100vh;
            padding: 30px 16px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            overflow: auto;
            position: relative;
        }

        /* 🔥 统一彩色浮动泡泡 */
        .bubble {
            position: absolute;
            border-radius: 50%;
            backdrop-filter: blur(6px);
            animation: float 12s infinite ease-in-out;
            z-index: 1;
        }

        .bubble:nth-child(1) {
            width: 160px; height: 160px;
            left: 15%; top: 20%;
            background: rgba(255, 209, 215, 0.35);
            animation-delay: 0s;
        }
        .bubble:nth-child(2) {
            width: 200px; height: 200px;
            right: 18%; top: 15%;
            background: rgba(255, 179, 186, 0.3);
            animation-delay: 2s;
        }
        .bubble:nth-child(3) {
            width: 140px; height: 140px;
            left: 22%; bottom: 25%;
            background: rgba(168, 209, 255, 0.35);
            animation-delay: 4s;
        }
        .bubble:nth-child(4) {
            width: 170px; height: 170px;
            right: 20%; bottom: 20%;
            background: rgba(255, 209, 215, 0.3);
            animation-delay: 6s;
        }
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

        .card {
            position: relative;
            z-index: 10;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 22px;
            max-width: 720px;
            width: 100%;
            padding: 50px 40px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
            animation: fadeIn 0.4s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .title {
            font-size: 28px;
            color: #333;
            text-align: center;
            margin-bottom: 12px;
            font-weight: 500;
        }

        .user-info {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
            font-size: 15px;
        }

        .links {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 35px;
            flex-wrap: wrap;
        }

        .links a {
            padding: 10px 18px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.25s ease;
        }

        .links .admin {
            background: #fef3c7;
            color: #d97706;
        }

        .links .logout {
            background: #fef2f2;
            color: #dc2626;
        }

        .links a:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        .section {
            margin-bottom: 40px;
        }

        .section-title {
            font-size: 18px;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #666;
            font-weight: 500;
            font-size: 14px;
        }

        .form-control {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid #eee;
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.3s;
            outline: none;
            background: #f9f9f9;
        }

        .form-control:focus {
            border-color: #a8d1ff;
            background: #fff;
        }

        textarea.form-control {
            min-height: 130px;
            resize: vertical;
        }

        /* 🔥 统一粉黄蓝渐变按钮 */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 15px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
            background: linear-gradient(135deg, #ffd1d7, #ffedbb, #a8d1ff);
            color: #444;
        }

        .btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        .alert {
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
            font-weight: 500;
        }

        .alert-success {
            background: #f0fff4;
            color: #36ac73;
        }

        .alert-error {
            background: #fef0f0;
            color: #f56c6c;
        }

        .message-item {
            background: #f9f9f9;
            border-radius: 14px;
            padding: 20px;
            margin-bottom: 14px;
            border: 1px solid #eee;
            transition: all 0.2s;
        }

        .message-item:hover {
            background: #f3f4f6;
        }

        .message-author {
            font-weight: 600;
            color: #a8d1ff;
            margin-bottom: 8px;
            font-size: 15px;
        }

        .message-content {
            color: #333;
            line-height: 1.6;
            margin-bottom: 10px;
            font-size: 15px;
        }

        .message-time {
            font-size: 12px;
            color: #999;
        }

        .message-delete {
            font-size: 13px;
            color: #f56c6c;
            text-decoration: none;
            margin-left: 12px;
            font-weight: 500;
        }

        .message-delete:hover {
            text-decoration: underline;
        }

        .empty {
            color: #999;
            text-align: center;
            padding: 30px 0;
            font-size: 14px;
        }
    </style>
</head>

<body>
<!-- 统一浮动泡泡 -->
<div class="bubble"></div>
<div class="bubble"></div>
<div class="bubble"></div>
<div class="bubble"></div>
<div class="bubble"></div>

<div class="card">
    <h1 class="title">👋 用户中心</h1>
    <div class="user-info">欢迎你，<?php echo $_SESSION['username'] ?></div>

    <div class="links">
        <?php if (is_admin()): ?>
            <a href="admin.php" class="admin">🔐 管理员后台</a>
        <?php endif; ?>
        <a href="logout.php" class="logout">🚪 退出登录</a>
    </div>


    <div class="section">
        <h3 class="section-title">🔑 修改密码</h3>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="form-group">
                <label>当前密码</label>
                <input type="password" name="old_pwd" class="form-control" required>
            </div>
            <div class="form-group">
                <label>新密码（至少6位）</label>
                <input type="password" name="new_pwd" class="form-control" required>
            </div>
            <button type="submit" name="change_pwd" class="btn">确认修改</button>
        </form>
    </div>


    <div class="section">
        <h3 class="section-title">💬 留言板</h3>

        <?php if ($msg_success): ?>
            <div class="alert alert-success"><?php echo $msg_success ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="form-group">
                <label>写下你的留言</label>
                <textarea name="content" class="form-control" placeholder="分享你的想法..." required></textarea>
            </div>
            <button type="submit" name="message" class="btn">发布留言</button>
        </form>
    </div>


    <div class="section">
        <h3 class="section-title">📜 最新留言</h3>

        <?php if (empty($messages)): ?>
            <div class="empty">暂无留言，快来发表第一条吧～</div>
        <?php endif; ?>

        <?php foreach ($messages as $msg): ?>
            <div class="message-item">
                <div class="message-author"><?php echo $msg['username'] ?></div>
                <div class="message-content"><?php echo htmlspecialchars($msg['content']) ?></div>
                <div class="message-time">
                    <?php echo $msg['created_at'] ?>
                    <?php if (is_admin() || $msg['user_id'] == $_SESSION['user_id']): ?>
                        <a href="?del_id=<?php echo $msg['id'] ?>" class="message-delete" onclick="return confirm('确定删除这条留言？')">删除</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

</body>
</html>