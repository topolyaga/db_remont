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
    $id_call = $_POST['id_call'];
    $about_object = $_POST['about_object'];
    $have_materials = $_POST['have_materials'];
    $preparatory = $_POST['preparatory'];
    $price_start = $_POST['price_start'];
    $price_finish = $_POST['price_finish'];
    $date_view = $_POST['date_view'];
    $date_work_start = $_POST['date_work_start'];
    $date_work_finish = $_POST['date_work_finish'];
    $address = $_POST['address'];
    $client_name = $_POST['client_name'];
    $client_phone = $_POST['client_phone'];
    $cpec_categorys = $_POST['cpec_categorys'];
    $prioritet= $_POST['prioritet'];
    $percent = $_POST['percent'];

    if($id_call=='')
    {
        $id_call = 0;
    }

    //Сделаем категории списком через запятую
    $str_categorys = '';

    foreach ($cpec_categorys as $val)
    {
        $str_categorys.=$val.',';
    }

    $str_categorys = substr($str_categorys, 0, -1);

    $stmt=$db->prepare("SELECT id_num_day FROM `orders` WHERE DATE(add_date) = DATE(NOW()) ORDER BY  `orders`.`id` DESC  LIMIT 0,1");
    $stmt->execute();
    $last_order_num_day_id=$stmt->fetch(PDO::FETCH_ASSOC);

    if($last_order_num_day_id['id_num_day']!='')
    {
        $last_order_num_day_id = $last_order_num_day_id['id_num_day'];
        $last_order_num_day_id++;
    }
    else
    {
        $last_order_num_day_id = 1;
    }

    //Выполняеем
    $stmt=$db->prepare("INSERT INTO `orders` (`id_num_day`,`id_call`,`id_manager`,`add_date`,`about_object`,`have_materials`,`preparatory`, `price_start`, `price_finish`,`date_view`,`date_work_start`,`date_work_finish`,`address`,`client_name`,`client_phone`,`cpec_categorys`,`prioritet`,`percent`,`status`) VALUES (:id_num_day,:id_call,:id_manager,NOW(),:about_object,:have_materials,:preparatory, :price_start, :price_finish, :date_view,:date_work_start,:date_work_finish,:address,:client_name,:client_phone,:cpec_categorys,:prioritet,:percent,'Новый')");
    $stmt->execute(array(
        "id_num_day"=>$last_order_num_day_id,
        "id_call"=>$id_call,
        "id_manager"=>$_SESSION["user_id"],
        "about_object"=>$about_object,
        "have_materials"=>$have_materials,
        "preparatory"=>$preparatory,
        "price_start"=>$price_start,
        "price_finish"=>$price_finish,
        "date_view"=>$date_view,
        "date_work_start"=>$date_work_start,
        "date_work_finish"=>$date_work_finish,
        "address"=>$address,
        "client_name"=>$client_name,
        "client_phone"=>$client_phone,
        "cpec_categorys"=>$str_categorys,
        "prioritet"=>$prioritet,
        "percent"=>$percent
        ));

    if($stmt)
    {
        $id_new_order = $db->lastInsertId();

        //Достаем информацию о менеджере
        $user_info = $user->GetInfo();

        switch($user_info['type'])
        {
            case 'manager': $comment = 'Менеджер'; break;
            case 'admin': $comment = 'Администратор'; break;
        }
        
        if($id_call==0)
        {
            //Заказ не на основе звонка
            $comment.= ' '.$user_info['name'].' '.$user_info['lastname'].' добавил заказ #'.$id_new_order.'.

            Заказу присвоен статус: Новый';
        }
        else
        {
            //Заказ на основе звонка
            $comment.= ' '.$user_info['name'].' '.$user_info['lastname'].' добавил заказ #'.$id_new_order.' на основе звонка #'.$id_call.'. 

            Заказу присвоен статус: Новый';
        }

        goEvent('order', 'sys_info', $_SESSION["user_id"], $id_new_order, 'Новый', $comment);

        header('Location: http://'.$_SERVER['HTTP_HOST'].'/orders/view_order.php?id='.$id_new_order);
        die();
    }
}
else
{
    header('Location: http://'.$_SERVER['HTTP_HOST']);
    die();
}
?>