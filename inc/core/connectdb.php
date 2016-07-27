<?php
$dblocation = "localhost"; //Сервер базы данных
$dbname = "r62447_dbremont"; //Имя базы данных
$dbuser = "r62447_u_dbrem"; //Пользователь базы данных
$dbpasswd = "dds121295dmldds"; //Пароль

try {
  $db = new PDO("mysql:host=$dblocation;dbname=$dbname", $dbuser, $dbpasswd);
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $db->exec("set names utf8");
}
catch(PDOException $e) {
    echo $e->getMessage();
}
?>