<?php
use think\Request;
/*
图片以逗号拆分成数组
 */
function pic_change($data){
	return $data;
	if ($data) {
		return 1;
		foreach ($data as $k => $v) {
			$res = explode(',',$v['picture']);
			return $res;
		}
	}
}
/*
时间转换函数，几小时前几天前
 */
function time_change($time){  
    $t=time()-$time;
    $f=array(  
        '31536000'=>'年',  
        '2592000'=>'个月',  
        '604800'=>'星期',  
        '86400'=>'天',  
        '3600'=>'小时',  
        '60'=>'分钟',  
        '1'=>'秒'  
    );  
    foreach ($f as $k=>$v)    {  
        if (0 !=$c=floor($t/(int)$k)) {  
            return $c.$v.'前';  
        }  
    }  
}
/*
过滤成纯文本用于显示
 */
function clear_all($area_str){ 
    if ($area_str!=''){
        $area_str = trim($area_str); //清除字符串两边的空格
        $area_str = strip_tags($area_str,""); //利用php自带的函数清除html格式
        $area_str = str_replace("&nbsp;","",$area_str);
         
        $area_str = preg_replace("/   /","",$area_str); //使用正则表达式替换内容，如：空格，换行，并将替换为空。
        $area_str = preg_replace("/
/","",$area_str); 
        $area_str = preg_replace("/
/","",$area_str); 
        $area_str = preg_replace("/
/","",$area_str); 
        $area_str = preg_replace("/ /","",$area_str);
        $area_str = preg_replace("/  /","",$area_str);  //匹配html中的空格
        $area_str = trim($area_str); //返回字符串
    }
    return $area_str;
}  



