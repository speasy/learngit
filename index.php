<?php
	/**
	 * 聊天页面
	 */
	define('TOKEN',true);
	require './init.php';

	if(empty($_SESSION['isLog'])) {//若用户未登录
		//TODO include './user.php'; 不要使用include，应该使用header跳转
		$url = './user.php';
		header('Location:'.$url);
	}
?>
<html>
	<head>
		<title>ajax聊天室</title>
		<style>
			.online {
				height:300px;
				width:200px;
				border:1px solid black;
				overflow-x:auto;
				overflow-y:auto;
			}
			.content {
				width:500px;
				height:300px;
				border:1px solid black;
				overflow-x:auto;
				overflow-y:auto;
			}
			.response {
				width:700px;
				height:100px;
			}
		</style>
	</head>
	<body>
		<h1>ajax在线聊天室 用户:<?php echo $_SESSION['username'];?></h1>
		<a href="javascript:void(0);" id="unlog">退出</a><br />
			<!-- TODO textarea 改为iframe -->
			<iframe class="online" name="online" ></iframe>
			<iframe class="content" name="content" ></iframe>
<!--
			<textarea class="online">在线列表随时更新，@功能,多终端消息推送,@用户信息，及时推送到相应用户</textarea>
			<textarea name="content" class="content">谁进入，退出</textarea><br />
-->
		<br />
		<form method="get" id="im">
<!--
对说有人回复，对单个人@功能回复
-->
			<textarea name="response" class="response" >@neteasy</textarea><br />
			<input type="submit" name="sub" value="回复" />&nbsp;按回车键（Enter）直接发送
		</form>
		<script>
			//通过id获取元素node
			function $(id) {
				return document.getElementById(id);
			}

			//ajax 返回注册成功或失败
			function createXML() {//TODO ajax请求会带过去cookie信息
				var xhr;
				if(window.XMLHttpRequest) {
					xhr = new XMLHttpRequest();
				} else if(window.ActionXObject) {
					xhr = new ActionXObject('Microsoft.XMLHTTP');
				} else {
					alert('no xhr');
				}
				return xhr;
			}

			$('unlog').onclick = function() {
				var xml = createXML();
				var url = './userpro.php?act=unlog&user=<?php echo $_SESSION['username'];?>';
				xml.open('get',url);
				xml.onreadystatechange = function() {
					if(xml.readyState === 4 && xml.status === 200) {
						var mes = JSON.parse(xml.responseText);
						var flag = mes.token;
						if(flag == 1) {
							alert(mes.mess);
							document.location.href = './user.php';
						} else if(flag == 2) {
							alert('非法操作');
						}
					}
				}
				xml.send(null);
			};

			//json编码的字符串：str
			function comMess(str) {
				var o = JSON.parse(str),rs = '<ul>';
				for(var i in o) {
					rs  += '<li>'+o[i]['username'] + '  在线</li>';
				}
				return rs+'</ul>';
			}

			//ajax获取在线人数信息
			function getList() {
				var xml = createXML();
				var url = './userpro.php?act=getList';
				xml.open('get',url);
				xml.onreadystatechange = function() {
					if(xml.readyState === 4 && xml.status === 200) {
						document.getElementsByName('online')[0].contentWindow.document.getElementsByTagName('body')[0].innerHTML =  comMess(xml.responseText);
					}
				}
				xml.send(null);
			}

			getList();
			setInterval(getList,60000);//每60秒进行一次请求

			//把发言框中的文字写入公屏
			$('im').onsubmit = send;
			function send() {
				var that = $('im');
				if(that.children[0].value == '') {//若输入框内容为空，提示
					that.children[0].value = '输入内容...';
					that.children[0].style.color = 'red';
					setTimeout(function() {that.children[0].value = '';that.children[0].style.color = 'black';},100);
					return false;
				}

				//ajax 把消息写入库
				var xml = createXML(),data = '';
				xml.open('post','./mess.php?act=toall',true);
				xml.onreadystatechange = function() {
					if(xml.readyState === 4 && xml.status === 200) {
						if(JSON.parse(xml.responseText).token == 0) {
							that.children[0].value = ':(消息发送失败';
						}
					}
				}
				xml.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
				data = 'user=<?php echo $_SESSION['username'];?>&'+'content='+that.children[0].value;//TODO 乱码
				xml.send(data);

				//显示到公屏
				document.getElementsByName('content')[0].contentWindow.document.getElementsByTagName('body')[0].innerHTML += '你说: '+that.children[0].value + '<br />';
				//回复框内容清空
				that.children[0].value = '';

				return false;
			}

			//按enter键直接发送
			window.document.onkeydown = function(evt) {
				evt = window.event || evt;
				if(evt.keyCode == 13) {
					send();
					return false;
				}
			}


			//定时获取聊天室消息
			setInterval(function() {
				var xml = createXML();
				xml.open('get','./mess.php?act=getMess',true);
				xml.onreadystatechange = function() {
					if(xml.readyState == 4 && xml.status == 200) {
						//把返回的消息写入公屏
						var tmp = JSON.parse(xml.responseText);
						if(tmp.token == 1) {
							writeToPublic(tmp.mess);
						}
					}
				}
				xml.send(null);
			},2000);

			//把消息写入公屏
			function writeToPublic(obj) {
				var rs = '';
				for(var i in obj) {
					if(obj[i]['fromer'] == '<?php echo $_SESSION['username'];?>')
						rs += '你说:' + obj[i]['messcontent'] + '<br />';
					else
						rs += '【'+obj[i]['fromer'] + '】说:' + obj[i]['messcontent'] + '<br />';
				}
				document.getElementsByName('content')[0].contentWindow.document.getElementsByTagName('body')[0].innerHTML += rs;
			}

			function doShowAtList() {
			}

			//艾特@功能开发
			document.getElementsByName('response')[0].addEventListener('input',function(event) {  //监听input事件
				var target = event.target,cursor = target.selectionStart;  //通过 selectionStart 获得光标所在位置
				if(target.value.charAt(cursor-1)==='@') {  //判断光标前的字符是不是'@'
				  doShowAtList(function(name){  //打开用户列表
					var end = cursor + name.length;  //原光标所在位置加用户名长度为end所在位置
					target.setRangeText(  //通过 setRangeText 接口将需要的值设置到输入框里
					  name,cursor,end,'end'  //将光标定位在文本最后
					  );
				  })
				}
			  }
			);
		</script>
	</body>
</html>
