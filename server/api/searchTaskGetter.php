<?php
  include('../db.php');
  include('../functions.php');
  include('../properties.php');
  
  $char_id = $_POST['char_id'];

  $result = "";
  if(isset($char_id)){
    $record = array();
    $selectTaskSql = "SELECT * FROM `stu_do_schedules` WHERE char_id = :char_id LIMIT 1";
    $stmt = $connection->prepare($selectTaskSql);
    $stmt->bindValue(':char_id', $char_id);
    $stmt->execute();
    $record = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $result = json_encode($record);
  }

  echo $result;