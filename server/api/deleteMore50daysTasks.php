<?php
  include('../db.php');
  include('../functions.php');
  include('../properties.php');

  $result = "";
  if(isset($_POST['allow'])){
    $records = array();
    try{
      $selectTaskSql = "SELECT char_id FROM `stu_do_schedules` WHERE first_date < (NOW() - INTERVAL 50 DAY)";
      $stmt = $connection->prepare($selectTaskSql);
      $stmt->execute();
      $char_ids = $stmt->fetchAll(PDO::FETCH_ASSOC);

      foreach($char_ids as $i=>$line){
        $deleteTaskSql = "DELETE FROM `stu_do_schedules` WHERE char_id = :char_id";
        $stmt = $connection->prepare($deleteTaskSql);
        $stmt->bindValue(':char_id', $line['char_id']);
        $stmt->execute();
        $stmt->fetch(PDO::FETCH_ASSOC);
      }
      $records = array("status"=>"success","error_message"=>"");
    }catch(Exception $e){
      $records = array("status"=>"error","error_message"=>$e);
    }
    
    $result = json_encode($records);
  }

  echo $result;