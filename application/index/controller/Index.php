<?php
namespace app\index\controller;
use think\Controller;

class Index extends Controller{

    public function index()
    {
    	define('key', 'imooc'); 
    	$array = array( 'key' => 'hello world!', 'imooc' => 'http://www.imooc.com/' ); 
    	echo $array["key"] . '</br>';
    	echo $array[key] . '</br>';
        return $this->fetch();
    }
}
