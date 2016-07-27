<?
function SendSms($to, $text)
{
    $ch = curl_init("http://sms.ru/auth/get_token");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $token = curl_exec($ch);
    curl_close($ch);

    $ch = curl_init("http://sms.ru/sms/send");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_POSTFIELDS, array(

        "login"		=>	"89284104722",
        "sha512"	=>	hash("sha512","dds121295dmldds".$token."F750D3B3-E38C-22B5-2476-E2A9E79D85DD"),
        "token"		=>	$token,
        "to"		=>	$to,
        "text"		=>	$text

    ));
    $body = curl_exec($ch);
    curl_close($ch);

    return $body;
}

function getSmsStatus($id_sms_smsru)
{
    $ch = curl_init("http://sms.ru/sms/status");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_POSTFIELDS, array(

        "api_id"		=>	"F750D3B3-E38C-22B5-2476-E2A9E79D85DD",
        "id"			=>	$id_sms_smsru

    ));

    $body = curl_exec($ch);
    curl_close($ch);

    return $body;
}

function f_GetSmsStatus($id_sms_smsru)
{
    $now_status = 0;

    //Попытка 1
    sleep(10);
    $now_status = getSmsStatus($id_sms_smsru);

    //103 - смс доставленно
    if($now_status!=103)
    {
        //Попытка 2
        sleep(30);
        $now_status = getSmsStatus($id_sms_smsru);

        if($now_status!=103)
        {
            //Попытка 3
            sleep(60);
            $now_status = getSmsStatus($id_sms_smsru);

            if($now_status!=103)
            {
                $now_status = getSmsStatus($id_sms_smsru);
            }
        }
    }

    return $now_status;
}


//function addSmsForLog($id_sms_smsru, $id_sender, $id_order, $id_specialist, $status)
//{
//    global $db;
//
//    $stmt=$db->prepare("INSERT INTO `sys_sended_sms` (`send_time`, `id_sms_smsru`, `id_sender`, `id_order`, `id_specialist`, `status`) VALUES (NOW(), :id_sms_smsru, :id_sender, :id_order, :id_specialist, :status)");
//    $stmt->execute(array(
//        "id_sms_smsru"=>$id_sms_smsru,
//        "id_sender"=>$id_sender,
//        "id_order"=>$id_order,
//        "id_specialist"=>$id_specialist,
//        "status"=>$status
//    ));
//
//    if($stmt)
//    {
//        return true;
//    }
//    else
//    {
//        return false;
//    }
//}
?>