<?php
require_once("classes/user.class.php");

//Создаем объект класса пользователя
$user = new User($dblocation, $dbname, $dbuser, $dbpasswd);
?>
