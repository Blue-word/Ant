<?php
namespace app\admin\model;

use think\Model;
use think\cache\driver\Redis;

class News extends Model
{

	public function __construct(){
		$redis = new Redis;
		$handler = $redis->handler();
		$this->handler = $handler;
	}
	

	public function profile()
    {
        return $this->hasOne('user_related','uid','uid');
    }

    public function getCategory($act,$id=0){
    	$field = ('id,name');
		if ($act == 'detail' || $act == 'edit') {
			$cate_info = M('news_category')->where('id',$id)->find();
			$data['cate_info'] = $cate_info;
		}
    	$cate = M('news_category')->where("cate=1")->field($field)->select();
    	$data['cate'] = $cate;
    	return $data;
    }

    public function getNewsCategory(){
    	$res = $this->judgeRedisSet('news_category');
    	if ($res['flag']) {		//已缓存
    		$data = json_decode($res['data'],true);
    	}else{					//未缓存
    		$news_category = M('news_category')->where("cate=1")->getField('id,name');
    		$news_category[0] = "未选择分类";
    		$this->handler->set('news_category',json_encode($news_category));
    		$data = json_decode($this->handler->get('news_category'),true);
    	}
    	return $data;
    }

    public function judgeRedisSet($name){
    	$res = $this->handler->get($name);
		if (!$res) {   	//未缓存
			$result['flag'] = false;
			$result['data'] = null;
		}else{		//已缓存
			$result['flag'] = true;
			$result['data'] = $res;
		}
		return $result;
    }

}