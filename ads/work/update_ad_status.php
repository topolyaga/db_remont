<?
session_start();
header("Content-Type:text/html;charset=UTF-8");

require_once("../../inc/core/connectdb.php");
require_once("../../inc/core/user.php");

if(!$user->isAuth())
{
    header('Location: http://'.$_SERVER['HTTP_HOST'].'/login.php');
    die();
}


if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
{
    $resultarray=array();

	$id_ad = $_POST['id_ad'];
	$status = $_POST['status'];

    if($status=='publish')
    {
        $status = 'Опубликовано';
    }

	$stmt=$db->prepare("UPDATE `ads` SET `status`=:status WHERE id=:id_ad");
    
    $stmt->execute(array(
        "id_ad"=>$id_ad,
        "status"=>$status
        ));

    if($stmt)
    {
        $resultarray = array("status"=>"ok");
    }
    else
    {
        $resultarray = array("status"=>"error");
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