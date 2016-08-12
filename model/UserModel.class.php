<?php
	/**
	* user表操作类
	*/
	defined('TOKEN') || exit('Access refused!');
	class UserModel extends Model {
		protected $tableName = 'user';

		/**
		 *return mixed boolen or double dimension array 
		 */
		public function log($user,$pwd) {
			$fields = array('uid');
			$cond = array(
				'username'=>$user,
				'pwd'=>md5($pwd),
				'status'=>'0',//TODO 可以加上符合index
			);
			return $this->db->find($fields,$cond);
		}

		/**
		 * 用户登录成功更新用户信息
		 **/
		public function updateStatus($user,$status = '1') {
			$time = date('Y-m-d h:i:s',time());
			$lastip = $_SERVER['REMOTE_ADDR'];
			$data = array(
				'status'=>$status,
				'lasttime'=>$time,
				'lastip'=>$lastip,
			);
			$cond = array(
				'username'=>$user,
			);
			return $this->db->update($data,$cond);
		}

		public function getLastTime($username = '') {
			if($rs = $this->db->find(array('lasttime'),array('username'=>$username))) {
				return $rs['lasttime'];
			}
			return false;
		}

		public function getList() {
			return $this->db->select(array('username'),array('status'=>1));
		}

		public function reg($username = '',$pwd = '') {
			return $this->db->add(array('username'=>$username,'pwd'=>md5($pwd)));
		}

		/**
		 * 用户退出
		 **/
		public function unlog($username = '') {
			return $this->db->update(array('status'=>0),array('username'=>$username));
		}

		/**
		 * 验证用户名是否存在
		 **/
		public function verifyUser($username = '')  {
			return $this->db->find(array('uid'),array('username'=>$username));
		}
	}
