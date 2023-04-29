<?php
  include("../../server/properties.php");
  include("../../server/functions.php");
  include("../../server/db.php");

  /* セッション開始 */
  session_start();
  if(!isset($_SESSION["inSession"]) || $_SESSION["contents"]!=$contents_name){
		// 当ページは表示せずログインページに遷移
		header('Location: ../login.php');
		exit;
  }else{
    $user = $_SESSION["name"];
  }

  $today = date('Y-m-d');
  $tomorrow = date('Y-m-d', strtotime('tomorrow'));
  $res = array();
  $id_num = $_SESSION["account_id"];
  if(isset($id_num)){
    // 検索条件に合致するノートレコードを取得
    $thisSql = "SELECT owner_id, char_id, task, is_del_ready, ".
      "concat(char_id,'_1') as first, first_date, is_first_done, ".
      "concat(char_id,'_2') as second, DATE_ADD(DATE_FORMAT(first_date, '%Y-%m-%d'),INTERVAL second DAY) second_date, is_second_done, ".
      "concat(char_id,'_3') as third, DATE_ADD(DATE_FORMAT(first_date, '%Y-%m-%d'),INTERVAL third DAY) third_date, is_third_done, ".
      "concat(char_id,'_4') as forth, DATE_ADD(DATE_FORMAT(first_date, '%Y-%m-%d'),INTERVAL forth DAY) forth_date, is_forth_done, ".
      "concat(char_id,'_5') as fifth, DATE_ADD(DATE_FORMAT(first_date, '%Y-%m-%d'),INTERVAL fifth DAY) fifth_date, is_fifth_done ".
      "FROM `stu_do_schedules` WHERE owner_id = '".$id_num."'";
    $statement = $connection->prepare($thisSql);
    $statement->execute();
    $sqlResult = $statement->fetchAll(PDO::FETCH_ASSOC);
    // ※[PDO::FETCH_ASSOC]は、配列内にナンバーインデックスを入れない（カラムデータのみを入れる）為に設定する
    $calendarTableArray = makeCltArray($sqlResult);
    $output_calendar = makeDateCollectedClt($calendarTableArray);
    $res = json_encode($output_calendar);
  }

  function generateTask($record){
    foreach($record['tasks'] as $inner){
      $times = substr($inner['id'], -1);
      $round = "（".$times."回目）";
      $done_style = $inner['is_done']==0 ? "" : "style='display:none;'";
      $undone_style = $inner['is_done']==1 ? "" : "style='display:none;'";
      $clearIcon = $inner['is_done']==1 ? '<span class="badge_clear">済</span>' : '';
      
      return '<div class="tasks">'.
              '<p id="'.$inner['id'].'" class="task_title" data-from="'.$inner['char_id'].'">'.
                '<span class="task_name">◆ </span>'.$clearIcon.
                '<span class="task_name">'.$inner['task'].$round.'</span>'.
                '<span class="done_btn mr-1" '.$done_style.' data-id="'.$inner['id'].'" data-done="'.$inner['is_done'].'" @click="changeDone($event)">実施済にする</span>'.
                '<span class="undone_btn mr-1" '.$undone_style.' data-id="'.$inner['id'].'" data-done="'.$inner['is_done'].'" @click="changeDone($event)">未実施にする</span>'.
                '<span class="editIcon">'.
                  '<i class="material-icons fader" data-ownerid="'.$inner['owner_id'].'" data-charid="'.$inner['char_id'].'" data-times="'.$times.'" '
                    .'@click="open_edit($event)">edit</i>'.
                '</span>'.
                '<span class="editIcon">'.
                  '<i class="material-icons fader" data-charid="'.$inner['char_id'].'" @click="deleteThis($event)">delete</i>'.
                '</span>'.
              '</p>'.
            '</div>';
    }
  }
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $user."'s ".$contents_name ?></title>
    <link rel="icon" href="../../images/StuDuSchedular_favicon.ico">
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Icons" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@6.x/css/materialdesignicons.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.min.css" rel="stylesheet" />
    <style scoped>
      .mr-05 { margin-right: 0.5em; }
      .mr-1 { margin-right: 1em; }
      .mr-2 { margin-right: 2em; }
      #title {
        color: #ff9200;
        font-size: 25px;
        font-weight: 600;
        -webkit-text-stroke: 0.7px white;
      }
      #for_today_task, .task_date {
        margin: 10px 0 10px 0;
        padding: 7px 10px;
        font-size: 17px;
        color: #EEEEEE;
      }
      #for_today_task {
        border-top: 1px solid #985522;
        border-right: 1px solid #985522;
        border-left: 10px solid #3f51b5;
        border-bottom: 1px solid #985522;
        background-color: #985522;
      }
      #not_yet_task {
        margin-bottom: 0.5em;
        padding-left: 0.5em;
        border-top: 1px solid #ffbf00;
        border-right: 1px solid #ffbf00;
        border-left: 10px solid #ffbf00;
        border-bottom: 1px solid #ffbf00;
        background-color: #fe0055;
        color: white;
      }
      .headerIcon>.material-icons,
      .editIcon>.material-icons {
        color:#f7f7cc;
        cursor:pointer;
        padding:3px;
        border:2px solid #f7f7cc;
        border-radius:5px;
      }
      .headerLogo {
        border-radius: 10px;
        border: solid orange 3px;
        height: 150px;
        box-shadow: 0px 1px 5px orange;
      }
      .headerIcon>.material-icons:hover,
      .editIcon>.material-icons:hover {
        color:yellow;
        border:2px solid yellow;
        box-shadow: 0 0 10px #fdff80;
        text-shadow: 0 0 10px #fdff80;
      }
      .editIcon>.material-icons { 
        font-size: 15px;
        margin: 3px;
      }
      .task_date {
        border-top: 1px solid #009688;
        border-right: 1px solid #009688;
        border-left: 10px solid #e3297d;
        border-bottom: 1px solid #009688;
        background-color: #009688;
      }
      .tasks {
        text-align: left;
        color: brown;
        padding-left: 1.5em;
        padding-bottom: 0.5em;
      }
      .badge_other {
        border-radius: 50%;
        cursor: context-menu;
        font-size: 12px;
        margin-left: 1em;
        padding: 5px 10px;
      }
      .badge_clear {
        border-radius: 50%;
        cursor: context-menu;
        font-size: 15px;
        margin-right: 0.5em;
        padding: 5px 10px;
        color: yellow;
        font-weight: 600;
      }
      .badge_other {
        border: 1px solid #e3297d;
        background-color: #e3297d;
      }
      .badge_clear {
        border: 1px solid #d61e1e;
        background-color: #ff7920;
      }
      .done_btn, .undone_btn, .open_btn {
        border-radius: 10px;
        cursor: pointer;
        font-size: 12px;
        margin-left: 1em;
        padding: 2px 10px;
        color: #EEEEEE;
      }
      .done_btn {
        border: 2px solid green;
        background-color: #25af2b;
      }
      .undone_btn {
        border: 2px solid red;
        background-color: #f86426;        
      }
      .open_btn {
        border: 2px solid blue;
        background-color: #2196f3;
      }
      .fader{
        animation-name:fadeInAnime;
        animation-duration:1s;
      }
      .spacer { width: 1em; }
      .changeFlexer { display: flex; }

      @keyframes fadeInAnime{
        from {opacity: 0}
        to {opacity: 1;}
      }
      #wrapper{
        margin:0 auto;
        position:relative;
      }
      .dialog > div > .v-application--wrap { display: none; }
      .v-application--wrap {
        min-height: 0vh;
        background-color: rgb(239, 235, 222);
      }
      .v-list-item {
        min-height: 0vh;
        height: 27px;
      }
      .invisible { opacity: 0; }
      .disp_none { display: none; }

      /* PC用 */
      @media only screen and (min-width:960px){
        #wrapper,.inner {
          width:80%;
          padding:0;
        }
        #wrapper {
          padding-top:20px;
          padding-bottom:20px;
        }
        .tasks > p > .task_name {
          -webkit-text-stroke: 0.6px white;
          font-weight: 700;
          font-size: 18px;
        }
      }

      /* スマホ用 */
      @media only screen and (max-width:400px){
        #wrapper { margin: 0.5em; }
        .headerLogo { width: 90%; }
        .tasks, .no_task {
          color: #a5900a;
          font-weight: 600;
          font-size: 15px;
        }
        .done_btn, .undone_btn {
          display: inline-block;
          margin-top: 10px;
        }
        .changeFlexer { display: inline-block; }
        .flexTopMargin { margin-top: 0.5em; }
      }
    </style>
    <style>
      body{
        background: rgb(255,210,0);
        background: radial-gradient(circle, rgba(255,210,0,1) 0%, rgba(0,212,255,1) 60%, #222dea 100%);
      }
    </style>
</head>
<body>
  <?php echo "<input type='hidden' id='op_calendar' value='".$res."'>"; ?>
  <!-- Vue Area -->
  <div id="vueArea">
    <div align="center">
      <br /><img src="../../images/Stu-Do-Schedular_headerLogo.png" alt="Stu-Do-Schedular HeaderLogo" class="headerLogo">
    </div>
    <h2 align="center" id="title" class="fader"><?php echo $user."'s ".$contents_name ?></h2>
    <!-- 共通クロックエリア -->
    <div id="clockArea" class="fader"><digi-clock /></div>

    <section id="wrapper" class="invisible">
      <div :style="styles.mg1ems" align="right">
        <div class="headerIcon">
          <i class="material-icons fader" title="ログアウトします" @click="dialog.instance.logout = true">logout</i>
        </div>
      </div>
      <main id="schedule_area">
        <input type="hidden" name="owner_id" id="owner_id" value="<?php echo $id_num ?>">

        <!-- 未着手のタスク -->
        <h3 id="not_yet_task" v-if="delayTasks.length!=0">Running Late Tasks</h3>
        <!-- 一括削除ボタン -->
        <?php 
          $countTaskSql = "SELECT count(char_id) length FROM `stu_do_schedules` WHERE first_date < (NOW() - INTERVAL 50 DAY)";
          $countQueryDo = $connection->prepare($countTaskSql);
          $countQueryDo->execute();
          $count_result = $countQueryDo->fetch(PDO::FETCH_ASSOC);
          $past50_length = $count_result['length'];
          if($past50_length>0){
        ?>
          <section align="center" v-if="delayTasks.length!=0">
            <v-btn :style="palette.redFront" @click="deletePassedMore50($event)">50日以上前のタスクを削除</v-btn>
          </section><br />
        <?php } ?>
        <div class="tasks" v-if="delayTasks.length!=0" v-for="item in this.delayTasks">
          <p><span class="task_name">◆ {{ item.task }}</span><span> （{{ item.date }} 実施分）</span></p>
        </div>

        <!-- 本日のタスク -->
        <h3 id="for_today_task">Today's Tasks</h3>
        <?php 
          $today_count = 0;
          foreach($output_calendar as $h => $record){ 
            if($record['date'] == $today){ 
              $today_count = 1;
              echo '<div id="today_task">'.generateTask($record).'</div><br /><br />';
              break;
            }
          }
          if($today_count != 1) echo "<div class='no_task' align='center'><p>本日はタスクはありません</p></div>";
        ?>

        <!-- 新規登録ボタン -->
        <section align="right">
          <v-btn :style="palette.blueFront" @click="startInsertMode">新規登録</v-btn>
        </section>

        <!-- 本日以外のタスク -->
        <?php foreach($output_calendar as $i => $record){ ?>
        <?php 
          if($record['date'] != $today){
            $add_grayStyle = $record['date'] < $today ? "style='color:#ababab;'" : "";
        ?>
          <h3 id="<?php echo "for_task_group_".($i+1) ?>" data-count="<?php echo ($i+1) ?>" class="task_date">
            <?php echo '<span '.$add_grayStyle.'>'.$record['date'].'</span>' ?>
            <span class="badge_other"><?php echo count($record['tasks']) ?></span>
            <?php if(($i+1)>=3) { ?>
              <span class="open_btn" data-for="<?php echo "task_group_".($i+1) ?>" @click="disp_open">展開する</span>
            <?php } ?>
          </h3>
          <div id="<?php echo "task_group_".($i+1) ?>" data-count="<?php echo ($i+1) ?>" <?php if(($i+1)>=3) echo "class='disp_none'" ?>>
            <?php echo generateTask($record); ?>
          </div>
        <?php } ?>
        <?php } ?>
        <br /><br />

        <div id="insertUpdate" :class="viewCardSection == true ? 'fader' : 'none'" v-if="viewCardSection == true">
          <card-sec>
            <template #title><tag-title>スケジュール登録・更新</tag-title></template>
            <template #contents>
              <section align="right">
                <v-btn class="mx-2" fab small @click="closeViewCardSection">×</v-btn>
              </section>

              <section>
                <v-text-field id="taskId" class="disp_none" v-model="form.id" ></v-text-field>
                <v-text-field id="taskTitle" label="タスクタイトル" placeholder="タスクのタイトルを入力して下さい" v-model="form.title"></v-text-field>
              </section>

              <section class="changeFlexer">
                <div id="dateFirst" class="mr-2">
                  <v-text-field label="学習開始日" type="date" v-model="form.date1st" @input="changeDate1st($event)" />
                </div>
                <div id="dateNumSecond" class="mr-2">
                  <v-app v-if="type=='insert'">
                    <v-select hide-details :items="dayItems.second" :label="type=='insert' ? '2回目' : ''" v-model="form.num2nd" @change="changeSelectNum('2nd', $event)"></v-select>
                  </v-app>
                  <v-app v-if="type!='insert'">
                    <v-select hide-details :items="dayItems.second" id="select_2" v-model="form.num2nd" @change="changeSelectNum('2nd', $event)"></v-select>
                  </v-app>
                </div>
                <div id="dateSecond">
                  <v-text-field label="2回目の日付" type="date" v-model="form.date2nd" disabled />
                </div>
              </section>

              <section class="changeFlexer">
                <div id="dateNumThird" class="mr-2">
                  <label class="mr-05">3回目{{ type!='insert' ? ' （最新：'+ last.third + '）' : '' }}</label>
                  <v-app v-if="type=='insert'">
                    <v-select hide-details :items="dayItems.third" v-model="form.num3rd" @change="changeSelectNum('3rd', $event)"></v-select>
                  </v-app>
                  <v-app v-if="type!='insert'">
                    <v-select hide-details :items="dayItems.third" id="select_3" v-model="form.num3rd" @change="changeSelectNum('3rd', $event)"></v-select>
                  </v-app>
                </div>
                <div id="dateNumForth" class="mr-2 flexTopMargin">
                  <label class="mr-05">4回目{{ type!='insert' ? ' （最新：'+ last.forth + '）' : '' }}</label>
                  <v-app v-if="type=='insert'">
                    <v-select hide-details :items="dayItems.forth" v-model="form.num4th" @change="changeSelectNum('4th', $event)"></v-select>
                  </v-app>
                  <v-app v-if="type!='insert'">
                    <v-select hide-details :items="dayItems.forth" id="select_4" v-model="form.num4th" @change="changeSelectNum('4th', $event)"></v-select>
                  </v-app>
                </div>
                <div id="dateNumFifth" class="mr-2 flexTopMargin">
                  <label class="mr-05">5回目{{ type!='insert' ? ' （最新：'+ last.fifth + '）' : '' }}</label>
                  <v-app v-if="type=='insert'">
                    <v-select hide-details :items="dayItems.fifth" v-model="form.num5th" @change="changeSelectNum('5th', $event)"></v-select>
                  </v-app>
                  <v-app v-if="type!='insert'">
                    <v-select hide-details :items="dayItems.fifth" id="select_5" v-model="form.num5th" @change="changeSelectNum('5th', $event)"></v-select>
                  </v-app>
                </div>
              </section><br /><br />

              <section class="changeFlexer">
                <div id="dateThird" class="mr-2">
                  <v-text-field label="3回目の日付" type="date" v-model="form.date3rd" disabled />
                </div>
                <div id="dateForth" class="mr-2">
                  <v-text-field label="4回目の日付" type="date" v-model="form.date4th" disabled />
                </div>
                <div id="dateFifth" class="mr-2">
                  <v-text-field label="5回目の日付" type="date" v-model="form.date5th" disabled />
                </div>
              </section><br /><br />

              <section align="center">
                <v-btn v-if="type=='insert'" :style="palette.brownFront" @click="validationJudge">これで登録する</v-btn>
                <v-btn v-else :style="palette.brownFront" @click="validationJudge">これで更新する</v-btn>
                <v-btn :style="palette.brownBack" @click="doReset">リセット</v-btn>
              </section>
          </template>
        </div>

        <!-- 登録・更新確認ダイアログ -->
        <dialog-frame-normal 
          :target="dialog.instance.axiosConfirm" 
          :title="type=='insert' ? '登録確認' : '更新確認'" 
          :contents="type=='insert' ? dialog.phrase.insertConfirm : dialog.phrase.updateConfirm">
          <v-btn @click="executeInsertUpdate" :style="palette.brownFront">OK</v-btn>
          <v-btn @click="dialog.instance.axiosConfirm = false" :style="palette.brownBack">キャンセル</v-btn>
        </dialog-frame-normal>

        <!-- 登録・更新完了ダイアログ -->
        <dialog-frame-normal 
          :target="dialog.instance.axiosComplete" 
          :title="type=='insert' ? '登録完了' : '更新完了'" 
          :contents="type=='insert' ? dialog.phrase.insertComplete : dialog.phrase.updateComplete">
          <v-btn @click="doReload" :style="palette.brownFront">リロード</v-btn>
        </dialog-frame-normal>

        <!-- タスク実施状況 更新完了ダイアログ -->
        <dialog-frame-normal 
          :target="dialog.instance.axiosTaskChangeComplete" 
          title="タスク実施状況 更新完了" 
          contents="タスクの実施状況を更新しました。">
          <v-btn @click="doReload" :style="palette.brownFront">リロード</v-btn>
        </dialog-frame-normal>

        <!-- 削除確認ダイアログ -->
        <dialog-frame-normal 
          :target="dialog.instance.axiosDeleteConfirm" :title="(deleteType==1) ? '削除確認' : '一括削除確認'" 
          :contents="dialog.phrase.deleteConfirm">
          <v-btn @click="doDelete" v-if="deleteType==1" :style="palette.brownFront">いいから消せ</v-btn>
          <v-btn @click="doDeletePassedMore50" v-else :style="palette.brownFront">いいからみんな消せ</v-btn>
          <v-btn @click="dialog.instance.axiosDeleteConfirm = false" :style="palette.brownBack">やっぱやめとく</v-btn>
        </dialog-frame-normal>

        <!-- 削除完了ダイアログ -->
        <dialog-frame-normal 
          :target="dialog.instance.axiosDeleteComplete" :title="(deleteType==1) ? '削除完了' : '一括削除完了'" 
          :contents="dialog.phrase.deleteComplete">
          <v-btn @click="doReload" :style="palette.brownFront">リロード</v-btn>
        </dialog-frame-normal>

        <!-- ログアウト確認ダイアログ -->
        <dialog-frame-normal 
          :target="dialog.instance.logout" 
          :title="'ログアウト確認'" 
          :contents="dialog.phrase.logout">
          <v-btn @click="doLogout" :style="palette.brownFront">OK</v-btn>
          <v-btn @click="dialog.instance.logout = false" :style="palette.brownBack">キャンセル</v-btn>
        </dialog-frame-normal>
      </main>
    </section>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/vue@2.x/dist/vue.js"></script>
  <script src="https://unpkg.com/vuejs-datepicker"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.18.0/axios.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
  <script type="module">
    import cardSection from '../../static/js/VJCP_cardSection.js';
    import colorPalette from '../../static/js/VJCP_colorPalette.js';
    import tagTitle from '../../static/js/VJCP_tagTitle.js';
    import digiClock from '../../static/js/VJCP_digiClock.js';
    import dialogFrameNormal from '../../static/js/VJCP_dialogFrameNormal.js';

      // #vueForCommon内でVue.jsの機能を有効化する
      const login = new Vue({
        el: '#vueArea',
        components: {
          'vuejs-datepicker': vuejsDatepicker
        },
        vuetify: new Vuetify(),
        data: function () {
          return {
            headerObject: {
              headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            },
            palette: colorPalette,
            styles: {
              mg1ems: 'margin-top: 1em; margin-bottom: 1em;',
              center3ems: 'text-align: center; font-size: 3em;',
              redPointer: 'color:red;cursor:pointer;',
            },
            type: "insert",
            viewCardSection: false,
            delayTasks: [],
            form: {
              id: "",
              title: "",
              date1st: "",
              num2nd: "",
              date2nd: "",
              num3rd: "",
              date3rd: "",
              num4th: "",
              date4th: "",
              num5th: "",
              date5th: "",
              owner_id: "",
            },
            deleteType: 1,
            dayItems: {
              second: [1,2],
              third: [5,6,7],
              forth: [12,13,14,15],
              fifth: [25,26,27,28,29,30],
            },
            dayDefo: {
              second: 1,
              third: 7,
              forth: 15,
              fifth: 30,
            },
            last: {
              third: 0,
              forth: 0,
              fifth: 0,
            },
            dialog: {
              instance: {
                axiosConfirm: false,
                axiosComPlete: false,
                axiosTaskChangeComplete: false,
                axiosDeleteConfirm: false,
                axiosDeleteComPlete: false,
                logout: false,
              },
              phrase: {
                insertConfirm: "これで登録します。よろしいですか？",
                updateConfirm: "これで更新します。よろしいですか？",
                insertComplete: "登録が完了しました。",
                updateComplete: "更新が完了しました。",
                deleteConfirm: "ホントに削除しますよ？後悔しませんね？",
                deleteComplete: "削除が完了しました。",
                logout: "ログアウトします。よろしいですか？",
              },
              confirm: {
                insertUpdate: false,
                logout: false,
              },
              complete: false,
            },
          };
        },
        created: function () {
          this.init();
        },
        methods: {
          // 画面初期表示処理
          async init() {
            setTimeout(function(){
              document.getElementById('wrapper').classList.remove('invisible');
              document.getElementById('wrapper').classList.add('fader');
            }, 1500);
            let task_lines = this.getTaskLines();
            this.delayTasks = this.getDelayTaskLines(task_lines);
          },
          startInsertMode(){
            this.type = 'insert';
            this.dayDefo = { second:1, third:7, forth:15, fifth:30 };
            this.viewCardSection = true;
            this.changeDate1st(this.generatedToday());
          },
          startEditMode(gotParam){
            this.type = 'edit';
            this.form.id = gotParam.char_id;
            this.form.title = gotParam.task;
            this.form.date1st = gotParam.first_date;
            this.dayDefo.second = gotParam.second;
            this.dayDefo.third = gotParam.third;
            this.dayDefo.forth = gotParam.forth;
            this.dayDefo.fifth = gotParam.fifth;
            this.viewCardSection = true;
            this.changeDate1st(this.form.date1st);
          },
          closeViewCardSection(){
            this.type = 'insert';
            this.viewCardSection = false;            
          },
          generatedToday(){
            let today = new Date();
            let month = this.generateZeroStartNum(today.getMonth() + 1);
            let day = this.generateZeroStartNum(today.getDate());
            return `${today.getFullYear()}-${month}-${day}`;
          },
          generateZeroStartNum(num){
            let numStr = num;
            if(Number(num)<10) numStr = "0" + num;
            return numStr;
          },
          changeDate1st(e){
            this.form.date1st = e;
            this.setRenew_date();
          },
          changeSelectNum(type, e){
            if(type=='2nd') this.dayDefo.second = e;
            if(type=='3rd') this.dayDefo.third = e;
            if(type=='4th') this.dayDefo.forth = e;
            if(type=='5th') this.dayDefo.fifth = e;
            this.setRenew_date();
          },
          isDateBeforeToday(date_str){
            const today = new Date();
            let this_date = new Date(date_str);
            return this_date < today;
          },
          getTaskLines(){
            const op_cal_val = document.getElementById("op_calendar").value;
            const op_cal_obj = JSON.parse(op_cal_val);
            let task_lines = [];
            op_cal_obj.forEach(line => {
              line.tasks.forEach(task => {
                task_lines.push(task);
              });
            });
            return task_lines;
          },
          getDelayTaskLines(task_lines){
            let delay_task_array = [];
            task_lines.forEach(line =>{
              if(this.isDateBeforeToday(line.date)){
                if(line.is_done=='0') delay_task_array.push(line);
              };
            });
            return delay_task_array;
          },
          setRenew_date(){
            this.form.num2nd = this.dayDefo.second;
            this.form.date2nd = this.getRenew_date(this.form.date1st, this.dayDefo.second);
            this.form.num3rd = this.dayDefo.third;
            this.form.date3rd = this.getRenew_date(this.form.date1st, this.dayDefo.third);
            this.form.num4th = this.dayDefo.forth;
            this.form.date4th = this.getRenew_date(this.form.date1st, this.dayDefo.forth);
            this.form.num5th = this.dayDefo.fifth;
            this.form.date5th = this.getRenew_date(this.form.date1st, this.dayDefo.fifth);
          },
          getRenew_date(dateStr, day) {
            let d = moment(dateStr).add(day,'days');
            return d.format('YYYY-MM-DD');
          },
          changeDone(e) {
            let this_id = e.target.dataset.id;
            let data = {
              type: "change",
              id: this_id,
              char_id: this_id.slice(0,-2),
              is_done: e.target.dataset.done,
            };
           // axiosでPHPのAPIにパラメータを送信する為、次のようにする
            let params = new URLSearchParams();
            Object.keys(data).forEach(function (key) {
              params.append(key, this[key]);
            }, data);
            // ajax通信実行
            axios
              .post('../../server/api/insertUpdateTask.php', params, this.headerObject)
              .then(response => {
                if(response.data) this.dialog.instance.axiosTaskChangeComplete = true;
              }).catch(error => alert("通信に失敗しました。"));
          },
          open_edit(e) {
            this.form.id = e.target.dataset.charid;
            this.form.owner_id = e.target.dataset.ownerid;
            let data = { char_id: this.form.id };
            // axiosでPHPのAPIにパラメータを送信する為、次のようにする
            let params = new URLSearchParams();
            Object.keys(data).forEach(function (key) {
              params.append(key, this[key]);
            }, data);
            // ajax通信実行
            axios
              .post('../../server/api/searchTaskGetter.php', params, this.headerObject)
              .then(response => {
                this.startEditMode(response.data);
                this.last.third = response.data.third;
                this.last.forth = response.data.forth;
                this.last.fifth = response.data.fifth;
              }).catch(error => alert("通信に失敗しました。"));
          },
          deleteThis(e) {
            this.form.id = e.target.dataset.charid;
            this.deleteType = 1;
            this.dialog.instance.axiosDeleteConfirm = true;
          },
          deletePassedMore50(e) {
            this.form.id = "";
            this.deleteType = 2;
            this.dialog.instance.axiosDeleteConfirm = true;
          },
          disp_open(e) {
            let open_for = e.target.dataset.for;
            document.getElementById(open_for).classList.remove('disp_none');
            document.getElementById(open_for).classList.add('fader');
            e.target.classList.add('disp_none');
          },
          doReset() {
            this.type=='insert' ? this.startInsertMode() : '';
          },
          executeInsertUpdate() {
            let data = { 
              type: this.type,
              char_id: this.form.id,
              owner_id: this.form.owner_id,
              task: this.form.title,
              first_date: this.form.date1st,
              second: this.form.num2nd,
              third: this.form.num3rd,
              forth: this.form.num4th,
              fifth: this.form.num5th,
            };
            // axiosでPHPのAPIにパラメータを送信する為、次のようにする
            let params = new URLSearchParams();
            Object.keys(data).forEach(function (key) {
              params.append(key, this[key]);
            }, data);
            // ajax通信実行
            axios
              .post('../../server/api/insertUpdateTask.php', params, this.headerObject)
              .then(response => {
                if(response.data){
                  this.dialog.instance.axiosConfirm = false;
                  this.dialog.instance.axiosComplete = true;
                };
              }).catch(error => alert("通信に失敗しました。"));
          },
          doDelete() {
            this.type = "delete";
            let data = { 
              type: this.type,
              char_id: this.form.id,
            };
            // axiosでPHPのAPIにパラメータを送信する為、次のようにする
            let params = new URLSearchParams();
            Object.keys(data).forEach(function (key) {
              params.append(key, this[key]);
            }, data);
            // ajax通信実行
            axios
              .post('../../server/api/insertUpdateTask.php', params, this.headerObject)
              .then(response => {
                if(response.data){
                  this.dialog.instance.axiosDeleteConfirm = false;
                  this.dialog.instance.axiosDeleteComplete = true;
                };
              }).catch(error => alert("通信に失敗しました。"));
          },
          doDeletePassedMore50() {
            this.type = "bulk_delete";
            let data = { 
              allow: true,
              type: this.type,
            };
            // axiosでPHPのAPIにパラメータを送信する為、次のようにする
            let params = new URLSearchParams();
            Object.keys(data).forEach(function (key) {
              params.append(key, this[key]);
            }, data);
            // ajax通信実行
            axios
              .post('../../server/api/deleteMore50daysTasks.php', params, this.headerObject)
              .then(response => {
                if(response.data.status=="success"){
                  this.dialog.instance.axiosDeleteConfirm = false;
                  this.dialog.instance.axiosDeleteComplete = true;
                }else{
                  alert("削除に失敗しました。");
                  this.dialog.instance.axiosDeleteConfirm = false;
                };
              }).catch(error => alert("通信に失敗しました。"));
          },
          doReload() {
            location.reload();
          },
          doLogout() {
            location.href = "../login.php";
          },
          validationJudge() {
            let judge = true;
            if(this.form.title=="") alert("タイトルが入力されていません。");
            if(this.form.title.length>48) alert("タイトルが長すぎます。45字以内で入力して下さい。");
            if(this.form.title=="" || this.form.title.length>48){
              return;
            }else{
              if(this.type=='insert'){
                this.form.id = this.generatedChars() + this.generatedQuatNums();
                this.form.owner_id = document.getElementById('owner_id').value;
              }
              this.dialog.instance.axiosConfirm = true;
            }
          },
          generatedChars() {
            let chars = "";
            const base = ["a","i","u","e","o","b","c","d","f","g","h","k","l","m","n","p","r","s","t","y","z"];
            const shi_in = ["a","i","u","e","o"];
            for(let i=0;i<3;i++){
              let base_rand = Math.floor(Math.random() * base.length);
              let shiIn_rand = Math.floor(Math.random() * 5);
              chars += base[base_rand];
              chars += shi_in[shiIn_rand];
            }
            return chars;
          },
          generatedQuatNums() {
            let nums = "";
            for(let i=0;i<3;i++){
              let num = String(Math.floor(Math.random() * 10));
              nums += num;
            }
            return nums;
          },
        },
      });
  </script>
</body>
</html>