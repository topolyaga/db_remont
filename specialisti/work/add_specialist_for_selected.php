<?
session_start();
header("Content-Type:text/html;charset=UTF-8");

require_once("../../inc/core/connectdb.php");
require_once("../../inc/core/user.php");
require_once("../../inc/core/events_for_specialist.php");
require_once("../../inc/core/add_rating_for_specialist.php");

if(!$user->isAuth())
{
    header('Location: http://'.$_SERVER['HTTP_HOST'].'/login.php');
    die();
}

$user_info = $user->GetInfo();

if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
{
    $resultarray=array();

    $id_specialist = $_POST['id_specialist'];

    $_SESSION['selected_specialist'] = $id_specialist;

    if($_SESSION['selected_specialist'] == $id_specialist)
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