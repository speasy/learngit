<?php
	/**
	* mes_public表操作类
	*/
	defined('TOKEN') || exit('Access refused!');
	class MesPModel extends Model {
		protected $tableName = 'mes_public';

		/**
		 * 返回从用户登录那个时刻未读的信息
		 */
		public function getMes($username = '') {
			/*
			$user = new UserModel();
			$lasttime = $user->getLastTime($username);

			$this->db->tableName = $this->tableName;//todo 如何解释，此时对象池中的其他方法有没有改变？？？
			 */

			$time = date('Y-m-d h:i:s',time());
			/**
			 * 只要mysql中mes_public中信息，被读一次，就缓冲进memcached，memcached的键如何设计，这么大的并发量
			 */
			$rs = $this->db->select(array('mid','fromer','messcontent','reltime'),"where isread='0' and reltime>='".$time."' order by mid asc");

			//把结果缓存进memcached

			//把当前时间
			$cond = 'where mid in(';
			if(!empty($rs)) {
				foreach($rs as $v) {
					$cond .= $v['mid'].',';
				}
				$cond = substr($cond,0,-1).')';
				$data = array(
					'isread'=>'1',
				);
				$this->updateMes($data,$cond);
			}
			return $rs;
		}


		//TODO MysqliModel UserModel 一个比一个具体
		public function updateMes($data = array(),$cond) {
			return $this->db->update($data,$cond);
		}


		/**
		 * 向mes_public添加消息
		 **/
		public function addMes($data = array()) {
			return $this->db->add($data);
		}
	}
