<?
session_start();
header("Content-Type:text/html;charset=UTF-8");

require_once("../core/connectdb.php");
require_once("../core/user.php");

if($user->isAuth())
{
    // Если пользователь аторизован
	$user->Out();
	header('Location: '.$_SERVER['HTTP_REFERER']);
	die();
}
else
{
	header('Location: '.$_SERVER['HTTP_REFERER']);
	die();	
}
?>