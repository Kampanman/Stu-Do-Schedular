<?php
/**
 * PHP関数機能集
 */

// htmlでのエスケープ処理
function h($var){
  if(is_array($var)){
    return array_map('h', $var);
  }else{
    return htmlspecialchars($var, ENT_QUOTES, 'UTF-8');
  }
}

// パスワードのハッシュ化
function hashpass($pass){
  // ハッシュ処理の計算コストを指定する
  $options = array('cost' => 10);
  // 方式にPASSWORD_DEFAULTを指定してハッシュ化したパスワードを返す
  return password_hash($pass, PASSWORD_DEFAULT, $options);
}

// コンテンツタイトルに応じた背景色の指定
function judgeBackColor($title){
  $length = mb_strlen($title);
  $amari = ($length<3) ? 0 : $length % 3;
  $num = 0;
  if($amari==1){
    $num = 5;
  }else if($amari==1){
    $num = 8;
  }
  
  return "background-color: #".$num."80a40";
}

// スケジュールテーブル情報取得のSQLをもとにタスクレコードの配列を作成
function makeCltArray($sqlResult){
  $calendarTableArray = array();
  foreach($sqlResult as $row){
    $firstArray = array('id' => $row['first'])
      + array('task' => $row['task'])
      + array('date' => $row['first_date'])
      + array('is_done' => $row['is_first_done'])
      + array('char_id' => $row['char_id'])
      + array('owner_id' => $row['owner_id']);
    $calendarTableArray[] = $firstArray;
    
    $secondArray = array('id' => $row['second'])
      + array('task' => $row['task'])
      + array('date' => $row['second_date'])
      + array('is_done' => $row['is_second_done'])
      + array('char_id' => $row['char_id'])
      + array('owner_id' => $row['owner_id']);
    $calendarTableArray[] = $secondArray;
    
    $thirdArray = array('id' => $row['third'])
      + array('task' => $row['task'])
      + array('date' => $row['third_date'])
      + array('is_done' => $row['is_third_done'])
      + array('char_id' => $row['char_id'])
      + array('owner_id' => $row['owner_id']);
    $calendarTableArray[] = $thirdArray;
    
    $forthArray = array('id' => $row['forth'])
      + array('task' => $row['task'])
      + array('date' => $row['forth_date'])
      + array('is_done' => $row['is_forth_done'])
      + array('char_id' => $row['char_id'])
      + array('owner_id' => $row['owner_id']);
    $calendarTableArray[] = $forthArray;
    
    $fifthArray = array('id' => $row['fifth'])
      + array('task' => $row['task'])
      + array('date' => $row['fifth_date'])
      + array('is_done' => $row['is_fifth_done'])
      + array('char_id' => $row['char_id'])
      + array('owner_id' => $row['owner_id']);
    $calendarTableArray[] = $fifthArray;
  }

  // dateの昇順（SORT_ASC）に並び替える.
  $dates = [];
  foreach($calendarTableArray as $arr) {
      $dates[] = $arr['date'];
  }
  array_multisort($dates, SORT_ASC, $calendarTableArray);

  return $calendarTableArray;
}

// タスクレコードを日付ごとにまとめる
function makeDateCollectedClt($calendarTableArray){
  $date_array = array();
  foreach($calendarTableArray as $row){
    $date_array[] = $row['date'];
  }
  $unique_dates = array_unique($date_array);
  $output_calendar = array();
  foreach($unique_dates as $ud){
    $tasks = array();
    foreach($calendarTableArray as $row){
      if($row['date']==$ud) $tasks[] = $row;
    }
    $this_output = array('date' => $ud) + array('tasks' => $tasks);
    $output_calendar[] = $this_output;
  }

  return $output_calendar;
}