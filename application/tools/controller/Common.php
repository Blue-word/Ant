<?php
namespace app\tools\controller;

class Common extends Controller{  
/**
 *
 *
 * @access  public
 * @param
 * @return  void
 */
function make_json_result($content, $message='', $append=array())
{
    make_json_response($content, 0, $message, $append);
}

/**
 * 创建一个JSON格式的错误信息
 *
 * @access  public
 * @param   string  $msg
 * @return  void
 */
function make_json_error($msg)
{
    make_json_response('', 1, $msg);
}

/**
 * 创建一个JSON格式的数据
 *
 * @access  public
 * @param   string      $content
 * @param   integer     $error
 * @param   string      $message
 * @param   array       $append
 * @return  void
 */
function make_json_response($content='', $error="0", $message='', $append=array())
{
    include_once(COMMON_PATH.'Util/Json.class.php');
    $json = new JSON;
    $res = array('error' => $error, 'message' => $message, 'content' => $content);
    if (!empty($append))
    {
        foreach ($append AS $key => $val)
        {
            $res[$key] = $val;
        }
    }
    $val = $json->encode($res);
    exit(print($val));
}
/**
 * 页面上调用的js文件
 *
 * @access public
 * @param string $files
 * @return void
 */
function smarty_insert_scripts($args) {
    static $scripts = array();
    $arr = explode(',', str_replace(' ', '', $args['files']));
    $str = '';
    foreach ($arr AS $val) {
        if (in_array($val, $scripts) == false) {
            $scripts[] = $val;
            $str .= '<script type="text/javascript" src="'.__ROOT__.'/Public/Js/' . $val . '"></script>';
        }
    }
    return $str;
}
/**
 * 页面上调用的css文件
 *
 * @access public
 * @param string $files
 * @return void
 */
function smarty_insert_css($args) {
    static $css = array();
    $arr = explode(',', str_replace(' ', '', $args['files']));
    $str = '';
    foreach ($arr AS $val) {
        if (in_array($val, $css) == false) {
            $css[] = $val;
            $str .= '<link href="'.__ROOT__.'/Public/Css/'.$val.'" rel="stylesheet" type="text/css" />';
        }
    }
    return $str;
}
/**
 * 创建分页的列表
 *
 * @access public
 * @param integer $count
 * @return string
 */
function smarty_create_pages($params) {
    extract($params);
    $str = '';
    $len = 10;
    if (empty($page)) {
        $page = 1;
    }
    if (!empty($count)) {
        $step = 1;
        $str .= "<option value='1'>1</option>";
        for ($i = 2; $i < $count; $i += $step) {
            $step = ($i >= $page + $len - 1 || $i <= $page - $len + 1) ? $len : 1;
            $str .= "<option value='$i'";
            $str .= $page == $i ? " selected='true'" : '';
            $str .= ">$i</option>";
        }
        if ($count > 1) {
            $str .= "<option value='$count'";
            $str .= $page == $count ? " selected='true'" : '';
            $str .= ">$count</option>";
        }
    }
    return $str;
}
/**
 * 分页的信息加入条件的数组
 *
 * @access  public
 * @return  array
 * @modify root 2013-12-6 10:05:06 每页最大显示数量改为2000
 */
function page_and_size($filter)
{
    $filter['page'] = empty($_REQUEST['page']) || (intval($_REQUEST['page']) <= 0) ? 1 : intval($_REQUEST['page']);

    if (isset($_REQUEST['page_size']) && intval($_REQUEST['page_size']) > 0)
    {
        $filter['page_size'] = intval($_REQUEST['page_size']);
    }
    elseif (isset($_COOKIE['SITE']['page_size']) && intval($_COOKIE['SITE']['page_size']) > 0)
    {
        $filter['page_size'] = intval($_COOKIE['SITE']['page_size']);
    }
    else
    {
        $filter['page_size'] = 30;
    }
    if($filter['page_size']>10000){//判断每页的显示数量，如果超过2000，则显示2000条
        $filter['page_size']=10000;
    }
    return $filter;
}
/*
 * 数组 排序
 * @access public
 * @author root 2014-1-6 18:47:41
 */
function arr_sort($sort_by,$sort_order,&$row,$page = 0,$page_size = 50){
    foreach($row as $k=>$v){
        $temp1[$k]=$v[$sort_by];
    }
    if(strtolower($sort_order)=='desc'){
        array_multisort($temp1,SORT_DESC,$row);
    }else{
        array_multisort($temp1,SORT_ASC,$row);
    }
    $row = array_slice($row,$page,$page_size);
}
/*
 * code39校验字符串是否正确
 * @access public
 * @author 何洋 2015-1-16 19:35:31
 */
function jiaoyan_code39($arr){
    
    $str = strtoupper($arr);//转换成大写
    $allow_word = array('0'=>'0','1'=>'1','2'=>'2','3'=>'3','4'=>'4','5'=>'5','6'=>'6','7'=>'7','8'=>'8','9'=>'9','A'=>'10','B'=>'11','C'=>'12','D'=>'13','E'=>'14','F'=>'15','G'=>'16','H'=>'17','I'=>'18','J'=>'19','K'=>'20','L'=>'21','M'=>'22','N'=>'23','O'=>'24','P'=>'25','Q'=>'26','R'=>'27','S'=>'28','T'=>'29','U'=>'30','V'=>'31','W'=>'32','X'=>'33','Y'=>'34','Z'=>'35','-'=>'36','.'=>'37',' '=>'38','$'=>'39','/'=>'40','+'=>'41','%'=>'42');
    
    $out = array();
    preg_match_all ( "/^(.+)(.){1}$/i", $str, $out, PREG_PATTERN_ORDER );//分隔字符串和校验位
    
    $jiaoyan_arr = str_split($out[1][0]);//字符串转换成数组
    $jiaoyan_num = 0;
    foreach($jiaoyan_arr as $key => $val){
        $jiaoyan_num += $allow_word[$val];
    }
    $check_word_arr = array_flip($allow_word);
    $check_word = $check_word_arr[($jiaoyan_num%43)];//校验位
    
    if(strcmp($check_word, $out[2][0]) == 0) {//校验位比对
        return true;
    }else{
        return false;
    }
}
/**
 * 将匹配/[^a-zA-Z0-9']+/的项替换为-
 * @access public
 * @param $filename 产品名称
 * @return string
 * @author root 2015-1-29 14:51:40
 * @modify 胡林霄 2016-09-09 17:33:34 转义单双引号
 */
function profilename($filename) {
    $filename = preg_replace("/[^a-zA-Z0-9']+/", '-', $filename);
    $filename = addslashes($filename);
    return $filename;
}

/**
 * 实例化图像类
 * @author root 2015-8-5 09:41:05
 */
function image(){
    import("Common.Util.Image");
    return new \image();
}

/**
 * 实例化验证码类
 * @author root 2015-8-5 09:41:05
 */
function captcha(){
    import("Common.Util.Captcha");
    return new \captcha('Public/Images/Captcha/');
}

/**
 * 实例化session类
 * @author root 2015-8-5 17:43:45
 */
function session_memcache(){
    import("Common.Util.Session");
    return new \cls_session(M(), 'rs_sessions', 'SITE_ID');;
}

/**
 * 事务提交
 * @access public
 * @param mixed $db 数据库对象
 * @param mixed $arr 事务中的执行结果
 * @return bool
 */
function db_commit($arr,$db=''){
    $rollback = false;
    if(!is_object($db)){    //如果没传数据库对象，则用M实例对象。
        $db = M();
    }
    if(empty($arr)){
        $db->rollback();    //回滚
        return false;   //没验证数据就不执行提交，进行回滚。
    }
    foreach($arr as $val){
        if($val === false){ //事务中只要一个不成功，则回滚。
            $rollback = true;
            break;
        }
    }
    if($rollback){
        $db->rollback();    //回滚
        return false;   
    }else{
        $db->commit();  //事务提交
        return true;
    }
}

/**
 * 清除指定后缀的模板缓存或编译文件
 * @access public
 * @param bool $is_cache 是否清除缓存还是清出编译文件
 * @param string $ext 文件后缀
 * @return int 返回清除的文件个数
 * @author root 2015-3-23 14:12:54
 */
function clear_tpl_files($is_cache = true, $ext = '') {
    $dirs = array();
    if ($is_cache) {
        $dirs[] = ROOT_PATH .''. APP_PATH . 'Runtime/Temp/';
    } else {
        $dirs[] = ROOT_PATH .''. APP_PATH . 'Runtime/Cache/Home/';
    }
    $str_len = strlen($ext);
    $count = 0;
    foreach ($dirs AS $dir) {
        $folder = @opendir($dir);
        if ($folder == false) {
            continue;
        } while ($file = readdir($folder)) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            if (is_file($dir . $file)) {
                /**
                 * 如果有后缀判断后缀是否匹配
                 */
                if ($str_len > 0) {
                    $ext_str = substr($file, - $str_len);
                    if ($ext_str == $ext) {
                        if (@unlink($dir . $file)) {
                            $count++;
                        }
                    }
                } else {
                    if (@unlink($dir . $file)) {
                        $count++;
                    }
                }
            }
        }
        closedir($folder);
    }
    return $count;
}

