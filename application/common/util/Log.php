<?php
namespace app\common\util;

class Log
{
    // 日志级别 从上到下，由低到高
    const EMERG     = 'EMERG';  // 严重错误: 导致系统崩溃无法使用
    const ALERT     = 'ALERT';  // 警戒性错误: 必须被立即修改的错误
    const CRIT      = 'CRIT';  // 临界值错误: 超过临界值的错误，例如一天24小时，而输入的是25小时这样
    const ERR       = 'ERR';  // 一般错误: 一般性错误
    const WARN      = 'WARN';  // 警告性错误: 需要发出警告的错误
    const NOTICE    = 'NOTICE';  // 通知: 程序可以运行但是还不够完美的错误
    const INFO      = 'INFO';  // 信息: 程序输出信息
    const DEBUG     = 'DEBUG';  // 调试: 调试信息

    const MAX_SIZE  = 2097152;


    static protected $root = '';

    static public function init()
    {
        self::$root = ROOT_PATH;
    }
    /**
     * 记录严重错误级别的日志
     *
     * @author 林祯 2016-11-30 16:04:06
     * @param  string $des     目标文件
     * @param  array  $context 占位数据
     * @return null          
     */
    static public function emerg($des = '', $context = array())
    {
        self::save(self::interpolate(self::EMERG, $context), $des);
    }
    /**
     * 记录警戒性错误级别的日志
     *
     * @author 林祯 2016-11-30T16:05:39
     * @param  string $des     目标文件路径
     * @param  array  $context 占位数据
     * @return null          
     */
    static public function alert($des = '', $context = array())
    {
        self::save(self::interpolate(self::ALERT, $context), $des);
    }
    /**
     * 记录临界值错误级别的日志
     *
     * @author 林祯 2016-11-30T16:05:39
     * @param  string $des     目标文件路径
     * @param  array  $context 占位数据
     * @return null          
     */
    static public function crit($des = '', $context = array())
    {
        self::save(self::interpolate(self::CRIT, $context), $des);
    }
    /**
     * 记录一般性错误级别的日志
     *
     * @author 林祯 2016-11-30T16:05:39
     * @param  string $des     目标文件路径
     * @param  array  $context 占位数据
     * @return null          
     */
    static public function err($des = '', $context = array())
    {
        self::save(self::interpolate(self::ERR, $context), $des);
    }
    /**
     * 记录警告性错误级别的日志
     *
     * @author 林祯 2016-11-30T16:05:39
     * @param  string $des     目标文件路径
     * @param  array  $context 占位数据
     * @return null          
     */
    static public function warn($des = '', $context = array())
    {
        self::save(self::interpolate(self::WARN, $context), $des);
    }
    /**
     * 记录通知级别的日志
     *
     * @author 林祯 2016-11-30T16:05:39
     * @param  string $des     目标文件路径
     * @param  array  $context 占位数据
     * @return null          
     */
    static public function notice($des = '', $context = array())
    {
        self::save(self::interpolate(self::NOTICE, $context), $des);
    }
    /**
     * 记录程序输出信息
     *
     * @author 林祯 2016-11-30T16:05:39
     * @param  string $des     目标文件路径
     * @param  array  $context 占位数据
     * @return null          
     */
    static public function info($des = '', $context = array())
    {
        self::save(self::interpolate(self::INFO, $context), $des);
    }
    /**
     * 记录调试信息
     *
     * @author 林祯 2016-11-30T16:05:39
     * @param  string $des     目标文件路径
     * @param  array  $context 占位数据
     * @return null          
     */
    static public function debug($des = '', $context = array())
    {
        self::save(self::interpolate(self::DEBUG,$context), $des);
    }
    /**
     * 记录任何一种错误
     *
     * @author 林祯 2016-11-30T16:05:39
     * @param  string $level  错误级别
     * @param  string $des     目标文件路径
     * @param  array  $context 占位数据
     * @return null          
     */
    static public function record($level,$des = '', $context = array())
    {
        self::save(self::interpolate($level, $context), $des);
    }

    /**
     * 用于记录用户操作日志
     *
     * @author 林祯 2017-06-09T14:56:41
     * @param  string $des     目标文件路径
     * @param  array  $context 需要记录的数据
     */
    static public function oplog($des = '', $context = array())
    {
        self::save(self::interpolate('', $context), $des,true);

    }

    /**
     * 占位符替换
     *
     * @author 林祯 2016-11-30T16:09:40
     * @param  string $level     日志等级
     * @param  array  $context 占位数据
     * @return string          使用占位数据替换了占位符的日志消息
     */
    static private function interpolate($level, $context = array())
    {
        $context = array_map(function($val) {
            $arr = @json_decode($val, true);
            return $arr ? $arr : $val;
        }, $context);

        $context['time'] = date('Y-m-d H:i:s');
        if ($level){
            $context['level'] = $level;
        }

        return json_encode($context);
    }
    /**
     * 保存日志
     *
     * @author 林祯 2016-11-30T16:10:30
     * @param  string $msg   日志消息
     * @param  string $des   目标文件路径
     * @param  bool $is_user_log  是否用户日志
     * @return null        
     */
    static public function save($msg, $des,$is_user_log = false)
    {
        if(empty($msg)) return ;
        $log_path = self::getLogPath($des,$is_user_log);
        self::init();
        $log_path = self::$root.$log_path;

        self::writeToLog($msg, $log_path);
    }


    /**
     * 日志写入文件
     *
     * @author 周金剑 2017-06-29 16:00:00
     * @param $content
     * @param $des
     */
    static private function writeToLog($content,$des)
    {
        if(empty($content)){
            return ;
        }
        //检查文件是否存在，不存在就创建写入，存在就直接写入
        if(is_file($des)){
            error_log("{$content}\r\n",3,$des);
        }else{
            $dir_name = dirname($des);
            if(!is_dir($dir_name)){
                mkdir($dir_name,0777,true);
            }
            error_log("{$content}\r\n",3,$des);
        }

    }
    /**
     * 获取log 存入地址
     *
     * @author 周金剑 2017-06-29 11:30:23
     * @param $des
     * @param bool|false $is_user_log
     * @return bool|mixed|string
     */
    static private function getLogPath($des, $is_user_log = false)
    {
        if(empty($des) && $is_user_log == false){
            $log_path = C('LOG_DEFAULT_PATH');
        }else{
            $split_time = C('LOG_SPLIT_TIME');
            $log_prefix = $is_user_log ? C('LOG_USER_PREFIX') : C('LOG_BUSINESS_PREFIX'); //log地址前缀
            $log_frequency = $is_user_log ? ($split_time['user_log']) : (!empty($split_time[$des]) ? $split_time[$des] : C('LOG_DEFAULT_SPLIT_TIME'));//根据log分割配置确定应存入哪个文件
            $log_name = date('Ymd').'-'.(floor((date('H')/intval($log_frequency)))+1)*$log_frequency;
            $des = str_replace('/_', '/', parse_name($des));// 将目录转为小写
            $log_path = $log_prefix.'/'.date('Ymd').$des.'/'.$log_name.'.log';
        }
        return $log_path;
    }
}