<?php
session_start();
if (!isset($_SESSION['name']) || !$_SESSION['admin']) {
    header("Location: denglu.php");
    exit();
}
require 'xss.php';
include 'lianjieku.php';
$x = '';

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_FILES["file"]["name"];
    $tmp_name = $_FILES["file"]["tmp_name"];
    $info = @getimagesize($tmp_name);
    $allowed = array('image/gif', 'image/png', 'image/jpeg', 'image/jpg');
    $allowed_ext = array('png', 'gif', 'jpeg', 'jpg');
    $file_mime = $_FILES["file"]["type"];
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

    if (in_array($file_mime, $allowed)) {
        $size = $_FILES["file"]["size"];
        $maxsize = 20*1024 * 1024;
        if ( $size < $maxsize) {
            $newname = bin2hex(random_bytes(16)) . "phpxiangmu." . $ext;
            $uploaddir = 'picture/' . $newname;
            if(move_uploaded_file($tmp_name, $uploaddir)) {
                $x = "上传成功！文件：" . $newname;
            } else {
                $x = "文件上传失败";
            }
        }else{
            $x = "文件太大";
        }
    } else {
        $x = "不是规定的文件类型";
    }
}
if (isset($_GET['del_user'])) {
    $pdo->prepare("DELETE FROM yonghu WHERE id=? AND name!='admin'")->execute([$_GET['del_user']]);
    header("Location: admin.php");
    exit();
}

if (isset($_GET['make_admin'])) {
    $pdo->prepare("UPDATE yonghu SET is_admin=1 WHERE id=?")->execute([$_GET['make_admin']]);
    header("Location: admin.php");
    exit();
}
$users = $pdo->query("SELECT id,name,email,is_admin FROM yonghu")->fetchAll();
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>管理员后台</title>
    <style>
        /* 全局样式初始化：清除默认边距，统一盒模型 */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Microsoft YaHei", sans-serif;
        }

        /* 设置页面全屏，隐藏滚动条 */
        html, body {
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        body {
            color: #104e7a;
        }

        /* 全屏视频背景样式 */
        .video-bg {
            position: fixed;      /* 固定定位，不随页面滚动 */
            left: 0;
            top: 0;
            width: 100vw;         /* 宽度占满屏幕 */
            height: 100vh;        /* 高度占满屏幕 */
            z-index: -1;          /* 置于最底层 */
            object-fit: cover;    /* 视频铺满屏幕，保持比例 */
            object-position: center;
            opacity: 1;
        }

        /* 主容器样式：居中、半透明、阴影、圆角 */
        .container {
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%); /* 水平垂直居中 */
            background: rgb(155 155 155 / 0.2); /* 半透明背景 */
            border-radius: 18px;              /* 圆角 */
            box-shadow: 0 6px 20px rgba(0, 120, 200, 1); /* 阴影效果 */
            padding: 40px;
            width: 100%;
            max-width: 800px;       /* 最大宽度800px */
            max-height: 90vh;      /* 最大高度90%屏幕 */
            overflow-y: auto;       /* 内容超出时纵向滚动 */
            text-align: center;     /* 文字居中 */
            border: 2px solid rgb(0 21 255 / 0.2);
        }

        /* 标题样式 */
        h1, h3 {
            color: #000;
            margin: 15px 0;
        }

        /* 上传提示信息样式 */
        .msg {
            color: #0049ff;
            margin: 10px 0;
            font-weight: bold;
        }

        /* 表单间距 */
        form {
            margin: 20px 0;
        }

        /* 文件选择框样式 */
        input[type="file"] {
            padding: 6px;
            margin-right: 10px;
        }

        /* 按钮通用样式 */
        button {
            background-color: #0099dd;
            color: #fff;
            border: none;
            padding: 10px 18px;
            border-radius: 8px;
            margin: 6px;
            cursor: pointer;       /* 鼠标悬浮变为手型 */
            font-size: 15px;
            transition: all 0.3s ease; /* 过渡动画 */
        }

        /* 按钮悬浮效果 */
        button:hover {
            background-color: #0077bb;
            transform: translateY(-2px); /* 轻微上移 */
        }

        /* 用户表格样式 */
        table {
            width: 100%;
            border-collapse: collapse; /* 合并边框 */
            margin: 20px 0;
            background: rgba(255,255,255,0.6); /* 半透明白色 */
            border-radius: 10px;
            overflow: hidden;
        }

        /* 表格单元格样式 */
        th, td {
            padding: 12px;
            border-bottom: 1px solid #94c8ee;
            text-align: center;
        }

        /* 表头样式 */
        th {
            background: #0099dd;
            color: #fff;
        }

        /* 表格行悬浮效果 */
        tr:hover {
            background: rgba(255,255,255,0.8);
        }

        /* 链接样式 */
        a {
            color: #00599c;
            text-decoration: none;
            margin: 0 5px;
        }

        /* 链接悬浮样式 */
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
    <h1>管理员后台 - 欢迎 <?=$_SESSION['name']?></h1>

    <?php if ($x): ?>
        <p class="msg"><?=$x?></p>
    <?php endif; ?>

    <h3>文件上传</h3>
    <form method="post" enctype="multipart/form-data">
        <input type="file" name="file" accept="image/*">
        <button>上传</button>
    </form>

    <h3>用户管理</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>用户名</th>
            <th>邮箱</th>
            <th>角色</th>
            <th>操作</th>
        </tr>
        <?php foreach ($users as $u): ?>
            <tr>
                <td><?=$u['id']?></td>
                <td><?=$u['name']?></td>
                <td><?=$u['email']?></td>
                <td><?=$u['is_admin'] ? '管理员' : '普通用户'?></td>
                <td>
                    <?php if ($u['name'] === 'admin'): ?>
                        无法操作
                    <?php else: ?>
                        <a href="?del_user=<?=$u['id']?>" onclick="return confirm('确定删除？')">删除</a>
                        <?php if (!$u['is_admin']): ?>
                            | <a href="?make_admin=<?=$u['id']?>">设为管理员</a>
                        <?php endif; ?>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <button onclick="location.href='denglu.php'">退出登录</button>
</div>

</body>
</html>