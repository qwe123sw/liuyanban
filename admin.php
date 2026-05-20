<?php
require 'config.php';
no_logged_in();

if (!is_admin()) {
    die("你没有管理员权限！");
}

$stmt = $pdo->prepare("SELECT * FROM users");
$stmt->execute();
$users = $stmt->fetchAll();


if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    if ($delete_id != $_SESSION['user_id']) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$delete_id]);
        echo "<div class='success'>用户已删除</div>";
    } else {
        echo "<div class='error'>不能删除自己</div>";
    }
}


if (isset($_POST['user_id']) && isset($_POST['role'])) {
    $user_id = (int)$_POST['user_id'];
    $role = $_POST['role'];

    if ($role === 'admin') {

        $pdo->exec("UPDATE users SET is_admin = 0");
        $stmt = $pdo->prepare("UPDATE users SET is_admin = 1 WHERE id = ?");
        $stmt->execute([$user_id]);
        echo "<div class='success'>权限修改成功</div>";
    } else {

        $stmt = $pdo->prepare("UPDATE users SET is_admin = 0 WHERE id = ?");
        $stmt->execute([$user_id]);
        echo "<div class='success'>权限修改成功</div>";
    }
}


$upload_error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['image'])) {
    $file = $_FILES['image'];
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 20 * 1024 * 1024;

    if (!in_array($file['type'], $allowed_types)) {
        $upload_error = "只能上传图片文件（jpg/png/gif）";
    } elseif ($file['size'] > $max_size) {
        $upload_error = "文件大小不能超过20MB";
    } else {
        $upload_dir = 'uploads/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $filename = time() . '_' . $file['name'];
        move_uploaded_file($file['tmp_name'], $upload_dir . $filename);
        echo "<div class='success'>上传成功！文件路径：uploads/$filename</div>";
    }
}




