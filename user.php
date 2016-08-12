<?php	
	/**
	 * 用户登录和注册
	 */
	define('TOKEN',true);
	require './init.php';

	//防止重复提交和跨站脚本攻击
	$hash = md5(substr(str_shuffle('abcdefghiljkmnopqrstuvwzyx'),10,5).'@#$^^*@');
	$_SESSION['hash'] = $hash;
?>
<html>
	<head>
		<style>
			* {
				margin:0;
				padding:0;
			}
			#container,#reg {
				width:300px;
				height:200px;
				position:absolute;
				left:50%;
				top:50%;
				margin-left:-150px;
				margin-top:-100px;
			}
			#reg {
				display:none;
			}
		</style>
	</head>
	<body>
		<div id="container">
			<table>
				<form action="" method="post" id="loginForm">
				<tr><th colspan="2">登录</th></tr>
				<tr>
					<td>用户名:</td>
					<td><input type="text" name="username" value="" class="username" /></td>
				</tr>
				<tr>
					<td>密码:</td>
					<td><input type="password" name="pwd" value="" class="pwd" /></td>
				</tr>
				<tr><td></td></tr>
				<tr align="center">
					<td colspan="2"><input type="submit" name="sub" value="登录" />&nbsp;&nbsp;<a href="javascript:void(0);" class="reg">注册</a></td>
				</tr>
				<input type="hidden" name="hash" value="<?php echo $_SESSION['hash'];?>" class="hash" />
				</form>
			</table>
		</div>
		<div id="reg">
			<table>
				<form action="" method="post" id="regForm">
				<tr><th colspan="2">注册</th></tr>
				<tr>
					<td>用户名:</td>
					<td><input type="text" name="username" value="" class="username" /></td>
				</tr>
				<span class="note_username"></span>
				<tr>
					<td>密码:</td>
					<td><input type="password" name="pwd" value="" class="pwd" /></td>
				</tr>
				<span class="note_pwd"></span>
				<tr>
					<td>重复密码:</td>
					<td><input type="password" name="repwd" value="" class="repwd" /></td>
				</tr>
				<span class="note_repwd"></span>
				<tr align="center">
					<td colspan="2"><input type="submit" name="sub" value="注册" /></td>
				</tr>
				<input type="hidden" name="hash" value="<?php echo $_SESSION['hash'];?>" class="hash" />
				</form>
			</table>
		</div>
		<script>
			/**
			* 
			* 
			*/
			function $() {
				var len = arguments.length;
				var rs = [];
				for(var i=0;i<len;i ++) {
					rs[arguments[i]] = document.getElementById(arguments[i]);
				}
				return rs;
			}

			var obj = $('container','reg');
			obj.container.getElementsByTagName('a')[0].onclick = function() {
				//console.log(this);//TODO 作为对象的方法调用的
				obj.container.style.display = 'none';
				obj.reg.style.display = 'block';
				return false;
			};


			//ajax 返回注册成功或失败
			function createXML() {
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

			$('regForm').regForm.onsubmit = function() {
				var url = './userpro.php?act=reg';
				var xml  = createXML();
				xml.open('post',url,true);
				xml.onreadystatechange = function() {
					if(xml.readyState === 4 && xml.status === 200) {
						console.log(xml.responseText);
						document.location = './user.php';
					}
				};

				xml.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
				xml.send('username='+document.getElementsByClassName('username')[1].value+'&pwd='+document.getElementsByClassName('pwd')[1].value+'&hash='+document.getElementsByClassName('hash')[1].value);
				 return false;
			}

			$('loginForm').loginForm.onsubmit = submit;
			function submit() {
				var url = './userpro.php?act=login';
				var xml  = createXML();
				xml.open('post',url,true);
				xml.onreadystatechange = function() {
					if(xml.readyState === 4 && xml.status === 200) {
						//判断返回json信息状态 TODO ajax请求时，服务器端连接mysql用时间太长，用户体验不好
						var res = JSON.parse(xml.responseText);
						var flag = res.token;
						if(flag == 1) {
							document.location.href = './index.php';
						} else if(flag == 0) {
							alert(res.mess);//TODO 美化弹出框
						} else if(flag == 2) {
							alert(res.mess);
						}
					}
				};

				xml.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
				xml.send('username='+document.getElementsByClassName('username')[0].value+'&pwd='+document.getElementsByClassName('pwd')[0].value+'&hash='+document.getElementsByClassName('hash')[0].value);
				 return false;
			}

			//根据
			function getInputVal(divContainerId,className) {
				return document.getElementById(divContainerId).getElementsByClassName(className)[0].value;
			}
			//用户名验证(重复+非法字符)
			obj.reg.getElementsByClassName('username')[0].onblur = function() {
				if(this.value == '') {
					document.getElementsByClassName('note_username')[0].textContent = 'username不能为空';
				} else {//非法字符校验+ajax校验
					//TODO 正则表达式非法字符校验
					//ajax 重复验证
					var xml = createXML();
					xml.open('get','./userpro.php?act=verifyUser&user='+this.value+'&r='+Math.random(),true);
					xml.onreadystatechange = function() {
						if(xml.readyState == 4 && xml.status == 200) {
							var data = JSON.parse(xml.responseText);
							if(data.token == 0) {
								document.getElementsByClassName('note_username')[0].textContent = '该username已经存在';
							}
						}
					}
					xml.send(null);
				}
			}

			//按enter键直接发送
			window.document.onkeydown = function(evt) {
				evt = window.event || evt;
				if(evt.keyCode == 13) {
					submit();
					return false;
				}
			}
		</script>
	</body>
</html>
