<?
function goEvent($type, $sub_type, $id_sender, $id_ansubject, $now_status, $comment, $dop_id_subject_1 = 0, $dop_id_subject_2 = 0)
{
    global $db;

    $stmt=$db->prepare("
INSERT INTO `events` (
`add_date`,
`type`,
`sub_type`,
`id_sender`,
`id_ansubject`,
`dop_id_subject_1`,
`dop_id_subject_2`,
`now_status`,
`comment`)
VALUES (
NOW(),
:type,
:sub_type,
:id_sender,
:id_ansubject,
:dop_id_subject_1,
:dop_id_subject_2,
:now_status,
:comment)");

    $stmt->execute(array(
        "type"=>$type,
        "sub_type"=>$sub_type,
        "id_sender"=>$id_sender,
        "id_ansubject"=>$id_ansubject,
        "dop_id_subject_1"=>$dop_id_subject_1,
        "dop_id_subject_2"=>$dop_id_subject_2,
        "now_status"=>$now_status,
        "comment"=>$comment,
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