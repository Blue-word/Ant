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
    /**
     * 获取分类列表
     *
     * @author 蓝勇强 2018-07-28
     * @act  [string]
     * @id  integer
     * @return [array]
     */
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
    /**
     * 获取新闻分类
     *
     * @author 蓝勇强 2018-07-28
     */
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
    /**
     * 判断redis是否缓存
     *
     * @author 蓝勇强 2018-07-28
     */
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
    /**
     * 注释说明
     *
     * @author 蓝勇强 2018-07-28
     * @data_arr  array
     */
    public function addNews($data_arr=array()){
        foreach ($data_arr as $k => $v) {
            $pic_arr = array();
            if ($v['thumbnail_pic_s']) {
                $pic_arr[0] = $v['thumbnail_pic_s'];
            }
            if ($v['thumbnail_pic_s02']) {
                $pic_arr[1] = $v['thumbnail_pic_s02'];
            }
            if ($v['thumbnail_pic_s03']) {
                $pic_arr[2] = $v['thumbnail_pic_s03'];
            }
            $photo = implode(',', $pic_arr);//照片处理
            $is_id = M('news')->where('_id',$v['uniquekey'])->count();
            if ($is_id) {
                continue;
            }else{
                $content = divQuery2($v['url']);    //采集新闻内容
                if ($content) {
                    $status = 1;
                }else{
                    $status = 0;
                    $content = '';
                }
                $data[$k] = array(
                    '_id' => $v['uniquekey'],
                    'title' => $v['title'],
                    'content' => $content,
                    'picture' => $photo,
                    'url' => $v['url'],
                    'cate' => $key,
                    'status' => $status,
                    'create_time' => time(),
                    'publish_time' => strtotime($v['date']),
                    'statement' => 1,
                    'type' => 2,
                    'source' => $v['author_name'],
                );  
            }
        }
        $res = @M('news')->saveAll($data);
        return $res;
    }

}