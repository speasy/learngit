<?php
	/**
	 * 初始化文件
	 */
	defined('TOKEN') || exit('Access refused!');
	//开启session
	session_start();
	header('Content-Type:text/html;charset=utf-8');
	//包含基础库文件
	require './base.inc.php';

	//定义网站根目录,最后面没有 '/'
	define('ROOT',str_replace('\\','/',dirname(__FILE__)));


	//自动加载类和接口
	spl_autoload_register(function($className) {
		if(strtolower(substr($className,-5)) === 'model') {//若为Model文件
			spl_autoload(ROOT.'/model/'.$className,'.class.php');
		} else if(strtolower(substr($className,-4)) === 'tool') {//若为Tool文件
			spl_autoload(ROOT.'/tool/'.$className,'.class.php');
		}
	},true,false);