/**
 * 清除模版编译文件
 * @access public
 * @param mix $ext 模版文件名后缀
 * @return void
 * @author root 2015-3-23 14:12:54
 */
function clear_compiled_files($ext = null) {
    return clear_tpl_files(false, $ext);
}

/**
 * 清除缓存文件
 * @access public
 * @param mix $ext 模版文件名后缀
 * @return void
 * @author root 2015-3-23 14:12:54
 */
function clear_cache_files($ext = null) {
    return clear_tpl_files(true, $ext);
}

/**
 * 清除模版编译和缓存文件
 * @access public
 * @param mix $ext 模版文件名后缀
 * @return void
 * @author root 2015-3-23 14:12:54
 */
function clear_all_files($ext = null) {
    $count = clear_tpl_files(false, $ext);
    $count += clear_tpl_files(true, $ext);
    return $count;
}

/**
 * 过滤若干特殊字符 ( 除了 , - _ )
 * @return string
 * @author 何洋  2015-5-15 17:43:38
 */
function str_filter($str){
    if($str){
        $str = str_replace("\n", " ", $str);
        $str = str_replace("\r", " ", $str);
        $str = str_replace("\t", " ", $str);
        $regex = "/\/|\~|\!|\@|\\$|\%|\^|\&|\*|\(|\)|\+|\{|\}|\:|\<|\>|\?|\[|\]|\.|\/|\;|\'|\"|\`|\=|\\\|\|/";
        $str = preg_replace($regex," ",$str);
    }
    return $str;
}
/**
 * 过滤非英文数字
 * @return string
 * @author 何洋 2015-5-20 14:58:08
 */
