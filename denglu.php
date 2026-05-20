<?php
session_start();
require "xss.php";
include 'lianjieku.php';

$name = "";
$password = "";
$x = "";

if (isset($_COOKIE['remember_name'])) {
    $name = $_COOKIE['remember_name'];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = isset($_POST["name"]) ? trim($_POST["name"]) : "";
    $password = isset($_POST["password"]) ? trim($_POST["password"]) : "";
    $remember = isset($_POST['remember']) ? $_POST['remember'] : "";

    if(empty($name) || empty($password)){
        $x = "用户名或密码不能为空！";
    }else{
        $sql = "SELECT * FROM yonghu WHERE name = :name ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':name' => $name]);
        if($stmt->rowCount() > 0){
            $row = $stmt->fetch();

            if (password_verify($password, $row['password'])) {
                if($remember == 1){
                    setcookie("remember_name", $name, time() + 86400 * 7);
                }else{
                    setcookie("remember_name", "", time() - 3600);
                }

                $_SESSION['name'] = $name;
                $_SESSION['admin'] = ($name === 'admin');

                header('location:huanying.php');
                exit();
            }else{
                $x = "密码错误";
            }
        }else{
            $x = "用户不存在，请先注册！";
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>登录页面</title>
    <style>
        /* 全局样式初始化：清除默认内外边距，统一盒模型 */
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
        /* 视频背景样式：全屏固定定位，置于底层 */
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
        /* 登录框容器：页面正中央、半透明、圆角、阴影 */
        .container {
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%); /* 居中核心代码 */
            background: rgb(155 155 155 / 0.5); /* 半透明背景 */
            border-radius: 18px;
            box-shadow: 0 6px 20px rgba(0, 120, 200, 1);
            padding: 40px;
            width: 100%;
            max-width: 500px;
            text-align: center;
            border: 2px solid rgb(0 21 255 / 0.2);
        }

        /* 大标题样式 */
        h1 {
            font-family: "华文楷体", sans-serif;
            color: #000000;
            margin-bottom: 25px;
            font-size: 50px;

        }

        /* 表单左对齐 */
        form {
            text-align: left;
        }

        /* 表单标签样式 */
        form label {
            font-family: "华文楷体", sans-serif;
            display: inline-block;
            margin-bottom: 6px;
            color: #000;
            font-size: 20px;
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

        /* 输入框获得焦点时高亮 */
        form input:focus {
            border-color: #0099dd;
            box-shadow: 0 0 6px rgba(0, 153, 221, 0.25);
        }

        /* 登录按钮样式 */
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

        /* 按钮鼠标悬浮效果 */
        button:hover {
            background-color: #0077bb;
            transform: translateY(-2px);
        }

        /* 记住用户 + 忘记密码 行布局 */
        .remember-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 5px 0 15px;
        }

        /* 链接样式 */
        a {
            color: #00599c;
            text-decoration: none;
        }

        /* 链接悬浮下划线 */
        a:hover {
            text-decoration: underline;
        }

        /* 错误提示文字样式 */
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
    <h1>欢迎用户登录</h1>

    <form action="" method="post">
        <label>用户名：</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($name) ?>" placeholder="请输入用户名"><br><br>

        <label>密码：</label>
        <input type="password" name="password" placeholder="请输入密码"><br><br>

        <div class="remember-row">
            <label>
                <input type="checkbox" name="remember" id="remember" value="1"
                        <?php if(isset($_COOKIE['rememb er_name'])) echo 'checked';?>>
                记住用户
            </label>
            <a href="wangji.php">忘记密码？</a>
        </div>
        <button type="submit">登录用户</button><br><br>
        <div style="text-align: center; color: #000; margin-top: 10px;">
            还没有账号？<a href="zhuce.php">立即注册</a>
        </div>
    </form>

    <?php if($x):?>
        <p><?php echo $x ?></p>
    <?php endif; ?>
</div>

</body>
</html>