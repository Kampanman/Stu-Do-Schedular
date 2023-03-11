-- phpMyAdmin SQL Dump
-- version 4.9.5
-- https://www.phpmyadmin.net/
--

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE `stu_do_schedules` (
  `char_id` varchar(9) NOT NULL COMMENT '学習タスクのID',
  `task` varchar(50) NOT NULL COMMENT '学習タスクの名称・範囲',
  `first_date` date NOT NULL COMMENT '初回学習日',
  `is_first_done` tinyint(1) NOT NULL DEFAULT '0' COMMENT '初回学習実行',
  `second` int(2) NOT NULL DEFAULT '1' COMMENT '2回目学習',
  `is_second_done` tinyint(1) NOT NULL DEFAULT '0' COMMENT '2回目学習実行',
  `third` int(2) NOT NULL DEFAULT '7' COMMENT '3回目学習',
  `is_third_done` tinyint(1) NOT NULL DEFAULT '0' COMMENT '3回目学習実行',
  `forth` int(2) DEFAULT '15' COMMENT '4回目学習',
  `is_forth_done` tinyint(1) DEFAULT '0' COMMENT '4回目学習実行',
  `fifth` int(2) DEFAULT '30' COMMENT '5回目学習',
  `is_fifth_done` tinyint(1) DEFAULT '0' COMMENT '5回目学習実行',
  `owner_id` int(8) NOT NULL DEFAULT '0' COMMENT '登録したユーザーのID',
  `created` timestamp NOT NULL COMMENT '登録日',
  `is_del_ready` tinyint(1) NOT NULL DEFAULT '0' COMMENT '間もなく削除されるものか'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- テーブルのインデックス `stu_do_schedules`
--
ALTER TABLE `stu_do_schedules`
  ADD UNIQUE KEY `char_id` (`char_id`);
COMMIT;
