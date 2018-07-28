<?php
namespace app\admin\controller;

use think\Page;
use think\Model;
use think\Db;
use think\File;
use app\admin\logic\NewsCatLogic;
use app\admin\controller\Uploadify;
use think\cache\driver\Redis;
use app\common\util\Log;


class News extends Base {

	private $news_status;//新闻状态
	private $news_category;//分类等级名称
	private $is_topic_status;//是否为专题
	private $news_statement;//是否为专题
	private $category_able_id = array(1,2,3,4,5,6,7,8,9,10,11,12,13,14);
	private $news_type = array(
        '1'=>'top',
        // '2'=>'aozhou',
        '3'=>'guonei',
        '4'=>'shehui',
        '5'=>'guoji',
        '6'=>'yule',
        '7'=>'tiyu',
        '8'=>'junshi',
        '9'=>'keji',
        '10'=>'caijing',
        '11'=>'shishang',
    );

	public function _initialize(){
		$this->news_status = C('NEWS_STATUS');
		$this->news_category = C('NEWS_CATEGORY');
		$this->is_topic_status = C('IS_TOPIC');
		$this->news_statement = C('NEWS_STATEMENT');
		
	}

	public function category(){
		$model = model('News');
		$act = I('get.act','add');
		$id = I('get.id',2);
		$res = $model->getCategory($act,$id);
		$this->assign('act',$act);
		$this->assign('cate_list',$res['cate']);
		$this->assign('cate',$res['cate_info']);
		return $this->fetch();
	}

	public function categoryList(){ 
		$cate_list = D('news_category');
		//搜索关键词
		$keywords = trim(I('keywords'));
		if (I('category')) {
			$where['cate'] = I('category');
		}
		$where['name'] = array('like', '%' . $keywords . '%');
		$p = input('p/d',1);
		$res = $cate_list->where($where)->order('sort asc')->page("$p,7")->select();
		if($res){
        	foreach ($res as $val){
        		$val['cate'] = $this->news_category[$val['cate']];
        		$pid_where['id'] = $val['pid'];
        		$pid_name = $cate_list->where($pid_where)->getField('name');
        		if ($pid_name == null) {
        			$pid_name = "无";
        		}
        		$val['pid_name'] = $pid_name;
        		$list[] = $val;
        	}
        }
		$count = $cate_list->where($where)->count();
		$pager = new Page($count,7);
		$page = $pager->show();
		//dump($res);
        $this->assign('list',$list);
        $this->assign('page',$page);
		$this->assign('pager',$pager);
		return $this->fetch();
	} 

	public function categoryHandle(){
    	$data = I('post.');
    	$cate = D('news_category');
    	if ($data['pid'] == 0) {  //0为顶级分类否则为二级分类
    		$data['cate'] = 1;
    	}else{
    		$data['cate'] = 2;
    	}
        if($data['act'] == 'add'){
        	unset($data['act']);
            $d = $cate->add($data);
            dump($data);
        }
        
        if($data['act'] == 'edit'){
        	unset($data['act']);
            $d = $cate->where('id',$data['id'])->save($data);
            dump($data);
        }
        
        if($data['act'] == 'del'){
        	exit ($data['id']);
            if(array_key_exists($data['id'],$this->category_able_id)){
                exit(json_encode('系统预定义的分类不能删除'));
            }
        	$pid = $cate->where('id',$data['id'])->getField('pid');
        	$res = $cate->where('id',$pid)->count();
        	if ($res)
        	{
        		exit(json_encode('还有父分类，不能删除'));
        	}
        	// $res = D('article')->where('cat_id', $data['cat_id'])->select();
        	// if ($res)
        	// {
        	// 	exit(json_encode('该分类下有文章，不允许删除，请先删除该分类下的文章.'));
        	// }
        	$r = $cate->where('id',$data['id'])->delete();
        	if($r) exit(json_encode(1));
        }
        if($d){
        	$this->redirect('Admin/News/categoryList');
        }else{
        	$this->error("操作失败",U('Admin/News/categoryList'));
        }
    }

    public function newsList(){
    	$redis = new Redis;
    	$handler = $redis->handler();
    	$cate = I('category',1);
		$res = $handler->get('newlist_cate'.$cate);
		if (!$res) {   	//未缓存
			$newlist = M('news')->where('cate',$cate)->order('id desc')->select();
			$handler->set('newlist_cate'.$cate,json_encode($newlist),600);
			$res = json_decode($handler->get('newlist_cate'.$cate),true);
		}else{		//已缓存
			$res = json_decode($res,true);
		}
		$news_category = model('News')->getNewsCategory();	//新闻分类
		if($res){
        	foreach ($res as $val){
        		$val['status'] = $this->news_status[$val['status']];
        		$val['is_topic'] = $this->is_topic_status[$val['is_topic']];
        		$val['statement'] = $this->news_statement[$val['statement']];
        		$val['create_time'] = date('Y-m-d H:i:s',$val['create_time']);
        		$val['cate_name'] = $news_category[$val['cate']];
        		$list[] = $val;
        	}
        }
        dump($list);
		// $count = M('news')->where('cate',$cate)->count();
		$pager = new Page($count,7);
		$page = $pager->show();
		$this->assign('news',$list);
		$this->assign('status',$status);
		$this->assign('page',$page);
		$this->assign('pager',$pager);
		$this->assign('category',$news_category);
		// return $this->fetch();
	}

