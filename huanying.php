<?php
session_start();
if(!isset($_SESSION["name"])){
    header("Location:denglu.php");
    exit();
}
require "xss.php";
include "lianjieku.php";

$oldpw = "";
$password = "";
$password2 = "";
$x = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $oldpw = $_POST["oldpw"] ?? "";
    $password = $_POST["password"] ?? "";
    $password2 = $_POST["password2"] ?? "";

    if (empty($oldpw)||empty($password)||empty($password2)) {
        $x = "密码栏不能为空！";
    }elseif ($password != $password2) {
        $x = "两次密码不一致！";
    }elseif (!preg_match("/^[A-Z0-9]{3,10}$/", $password)) {
        $x = "密码格式不正确！";
    }else{
        $name = $_SESSION["name"];
        $sql = "SELECT * FROM yonghu WHERE name = :name";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([":name" => $name]);
        $row = $stmt->fetch();
        if (!$row || !password_verify($oldpw, $row["password"])) {
            $x = "原密码错误，不能修改！";
        }else{
            $newpd = password_hash($password, PASSWORD_DEFAULT);
            $update_sql = "UPDATE yonghu SET password = :pwd WHERE name = :name";
            $update_stmt = $pdo->prepare($update_sql);
            $update_stmt->execute([":name" => $name, ":pwd" => $newpd]);
            $x = "密码修改成功";
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>欢迎页面</title>
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
            color: #000000;
        }

        /* 视频背景样式：固定定位，全屏，置于底层 */
        .video-bg {
            position: fixed;
            left: 0;
            top: 0;
            width: 100vw;
            height: 100vh;
            z-index: -1;
            object-fit: cover;    /* 视频铺满屏幕，保持比例 */
            object-position: center;
            opacity: 1;
        }

        /* 主容器样式：页面居中、半透明、圆角、阴影 */
        .container {
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%); /* 核心：水平垂直居中 */
            background: rgb(155 155 155 / 0.2); /* 半透明背景 */
            border-radius: 18px;
            box-shadow: 0 6px 20px rgba(0, 120, 200, 1); /* 蓝色阴影 */
            padding: 40px;
            width: 100%;
            max-width: 500px;       /* 最大宽度500px */
            text-align: center;     /* 文字居中 */
            border: 2px solid rgb(0 21 255 / 0.2);
        }

        /* 一级标题样式 */
        h1 {
            color: #000000;
            margin-bottom: 10px;
            font-size: 26px;
        }

        /* 二级标题样式 */
        h2 {
            color: #000000;
            margin: 20px 0 15px;
            font-size: 20px;
        }

        /* 通用按钮样式 */
        button {
            background-color: #0099dd;
            color: #fff;
            border: none;
            padding: 10px 18px;
            border-radius: 8px;
            margin: 6px;
            cursor: pointer;       /* 鼠标悬浮变为手型 */
            font-size: 15px;
            transition: all 0.3s ease; /* 过渡动画效果 */
        }

        /* 按钮悬浮效果 */
        button:hover {
            background-color: #0077bb;
            transform: translateY(-2px); /* 按钮轻微上移 */
        }

        /* 修改密码表单样式 */
        form {
            margin-top: 30px;
            text-align: left; /* 表单内容左对齐 */
        }

        /* 表单标签样式 */
        form label {
            display: inline-block;
            margin-bottom: 6px;
            color: #104e7a;
            font-weight: 500;
        }

        /* 输入框样式 */
        form input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #94c8ee;
            border-radius: 8px;
            font-size: 15px;
            outline: none; /* 去掉默认聚焦边框 */
            transition: border 0.3s;
            background: #f7fbff;
        }

        /* 输入框聚焦高亮效果 */
        form input:focus {
            border-color: #0099dd;
            box-shadow: 0 0 6px rgba(0, 153, 221, 0.25);
        }

        /* 修改密码提交按钮样式 */
        .btn {
            width: 100%;
            background: #0099dd;
            margin-top: 10px;
            padding: 12px;
            font-size: 16px;
        }

        /* 提交按钮悬浮效果 */
        .btn:hover {
            background: #0077bb;
        }

        /* 提示信息段落样式 */
        p {
            margin-top: 20px;
            font-size: 15px;
            text-align: center;
        }
    </style>
</head>
<body>

<video class="video-bg" autoplay muted loop playsinline>
    <source src="1.mp4" type="video/mp4">
</video>

<div class="container">
    <h1>欢迎用户：<?php echo $_SESSION['name'] ?></h1>

    <?php if($_SESSION['admin'] === true): ?>
        <h2>管理员功能</h2>
        <button onclick="location.href='admin.php'">管理后台</button>
        <button onclick="location.href='liuyan.php'">留言板</button>
        <button onclick="location.href='denglu.php'">退出登录</button>
    <?php else: ?>
        <h2>普通用户功能</h2>
        <button onclick="location.href='liuyan.php'">留言板</button>
        <button onclick="location.href='denglu.php'">退出登录</button>
    <?php endif; ?>

    <form method="POST" id="resetFrom" onsubmit="return confirm('是否修改')">
        <label>原密码：</label>
        <input type="password" name="oldpw" placeholder="请输入原密码" required><br><br>

        <label>新密码：</label>
        <input type="password" name="password" placeholder="包含大写字母或数字" required><br><br>

        <label>确认密码：</label>
        <input type="password" name="password2" placeholder="再次输入密码" required><br><br>

        <button type="submit" class="btn">提交修改</button>
    </form>

    <?php if($x):?>
        <p style="color:red;"><?php echo $x ?></p>
    <?php endif; ?>
</div>

</body>
</html>