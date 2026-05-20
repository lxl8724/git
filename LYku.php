<?php
include 'lianjieku.php';
$sql = "CREATE TABLE IF NOT EXISTS liuyan (
id INT AUTO_INCREMENT PRIMARY KEY,
name VARCHAR(30) NOT NULL,
content VARCHAR(200) NOT NULL,
time DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
$result = $pdo->exec($sql);
if ($result !== false) {
    echo "";
} else {
    $error = $pdo->errorInfo();
    echo "失败" . $error[2];
}
?>