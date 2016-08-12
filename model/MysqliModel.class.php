<?php
	/**
	 * MySQL数据库操作类
	 */
	final class MysqliModel extends DbModel {
		private $mysqli_link = null;//保存一个mysqli链接
		private $mysqli_rs = null;//保持一个mysqli query的结果集资源 mysqli_rs
		private $config;//数据库配置文件
		public $tableName = ''; //要操作的表名
		static private $ins = null;//保持MySQLi实例

		final private function __construct() {
			require ROOT.'/config.php';//TODO MysqliModel被包含了，因此这里使用不加上ROOT会出错
			$this->config = $config;
			//connect database
			$this->connect($config['host'],$config['user'],$config['pwd'],$config['database']);
		}


		/**
		 * 返回一个单例
		 */
		static public function getIns() {
			if(empty(self::$ins) || !(self::$ins instanceof self)) {
				self::$ins = new self();
			}
			return self::$ins;
		}

		/**
		* 连接数据库
		* param $host 数据库地址
		* param $user 数据库用户名
		* param $password 密码
		* param $database 库名
		 */
		public function connect($host,$user,$password,$database) {
			$this->mysqli_link = new mysqli($host,$user,$password,$database);
		}

		/**
		 * 对于add和update自动执行
		 * param $data array('field1'=>'value1','field2'=>'value2',...)
		 * param $cond array/string(两种形式)
		 *  $cond array  eg:array('fieled1'=>'value1','field2'=>'value2',....)
		 *  $cond string "where field1=value1 and field2=value2 and ...."
		 * 最终拼接结果:
		 * 对于增加:insert to tableName(field1,field2,...) values(value1,value2,...)
		 * 对于修改:update tableName set field1=value1,field2=value2,...  where field1=value1 and field2=value2 and ....
		 */
		public function execute($data = array(),$cond = null) {
			//若没有给字段，直接返回false
			if(empty($data)) {
				return false;
			}

			$sql = '';
			if(empty($cond)) {//若$cond为'',则表示为add
				$sql .= 'insert into '.$this->tableName.'(';
				$vals = ' values(';
				foreach($data as $k=>$v) {
					$sql .= $k.',';
					$vals .= "'$v'".',';
				}
				$sql = substr($sql,0,-1).')'.' '.substr($vals,0,-1).')';
			} else {//update
				$sql = 'update '.$this->tableName.' set ';
				foreach($data as $k=>$v) {
					$sql .= $k.'='."'$v'".',';
				}
				$sql = substr($sql,0,-1);
				if(is_array($cond)) {//条件写成array形式，主要是相等
					$sql .= ' where ';
					foreach($cond as $k=>$v) {
						$sql .= $k.'='."'$v'".' and ';
					}
					$sql = substr($sql,0,strrpos($sql,' and'));
				} else {//条件直接写成字符串的形式
					$sql .= ' '.$cond;
				}
			}
			$this->query("set names {$this->config['character']}");
			return $this->query($sql);
		}

		/**
		 * param $data array('field1'=>'value1','field2'=>'value2',...)
		 * 最终拼接结果:
		 * 对于增加:insert to tableName(field1,field2,...) values(value1,value2,...)
		 */
		public function add($data = array()) {
			$this->execute($data);
			return $this->isSucess();
		}

		/**
		 * param $data array array('field1'=>'value1','field2'=>'value2',...)
		 * param $cond array/string
		 * $cond array array('fieled1'=>'value1','field2'=>'value2',....)
		 * $cond string "where field1=value1 and field2=value2 ..."(写出字符串形式的条件主要用于一些不常见的条件表达式)
		 * 最终拼接结果:
		 * 对于修改:update tableName set field1=value1,field2=value2,...  where field1=value1 and field2=value2 and ....
		 */
		public function update($data = array(),$cond) {
			$this->execute($data,$cond);
			return $this->isSucess();
		}

		/**
		* param $cond array
		* eg:array('fieled1'=>'value1','field2'=>'value2',....)
		* 最终拼接结果:
		* delete from tableName where field1=value1 and field2=value2 and ....
		 */
		public function del($cond = array()) {
			$sql = 'delete from '.$this->tableName.' where ';
			foreach($cond as $k=>$v) {
				$sql .= $k.'='.$v.' and ';
			}
			$sql = substr($sql,0,strpos($sql,' and'));
			$this->query($sql);
			return $this->isSucess();
		}


		/**
		* 执行sql语句
		* return false，mysqli_result object,true
		 */
		public function query($sql) {
			//调用LogModel类进行记录sql语句 TODO 大并发时如何搞？？
			$ins = LogTool::getIns();
			$ins->log($sql);
			//return $this->mysqli_rs = $this->mysqli_link->query($sql);
			return $this->mysqli_link->query($sql);//NOTE 简单的执行，其不一定返回一个mysqli_rs对象
		}

		/**
		 * 返回结果集中的一行数据
		 * param $fields array('field1','field2',...)
		 * param $cond array/string
		 * array array('field1'=>'value1','field2'=>'value2',...)
		 * string "where field1='value1' and field2='value2' and .."
		 */
		public function find($fields = array(),$cond) {
			$sql = 'select ';
			foreach($fields as $v) {
				$sql .= $v.',';
			}
			$sql = substr($sql,0,-1).' from '.$this->tableName;
			if(is_array($cond)) {//如果$cond为数组，进行拼接，主要是等于条件
				$sql .= ' where ';
				foreach($cond as $k=>$v) {
					$sql .= $k.'='."'$v' and ";
				}
				$sql = substr($sql,0,strpos($sql,' and'));
			} else {// 对于>,<,>=,<=,in,between and等条件直接传入条件字符串
				$sql .= ' '.$cond;
			}

			$this->query("set names {$this->config['character']}");
			$this->mysqli_rs = $this->query($sql);
			if($this->mysqli_link && $this->mysqli_link->affected_rows > 0) {
				return $this->mysqli_rs->fetch_assoc();
			}
			return false;
		}

		/**
		 * 返回结果集中全部数据
		 * param $fields array('field1','field2',...)
		 * param $cond array/string
		 * array array('field1'=>'value1','field2'=>'value2',...)
		 * string "where field1='value1' and field2='value2' and .."
		 */
		public function select($fields = array(),$cond) {
			$sql = 'select ';
			foreach($fields as $v) {
				$sql .= $v.',';
			}
			$sql = substr($sql,0,-1).' from '.$this->tableName;
			if(is_array($cond)) {//如果$cond为数组，进行拼接，主要是等于条件
				$sql .= ' where ';
				foreach($cond as $k=>$v) {
					$sql .= $k.'='."'$v' and ";
				}
				$sql = substr($sql,0,strpos($sql,' and'));
			} else {// 对于>,<,>=,<=,in,between and等条件直接传入条件字符串
				$sql .= ' '.$cond;
			}

			$this->query("set names {$this->config['character']}");
			$this->mysqli_rs = $this->query($sql);
			if($this->mysqli_link && $this->mysqli_link->affected_rows > 0) {
				return $this->mysqli_rs->fetch_all(MYSQLI_ASSOC);
			}
			return false;
		}

		private function isSucess() {
			if($this->mysqli_link && $this->mysqli_link->affected_rows > 0) {
				return true;
			}
			return false;
		}

		public function close() {
			$this->mysqli_link->close();
		}


		public function __destruct() {
			$this->close();
		}


		public function __clone() {
			trigger_error('Prohibit Clone,this is single mysqli class.');
		}
	}