	public function newsAdd(){
		$ArticleCat = new NewsCatLogic(); 
		$news = D('news');
		$act = I('get.act','add');
		$id = I('get.id');
		$p = I('p');
		$status = I('get.status');
		$res = $news->where('id',$id)->find();
		$news_topic = D('news_topic')->where('status',1)->select();
		if($id){
            $cat_id = D('news')->where('id',$id)->getField('cate');
        }
		$cats = $ArticleCat->news_cat_list(0,$cat_id,true);
		
		// $show = M('news')->where('id',$id)->find();
		//dump($p);
		// $this->assign('show',$show);
		$this->assign('id',$id);
        $this->assign('act',$act);
        $this->assign('news',$res);
        $this->assign('status',$status);
        $this->assign('cat_select',$cats);
        $this->assign('category',$cate);
        $this->assign('news_topic',$news_topic);
		return $this->fetch();
	}

	public function newsHandle(){
		$news = D('news');
		$data = I('post.');
		$type = I('id') > 0 ? 2 : 1;//标识标识自动验证时的场景 1 插入 2 更新
		$data['content'] = I('content'); // 新闻内容单独过滤
		//dump($data);
		if($data['act'] == 'add'){
			unset($data['act']);   
			// $data['author_id'] = ;
			// $data['picture'] = ;
			// $data['cate'] = ;
			$data['status'] = 0;
			$data['create_time'] = time();
			//dump($data);  
            $d = D('news')->add($data);
            if ($data['is_topic'] == '1') {
            	$where['topic_id'] = $data['topic_id'];
            	$news_id = D('news_topic')->where($where)->getField('news_id');
            	if ($news_id == '') {
	            	$topic_data['news_id'] = $d;
	            	$res = D('news_topic')->where($where)->save($topic_data);
	            }
	            if ($news_id !== '') {
	            	$topic_data['news_id'] = $news_id.','.$d;
	            	$res = D('news_topic')->where($where)->save($topic_data);
	            }
            }
            //dump($res);
        }
        if($data['act'] == 'edit'){
        	if (empty($data['status'])) {
        		$this->error("请审核是否通过",U('Admin/News/newsAdd',array('id'=>$data['id'])));
        	}
			unset($data['act']);
			$url = $data['url'];
			$is_news = M('news')->where('id',$data['id'])->field('content,type')->find();
			if ($is_news['content'] == '') {
				if ($is_news['type'] == '1') {
					$res_url = str_replace('content/','content-',$url);
					$res = http_request($res_url);
					if (empty($res)) {
						$this->error("获取到的新闻为空",U('Admin/News/newsAdd',array('id'=>$data['id'],'act'=>'edit')));
					}
					$data['cate'] = 2;
				}else{
					$res = divQuery2($url);
				}
				$data['content'] = $res;
			}
			$data['author_id'] = 1;
			$data['publish_time'] = time();
			//dump($data);
            $c = M('news')->where('id',$data['id'])->save($data);
            if($c){
	        	$this->success("操作成功",U('Admin/News/newsAdd',array('id'=>$data['id'],'act'=>'edit')));
	        }else{
	        	$this->error("操作失败",U('Admin/News/newsAdd',array('id'=>$data['id'],'act'=>'edit')));
	        }
            //dump($res);
        }
		if($data['act'] == 'del'){
        	$r = D('news')->where('id', $data['id'])->delete();
        	if($r) exit(json_encode(1));       	
        }
        if($d){
        	$this->success("操作成功",U('Admin/News/newsList'));
        }else{
        	$this->error("操作失败",U('Admin/News/newsAdd',array('id'=>$data['id'])));
        }
	}
	public function aonewsAdd($url=''){
		$url = "http://www.sydneytoday.com/content/101731377359011";
		$type = 1;
		$res = divQuery1($url);
		var_dump($res);
	}

	public function topicList(){
		$topic_list = D('news_topic');
		//搜索关键词
		$keywords = trim(I('keywords'));
		if (I('status')) {
			$where['status'] = I('status');
		}
		$where['title'] = array('like', '%' . $keywords . '%');
		$p = input('p/d',1);
		$res = $topic_list->where($where)->order('topic_id desc')->page("$p,7")->select();
		if($res){
        	foreach ($res as $val){
        		$val['create_time'] = date('Y-m-d H:i:s',$val['create_time']);
        		if ($val['status'] == 0) {
        			$val['status'] = '不显示';
        		}else{
        			$val['status'] = '显示';
        		}
        		$list[] = $val;
        	}
        }
		$count = $topic_list->where($where)->count();
		$pager = new Page($count,7);
		$page = $pager->show();
		//dump($list);
        $this->assign('list',$list);
        $this->assign('page',$page);
		$this->assign('pager',$pager);
		return $this->fetch();
	}

