<?php
require 'xss.php';
include 'lianjieku.php';

$name = "";
$email = "";
$password = "";
$password2 = "";
$x = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST["name"]) ? trim($_POST["name"]) : "";
    $password = isset($_POST["password"]) ? trim($_POST["password"]) : "";
    $password2 = isset($_POST["password2"]) ? trim($_POST["password2"]) : "";
    $email = isset($_POST["email"]) ? trim($_POST["email"]) : "";

    if (empty($name)||empty($email)) {
        $x = '用户名和邮箱是空的！';
    } elseif (empty($password)||empty($password2)) {
        $x = '密码是空的！';
    } elseif ($password !== $password2) {
        $x = "两次输入的密码不一致";
    } elseif (!preg_match("/^[a-z0-9]{3,10}$/", $password)) {
        $x = "密码格式错误";
    } elseif ($email === false) {
        $x = "邮箱格式有毛病";
    } else {

        $sql = "SELECT * FROM yonghu WHERE name = :name AND email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
                ':name' => $name,
                ':email' => $email
        ]);

        if ($stmt->rowCount() > 0) {
            $hash_password = password_hash($password, PASSWORD_DEFAULT);

            $sql = "UPDATE yonghu SET password = :password WHERE name = :name AND email = :email";
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([
                    ':password' => $hash_password,
                    ':name' => $name,
                    ':email' => $email
            ]);

            if ($result) {
                $x = "密码重置成功！";
            } else {
                $x = "修改失败";
            }
        } else {
            $x = "用户名与邮箱不匹配，重置失败";
        }
    }
}
?>
<!doctype html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>重置页面</title>
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
        /* 视频背景：固定定位，全屏，置于底层 */
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
        /* 注册框容器：居中、半透明、圆角、阴影 */
        .container {
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%); /* 核心：水平垂直居中 */
            background: rgb(155 155 155 / 0.2); /* 半透明背景 */
            border-radius: 18px;
            box-shadow: 0 6px 20px rgba(0, 120, 200, 1);
            padding: 40px;
            width: 100%;
            max-width: 500px;
            text-align: center;
            border: 2px solid rgb(0 21 255 / 0.2);
        }
        /* 标题样式 */
        h1 {
            color: #000;
            margin-bottom: 25px;
            font-size: 26px;
        }
        /* 表单左对齐 */
        form {
            text-align: left;
        }
        /* 表单标签 */
        form label {
            display: inline-block;
            margin-bottom: 6px;
            color: #000;
            font-weight: 500;
        }
        /* 输入框样式 */
        form input[type="text"],
        form input[type="password"] {
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
        /* 注册按钮 */
        button {
            width: 100%;
            background-color: #0099dd;
            color: #fff;
            border: none;
            padding: 12px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        /* 按钮悬浮效果 */
        button:hover {
            background-color: #0077bb;
            transform: translateY(-2px);
        }
        /* 链接样式 */
        a {
            color: #00599c;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        /* 错误/成功提示文字 */
        p {
            margin-top: 15px;
            font-size: 15px;
            text-align: center;
            color: red;
        }
    </style>
</head>
<body>

<video class="video-bg" autoplay muted loop playsinline>
    <source src="1.mp4" type="video/mp4">
</video>

<div class="container">
    <h2 class="title">重置密码</h2>

    <form method="POST" id="resetForm" onsubmit="return confirm('是否确认修改')">
        <span>用户：</span>
        <input type="text" id="regUser" name="name" placeholder="请输入用户名"><br><br>

        <span>新密码：</span>
        <input type="password" id="regPwd" name="password" placeholder="包含小写字母或数字"><br><br>

        <span>确认密码：</span>
        <input type="password" id="regPwd2" name="password2" placeholder="再次输入密码"><br><br>

        <span>邮箱：</span>
        <input type="text" name="email" placeholder="输入邮箱"><br><br>

        <button type="submit" class="btn">重置密码</button><br><br>
        <div style="text-align: center; color: #000; margin-top: 10px;">
            密码重置成功？<a href="denglu.php">返回登录</a>
        </div>
    </form>

    <?php if ($x): ?>
        <p><?php echo $x; ?></p>
    <?php endif; ?>
</div>

</body>
</html>