function str_noengnum_filter($str){
    if($str){
        $regex = "/[^a-zA-Z0-9]/";
        $str = preg_replace($regex," ",$str);
    }
    return $str;
}

/**
 * 是否包含对于sql危险的符号
 * @return bool
 * @author root 2015-6-5 17:51:26
 */
function is_sql_injection_str($str){
    return preg_match("/,|'|\"|;|\!|\*|\\\|\(|\)|\{|\}/", $str);
}
/**
 * 二位数组指定键值排序
 * @param array $multi_array 待排序数组
 * @param string $sort_key 键值
 * @param string $sort 排序方式
 * @return array
 * @author root 2015-6-10 15:00:28
 */
function multi_array_sort($multi_array,$sort_key,$sort=SORT_ASC){
    $new = array();
    if(is_array($multi_array)){
        foreach ($multi_array as $k=> $row_array){
            if(is_array($row_array)){
                $key_array[$k] = $row_array[$sort_key];
            }else{
                return false;
            }
        }
    }else{
        return false;
    }
    asort($key_array);
    foreach($key_array as $key =>$val){
        $new[] = $multi_array[$key];
    }
    return $new;
}
/**
 * 生成带校验位的字符串
 * @return string
 * @author 何洋  2015-7-15 15:43:53
 */
function get_jiaoyan_code39($str){
    $str = strtoupper($str.' ');//转换成大写
    $allow_word = array('0'=>'0','1'=>'1','2'=>'2','3'=>'3','4'=>'4','5'=>'5','6'=>'6','7'=>'7','8'=>'8','9'=>'9','A'=>'10','B'=>'11','C'=>'12','D'=>'13','E'=>'14','F'=>'15','G'=>'16','H'=>'17','I'=>'18','J'=>'19','K'=>'20','L'=>'21','M'=>'22','N'=>'23','O'=>'24','P'=>'25','Q'=>'26','R'=>'27','S'=>'28','T'=>'29','U'=>'30','V'=>'31','W'=>'32','X'=>'33','Y'=>'34','Z'=>'35','-'=>'36','.'=>'37',' '=>'38','$'=>'39','/'=>'40','+'=>'41','%'=>'42');
    $out = array();
    preg_match_all ( "/^(.+)(.){1}$/i", $str, $out, PREG_PATTERN_ORDER );//分隔字符串和校验位
    $jiaoyan_arr = str_split($out[1][0]);//字符串转换成数组
    $jiaoyan_num = 0;
    foreach($jiaoyan_arr as $key => $val){
        $jiaoyan_num += $allow_word[$val];
    }
    $check_word_arr = array_flip($allow_word);
    $check_word = $check_word_arr[($jiaoyan_num%43)];//校验位
    return $out[1][0].$check_word;
}

/**
 * 获取csv文件
 * 
 * @author 何洋 2015-8-11 09:54:17
 */
function get_csv($file){
    $handle = fopen($file,"r");
    while($row = fgetcsv($handle)){
        $data[] = $row;
    }
    fclose($handle);
    return $data;
}
/**
 * 将数组转为csv字符串
 *
 * @author 何洋 2015-8-11 09:54:17
 */
function make_csv_str($data , $title = array()){
    $res = '';
    if(!empty($title))
        $res = implode(',' , $title)."\r\n";
    if(empty($data))
        return $res;
    foreach($data as $row){
        $row_str = '';
        foreach($row as $col){
            $row_str .= str_replace(',' , '，' , $col).',';
        }
        $res .= rtrim($row_str , ',')."\r\n";
    }
    return $res;
}

/**
 * 对数组中的所有元素进行转义
 * @param array 需要转义的数组
 * @author 林祯 2015-9-21 17:34:15
 */
function addslashes_array($array){
    foreach($array as $key => $val){
        if(is_array($val)){
            $array[$key] = addslashes_array($array[$key]);
        }else{
            $array[$key] = addslashes($val);
        }
    }
    return $array;
}

/*
 * 异步发送curl请求
 * @return mix
 * @param string $url 请求的链接
 * @param array $data_multi 发送的数据
 * @param boolean $need_return 是否需要返回数据，默认false
 * @author 林祯 2015-10-22 09:00:49
 * @modify 林祯 2015-11-4 17:18:38 修改变量名bug
 */
