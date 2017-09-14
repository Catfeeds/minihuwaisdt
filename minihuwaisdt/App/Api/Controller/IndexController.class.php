<?php
namespace Api\Controller;
use Think\Controller;
class IndexController extends PublicController {
	//***************************
	//  首页数据接口
	//***************************
    public function index(){
    	//如果缓存首页没有数据，那么就读取数据库
    	/***********获取首页顶部轮播图************/
  //   	$ggtop=M('guanggao')->order('sort desc,id asc')->field('id,name,photo')->limit(10)->select();
		// foreach ($ggtop as $k => $v) {
		// 	$ggtop[$k]['photo']=__DATAURL__.$v['photo'];
		// 	$ggtop[$k]['name']=urlencode($v['name']);
		// }
    	/***********获取首页顶部轮播图 end************/
        //=================
        //  首页LOGO
        //=================
        $logolink=M("program")->where("id=1")->getField("logo");
        $logo=__DATAURL__.$logolink;
    	//======================
    	//首页推荐5个分类
    	//======================
    	$topcat = M('index_topcat')->select();
    	foreach ($topcat as $k => $v) {
    		$topcat[$k]['photo'] = __DATAURL__.$v['photo'];
    	}

        //======================
        //首页推荐分类3个
        //======================
        $midcat = M('index_midcat')->select();
        foreach ($midcat as $k => $v) {
            $midcat[$k]['photo'] = __DATAURL__.$v['photo'];
        }
    	//======================
    	//首页推荐产品
    	//======================
    	$pro_list = M('product')->where('del=0 AND pro_type=1 AND is_down=0 AND type=1')->order('sort desc,id desc')->field('id,name,photo_x,price_yh,price,num,is_show,shiyong,renqi,v_type')->limit(20)->select();
    	foreach ($pro_list as $k => $v) {
    		$pro_list[$k]['photo_x'] = __DATAURL__.$v['photo_x'];
    	}

    	echo json_encode(array('logo'=>$logo,'topcat'=>$topcat,'midcat'=>$midcat,'prolist'=>$pro_list));
    	exit();
    }
    /**
     * [getlist 加载更多]
     * @return [type] [description]
     */
    public function getlist(){
        $page = intval($_REQUEST['page']);
        $limit = intval($page*20)-20;

        $pro_list = M('product')->where('del=0 AND pro_type=1 AND is_down=0 AND type=1')->order('sort desc,id desc')->field('id,name,photo_x,price_yh,price,num,is_show,shiyong,renqi,v_type')->limit($limit.',20')->select();
        foreach ($pro_list as $k => $v) {
            $pro_list[$k]['photo_x'] = __DATAURL__.$v['photo_x'];
        }

        echo json_encode(array('prolist'=>$pro_list));
        exit();
    }


}