	public function topic(){
		$web_path = WEB_PATH;
		$this->assign('web_path',$web_path);//网站根目录
		$data = I('post.');
		$act = I('get.act','add');
		$topic = D('news_topic');
		if ($act == 'add') {
			unset($data['act']);
			$data['create_time'] = time();
			$res = $topic->add($data);
		}
		dump($web_path);
		$this->assign('act',$act);
		return $this->fetch();
	}

	public function rotation_imgList(){
		return $this->fetch();
	}

	public function rotation_imgAdd(){
		return $this->fetch();
	}

	public function test(){
		$id = I('get.id',978);
		$res = M('news')->where('id',$id)->find();
		$this->assign('list',$res);
		return $this->fetch();
	}
	public function test1(){
		$url = 'http://sydney.jinriaozhou.com/content-101731433269015';
		//$res = divQuery1($url,1);
		// $url = 'http://sydney.jinriaozhou.com/content/101730473160005';
		$res = http_request($url);
		dump($res);
	}

	public function upload(){
		return $this->fetch();
	}

	public function uploads(){
		$upload = new Uploadify();
		$rootpath = 'forum_image';
        $savepath = 'house';
        $type = 2;
        $info = $upload->imgUpload($rootpath,$savepath,$type);//单图上传
        if ($info['code']) {
            foreach ($info['fileinfo'] as $k => $v) {
                dump($v['relative_path']);
                $path_arr[] = $v['relative_path'];//地址放入数组
                $data['picture'] = implode(',', $path_arr);//将数组合成以逗号相隔的字符串
                dump($data);
            }
        }
        dump($info);
		return $this->fetch();
	}

	public function getNews(){
		
		return $this->fetch();
	}
	/*
	* 获取新闻，同时采集新闻内容
	*/
	public function getNewsList(){
		$ao_url = 'http://app.jinriaozhou.com/news-au';		//澳洲新闻数据
		$all_url = 'http://v.juhe.cn/toutiao/index';		//聚合新闻数据
		$appkey = '468c41b4c59a77a5047c1ccddf79ed70';
		$post_url = I('post.url','http://v.juhe.cn/toutiao/index');
		$type = I('post.type',3);
		$news_type = $this->$news_type[$type];
		if ($type == 0 && $post_url == $ao_url) {
			$url = $post_url;
			$code = 'jinriaozhou';
		}
		if ($type != 0 && $post_url == $all_url) {
			$url = $post_url.'?type='.$news_type.'&key='.$appkey;
			$code = 'juhe';
		}
        $url_res = request_post($url);
        $arr = json_decode($url_res,true);
        if ($code == 'jinriaozhou') {
        	$list_arr = $arr['data']['bignews'];
        	if ($list_arr) {
        		foreach ($list_arr as $k => $v) {
	        		$v['photo'] = implode(',', $v['photo']);//照片处理
	        		$is_id = M('news')->where('_id',$v['_id'])->count();
	        		if ($is_id) {
	        			continue;
	        		}else{
	        			$data = array(
		        			'_id' => $v['_id'],
		        			'title' => $v['title'],
		        			'url' => $v['url'],
		        			'picture' => $v['photo'],
		        			'status' => 0,
		        			'type' => 1,
		        			'cate' => 2,
		        			'statement' => 1,
		        			'create_time' => time(),
	        			);  
	        			$res = M('news')->add($data);
	        		}
        		}
        	}
        }
        if ($code == 'juhe') {
        	$list_arr = $arr['result']['data'];
        	if ($list_arr) {
        		$res[$key] = model('news')->addNews($list_arr);
        	}
        }
        if ($res) {
        	$this->success("操作成功",U('Admin/news/newsList'));
        }else{
        	$this->error("操作失败",U('Admin/news/getNews'));
        }
	}
	/*
	* 定时任务,自动获取新闻,采集新闻内容
	*/
	public function autoGetNewsList(){
		ini_set('memory_limit', '500M');
        set_time_limit(0);//无超时验证
		$all_url = 'http://v.juhe.cn/toutiao/index';		//聚合新闻数据
		$appkey = '468c41b4c59a77a5047c1ccddf79ed70';
		$news_type = $this->news_type;
		foreach ($news_type as $key => $value) {
			$url = $all_url.'?type='.$value.'&key='.$appkey;
			$url_res = request_post($url);
        	$arr = json_decode($url_res,true);
        	$list_arr = $arr['result']['data'];
        	if ($list_arr) {
        		$res[$key] = model('news')->addNews($list_arr);
        	}
		}
		var_dump($res);
	}

	
	

}