function asynch_curl($url,$data_multi,$need_return=false){
    //创建curl批处理句柄
    $mh = curl_multi_init();
    foreach($data_multi as $data){
        //创建单个curl资源
        $ch = curl_init();
        $mch[] = $ch;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
        if(!$need_return){
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,0);
            curl_setopt($ch, CURLOPT_TIMEOUT_MS, 1000);
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        //将单个curl资源添加到批处理句柄中
        curl_multi_add_handle($mh,$ch);
    }
    $running = null;
    //执行批处理句柄
    do {
        $mrc = curl_multi_exec($mh, $active);
    } while ($mrc == CURLM_CALL_MULTI_PERFORM);
    while ($active and $mrc == CURLM_OK) {  
        if(curl_multi_select($mh) === -1){
            usleep(100);
        }
        do {
            $mrc = curl_multi_exec($mh, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM); 
    }
    $result = array();
    //关闭全部句柄
    foreach($mch as $ch){        
        if($need_return){
            //返回的数据
            $result[] = curl_multi_getcontent($ch);
        }
        curl_multi_remove_handle($mh,$ch);
    }
    curl_multi_close($mh);
    return $need_return ? $result : null;
}

/**
 * 将对象转换成数组
 *
 * @author 林祯 2016-3-9 13:17:49
 */
function object_array($obj){
    //强制转换成数组
    $arr = (array)$obj;
    //兼容多维数组
    foreach($arr as $key => $val){
        if(is_object($val) ||is_array($val)){
            $arr[$key] = object_array($val);
        }
    }
    return $arr;
}
/**
 * 多维数组去重
 *
 * @author 林祯 2016-05-03 15:02:31
 */
function unique($array){
    $new_arr = array();
    foreach($array as $arr){
        $new_arr[] = json_encode($arr);//转换成一维数组
    }
    $new_arr = array_unique($new_arr);//去掉重复
    foreach($new_arr as $key => $arr){
        $new_arr[$key] = json_decode($arr,true);//恢复数据
    }
    return $new_arr;
}

/** 
 * XML转化为数组
 *
 * @author 何洋 2016-3-17 14:37:20
 */
function xml_to_array($xml){
    $reg = "/<(\\w+)[^>]*?>([\\x00-\\xFF]*?)<\\/\\1>/";
    if(preg_match_all($reg, $xml, $matches)){
        $count = count($matches[0]);
        $arr = array();
        for($i = 0; $i < $count; $i++){
            $key= $matches[1][$i];
            $val = xml_to_array( $matches[2][$i] );  // 递归
            if(array_key_exists($key, $arr)){
                if(is_array($arr[$key])){
                    if(!array_key_exists(0,$arr[$key])){
                        $arr[$key] = array($arr[$key]);
                    }
                }else{
                    $arr[$key] = array($arr[$key]);
                }
                $arr[$key][] = $val;
            }else{
                $arr[$key] = $val;
            }
        }
        return $arr;
    }else{
        return $xml;
    }
}

/**
 * 实例化RedisQ
 * @author root 2015-2-15 13:25:38
 * @modify root 2015-11-25 12:59:01 redis实例化修改
 */
function redises(){
    static $redis = null;
    if ($redis === null){
        import("Common.Util.Redisrs");
        $redis = new \RedisR();
        $options = array (
            'host'  =>  C('REDIS_HOST') ? C('REDIS_HOST') : '127.0.0.1',
            'port'  =>  C('REDIS_PORT') ? C('REDIS_PORT') : 6379,
        );
        $redis->addserver($options['host'],$options['port']);
    }
    return $redis;
}
/**
 *
 * 缓存管理（Redis key=>value）
 * @param mixed $name 缓存名称
 * @param mixed $value 缓存值
 * @param mixed $options 缓存参数/过期时间
 * @return mixed
 * @author root 2016-6-22 14:09:05
 * @modify root 2016-07-04 14:14:48 key加上前缀supplychain
 */
function redis_cache($name,$value='',$options=null) {
    $redis_obj = redises();//获取系统封装的reids的类
    $redis = $redis_obj->redis();//获取redis的原生类
    $name = 'supplychain'.$name;//加前缀
    if(''=== $value){ // 获取缓存
        return $redis->get($name);
    }elseif(is_null($value)) { // 删除缓存
        return $redis->delete($name);
    }else { // 缓存数据
        if(is_array($options)) {
            $expire     =   isset($options['expire'])?$options['expire']:NULL;
        }else{
            $expire     =   is_numeric($options)?$options:NULL;
        }
        if(empty($expire)){
            $expire = 3600;//默认缓存一小时
        }
        return $redis->setex($name, $expire,$value);//带有时间的写入
    }
}

/**
 * 返回数组中指定的一列
 * @param array $input 需要取出数组列的多维数组（或结果集）
 * @param mixed $column_key 需要返回值的列，它可以是索引数组的列索引，或者是关联数组的列的键。
 * @param mixed $index_key 作为返回数组的索引/键的列，它可以是该列的整数索引，或者字符串键值。
 * @return array
 * @author root 2016-6-27 13:46:49
 */
if (!function_exists('array_column')) {
    function array_column($input, $column_key, $index_key = null)
    {
        $result = array();
        foreach ($input as $subArray) {
            if (!is_array($subArray)) {
                continue;
            } elseif (is_null($index_key) && array_key_exists($column_key, $subArray)) {
                $result[] = $subArray[$column_key];
            } elseif (array_key_exists($index_key, $subArray)) {
                if (is_null($column_key)) {
                    $result[$subArray[$index_key]] = $subArray;
                } elseif (array_key_exists($column_key, $subArray)) {
                    $result[$subArray[$index_key]] = $subArray[$column_key];
                }
            } elseif(is_null($column_key)) {
                $result[] = $subArray;
            }
        }
        return $result;
    }
}
/**
 * 获得用户的真实IP地址
 *
 * @access public
 * @return string
 */
function real_ip() {
    static $realip = null;

    if ($realip !== null) {
        return $realip;
    }

    if (isset($_SERVER)) {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);

            /**
             * 取X-Forwarded-For中第一个非unknown的有效IP字符串
             */
            foreach ($arr AS $ip) {
                $ip = trim($ip);

                if ($ip != 'unknown') {
                    $realip = $ip;

                    break;
                }
            }
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $realip = $_SERVER['HTTP_CLIENT_IP'];
        } else {
            if (isset($_SERVER['REMOTE_ADDR'])) {
                $realip = $_SERVER['REMOTE_ADDR'];
            } else {
                $realip = '0.0.0.0';
            }
        }
    } else {
        if (getenv('HTTP_X_FORWARDED_FOR')) {
            $realip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('HTTP_CLIENT_IP')) {
            $realip = getenv('HTTP_CLIENT_IP');
        } else {
            $realip = getenv('REMOTE_ADDR');
        }
    }

    preg_match("/[\d\.]{7,15}/", $realip, $onlineip);
    $realip = !empty($onlineip[0]) ? $onlineip[0] : '0.0.0.0';

    return $realip;
}
/**
 * 根据过滤条件获得排序的标记
 *
 * @access  public
 * @param   array   $filter
 * @return  array
 */
