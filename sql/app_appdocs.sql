-- phpMyAdmin SQL Dump
-- version 3.3.8.1
-- http://www.phpmyadmin.net
--
-- 主机: w.rdc.sae.sina.com.cn:3307
-- 生成日期: 2016 年 09 月 06 日 14:53
-- 服务器版本: 5.6.23
-- PHP 版本: 5.3.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- 数据库: `app_appdocs`
--

-- --------------------------------------------------------

--
-- 表的结构 `HM_Files`
--

CREATE TABLE IF NOT EXISTS `HM_Files` (
  `RecordID_` int(6) unsigned NOT NULL,
  `FileName_` varchar(255) NOT NULL,
  `Remark_` varchar(255) DEFAULT NULL,
  `AppUser_` varchar(10) NOT NULL,
  `AppDate_` date NOT NULL,
  `UpdateKey_` varchar(36) NOT NULL DEFAULT 'UUID()',
  PRIMARY KEY (`RecordID_`,`FileName_`),
  UNIQUE KEY `UpdateKey_` (`UpdateKey_`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `HM_Like`
--

CREATE TABLE IF NOT EXISTS `HM_Like` (
  `RecordID_` int(6) unsigned NOT NULL,
  `It_` int(4) unsigned NOT NULL,
  `LikeID_` int(6) unsigned NOT NULL,
  `AppUser_` varchar(10) NOT NULL,
  `AppDate_` date DEFAULT NULL,
  `UpdateKey_` varchar(36) NOT NULL DEFAULT 'UUID()',
  PRIMARY KEY (`RecordID_`,`It_`),
  UNIQUE KEY `UpdateKey_` (`UpdateKey_`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `HM_Menus`
--

CREATE TABLE IF NOT EXISTS `HM_Menus` (
  `Code_` varchar(30) NOT NULL,
  `RecordID_` int(11) NOT NULL,
  `AppUser_` varchar(10) NOT NULL,
  `AppDate_` datetime NOT NULL,
  `UpdateKey_` varchar(36) NOT NULL,
  PRIMARY KEY (`Code_`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `HM_Record`
--

CREATE TABLE IF NOT EXISTS `HM_Record` (
  `ParentID_` int(6) unsigned DEFAULT NULL,
  `ID_` int(6) unsigned NOT NULL,
  `Subject_` varchar(80) NOT NULL,
  `Body_` text,
  `IndexFile_` varchar(255) DEFAULT NULL,
  `Type_` tinyint(1) unsigned NOT NULL,
  `Final_` tinyint(1) unsigned NOT NULL,
  `UpdateUser_` varchar(10) NOT NULL,
  `UpdateDate_` datetime NOT NULL,
  `AppUser_` varchar(10) NOT NULL,
  `AppDate_` datetime NOT NULL,
  `UpdateKey_` varchar(48) NOT NULL,
  PRIMARY KEY (`ID_`),
  UNIQUE KEY `UpdateKey_` (`UpdateKey_`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `HM_Values`
--

CREATE TABLE IF NOT EXISTS `HM_Values` (
  `RecordID_` int(6) unsigned NOT NULL,
  `Value_` int(4) unsigned NOT NULL,
  `Remark_` varchar(255) DEFAULT NULL,
  `Address_` varchar(20) NOT NULL,
  `AppUser_` varchar(10) NOT NULL,
  `AppDate_` datetime NOT NULL,
  `UpdateKey_` varchar(36) NOT NULL DEFAULT 'UUID()',
  UNIQUE KEY `UpdateKey_` (`UpdateKey_`),
  KEY `RecordID_` (`RecordID_`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `WF_Dept`
--

CREATE TABLE IF NOT EXISTS `WF_Dept` (
  `CorpCode_` varchar(10) NOT NULL,
  `ParentCode_` varchar(10) NOT NULL,
  `Code_` varchar(30) NOT NULL,
  `Class_` tinyint(1) NOT NULL DEFAULT '1' COMMENT '部门=>1,群组=>2',
  `Name_` varchar(30) NOT NULL,
  `Level_` int(11) NOT NULL,
  `UpdateUser_` varchar(30) NOT NULL,
  `UpdateDate_` datetime NOT NULL,
  `AppUser_` varchar(30) NOT NULL,
  `AppDate_` datetime NOT NULL,
  `UpdateKey_` varchar(36) NOT NULL,
  `Remark_` varchar(300) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='部门表';

-- --------------------------------------------------------

--
-- 表的结构 `WF_UserInfo`
--

CREATE TABLE IF NOT EXISTS `WF_UserInfo` (
  `UserCode_` varchar(30) NOT NULL COMMENT '用户编号,员工编号',
  `CorpCode_` varchar(10) NOT NULL COMMENT '关联企业表的ID,对应所属企业',
  `DeptCode_` varchar(15) NOT NULL,
  `UserName_` varchar(30) NOT NULL COMMENT '用户姓名',
  `DeptName_` varchar(30) NOT NULL,
  `UserPasswd_` varchar(32) NOT NULL,
  `QQ_` varchar(50) NOT NULL COMMENT '用户QQ',
  `EmailUse_` tinyint(1) NOT NULL DEFAULT '0',
  `Email_` varchar(50) DEFAULT NULL,
  `SMSUse_` tinyint(1) NOT NULL DEFAULT '0',
  `SMSNo_` varchar(20) DEFAULT NULL,
  `Level_` tinyint(1) NOT NULL DEFAULT '2' COMMENT '0=>超级用户, 1=>企业管理员, 2=>普通用户',
  `LoginTime_` datetime NOT NULL,
  `Image_` blob NOT NULL,
  `Remark_` varchar(255) NOT NULL COMMENT '用户介绍说明',
  `Enabled_` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否启用该用户,0=>不启用,1=>已启用 未启用之用户，不得登入到系统中',
  `UpdateUser_` varchar(30) NOT NULL COMMENT '最近更新用户',
  `UpdateDate_` datetime NOT NULL COMMENT '最近更新时间',
  `AppUser_` varchar(30) NOT NULL COMMENT '创建人用户ID',
  `AppDate_` datetime NOT NULL COMMENT '创建文档的时间',
  `UpdateKey_` char(36) NOT NULL COMMENT '更新标识 ',
  PRIMARY KEY (`UserCode_`,`CorpCode_`),
  UNIQUE KEY `UpdateKey_` (`UpdateKey_`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户讯息表';
