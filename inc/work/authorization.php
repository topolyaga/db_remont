<?
session_start();
header("Content-Type:text/html;charset=UTF-8");

require_once("../core/connectdb.php");
require_once("../core/user.php");

if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' && !$user->isAuth())
{
    // Если запрос послан с xmlhttprequest, то есть это ajax запрос

	$login=$_POST['u_login'];
	$password=$_POST['u_password'];
	
	$resultarray=array();
	
	$go_login=$user->Auth($login, $password);
	
	if($go_login['status']=='success')
	{
		$resultarray=array("status"=>"ok");
	}
	else
	{
		$resultarray=array("status"=>"error", "type"=>$go_login['type'], "text"=>$go_login['text']);
	}
	
	//Возращаем ответ
	echo json_encode($resultarray);	
	die();
}
else
{
	echo 'error';
	die();	
}
?>