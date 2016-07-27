<?
function sendEventForSpecialist($id_specialist, $type, $sub_type, $id_sender, $comment, $id_ansubject = 0)
{
    global $db;

    $stmt=$db->prepare("INSERT INTO `specialist_events`(`add_date`, `id_specialist`, `type`, `sub_type`, `id_ansubject`, `id_sender`, `comment`) VALUES (NOW(), :id_specialist, :type, :sub_type, :id_ansubject, :id_sender, :comment)");

    $stmt->execute(array(
        "id_specialist"=>$id_specialist,
        "type"=>$type,
        "sub_type"=>$sub_type,
        "id_ansubject"=>$id_ansubject,
        "id_sender"=>$id_sender,
        "comment"=>$comment
        ));

    if($stmt)
    {
        return true;
    }
    else
    {
        return false;
    }
}
?>