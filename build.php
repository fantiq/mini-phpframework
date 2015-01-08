<?php
$file_lists = array();
$path = 'core';
$dir = opendir($path);
while($file = readdir($dir)){
	if($file!='.'&&$file!='..'&&!is_dir($path.DIRECTORY_SEPARATOR.$file)){
		$file_lists[] = $path.DIRECTORY_SEPARATOR.$file;
	}
}
build($file_lists);
function build($file_lists=array(),$file_name='framework.php'){
	$output = '';
	foreach($file_lists as $file){
		$output .= file_get_contents($file);
	}
	$output = preg_replace('/<\?php[\\r|\\n|\\r|\\n]/i', '', $output);
	file_put_contents($file_name,"<?php\r\n".$output) or exit('生成失败');
}
function load($file_lists){
	foreach($file_lists as $file) include $file;
}
