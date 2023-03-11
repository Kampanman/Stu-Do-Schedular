<?php
  include('../server/properties.php');
  $page_name = "二段階認証ページ";
  /* セッション開始 */
  session_start();

  if(!isset($_SESSION["inSession"]) || $_SESSION["inSession"]==false){
		// 当ページは表示せずログインページに遷移
		header('Location: ./login.php');
		exit;
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Stu-Do Schedular | ログインページ" />
  <link rel="icon" href="../images/StuDuSchedular_favicon.ico">
  <meta name="keywords" content="" />
  <title><?php echo $contents_name." | ".$page_name ?></title>
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Icons" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@6.x/css/materialdesignicons.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.min.css" rel="stylesheet" />
    <style scoped>
      body { background-color: #080a40; }

      .headerIcon>.material-icons {
        color:#fff;
        cursor:pointer;
        padding:3px;
        border:2px solid #fff;
        border-radius:5px;
      }

      .headerIcon>.material-icons:hover {
        color:yellow;
        border:2px solid yellow;
        box-shadow: 0 0 10px #fdff80;
        text-shadow: 0 0 10px #fdff80;
      }

      .sloganSub, .sloganMain {
        margin-top: 5px;
        margin-bottom: 10px;
      }

      .sloganSub span {
        color: rgb(141, 0, 0);
        font-size: 25px;
        font-weight: 600;
        -webkit-text-stroke: 0.7px white;
      }

      .sloganMain span {
        font-size: 50px;
        padding: 5px 10px;
        color: white;
        font-weight: 600;
        -webkit-text-stroke: 2px rgb(141, 0, 0);
      }

      .fader{
        animation-name:fadeInAnime;
        animation-duration:1s;
      }

      @keyframes fadeInAnime{
        from {opacity: 0}
        to {opacity: 1;}
      }

      #wrapper{
        margin:0 auto;
        position:relative;
      }

      /* PC用 */
      @media only screen and (min-width:960px){	
        #wrapper,.inner{
          width:80%;
          padding:0;
        }
        #wrapper{
          padding-top:20px;
          padding-bottom:20px;
        }
      }

      @media only screen and (max-width: 480px) {
        .sloganSub span {
          font-size: 20px;
          -webkit-text-stroke: 0.5px white;
        }
        .sloganMain span {
          font-size: 45px;
          -webkit-text-stroke: 1.5px rgb(141, 0, 0);
        }

        .headerIcon{ margin:10px; }

        #loginFormArea { padding: 1em; }
      }
    </style>
</head>
<body>
  <!-- Vue Area -->
  <div id="vueArea">
    <div id="wrapper">

      <div :style="styles.mg1ems" align="right">
        <div class="headerIcon">
          <i class="material-icons fader" title="ログアウトします" @click="doLogout">logout</i>
        </div>
      </div>
      <!-- メッセージエリア -->
      <div id="messageArea" class="fader">
        <slogan>
          <template #sub>
            ていうか おヌシァ 人間か？<br />
            ホモ・サピエンスか？
          </template>
          <template #main>指定文字を打ち込み 証明してみよ</template>
        </slogan>
      </div>

      <!-- 共通クロックエリア -->
      <div id="clockArea" class="fader"><digi-clock /></div>

      <!-- 数値入力フォームエリア -->

      <div id="loginFormArea" class="fader">
        <card-sec>
          <template #title><tag-title><?php echo $contents_name." " ?>指定文字入力フォーム</tag-title></template>
          <template #contents>
            <section style="margin:5px;align-items:center;">
              <h3 id="alphaNumeric" v-text="alphaNumeric" :style="styles.center3ems" style="user-select: none;"></h3>
              <div style="margin:5px">
                <v-text-field label="表示されている文字を入力するのだ" type="text" v-model="inputWord" maxlength="10" />
              </div>
            </section>
            <div align="center">
              <p v-if="judgeInvalid" :style="styles.redPointer" @click="judgeInvalid=false">{{ message.confirmFail }}</p>
              <p v-if="judgeSuccess" style="color:#0082ff;">{{ message.confirmSuccess }}</p>
              <br v-if="judgeInvalid || judgeSuccess" />
              <v-btn :style="palette.brownFront" :disabled="inputWord == ''" @click="confirmJudge">決定</v-btn>
              <v-btn :style="palette.brownBack" :disabled="inputWord == ''" @click="reset_alphaNumerics">リセット</v-btn>
            </div>
          </template>
        </card-sec>
      </div>

    </div>
  </div>
  <!-- Vue Area -->

  <script src="https://cdn.jsdelivr.net/npm/vue@2.x/dist/vue.js"></script>
  <!-- ↓ 非同期通信を実行するために必要 -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.18.0/axios.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.js"></script>
  <script type="module">
    import slogan from '../static/js/VJCP_slogan.js';
    import cardSection from '../static/js/VJCP_cardSection.js';
    import colorPalette from '../static/js/VJCP_colorPalette.js';
    import tagTitle from '../static/js/VJCP_tagTitle.js';
    import digiClock from '../static/js/VJCP_digiClock.js';

      // #vueForCommon内でVue.jsの機能を有効化する
      const login = new Vue({
        el: '#vueArea',
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
            message: {
              confirmSuccess: "よろしい。さあ！人の道を行け！",
              confirmFail: "あァ！？あンだってェェェ！？",
            },
            alphaNumeric: "",
            inputWord: "",
            judgeInvalid: false,
            judgeSuccess: false,
          };
        },
        created: function () {
          this.init();
        },
        methods: {
          // 画面初期表示処理
          async init() {
            this.alphaNumeric = this.generatedChars() + this.generatedQuatNums();
          },
          doLogout() {
            location.href = "./login.php";
          },
          confirmJudge() {
            if(this.inputWord == this.alphaNumeric){
              this.judgeInvalid = false;
              this.judgeSuccess = true;
              // ajax通信実行
              axios.get('../server/api/confirmJudge.php')
                .then(response => {
                  setTimeout(function(){
                    location.href = "./ownMyPage/index.php";
                  }, 2000);
                }).catch(error => alert("通信に失敗しました。"));
            }else{
              this.judgeInvalid = true;
              this.reset_alphaNumerics();
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
            for(let i=0;i<4;i++){
              let num = String(Math.floor(Math.random() * 10));
              nums += num;
            }
            return nums;
          },
          reset_alphaNumerics() {
            this.alphaNumeric = this.generatedChars() + this.generatedQuatNums();
            this.inputWord = "";
          },
        },
      });
  </script>
</body>
</html>