?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>管理员后台</title>
    <style>
        /* 全局样式重置：清除所有元素默认内外边距，统一盒模型和字体 */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Microsoft YaHei", sans-serif;
        }

        /* 🔥 统一渐变背景：粉 + 黄 + 蓝 */
        body {
            background: linear-gradient(135deg, #ffd1d7 0%, #ffedbb 50%, #a8d1ff 100%);
            padding: 30px 20px;
            color: #333;
            line-height: 1.6;
            min-height: 100vh;
            position: relative;
            overflow: auto;
        }

        /* 🔥 统一彩色浮动泡泡 */
        .bubble {
            position: absolute;
            border-radius: 50%;
            backdrop-filter: blur(6px);
            animation: float 12s infinite ease-in-out;
            z-index: 1;
        }
        /* 第1个气泡：粉色、左上区域 */
        .bubble:nth-child(1) {
            width: 160px; height: 160px;
            left: 15%; top: 20%;
            background: rgba(255, 209, 215, 0.35);
            animation-delay: 0s;
        }
        /* 第2个气泡：浅粉色、右上区域 */
        .bubble:nth-child(2) {
            width: 200px; height: 200px;
            right: 18%; top: 15%;
            background: rgba(255, 179, 186, 0.3);
            animation-delay: 2s;
        }
        /* 第3个气泡：蓝色、左下区域 */
        .bubble:nth-child(3) {
            width: 140px; height: 140px;
            left: 22%; bottom: 25%;
            background: rgba(168, 209, 255, 0.35);
            animation-delay: 4s;
        }
        /* 第4个气泡：粉色、右下区域 */
        .bubble:nth-child(4) {
            width: 170px; height: 170px;
            right: 20%; bottom: 20%;
            background: rgba(255, 209, 215, 0.3);
            animation-delay: 6s;
        }
        /* 第5个气泡：深粉色、顶部中间 */
        .bubble:nth-child(5) {
            width: 110px; height: 110px;
            left: 48%; top: 12%;
            background: rgba(255, 149, 159, 0.25);
            animation-delay: 8s;
        }
        /* 气泡悬浮动画：上下缓慢浮动 */
        @keyframes float {
            0% { transform: translateY(0); }
            50% { transform: translateY(-40px); }
            100% { transform: translateY(0); }
        }

        /* 内容容器卡片：白色半透明、居中、阴影、圆角 */
        .container {
            position: relative;
            z-index: 10;
            max-width: 900px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 22px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
        }
        /* 标题通用样式：左边框、间距 */
        h2, h3 {
            margin-bottom: 20px;
            color: #333;
            border-left: 4px solid #a8d1ff;
            padding-left: 12px;
            font-weight: 500;
        }
        h2 {
            font-size: 24px;
        }
        h3 {
            font-size: 19px;
            margin-top: 30px;
        }
        /* 段落：底部间距，保证排版不拥挤 */
        p {
            margin-bottom: 12px;
        }
        /* 链接：蓝色、无下划线、hover出现下划线 */
        a {
            color: #a8d1ff;
            text-decoration: none;
            margin-right: 15px;
            transition: 0.2s;
        }
        a:hover {
            text-decoration: underline;/* 鼠标悬浮显示下划线 */
        }
        /* 分割线：细灰线，美观不突兀 */
        hr {
            border: none;
            height: 1px;
            background: #eee;
            margin: 25px 0;
        }

        /* 表格样式：无边距、圆角、白色背景 */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
        }
        /* 表格单元格内边距、下边框 */
        table th, table td {
            padding: 14px 15px;
            text-align: left;
            border-bottom: 1px solid #f3f4f6;
        }
        /* 表头背景浅灰，加粗 */
        table th {
            background-color: #f9f9f9;
            font-weight: 500;
            color: #444;
        }
        /* 表格行hover高亮 */
        table tr:hover {
            background-color: #fbfbfb;
        }

        /* 按钮：渐变色、圆角、悬浮上浮效果 */
        button {
            padding: 12px 22px;
            background: linear-gradient(135deg, #ffd1d7, #ffedbb, #a8d1ff);
            color: #444;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-size: 14px;
            margin-top: 10px;
            transition: all 0.3s;
            font-weight: 500;
        }
        /* 按钮hover：透明度降低、轻微上浮 */
        button:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        /* 成功提示框：绿色背景、绿色文字 */
        .success {
            background: #f0fff4;
            color: #36ac73;
            padding: 12px 15px;
            border-radius: 10px;
            margin: 15px 0;
            font-weight: 500;
        }
        /* 错误提示框：红色背景、红色文字 */
        .error {
            background: #fef0f0;
            color: #f56c6c;
            padding: 12px 15px;
            border-radius: 10px;
            margin: 15px 0;
            font-weight: 500;
        }

        /* 文件上传表单控件 */
        input[type="file"] {
            margin: 10px 0;
            padding: 8px;
            border-radius: 8px;
            border: 1px solid #eee;
            width: 100%;
        }
        /* 下拉选择框 */
        select {
            padding: 8px 12px;
            border: 1px solid #eee;
            border-radius: 8px;
            outline: none;
            background: #f9f9f9;
            cursor: pointer;
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

<div class="container">
    <h2>管理员后台</h2>
    <p>
        <a href="welcome.php">返回欢迎页</a>
        <a href="logout.php">退出登录</a>
    </p>

    <hr>

    <h3>用户管理</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>用户名</th>
            <th>邮箱</th>
            <th>当前角色</th>
            <th>权限设置</th>
            <th>操作</th>
        </tr>
        <?php foreach ($users as $u): ?>
            <tr>
                <td><?php echo $u['id']; ?></td>
                <td><?php echo $u['username']; ?></td>
                <td><?php echo $u['email']; ?></td>
                <td><?php echo $u['is_admin'] ? '管理员' : '普通用户'; ?></td>
                <td>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                        <select name="role" onchange="this.form.submit()">
                            <option value="user" <?php if(!$u['is_admin']) echo 'selected'; ?>>普通用户</option>
                            <option value="admin" <?php if($u['is_admin']) echo 'selected'; ?>>管理员</option>
                        </select>
                    </form>
                </td>
                <td>
                    <?php if ($u['id'] != $_SESSION['user_id']): ?>
                        <a href="?delete_id=<?php echo $u['id']; ?>" onclick="return confirm('确定删除？')">删除</a>
                    <?php else: ?>
                        自己
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <hr>

    <h3>文件上传（仅图片，最大20MB）</h3>
    <?php if ($upload_error): ?>
        <div class="error"><?php echo $upload_error; ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <div>
            <input type="file" name="image" accept="image/*" required>
        </div>
        <button type="submit">上传文件</button>
    </form>
</div>

</body>
</html>