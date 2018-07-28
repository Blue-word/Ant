<?php
namespace app\tools\controller;
use think\Controller;
use FileToZip\tratraverseDir;

class Log extends Controller
{
    /**
     * 日志模板类型数组
     *
     * @author 陈伟 2017-08-04T11:30:00
     */
    private $logTemp = array(
        'SupplyLogs' => array(
            'time'             => array('时间', 10),
            'request_url'      => array('请求链接', 30),
            'title'            => array('主题', 10),
            'level'            => array('级别'),
            'other_content'    => array('其他内容', 10),
            'request_content'  => array('请求内容', 10),
            'response_content' => array('返回内容', 10)
        ),
        'SupplyUserLogs' => array(
            'time'            => array('时间', 10),
            'user'            => array('用户', 10),
            'user_ip'         => array('IP', 15),
            'request_url'     => array('请求链接', 30),
            'request_content' => array('请求内容', 10)
        )
    );
    public function __construct()
    {
        parent::__construct();
        header('content-type: text/html; charset=utf-8');
        header('Expires: Fri, 14 Mar 1980 20:53:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
    }
    /**
     * 日志展示页面
     *
     * @author 林祯 2017-01-13T14:54:40
     */
    public function index()
    {
        // dump('132');
        $this->assign('title', 'Supply日志');
        // $this->display('log_view');
        return $this->fetch('log_view');
    }
    /**
     * 获取所有日志目录
     *
     * @author 陈伟 2017-08-03T17:54:54
     */
    public function getLogDir()
    {
        // 组装日志根目录数组
        $roots = array(
            'AntAdminLogs', 'AntUserLogs'
        );

        // 读取子目录
        $dirs = array();
        foreach($roots as $val) {
            $dirs[$val] = $this->readDir($val);
        }
        // dump($dirs);
        echo json_encode($dirs);
        exit;
    }
    /**
     * 读取目录下的日志
     *
     * @author 林祯 2017-01-13T14:56:03
     */
    function readDir($file)
    {	
    	$asd = new \tratraverseDir();
        $all = $asd->scandir($file);
        dump($all);die;
        unset($all[0]);unset($all[1]);
        $new = array();
        foreach ($all as $key => $val) {
            $path = $file.'/'.$val;
            if (is_dir($path)) {
                if (preg_match("/\d{8}/", $val) && strtotime($val) < mktime(0,0,0,date('m'),date('d')-30,date('Y'))){
                    continue;
                }
                $new[$val] = $this->readDir($path);
            } else {
                $new[] = $val;
            }
        }
        return $new;
    }
    /**
     * 解析路径获取相应模板
     *
     * @author 陈伟 2017-08-04T11:30:00
     */
    private function parsePath($path)
    {
        $temp = ltrim($path, '/');
        $temp = explode('/', $temp);
        // var_dump($temp);exit;
        $conf = $this->logConf;

        foreach ($temp as $val) {
            if (isset($this->logTemp[$val])) {
                return $this->logTemp[$val];
            }
        }
        return false;
    }
    /**
     * 展示日志内容
     *
     * @author 林祯 2017-01-13T14:56:22
     */
    public function showLog()
    {
        $path = I('request.path');
        $key_word = htmlspecialchars_decode(I('request.key_word'));
        $begin = I('request.begin', 1, 'intval');
        $end = I('request.end', $begin + 99, 'intval');
        $filter = array(
            'begin' => $begin,
            'end' => $end
        );
        if ($key_word) { // 有关键字 取消行数限制
            $filter = explode(',', $key_word);
        }
        $ext = substr(strrchr($path, '.'), 1);
        if ($ext == 'log') {
            if ($temp = $this->parsePath($path)) {
                $content = self::getLogContent($path, $filter);
                foreach ($content['log'] as $k => $log) {
                    $log = self::parseLog($log, array_keys($temp));
                    if ($log[0]['time']) {
                        $log[0]['time'] = preg_replace('/\d{4}-\d{2}-\d{2} /', '',$log[0]['time']);
                    }
                    $content['log'][$k] = $log[0];
                }
                unset($temp['level']);
                echo json_encode(array(
                    'log' => $content['log'],
                    'header' => $temp,
                    'begin' => $begin,
                    'end' => $end
                ));exit;
            } else {
                echo json_encode(array(
                    'header' => ''
                ));exit;
            }
        }
        header('Location:http://'.$_SERVER['HTTP_HOST'].$path);
    }
    /**
     * 解析日志文件
     *
     * @author 林祯 2017-06-14T11:13:39
     * @param  string $path 日志路径
     * @param  string $temp 模板
     */
    static public function parseLog($log, $temp = array())
    {
        if (is_array($log)) {
            foreach($log as $key => $val) {
                $log[$key] = json_decode($val, true);
            }
        } else {
            $log = array(json_decode($log, true));
        }
        return $log;
    }
    /**
     * 获取日志内容
     *
     * @author 林祯 2017-07-05T11:17:32
     */
    static public function getLogContent($path, $filter = array())
    {
        $fp    = fopen(ROOT_PATH.$path, 'r');
        $pat = '/[A-Za-z\d+\/=]{500,}/';
        if ($fp) {
            $line  = 0;
            $begin = array_shift_key('begin', $filter);// 开始行
            $end = array_shift_key('end', $filter);// 结束行
            $log   = array();
            while(!feof($fp)) {
                if ($c = fgets($fp)) {
                    $line ++;
                    if ($begin && $line < $begin || $end && $line > $end) {
                        continue;
                    }
                    foreach($filter as $val) {
                        if (stripos(unicode_decode($c), $val) === false) {
                            continue 2;
                        }
                    }
                    $c = str_replace(array('\/','\n'),array('/',''),$c);
                    $log[] = preg_replace($pat, '', $c);
                    if ($begin && $end && count($log) == ($end - $begin +1)) {
                        break;
                    }
                }
            }
            fclose($fp);
            return array(
                'log' => $log,
                'lines' => $line,
                'pages' => ceil($line / $per_page)
            );
        } 
        return false;
    }
    //遍历目录
    public function scandir($filepath){
        // return '132';
        if (is_dir($filepath)){
            $arr=scandir($filepath);
            foreach ($arr as $k=>$v){
                $this->fileinfo[$v][]=$this->getfilesize($v);
            }
        }else {
            echo "<script>alert('当前目录不是有效目录');</script>";
        }
    }
    /**
     * 返回文件的大小
     *
     * @param string $filename 文件名
     * @return 文件大小(KB)
     */
    public function getfilesize($fname){
        return filesize($fname)/1024;
    }
}
