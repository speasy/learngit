<?php
	defined('TOKEN') || exit('Access refused!');
	/**
	 * 数据库抽象类
	 */
	abstract class DbModel {
		/**
		* 连接数据库
		* param $host 数据库地址
		* param $user 数据库用户名
		* param $password 密码
		* param $database 库名
		 */
		abstract public function connect($host,$user,$password,$database);

		abstract public function query($sql);

		/**
		* 返回结果集中的一行数据
		*/
		abstract public function find($fields = array(),$cond);

		/**
		* 返回结果集中全部数据
		*/
		abstract public function select($fields = array(),$cond);


		abstract public function close();
	}
