<?php
include 'lianjieku.php'; //引入数据库
$sql = "CREATE TABLE IF NOT EXISTS yonghu (
id INT AUTO_INCREMENT PRIMARY KEY,
name VARCHAR(30) NOT NULL UNIQUE,
password VARCHAR(255) NOT NULL,
email VARCHAR(30)  UNIQUE,
is_admin TINYINT(1) NOT NULL DEFAULT 0  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
$result = $pdo->exec($sql);//执行无结果的sql语句
if ($result !== false) {
    echo "打印成功";
} else {
    $error = $pdo->errorInfo();
    echo "失败" . $error[2];
}
?>