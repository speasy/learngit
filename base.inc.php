<?php
	/**
	 * 基础函数
	 */
	//对数组进行递归转义
	function _addslashes($arr = array()) {
		if(get_magic_quotes_gpc()) {
			return $arr;
		}
		if(is_array($arr)) {
			foreach($arr as $k=>$v) {
				if(is_array($v)) {
					$arr[$k] = _addslashes($v);
				} else {
					$arr[$k] = addslashes($v);
				}
			}
		}
		return $arr;
	}

	$_GET = _addslashes($_GET);
	$_POST = _addslashes($_POST);
	$_COOKIE = _addslashes($_COOKIE);
