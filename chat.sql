CREATE TABLE `user` (
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(20) NOT NULL,
  `pwd` char(32) NOT NULL,
  `status` char(1) NOT NULL DEFAULT '0',
  `lastip` varchar(15) NOT NULL DEFAULT '000.000.000.000',
  `lasttime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`uid`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 |

create table mes_private (
	mid int unsigned not null auto_increment primary key,
	fromer varchar(20) not null,
	toer varchar(20) not null,
	reltime  datetime not null default '0000-00-00 00:00:00',
	isread char(1) not null default '0', #0表示未读，1表示已读
	index(fromer,toer)
)engine myisam charset utf8;

#可以对mes_pulbic进行分表
#可以对mes_priv进行加复合索引
#垂直分表，水平分表
#read memcached做缓冲
#write queue
#单例模式改进 mysqli
#mess.php 只要访问 传入一个act就可以获取数据，安全问题
#如何保证单一终端登录 能否用位图
#多角色登录

mid
from
to
messcontent
reltime
