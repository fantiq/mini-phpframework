<html lang='zh-cn'>
	<head>
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8;">
		<title></title>
		<style type="text/css">
			.main{
				width: 98%;
				margin: 20px auto;
			}
			.brig{
				color: red;
				font-weight: 800;
				width: 88px;
				display: inline-block;
				text-align: right;
			}
			.content{
				background: #EEE;
				color: red;
				padding: 20px;
				border-radius: 1px dashed #AAA;
			}
			.main ul{
				list-style: none;
			}
			.main ul li{
				line-height: 40px;
			}
		</style>
	</head>
	<div class='main'>
		<h3 style='border-bottom:1px solid #AAA;'>执行错误</h3>
		<ul>
			<li><span class='brig'>错&nbsp;误&nbsp;码：</span><?php echo $e->getCode();?></li>
			<li><span class='brig'>错误文件：</span><?php echo $e->getFile();?></li>
			<li><span class='brig'>错&nbsp;误&nbsp;行：</span><?php echo $e->getLine();?></li>
		</ul>
		<div class='content'>
			<?php echo $e->getMessage();?>
		</div>
	</div>
</html>