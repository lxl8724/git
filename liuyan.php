<?php
session_start();
if (!isset($_SESSION['name'])) {
    header("Location: denglu.php");
    exit();
}
require "xss.php";
include "LYku.php";

$name = "";
$content = "";
$x = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = isset($_POST["name"]) ? trim($_POST["name"]) : '';
    $content = isset($_POST["content"]) ? trim($_POST["content"]) : '';
    if (empty($name)) {
        $x = "用户不能为空";
    }
    elseif (empty($content)) {
        $x = "内容不能为空";
    }
    else {
        $sql = "INSERT INTO liuyan (name, content) VALUES (:name, :content)";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([':name' => $name, ':content' => $content]);
        if ($result) {
            $x = "留言发布成功！ID：" . $pdo->lastInsertId();
        } else {
            $error = $stmt->errorInfo();
            $x = "发布失败：" . $error[2];
        }
    }
}

if (isset($_GET['id']) && isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
    $id = $_GET['id'];
    $sql = "DELETE FROM liuyan WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);
    header("Location: liuyan.php");
    exit();
}
$sql = "SELECT * FROM liuyan ORDER BY id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
?>

<!doctype html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>留言板</title>
    <style>
        /* 全局样式初始化：清除默认边距，统一盒模型 */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Microsoft YaHei", sans-serif;
        }

        /* 页面全屏，隐藏滚动条 */
        html, body {
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        body {
            color: #104e7a;
        }

        /* 视频背景：固定全屏，置于最底层 */
        .video-bg {
            position: fixed;
            left: 0;
            top: 0;
            width: 100vw;
            height: 100vh;
            z-index: -1;
            object-fit: cover;
            object-position: center;
            opacity: 1;
        }

        /* 主容器：页面居中、半透明、圆角、阴影 */
        .container {
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%); /* 居中核心 */
            background: rgb(155 155 155 / 0.2); /* 半透明背景 */
            border-radius: 18px;
            box-shadow: 0 6px 20px rgba(0, 120, 200, 1);
            padding: 40px;
            width: 100%;
            max-width: 650px;
            max-height: 90vh;
            overflow-y: auto; /* 内容过多时自动滚动 */
            text-align: center;
            border: 2px solid rgb(0 21 255 / 0.2);
        }

        /* 标题样式 */
        h2, h3 {
            color: #000;
            margin: 15px 0;
        }

        /* 留言表单：左对齐 */
        form {
            text-align: left;
            margin-bottom: 25px;
        }

        /* 表单标签 */
        form label {
            display: inline-block;
            margin-bottom: 6px;
            color: #000;
            font-weight: 500;
        }

        /* 输入框样式 */
        form input[type="text"] {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #94c8ee;
            border-radius: 8px;
            font-size: 15px;
            outline: none;
            transition: border 0.3s;
            background: #f7fbff;
        }

        /* 输入框聚焦高亮 */
        form input:focus {
            border-color: #0099dd;
            box-shadow: 0 0 6px rgba(0, 153, 221, 0.25);
        }

        /* 按钮通用样式（包括a标签模拟按钮） */
        button, a.button {
            display: inline-block;
            background-color: #0099dd;
            color: #fff !important;
            border: none;
            padding: 10px 18px;
            border-radius: 8px;
            margin: 6px;
            cursor: pointer;
            font-size: 15px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        /* 按钮悬浮效果 */
        button:hover, a.button:hover {
            background-color: #0077bb;
            transform: translateY(-2px);
        }

        /* 提示信息样式 */
        .msg {
            color: #0049ff;
            margin: 10px 0;
            text-align: center;
        }

        /* 单条留言卡片样式 */
        .message {
            background: rgba(255,255,255,0.5);
            padding: 15px;
            border-radius: 10px;
            margin: 10px 0;
            text-align: left;
            line-height: 1.5;
        }

        /* 留言内容间距 */
        .message p {
            margin: 4px 0;
        }

        /* 分割线 */
        hr {
            border: none;
            height: 1px;
            background: #94c8ee;
            margin: 10px 0;
        }

        /* 链接样式 */
        a {
            color: #00599c;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<video class="video-bg" autoplay muted loop playsinline>
    <source src="1.mp4" type="video/mp4">
</video>

<div class="container">
    <h2>留言发布</h2>
    <form method="post" action="">
        <label for="name">用户：</label>
        <input type="text" name="name" id="name" placeholder="请输入用户名"><br><br>

        <label for="content">内容：</label>
        <input type="text" name="content" id="content" placeholder="请输入内容"><br><br>

        <button type="submit">提交留言</button>
        <a href="huanying.php" class="button">返回上一页面</a>
    </form>

    <?php if ($x): ?>
        <p class="msg"><?php echo $x; ?></p>
    <?php endif; ?>

    <h3>留言列表</h3>
    <?php if ($stmt->rowCount() === 0): ?>
        <p>暂无留言</p>
    <?php else: ?>
        <?php while ($row = $stmt->fetch()): ?>
            <div class="message">
                <p>ID：<?php echo $row["id"]; ?></p>
                <p>用户：<?php echo $row["name"]; ?></p>
                <p>内容：<?php echo $row["content"]; ?></p>
                <p>时间：<?php echo $row["time"]; ?></p>

                <?php if (isset($_SESSION['admin']) && $_SESSION['admin'] === true): ?>
                    <a href="?id=<?php echo $row['id']; ?>"
                       onclick="return confirm('确定删除这条留言吗？')">删除留言</a>
                <?php endif; ?>
            </div>
            <hr>
        <?php endwhile; ?>
    <?php endif; ?>
</div>

</body>
</html>