function sort_flag($filter)
{
    $flag['tag']    = 'sort_' . preg_replace('/^.*\./', '', $filter['sort_by']);
    $flag['img']    = '<img src="'.__ROOT__.'/Public/Images/' . ($filter['sort_order'] == "DESC" ? 'sort_desc.gif' : 'sort_asc.gif') . '"/>';
    return $flag;
}
function get_mysql_condition($data_arr = array()){
    $res = '';
    $where = '';
    $group = '';
    $order = '';
    $limit = '';
    if(!empty($data_arr)){
        //WHERE
        if($data_arr['where']){
            $where = ' WHERE 1 ';
            foreach($data_arr['where'] as $value){
                $where .= ' AND '.$value.' ';
            }
        }
        //GROUP BY
        if($data_arr['group']){
            $group = ' GROUP BY ';
            foreach($data_arr['group'] as $value){
                $group .= ' '.$value.',';
            }
            $group = rtrim($group, ',');
        }
        //ORDER BY
        if($data_arr['order']){
            $order = ' ORDER BY ';
            foreach($data_arr['order'] as $value){
                if(isset($value[1])){
                    $order .= ' '.$value[0].' '.$value[1];
                }else{//默认正序
                    $order .= ' '.$value[0].' ASC ';
                }
                $order .= ',';
            }
            $order = rtrim($order, ',');
        }
        //LIMIT
        if($data_arr['limit']){
            $limit = ' LIMIT '.$data_arr['limit'][0];
            if(isset($data_arr['limit'][1])){
                $limit .= ', '.$data_arr['limit'][1];
            }
        }
        $res = $where.$group.$order.$limit;
    }
    return $res;
}
/**
 * 检查客户是否在办公室登陆
 * @return bool
 * @author root 2015-5-25 15:53:57
 */
function is_office_ip($ip){
    $office_ip_list = C('OFFICE_IP_LIST');
    if(in_array($ip, $office_ip_list)){
        return true;
    }else{
        return false;
    }
}
/**
 * 根据数据生成树型结构
 * This is a cool function
 *
 * @author 林祯 2016-12-14T09:51:07
 * @param  array $data  数据
 * @param  string $t_key 父子节点依赖的键
 * @return array        树型结构数组
 */
function tree($data, $t_key = 'pid')
{
    $index = array();//索引数组
    $tree = '';//数据树,

    foreach ($data as $key => $val) {
        //整合数据
        foreach ($val as $k => $v) {
            $index[$val['id']][$k] = $v;
        }
        $index[$val['id']]['child'] = array();
    }

    //将每个节点与父节点进行拼接
    foreach ($index as $k=>$v) {
        if ($v[$t_key] > 0) {
            $index[$v[$t_key]]['child'][] = &$index[$k];
        } else {
            //根节点
            $tree[$k] = &$index[$k];
        }
    }
    return $tree;
}
/**
 * 判断表中某字段是否无重复
 * @access  public
 * @param   string  $table  表名
 * @param   string  $field    字段名
 * @param   string  $value   字段值
 * @param   array   $id_arr  主键的字段名和值
 * @return   boolean   无重复返回true ，有重复false
 * @author root 2014-12-18 12:10:50
 */
function is_only($table, $field, $value, $id_arr=array('field'=>'', 'value'=>''), $where='')
{
    $sql = "SELECT COUNT(*) FROM ".C('DB_PREFIX')."" .$table. " WHERE $field = '$value'";
    if($id_arr['field'] && $id_arr['value']){
        $sql .= " AND {$id_arr['field']} <> {$id_arr['value']}";
    }
    $sql .= empty($where) ? '' : ' AND ' .$where;
    return (M()->getOne($sql) == 0);
}
/**
 * 删除数组中指定键并返回
 *
 * @author 林祯 2016-12-29T16:29:26
 * @param  string $key    要删除的键
 * @param  array &$array  搜索的数组引用
 * @return 
 */
