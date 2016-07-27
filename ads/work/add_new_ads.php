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

	$title = $_POST['ad_title'];
	$description = $_POST['ad_description'];
    $price = $_POST['ad_price'];

	$id_category = $_POST['ad_id_category'];
	$public_date = $_POST['ad_publish_date'];
	$status = $_POST['ad_status'];

	$stmt=$db->prepare("INSERT INTO `ads` (`id_manager`, `add_date`, `title`, `description`, `price`, `public_date`, `id_categorys`, `status`) VALUES (:id_manager, NOW(), :title, :description, :price, :public_date, :id_category, :status);");
    
    $stmt->execute(array(
        "id_manager"=>$_SESSION["user_id"],
        "title"=>$title,
        "description"=>$description,
        "price"=>$price,
        "public_date"=>$public_date,
        "id_category"=>$id_category,
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