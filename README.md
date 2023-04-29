# Stu-Do-Schedular
実利8割ネタ2割の、記憶系科目の学習スケジュール管理に便利なウェブアプリです。

# 概要｜Description
　過去の担当業務でVue.jsを使う機会があったので、そこで得た知識をもっと活かすべく、『Linker』に引き続きこのアプリを作りました。<br>
　「エビングハウスの忘却曲線」に則って、覚えておきたい様々なことを、無理なく気軽に記憶して、使える長期記憶にしていくまでの、スケジューリングをサポートするアプリです。<br>
　勉強、仕事、打ち込んでいること・・・目的に応じて奮ってご活用ください。

# インストール方法｜Install
## クローンする場合｜Clone
ターミナルまたはコマンドプロンプトで、こちらを入力して下さい。パッケージをインストールできます。
```
$ git clone https://github.com/Kampanman/Stu-Do-Schedular.git
```
パッケージを別名で保存する場合は、以下のように入力して下さい。
```
$ git clone https://github.com/Kampanman/Stu-Do-Schedular.git [お好きなプロジェクト名]
```
## サーバーへのアップロード｜Upload
クローンが終了しましたら、お使いのサーバーにアップロードして下さい。
尚、製作者が利用しているサーバーはこちらです。
```
https://secure.sakura.ad.jp/rs/cp/
```
## DBコネクション設定｜Database Connection
/Stu-Do-Schedular/server/db.phpにアクセスし、$dsn,$dbname,$username,$passwordのそれぞれを、お使いのデータベースに合わせて編集してください。
## SQL読み込み｜SQL Reading
お使いのMySQLで、/Stu-Do-Schedular/sqlに格納されているsqlファイルをインポートしてください。

# 最近の更新｜Recent updates
- 基本機能搭載完了：2023/03/11 (東日本大震災発生から12年)
- バージョン1.0（β版）GitHub公開開始：2022/03/11
- バージョン2.0（β版）GitHub公開開始：2022/04/30  
  - 操作日よりも前の日の未着手タスク一覧を表示する機能を追加  
  - 登録日から50日以上経過しているタスクを一括削除する機能を追加  

# 環境と使用言語｜Requirement and Language
- フロントフレームワーク：Vue.js 2.x, Vuetify 2.x, JQuery 3.3.1
- サーバー言語：PHP 7.4.30
- サーバー：Apache/2.4.54（さくらレンタルサーバー）
- データベース：MySQL 5.7（さくらレンタルサーバー）

# 文責｜Author
- APT-I.T
