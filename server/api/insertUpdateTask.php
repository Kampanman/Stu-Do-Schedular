<?php
  include('../db.php');
  include('../functions.php');
  include('../properties.php');
  
  // apiに直接アクセスした場合、画面には0とだけ表示されるように設定
  $res = 0;

  if(isset($_POST['char_id'])){
    $res = array();

    if($_POST['type'] == 'insert'){
      $title = h($_POST['task']);
      $insertSql = "INSERT INTO stu_do_schedules VALUES "
                   ."(:char_id, :task, :first_date, 0, :second, 0, :third, 0, :forth, 0, :fifth, 0, :owner_id, now(), 0)";
      $statement = $connection->prepare($insertSql);
      $statement->bindValue(':char_id', $_POST['char_id']);
      $statement->bindValue(':task', $title);
      $statement->bindValue(':first_date', $_POST['first_date']);
      $statement->bindValue(':second', $_POST['second']);
      $statement->bindValue(':third', $_POST['third']);
      $statement->bindValue(':forth', $_POST['forth']);
      $statement->bindValue(':fifth', $_POST['fifth']);
      $statement->bindValue(':owner_id', $_POST['owner_id']);

      $result = $statement->execute();
      $res = json_encode($result);
    }else if($_POST['type'] == 'change'){
      $changeNumFor = $_POST['is_done']=="0" ? 1 : 0;
      $changeTarget = substr($_POST['id'], -1);
      $targetColumn = "is_first_done";
      if($changeTarget=="2") $targetColumn = "is_second_done";
      if($changeTarget=="3") $targetColumn = "is_third_done";
      if($changeTarget=="4") $targetColumn = "is_forth_done";
      if($changeTarget=="5") $targetColumn = "is_fifth_done";
      // タスクの実施状態を更新
      $taskChangeSql = "UPDATE stu_do_schedules SET ".$targetColumn." = :changenum WHERE char_id = '".$_POST['char_id']."'";
      $statement = $connection->prepare($taskChangeSql);
      $statement->bindValue(':changenum', $changeNumFor);

      $result = $statement->execute();
      $res = json_encode($result);
    }else if($_POST['type'] == 'delete'){
      $taskDeleteSql = "DELETE FROM `stu_do_schedules` WHERE char_id = '".$_POST['char_id']."'";
      $statement = $connection->prepare($taskDeleteSql);

      $result = $statement->execute();
      $res = json_encode($result);
    }else{
      $title = h($_POST['task']);
      // タスクレコードを更新
      $taskUpdateSql = "UPDATE stu_do_schedules SET "
      ."task = :task, first_date = :first_date, second = :second, third = :third, forth = :forth, fifth = :fifth "
      ."WHERE char_id = '".$_POST['char_id']."'";

      $statement = $connection->prepare($taskUpdateSql);
      $statement->bindValue(':task', $title);
      $statement->bindValue(':first_date', $_POST['first_date']);
      $statement->bindValue(':second', $_POST['second']);
      $statement->bindValue(':third', $_POST['third']);
      $statement->bindValue(':forth', $_POST['forth']);
      $statement->bindValue(':fifth', $_POST['fifth']);

      $result = $statement->execute();
      $res = json_encode($result);
    }

  }
  echo $res;
