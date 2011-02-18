<?php

/**
 * 为release运行提供一个接口
 * 根据release参数中的四位版本号提取svn中的文件到release目录
 * 依赖winrar和wget
 */
$release = preg_match('/release=(\d+\.\d+\.\d+\.\d+)/i', $_SERVER['QUERY_STRING'], $arr);

require 'config.php';
/**
 * 考虑单浏览器执行入口，从config提取浏览器ip信息，通过staf启动用例的执行
 * @param $b
 * @param $filter
 * @param $debug
 */
function run($b, $release=false, $debug = false){
	$browser = Config::$BROWSERS[$b];
	$host = $debug ? 'localhost' : $browser[0];
	$path = $debug ? 'C:\\Users\\yangbo\\AppData\\Local\\Google\\Chrome\\Application\\chrome.exe' : $browser[1];

	$url = "http://".$_SERVER['SERVER_ADDR'].":8000".substr($_SERVER['PHP_SELF'], 0, -11)."/list.php?batchrun=true";
	$url .= "^&browser=$b^&mail=true";

	if($release)
	$url .= "^&release=true";
	//	else
	//	$url .= "^&cov=true";

	if($b == 'baidu'){
		$url = "--'$url'";
	}

	require_once 'lib/Staf.php';
	$result = Staf::process_start($path, $url, $host);
	//	print $result;
}

function delDirAndFile( $dirName )
{
	if ( $handle = opendir( "$dirName" ) ) {
		while ( false !== ( $item = readdir( $handle ) ) ) {
			if ( $item != "." && $item != ".." ) {
				if ( is_dir( "$dirName/$item" ) ) {
					delDirAndFile( "$dirName/$item" );
				} else {
					if( unlink( "$dirName/$item" ) )echo "成功删除文件： $dirName/$item<br />\n";
				}
			}
		}
		closedir( $handle );
		if( rmdir( $dirName ) )echo "成功删除目录： $dirName<br />\n";
	}
}

if(array_key_exists('clear', $_GET)){
	print 'debug - clear report';
	require_once 'lib/Staf.php';
	Staf::process('stop all confirm', '10.81.23.218');
	Staf::process('stop all confirm', '10.81.23.219');
	Staf::process('stop all confirm', '10.81.23.220');
	Staf::process('free all', '10.81.23.218');
	Staf::process('free all', '10.81.23.219');
	Staf::process('free all', '10.81.23.220');
	if(file_exists('report'))
	delDirAndFile('report');
}

if(file_exists('report')){
	//	rmdir('report');
	$reports = scandir('report');
	print 'on batch run, please waiting : '. (sizeof($reports)-2);
	return;
}else{
	mkdir('report');
}

/*记录运行时信息*/
$b = array_key_exists('browser', $_GET) ? $_GET['browser'] : 'all';
if($b !='all'){
	run($b, $tag, true);
}else{
	foreach(Config::$BROWSERS as $b=>$i){
		run($b, $tag);
	}
}
?>