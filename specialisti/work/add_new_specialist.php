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
    $name = $_POST['name'];
    $lastname = $_POST['lastname'];

    $phone = $_POST['phone'];
    $phone2 = $_POST['phone2'];

    $categorys = $_POST['categorys'];

    $min_price = $_POST['min_price'];
    $min_price_opt = $_POST['min_price_opt'];
    $min_price_for_order = $_POST['min_price_for_order'];

    $experience = $_POST['experience'];
    $contract = $_POST['contract'];
    $guarantee = $_POST['guarantee'];
    $scale = $_POST['scale'];

    $areas = $_POST['areas'];

    $comment = $_POST['comment'];

    //Сделаем категории списком через запятую
    $str_categorys = '';

    foreach ($categorys as $val)
    {
        $str_categorys.=$val.',';
    }

    $str_categorys = substr($str_categorys, 0, -1);

    //Выполняеем
    $stmt=$db->prepare("INSERT INTO `specialist_db`(`id_manager`, `add_date`, `name`, `lastname`, `id_categorys`, `phone`, `phone2`, `min_price`, `min_price_opt`, `min_price_for_order`, `experience`, `contract`, `guarantee`, `scale`, `areas`, `comment`, `status`) VALUES (:id_manager, NOW(), :name, :lastname, :id_categorys, :phone, :phone2, :min_price, :min_price_opt, :min_price_for_order, :experience, :contract, :guarantee, :scale, :areas, :comment, 'Свободен')");
    $stmt->execute(array(
        "id_manager"=>$_SESSION["user_id"],
        "name"=>$name,
        "lastname"=>$lastname,
        "id_categorys"=>$str_categorys,
        "phone"=>$phone,
        "phone2"=>$phone2,
        "min_price"=>$min_price,
        "min_price_opt"=>$min_price_opt,
        "min_price_for_order"=>$min_price_for_order,
        "experience"=>$experience,
        "contract"=>$contract,
        "guarantee"=>$guarantee,
        "scale"=>$scale,
        "areas"=>$areas,
        "comment"=>$comment
        ));

    if($stmt)
    {
        //Получаем id спеца
        $id_new_specialist = $db->lastInsertId();

        //Теперь смотрим есть ли аватарка и добавляем ее
        $avatar_link='';

        if($_FILES['avatar']['size'] != 0)
        {
            //Есть тогда проверяем и грузим

            //Путь
            $uploaddir = '../../content/';

            //Имя файла
            $apend='ava_user_'.$id_new_specialist.'.jpg';

            //в переменную $uploadfile будет входить путь и имя файла
            $uploadfile = "$uploaddir$apend";

            // В данной строке самое важное - проверяем загружается ли изображение (а может вредоносный код?)
            if(($_FILES['avatar']['type'] == 'image/gif' || $_FILES['avatar']['type'] == 'image/jpeg' || $_FILES['avatar']['type'] == 'image/png'))
            {
                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadfile))
                {
                    $size = getimagesize($uploadfile);

                    //Проверим что изображение больше 10х10 пикселей
                    if ($size[0] > 10 && $size[1]>10)
                    {
                        //Все ок
                        $avatar_link='http://'.$_SERVER['HTTP_HOST'].'/content/'.$apend;
                    }
                    else
                    {
                        //Пофиг все равно даем ссылку
                        $avatar_link='http://'.$_SERVER['HTTP_HOST'].'/content/'.$apend;
                    }
                }
                else
                {
                    $avatar_link = '';
                }
            }
        }

        if($avatar_link!='')
        {
            $stmt=$db->prepare("UPDATE `specialist_db` SET avatar_link=:avatar_link WHERE id=:id_specilaist");
            $stmt->execute(array(
                "avatar_link"=>$avatar_link,
                "id_specilaist"=>$id_new_specialist
            ));

            if($stmt)
            {
                header('Location: http://'.$_SERVER['HTTP_HOST'].'/specialisti/view_profile.php?id='.$id_new_specialist.'&result_add=1');
                die();
            }
        }

        header('Location: http://'.$_SERVER['HTTP_HOST'].'/specialisti/view_profile.php?id='.$id_new_specialist.'&result_add=2');
        die();
    }
    else
    {
        echo 'Ошибка специалист не добавлен!';
        die();
    }
}
else
{
    header('Location: http://'.$_SERVER['HTTP_HOST']);
    die();
}
?>