function array_shift_key($key, &$array) 
{
    if (array_key_exists($key, $array)) {
        $val = $array[$key];
        unset($array[$key]);
        return $val;
    }
}
/**
 * 可读取多维数组配置
 *
 * @author 林祯 2017-01-22T15:15:40   
 * @param  string $name 键名
 */
function mC($name)
{
    if (strpos($name, '.') !== false) {
        $names = explode('.', $name);
        if (count($names) > 2) {
            $key  =  strtoupper(array_shift($names));
            $value = C($key);
            foreach ($names as $val) {
                $value = $value[$val];
            }
            return $value;
        }
    }
    return C($name);
}
/**
 * 格式化时间戳
 *
 * @author 林祯 2017-02-06T15:54:25
 * @param  int $time 时间戳
 * @return string       时间格式
 */
function f_date($time)
{
    return $time ? date('Y-m-d H:i:s', $time) : '';
}
/*
* 包含A级字符的小写转大写
*
* @author 钟宏亮 2016-9-18 10:08:00
*/
function a_str_upper($string)
{
    //A级字符大小写对应表
    $a_charater_table = array(
        'Á'=>'á', 'À'=>'à', 'Ä'=>'ä', 'Â'=>'â', 'Å'=>'å', 'Ã'=>'ã', 
        'Ą'=>'ą', 'Ā'=>'ā', 'Æ'=>'æ', 'Ç'=>'ç', 'Č'=>'č', 'Ć'=>'ć', 
        'Ĉ'=>'ĉ', 'Ċ'=>'ċ', 'È'=>'è', 'É'=>'é', 'Ê'=>'ê', 'Ë'=>'ë', 
        'Ē'=>'ē', 'Ĕ'=>'ě', 'Ė'=>'ė', 'Ę'=>'ę', 'Ě'=>'ĕ', 'Ĝ'=>'ĝ', 
        'Ğ'=>'ğ', 'Ġ'=>'ġ', 'Ĥ'=>'ĥ', 'Ì'=>'ì', 'Í'=>'í', 'Î'=>'î', 
        'Ï'=>'ï', 'Ĩ'=>'ĩ', 'Ī'=>'ī', 'Ĭ'=>'ĭ', 'Į'=>'į', 'İ'=>'ı', 
        'Ĵ'=>'ĵ', 'Ķ'=>'ķ', 'Ĺ'=>'ĺ', 'Ļ'=>'ļ', 'Ľ'=>'ľ', 'Ŀ'=>'ŀ', 
        'Ł'=>'ł', 'Ń'=>'ń', 'Ņ'=>'ņ', 'Ň'=>'ň', 'Ñ'=>'ñ', 'Ŋ'=>'ŋ', 
        'Ō'=>'ō', 'Ó'=>'ó', 'Ŏ'=>'ŏ', 'Ò'=>'ò', 'Ô'=>'ô', 'Õ'=>'õ', 
        'Œ'=>'œ', 'Ŕ'=>'ŕ', 'Ŗ'=>'ŗ', 'Ř'=>'ř', 'Ś'=>'ś', 'Ŝ'=>'ŝ', 
        'Ş'=>'ş', 'Š'=>'š', 'Ţ'=>'ţ', 'Ŧ'=>'ŧ', 'Ù'=>'ù', 'Ú'=>'ú', 
        'Û'=>'û', 'Ü'=>'ü', 'Ũ'=>'ũ', 'Ū'=>'ū', 'Ŭ'=>'ŭ', 'Ů'=>'ů', 
        'Ű'=>'ű', 'Ų'=>'ų', 'Ŵ'=>'ŵ', 'Ȳ'=>'ȳ', 'Ý'=>'ý', 'Ỳ'=>'ỳ', 
        'Ŷ'=>'ŷ', 'Ÿ'=>'ÿ', 'Ỷ'=>'ỷ', 'Ỹ'=>'ỹ', 'Ƴ'=>'ƴ', 'Ỵ'=>'ỵ', 
        'Ɏ'=>'ɏ', 'Ｙ'=>'ｙ', 'Ź'=>'ź', 'Ż'=>'ż', 'Ž'=>'ž','Ø'=>'ø',
        'Ö'=>'ö'
    );
    $string = strtoupper($string);
    //将A级转换成对应的大写
    foreach($a_charater_table as $key => $val){
        $string = str_replace($val,$key,$string);
    }
    return $string;
}
/*
* A级字符转换
*
* @author 钟宏亮 2016-9-18 10:08:06
*/
function place_a_str($string)
{
    $string = a_str_upper($string);
    //A级字符表
    $a_character = array(
        'Á'=>'A', 'À'=>'A', 'Ä'=>'A', 'Â'=>'A', 'Å'=>'A', 'Ã'=>'A',
        'Æ'=>'AE','Ç'=>'C', 'Č'=>'C', 'Ć'=>'C', 'Ĉ'=>'C', 'Ċ'=>'C',
        'Ď'=>'D', 'Đ'=>'D', 'ď'=>'D', 'đ'=>'D', 'È'=>'E', 'É'=>'E', 
        'Ê'=>'E', 'Ë'=>'E', 'Ē'=>'E', 'Ĕ'=>'E', 'Ė'=>'E', 'Ę'=>'E',
        'Ě'=>'E', 'Ĝ'=>'G', 'Ğ'=>'G', 'Ġ'=>'G', 'Ģ'=>'G', 'Ĥ'=>'H', 
        'Ħ'=>'H', 'ħ'=>'H', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 
        'Ĩ'=>'I', 'Ī'=>'I', 'Ĭ'=>'I', 'Į'=>'I', 'ĺ'=>'I', 'Ĵ'=>'J',
        'Ķ'=>'K', 'Ĺ'=>'L', 'Ļ'=>'L', 'Ľ'=>'L', 'Ŀ'=>'L', 'Ł'=>'L',
        'Ń'=>'N', 'Ņ'=>'N', 'Ň'=>'N', 'Ñ'=>'N', 'ŉ'=>'N', 'Ŋ'=>'N',
        'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ø'=>'O', 'Ō'=>'O', 
        'Ŏ'=>'O', 'Ő'=>'O', 'Ö'=>'OE','Œ'=>'OE','Ŕ'=>'R', 'Ŗ'=>'R', 
        'Ř'=>'R', 'Ś'=>'S', 'Ŝ'=>'S', 'Ş'=>'S', 'Š'=>'S', 'Ţ'=>'T', 
        'Ť'=>'T', 'Ŧ'=>'T', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 
        'Ũ'=>'U', 'Ū'=>'U', 'Ŭ'=>'U', 'Ů'=>'U', 'Ű'=>'U', 'Ų'=>'U',
        'Ü'=>'UE','Ŵ'=>'W', 'Ŷ'=>'Y', 'Ÿ'=>'Y', 'Ý'=>'Y', 'Ȳ'=>'Y', 
        'Ỳ'=>'Y', 'Ỷ'=>'Y', 'Ỹ'=>'Y', 'ẙ'=>'Y', 'Ƴ'=>'Y', 'Ỵ'=>'Y', 
        'Ɏ'=>'Y', 'Ｙ'=>'Y','ʏ'=>'Y', 'Ź'=>'Z', 'Ż'=>'Z', 'Ž'=>'Z',
        'ß'=>'SS'
        );
    //替换参数中的所有A级字符
    foreach($a_character as $key=>$val){
        $string = str_replace($key,$val,$string);
    }
    return $string;
}
/**
 * redis hash操作
 *
 * @author 林祯 2016-08-29 11:17:23
 */
