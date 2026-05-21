<?php
session_start();
require "xss.php";
include 'lianjieku.php';

$name = "";
$password = "";
$x = "";

// 记住用户名
if (isset($_COOKIE['remember_name'])) {
    $name = $_COOKIE['remember_name'];
}

// ============== 登录失败锁定功能（新增）==============
$lock_key = 'login_lock_' . $_SERVER['REMOTE_ADDR'];
$attempt_key = 'login_attempt_' . $_SERVER['REMOTE_ADDR'];
$max_attempts = 5;    // 最多输错5次
$lock_time = 600;      // 锁定10分钟（秒）

// 检查是否被锁定
if (isset($_SESSION[$lock_key]) && $_SESSION[$lock_key] > time()) {
    $left = $_SESSION[$lock_key] - time();
    $x = "登录失败次数过多，请" . ceil($left/60) . "分钟后再试！";
}

// ============== 登录提交 ==============
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_SESSION[$lock_key])) {
    $name = trim($_POST["name"] ?? "");
    $password = trim($_POST["password"] ?? "");
    $remember = $_POST['remember'] ?? "";

    if (empty($name) || empty($password)) {
        $x = "用户名或密码不能为空！";
    } else {
        // 查询用户
        $stmt = $pdo->prepare("SELECT * FROM yonghu WHERE name = :name");
        $stmt->execute([':name' => $name]);

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch();

            // 密码验证
            if (password_verify($password, $row['password'])) {
                // 登录成功：清除失败次数
                unset($_SESSION[$attempt_key], $_SESSION[$lock_key]);

                // 记住用户
                if ($remember == 1) {
                    setcookie("remember_name", $name, time() + 86400 * 7);
                } else {
                    setcookie("remember_name", "", time() - 3600);
                }

                // 写入SESSION
                $_SESSION['id'] = $row['id'];
                $_SESSION['name'] = $row['name'];
                $_SESSION['email'] = $row['email'] ?? '';
                $_SESSION['admin'] = ($row['is_admin'] == 1); // 用数据库字段更安全

                // ============== 记录登录日志（新增）==============
                $ip = $_SERVER['REMOTE_ADDR'];
                $time = date('Y-m-d H:i:s');
                $pdo->prepare("INSERT INTO login_log(username,ip,login_time) VALUES(?,?,?)")
                        ->execute([$name, $ip, $time]);

                // ============== 区分跳转（新增）==============
                if ($_SESSION['admin']) {
                    header("Location: admin.php"); // 管理员去后台
                } else {
                    header("Location: huanying.php"); // 普通用户去欢迎页
                }
                exit();
            } else {
                $x = "密码错误";
                // 失败次数+1
                $_SESSION[$attempt_key] = ($_SESSION[$attempt_key] ?? 0) + 1;

                // 达到上限 → 锁定
                if ($_SESSION[$attempt_key] >= $max_attempts) {
                    $_SESSION[$lock_key] = time() + $lock_time;
                    $x = "输错5次，已锁定10分钟！";
                }
            }
        } else {
            $x = "用户不存在，请先注册！";
        }
    }
}
?>
<!doctype html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>用户登录</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Microsoft YaHei", sans-serif;
        }
        html, body {
            width: 100%;
            height: 100%;
            overflow: hidden;
        }
        body {
            color: #104e7a;
        }
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
        .container {
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            background: rgb(155 155 155 / 0.5);
            border-radius: 18px;
            box-shadow: 0 6px 20px rgba(0, 120, 200, 1);
            padding: 40px;
            width: 100%;
            max-width: 500px;
            text-align: center;
            border: 2px solid rgb(0 21 255 / 0.2);
        }
        h1 {
            font-family: "华文楷体", sans-serif;
            color: #000000;
            margin-bottom: 25px;
            font-size: 50px;
        }
        form {
            text-align: left;
        }
        form label {
            font-family: "华文楷体", sans-serif;
            display: inline-block;
            margin-bottom: 6px;
            color: #000;
            font-size: 20px;
        }
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
        form input:focus {
            border-color: #0099dd;
            box-shadow: 0 0 6px rgba(0, 153, 221, 0.25);
        }
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
        button:hover {
            background-color: #0077bb;
            transform: translateY(-2px);
        }
        .remember-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 5px 0 15px;
        }
        a {
            color: #00599c;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        p {
            margin-top: 15px;
            font-size: 15px;
            text-align: center;
            color: red;
            font-weight: bold;
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
                <input type="checkbox" name="remember" value="1"
                        <?php if(isset($_COOKIE['remember_name'])) echo 'checked';?>>
                记住用户
            </label>
            <a href="wangji.php">忘记密码？</a>
        </div>

        <button type="submit">登录用户</button><br><br>

        <div style="text-align:center; color:#000; margin-top:10px;">
            还没有账号？<a href="zhuce.php">立即注册</a>
        </div>
    </form>

    <?php if($x):?>
        <p><?php echo $x ?></p>
    <?php endif; ?>
</div>

</body>
</html>