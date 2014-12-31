<?php
header("Content-type: text/html; charset=utf-8");
$logPath = "/doxygen/app_serv.warn";
if(file_exists($logPath)){
    $file_arr = file($logPath);
    echo "<div style='font-size:22px;font-weight:bold;color:green;text-align:center;padding:10px;'>AppServ接口类当中存在注释有问题的地方如下：</div>";
    echo "<div style='font-size:18px;color:green;text-align:left;padding:10px;'>改完错误之后提交svn，然后刷新本页面，如果没有错误的话，错误内容将会从本页面移除。</div>";
    for($i=0;$i<count($file_arr);$i++){//逐行读取文件内容
	//可以忽略掉的警告
	if (stristr($file_arr[$i], "The selected output language") !== false) {
	    continue;
	}
	if (stristr($file_arr[$i], "since release") !== false) {
            continue;
        }
	if (stristr($file_arr[$i], "parameter") !== false) {
	    continue;
	}
        //开始处理警告内容 
        $lineStr = trim($file_arr[$i]);
        if (!empty($lineStr)) {
            $lineStr  = str_replace("/doxygen/","",$lineStr);
	    $lineStr  = str_replace("warning:", "", $lineStr);
            $lineInfo = explode("+++", $lineStr);
	    echo "<div style='font-size:12px;border:solid 1px orange;margin:5px;'>"
		."<table><tr><td style='font-weight:bold;'>错误描述：</td><td style='color:red;'>" . $lineInfo['2'] . "</td></tr>"
		."<tr><td style='font-weight:bold;'>错误文件：</td><td style='color:green;'>" . $lineInfo['0'] . "</td></tr>"
		."<tr><td style='font-weight:bold;'>错误行数：</td><td style='color:green;'>" . $lineInfo['1'] . "</td></tr></table></div>";
	}
    }
}
