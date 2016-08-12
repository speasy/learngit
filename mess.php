<?php
	/**
	 * 
	 */
	define('TOKEN',true);
	require 'init.php';
	require './config.php';

	/**
	 * 把用户发送的消息写入数据库
	 **/
	if($_GET['act'] == 'toall') {
		$mes = new MesPModel();
		$data = array(
			'fromer'=>$_POST['user'],
			'messcontent'=>$_POST['content'],
			'reltime'=>date('Y-m-d H:i:s',time()),
		);

		if($mes->addMes($data)) {//TODO 使用队列机制
			echo json_encode(array('mess'=>'true','token'=>1));
		} else {
			echo json_encode(array('mess'=>'false','token'=>0));
		}
		exit;
	}


	if($_GET['act'] == 'getMess') {
		//实例化MesPModel,获取至用户最后登录之后入库的消息
		$username = $_SESSION['username'];
		$mes = new MesPModel();
		if($data = $mes->getMes($username)) {
			echo json_encode(array('mess'=>$data,'token'=>1));//TODO 写入memcached
		} else {
			echo json_encode(array('mess'=>'false','token'=>0));
		}
		exit;
	}
