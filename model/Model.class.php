<?php
	/**
	 * 所有数据库操作的基类，都要继承此基类
	 */
	defined('TOKEN') || exit('Access refused!');
	class Model {
		protected $db = null;//保持数据库操作单例
		public function __construct() {
			$this->db = MysqliModel::getIns();
			/**
			 * 修改单例$this->db中的tableName,$this->db->tableName
			 **/
			$this->db->tableName = $this->tableName;//TODO NOTE 对于单例模式如何对其属性进行修改
		}
	}