function redis_hash($hash_key,$field = '',$data = '')
{
    $redis = redises();
    if(empty($hash_key)){
        return false;
    }
    if($data === ''){//获取数据
        if($field == ''){
            $data = $redis->hGetAll($hash_key);
            foreach($data as $key => $val){//逐个判断是否为json
                $json = json_decode($val,true);
                if(is_array($json)){
                    $data[$key] = $json;
                }
            }
        }else{
            $data = $redis->hGet($hash_key,$field);
            $json = json_decode($data,true);//判断是否为json
            if(is_array($json)){
                $data = $json;
            }
        }
        return $data;
    }elseif(is_null($data)){//删除数据
        if($field){//删除单个域
            return $redis->hDel($hash_key,$field);
        }
        //删除所有域
        $keys = $redis->hKeys($hash_key);//获取这个键的所有域
        $redis->multi();//事务
        foreach($keys as $val){//遍历进行删除
            $redis = $redis->hDel($hash_key,$val);
        }
        $res = $redis->exec();//执行事务
        foreach($res as $val){
            if(!$val) return false;
        }
        return true;
    }else{//存储数据
        if($field == ''){//批量存储
            foreach($data as $key => $val){
                if(is_array($val)){
                    $data[$key] = json_encode($val);
                }
            }
            return $redis->hMset($hash_key,$data);
        }else{//单个键值存储
            if(is_array($data)){
                $data = json_encode($data);
            }
            return $redis->hSet($hash_key,$field,$data);
        }
    }
}
/**
 * 返回生成各种条码的链接
 *
 * @author 林祯 2017-03-13T14:21:20
 * @param  string $str 条码内容
 * @return string 条码类型 C128 DATAMATRIX PDF147 QRCODE,H
 */
function barcode_link($str, $type = 'C128')
{
    $type = strtoupper($type);

    return __HOST__.'/ThinkPHP/Library/Vendor/tcpdf/tcpdf_barcode.php?code='.$type.'&filetype=PNG&text='.urlencode($str);
}
/**
 * 获取pdf对象
 *
 * @author 林祯 2016-3-23 17:03:37
 */
function pdf($dire = 'P',$unit = 'mm',$size = array(100,150)){
    // static $pdf = null;
    // if ($pdf === null) {
        vendor('tcpdf.pdf');
        $pdf = new \PDF($dire,$unit,$size);
    // }
    return $pdf;
}
/**
 * 获取毫秒时间戳
 *
 * @author 林祯 2017-03-28T11:39:53
 */
