<?php
session_start();
header("Content-Type:text/html;charset=UTF-8");

require_once("../../inc/core/connectdb.php");
require_once("../../inc/core/user.php");
require("../../inc/core/events.php");

if(!$user->isAuth())
{
    header('Location: http://'.$_SERVER['HTTP_HOST'].'/login.php');
    die();
}

if(isset($_POST))
{
    $id_order= $_POST['id_order_for_status_process'];


    $stmt=$db->prepare("UPDATE `orders` SET `status`='Принят в обработку' WHERE id=:id_order");
    $stmt->execute(array("id_order"=>$id_order));

    if($stmt)
    {
        //Достаем информацию о пользователе
        $user_info = $user->GetInfo();

        switch($user_info['type'])
        {
            case 'manager': $comment = 'Менеджер'; break;
            case 'admin': $comment = 'Администратор'; break;
        }

        $comment.= ' '.$user_info['name'].' '.$user_info['lastname'].' принял в обработку заказ #'.$id_order.'

        Заказу присвоен статус: Принят в обработку';


        goEvent('order', 'working', $_SESSION["user_id"], $id_order, 'Принят в обработку', $comment);

        header('Location: http://'.$_SERVER['HTTP_HOST'].'/orders/view_order.php?id='.$id_order);
        die();
    }
}
else
{
    header('Location: http://'.$_SERVER['HTTP_HOST']);
    die();
}
?>