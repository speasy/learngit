<?php
	define('TOKEN',true);
	require './init.php';
	require './config.php';


	if($_GET['act'] == 'reg') {
		if($_POST['hash'] !== $_SESSION['hash']) {
			echo json_encode(array('mess'=>'非法操作','token'=>2));
			$_SESSION['hash'] = null;
			exit;
		}

		$user = new UserModel();
		if($user->reg($_POST['username'],$_POST['pwd'])) {
			echo json_encode(array('mess'=>'注册成功','token'=>1));
		} else {
			echo json_encode(array('mess'=>'注册失败','token'=>0));
		}
	}


	if($_GET['act'] == 'login') {
		if($_POST['hash'] !== $_SESSION['hash']) {
			echo json_encode(array('mess'=>'非法操作','token'=>2));
			$_SESSION['hash'] = null;
			exit;
		}

		$user = new UserModel();
		if($user->log($_POST['username'],$_POST['pwd'])) {
			$_SESSION['isLog'] = true;//TODO isLog这段简单，加密处理
			$_SESSION['username'] = $_POST['username'];
			$user->updateStatus($_SESSION['username']);//TODO 如何保证单一终端
			echo json_encode(array('mess'=>'登录成功','token'=>1));
		} else {
			echo json_encode(array('mess'=>'登录失败或用户已经登录','token'=>0));
		}
	}


	if($_GET['act'] == 'unlog') {
		//防止退出其他人账号
		if($_GET['user'] !== $_SESSION['username']) {
			echo json_encode(array('mess'=>'非法操作','token'=>2));
			exit;
		}

		$user = new UserModel();
		$username = $_SESSION['username'];

		$_SESSION = array();
		if(ini_get('session.use_cookies')) {
			$params = session_get_cookie_params();
			//删除客户端cookie保存的session_id
			setcookie(session_name(),'',time()-1,$params['path'],$params['domain'],$params['secure'],$params['httponly']);
		}
		session_destroy();

		$user->unlog($username);
		echo json_encode(array('mess'=>'再见','token'=>1));
		exit;
	}


	if($_GET['act'] == 'verifyUser') {
		$username = $_GET['user'];
		$user = new UserModel();
		if($user->verifyUser($username)) {
			echo json_encode(array('token'=>0));
		} else {
			echo json_encode(array('token'=>1));
		}
		exit;
	}

	if($_GET['act'] == 'getList') {
		//若用户没有登录直接进行请求直接die
		if(empty($_SESSION['isLog'])) {
			exit;
		}

		$user = new UserModel();
		if($rs = $user->getList()) {
			echo json_encode($rs);
		}
		exit;
	}