function m_time(){
    list($usec, $sec) = explode(" ", microtime());
    return (float)sprintf('%.0f', (floatval($usec) + floatval($sec)) * 1000);
}

/**
 * 检测pdf是否完整
 *
 * @author 林祯 2017-05-23 10:57:57
 */
function check_pdf($pdf)
{
    return strpos($pdf, '%%EOF') !== false;
}
/**
 * unicode解码
 *
 * @author 林祯 2017-06-29T11:45:51
 */
function unicode_decode($str)
{
    return preg_replace_callback('/\\\\u([0-9a-f]{4})/i',
        create_function(
            '$matches',
            'return mb_convert_encoding(pack("H*", $matches[1]), "UTF-8", "UCS-2BE");'
        ), $str);
}

/**
 * 读取目录下的日志
 *
 * @author 林祯 2017-01-13T14:56:03
 */
function read_dir($file)
{
    $all = scandir($file);
    unset($all[0]);unset($all[1]);
    $new = array();
    foreach ($all as $key => $val) {
        $path = $file.'/'.$val;
        if (is_dir($path)) {
            $new[$val] = read_dir($path);
        } else {
            $new[] = $val;
        }
    }
    return $new;
}

/**
 * 把get请求的参数拼接到链接中
 *
 * @author 林祯 2017-07-14T12:55:47
 */
function parse_get_url($url, $param)
{
    $url .= '?';// 拼接链接
    foreach($param as $key => $val) {
        $url .= $key.'='.$val.'&';
    }
    return rtrim($url, '&');
}

/**
 * 请求商品中心接口
 * @access public
 * @param string $uri PDC接口的path，如：/product/get_product_addition
 * @param array $postData
 * @return string
 * @throws \Think\Exception
 * @author root 2017-07-05 17:28:23
 */
function pdcApiRequest($uri = '', $postData = null)
{
    $postData = json_encode($postData);
    $rdsKey = "Home_Common_Auth_Token_Of_PDC";
    if (!$token = redis_cache($rdsKey)) {
        //获取token并存储
        $info = getAuthInfoOfPDC();
        redis_cache($rdsKey, $token = $info['token'], array('expire' => 1800)); // 缓存30分钟
    }
    $curl = curl_init(C('PDC_BASE_URL') . $uri);
    curl_setopt($curl, CURLOPT_HEADER, 1);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($curl, CURLOPT_TIMEOUT,30);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
    $header = array(
        "accept: application/json",
        "cache-control: no-cache",
        "content-type: application/json",
        "token: " . $token,
    );
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    $response = curl_exec($curl);
    $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
    $error = curl_error($curl);
    curl_close($curl);
    if ($error) {
        E('cURL error: ' . $error . ' Api: ' . $uri);
    }
    $responseHeaders = array_filter(explode("\r\n", substr($response, 0, $headerSize)));
    $tokenHeaderPrefix = 'token: ';
    foreach ($responseHeaders as $item) {
        if (strpos($item, $tokenHeaderPrefix) !== false) {
            $token = str_replace($tokenHeaderPrefix, '', $item);
            redis_cache($rdsKey, $token, array('expire' => 1800)); // 缓存30分钟
            break;
        }
    }
    $content = substr($response, $headerSize);
    $result = json_decode($content, true);
    if (!is_array($result)) {
        E('PostData: ' . $postData . 'Api: ' . $uri . ' Res: ' . $content);
    }
    if ($result['code']) {
        E('Error Code: ' . $result['code'] . ' Error message: ' . $result['msg']);
    }
    return $result['info'];
}

/**
 * 获取商品中心用户登录授权信息，记录日志
 * @access public
 * @return array|false 请求成功返回json并转成数组，curl异常返回false；
 * demo:
 * 请求成功时：json {"code":"0","msg":"OK","info":{"token":"","user":{"id":0,"name":"","nick_name":"''","is_extranet_login":null,"last_login_time":null,"status":0},"role_list":[{"id":0,"name":"","system":""}],"permission_list":[]}}
 * code码为0时，表示商品中心授权成功，返回info中的数组；非0时，表示授权失败，msg含有失败信息
 * @throws \Think\Exception 务必捕获异常
 * @author root 2017-6-13 11:15:17
 */
function getAuthInfoOfPDC()
{
    $url = C('PDC_BASE_URL') . C('PDC_AUTH_GATEWAY');
    $curl = \Common\Util\Curl::init($url);
    $response = $curl->option(
        array(
            'postfields' => json_encode(array('name' => C('PDC_USER'), 'password' => C('PDC_PASSWORD'))),
            'httpheader' => array(
                "accept: application/json",
                "cache-control: no-cache",
                "content-type: application/json",
            )
        )
    )->run();
    $result = json_decode($response, true);
    if (!isset($result['code'])) { //如果返回值不满足
        E('cURL error: ' . $curl->error() . ' Api: ' . $url . ' Res: ' . $response);
    }
    if ($result['code']) { //为真表示有异常
        E($result['msg']);
    }
    return $result['info'];
}
/**
 * LIKE过滤
 *
 * @author 林祯 2017-07-15 09:48:23
 */
function mysql_like_quote($str) {
    return strtr($str, array("\\\\" => "\\\\\\\\", '_' => '\_', '%' => '\%'));
}


}