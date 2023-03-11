/**
 * コンポーネント：デジタルクロック
 */

 let digiClock = Vue.component("digi-clock", {
  template: `<div class="container" style="
    display: flex;
    margin-bottom: 10px;
    align-items: center;
    justify-content: center;
    text-align: center;
  ">
    <div class="clock" style="
      color: rgb(255 235 175);
      text-shadow: rgb(10 155 230) 0px 0px 20px;
      line-height: 1.2;
      text-align: center;
    ">
      <p class="clock-date" style="font-size: 20px;"></p>
      <p class="clock-time" style="font-size: 50px;"></p>
    </div>
  </div>`,
  data: function(){
    return {
      // 
    }
  },
  created: function () {
    this.init();
  },
  mounted: function () {
    this.clockMover();
  },
  methods: {
    // 画面初期表示処理
    async init() {
      // 
    },
    clockMover() {
      const clock = () => {
        // 現在の日時・時刻の情報を取得
        const d = new Date();
      
        // 年を取得
        let year = d.getFullYear();
        // 月を取得
        let month = d.getMonth() + 1;
        // 日を取得
        let date = d.getDate();
        // 曜日を取得
        let dayNum = d.getDay();
        const weekday = ["SUN", "MON", "TUE", "WED", "THU", "FRI", "SAT"];
        let day = weekday[dayNum];
        // 時を取得
        let hour = d.getHours();
        // 分を取得
        let min = d.getMinutes();
        // 秒を取得
        let sec = d.getSeconds();
      
        // 1桁の場合は0を足して2桁に
        month = month < 10 ? "0" + month : month;
        date = date < 10 ? "0" + date : date;
        hour = hour < 10 ? "0" + hour : hour;
        min = min < 10 ? "0" + min : min;
        sec = sec < 10 ? "0" + sec : sec;
      
        // 日付・時刻の文字列を作成
        let today = `${year}.${month}.${date} ${day}`;
        let time = `${hour}:${min}:${sec}`;
      
        // 文字列を出力
        document.querySelector(".clock-date").innerText = today;
        document.querySelector(".clock-time").innerText = time;
      };
      
      // 1秒ごとにclock関数を呼び出す
      setInterval(clock, 1000);
    },
  },
});

export default digiClock;
