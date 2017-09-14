<?php
// 本类由系统自动生成，仅供测试用途
namespace Api\Controller;
use Think\Controller;
class ProductController extends PublicController {
	//***************************
	//  获取商品详情信息接口
	//***************************
    public function index(){
		$product=M("product");

		$pro_id = intval($_REQUEST['pro_id']);
		if (!$pro_id) {
			echo json_encode(array('status'=>0,'err'=>'商品不存在或已下架！'));
			exit();
		}
		
		$pro = $product->where('id='.intval($pro_id).' AND del=0 AND is_down=0')->find();
		if(!$pro){
			echo json_encode(array('status'=>0,'err'=>'商品不存在或已下架！'.__LINE__));
			exit();
		}

		$pro['photo_x'] =__DATAURL__.$pro['photo_x'];
		$pro['photo_d'] = __DATAURL__.$pro['photo_d'];
		$pro['brand'] = M('brand')->where('id='.intval($pro['brand_id']))->getField('name');
		$pro['cat_name'] = M('category')->where('id='.intval($pro['cid']))->getField('name');

		//图片轮播数组
		$img=explode(',',trim($pro['photo_string'],","));
		$b=array();
		if ($pro['photo_string']) {
			foreach ($img as $k => $v) {
				$b[] = __DATAURL__.$v;
			}
		}else{
			$b[] = $pro['photo_d'];
		}
		$pro['img_arr']=$b;//图片轮播数组
		
		//处理产品属性
		$catlist=array();
		if($pro['pro_buff']){//如果产品属性有值才进行数据组装
			$pro_buff = explode(',',$pro['pro_buff']);
			$commodityAttr=array();//产品库还剩下的产品规格
			$attrValueList=array();//产品所有的产品规格
			foreach($pro_buff as $key=>$val){
				$attr_name = M('attribute')->where('id='.intval($val))->getField('attr_name');
				$guigelist=M('guige')->where("attr_id=".intval($val).' AND pid='.intval($pro['id']))->field("id,name")->select();
				$ggss = array();
				$gg=array();
				foreach ($guigelist as $k => $v) {
					$gg[$k]['attrKey']=$attr_name;
					$gg[$k]['attrValue']=$v['name'];
					$ggss[] = $v['name'];
				}
				$commodityAttr[$key]['attrValueList'] = $gg;
				$attrValueList[$key]['attrKey']=$attr_name;
				$attrValueList[$key]['attrValueList']=$ggss;
			}
		}

		$content = str_replace(C('content.dir'), __DATAURL__ , $pro['content']);
		$pro['content']= html_entity_decode($content, ENT_QUOTES ,'utf-8');

		//检测产品是否收藏
		$col = M('product_sc')->where('uid='.intval($_REQUEST['uid']).' AND pid='.intval($pro_id))->getField('id');
		if ($col) {
			$pro['collect']= 1;
		}else{
			$pro['collect']= 0;
		}
		echo json_encode(array('status'=>1,'pro'=>$pro,'commodityAttr'=>$commodityAttr,'attrValueList'=>$attrValueList));
		exit();

	}

	//***************************
	//  获取商品详情接口
	//***************************
	public function details(){
		header('Content-type:text/html; Charset=utf8');
		$pro_id = intval($_REQUEST['pro_id']);
		$pro = M('product')->where('id='.intval($pro_id).' AND del=0 AND is_down=0')->find();
		if(!$pro){
			echo json_encode(array('status'=>0,'err'=>'商品不存在或已下架！'));
			exit();
		}
		//$content = preg_replace("/width:.+?[\d]+px;/",'',$pro['content']);
		$content = htmlspecialchars_decode($pro['content']);
		echo json_encode(array('status'=>1,'content'=>$content));
		exit();
	}

	//***************************
	//  下单信息预处理
	//  处理产品信息，用户地址
	//  uid:uid,pid:pro_id,aid:addr_id,sid:shop_id,buff:buff,num:num,price_yh:price_yh,p_price:p_price,price:z_price,type:pay_type,yunfei:yun_id,cart_id:cart_id,remark:ly
	//***************************
	public function make_order(){
		header('Content-type:text/html; Charset=utf8');
		//产品
		$pro_id = I('request.pro_id');
		//$uid=I('request.uid');
		$uid=1;
		//获得产品信息
		$pro = M('product')->field('id,photo_x,name,price,price_yh')->where('id='.intval($pro_id).' AND del=0 AND is_down=0')->find();
		$pro['photo_x']=__DATAURL__.$pro['photo_x'];
		if(!$pro){
			echo json_encode(array('status'=>0,'err'=>'商品不存在或已下架！'));
			exit();
		}
		//获取地址
		$address="";
		$addr=M("address")->where("uid=$uid")->select();
		if($addr){
			foreach($addr as $k=>$v){
				if($v['is_default']==1){
					$address=$address[$k];
				}
			}
			if(!$address){
				$address=$addr[0];
			}
		}

		echo json_encode(array('status'=>1,'pro'=>$pro,'address'=>$address));
		exit();
	}
	//***************************
	//  获取商品详情接口
	//***************************
	public function get_buff(){
		$pro = M('product')->where('id='.intval($_POST['pro_id']).' AND del=0 AND is_down=0')->find();
		if(!$pro){
			echo json_encode(array('status'=>0,'err'=>'商品不存在或已下架！'.__LINE__));
			exit();
		}
		//处理产品属性
		$catlist=array();
		if($pro['pro_buff']){//如果产品属性有值才进行数据组装
			$pro_buff = explode(',',$pro['pro_buff']);
			$buff=array();
			foreach($pro_buff as $key=>$val){
				$attr_name = M('attribute')->where('id='.intval($val))->getField('attr_name');
				$guigelist=M('guige')->where("attr_id=".intval($val).' AND pid='.intval($pro['id']))->field("id,name")->select();
				$gg = array();$ggss = array();
				foreach ($guigelist as $k => $v) {
					$gg['attrKey'] = $attr_name;
					$gg['attr_id'] = $val;
					$gg['attrValue'] = $v['name'];
					$gg['selectedValue'] = $v['id'];
					$ggss[] = $gg;
				}
				$buff['attrValueList']=$ggss;
				$catlist[] = $buff;
			}
			echo json_encode(array('status'=>1,'buff'=>$catlist));
			exit();
		}else{
			echo json_encode(array('status'=>0));
			exit();
		}
	}

   	public function lists(){
 		$json="";
 		$catid=I('request.catid');//获得分类id
 		$sort=I('request.sort');//获得品牌id 这里的id是brand表里的cid
 		$keyword=I('post.keyword');

 		//排序 0最新 1 人气 2销量 3价格
 		switch ($sort) {
 			case '0':
 				$order="addtime desc";
 				break;
 			case '1':
 				$order="renqi desc";
 				break;
 			case '2':
 				$order="shiyong desc";
 				break;
 			case '3':
 				$order="price_yh desc";
 				break;
 			default:
 				$order="addtime desc";
 				break;
 		}
 		//条件
 		$where="1=1 AND pro_type=1 AND del=0 AND is_down=0";
 		if($catid){
 			$where.=" AND cid in(".$this->cid_tree($catid).")";
 		}
 		if($keyword) {
            $where.=' AND name LIKE "%'.$keyword.'%"';
        }
 		$product=M('product')->where($where)->order($order)->limit(20)->select();
 		//echo M('product')->_sql();exit;
 		$json = array();$json_arr = array();
 		foreach ($product as $k => $v) {
 			$json['id']=$v['id'];
 			$json['v_type']=$v['v_type'];
 			$json['name']=$v['name'];
 			$json['photo_x']=__DATAURL__.$v['photo_x'];
 			$json['price']=$v['price'];
 			$json['price_yh']=$v['price_yh'];
 			$json['shiyong']=$v['shiyong'];
 			$json['renqi']=$v['renqi'];
 			$json['company']=$v['company'];
 			$json_arr[] = $json;
 		}
 		$cat_name=M('category')->where("id=".intval($catid))->getField('name');
 		echo json_encode(array('status'=>1,'pro'=>$json_arr,'cat_name'=>$cat_name));
 		exit();
    }
    public function getlist(){
    	$json="";
 		$catid=I('request.catid');//获得分类id
 		$sort=I('request.sort');//获得品牌id 这里的id是brand表里的cid

 		$page=I('request.page');
 		if (!$page) {
 			$page=1;
 		}
 		$limit = intval($page*20)-20;

 		
 		$keyword=I('post.keyword');
 		//排序 0最新 1 人气 2销量 3价格
 		switch ($sort) {
 			case '0':
 				$order="addtime desc";
 				break;
 			case '1':
 				$order="renqi desc";
 				break;
 			case '2':
 				$order="shiyong desc";
 				break;
 			case '3':
 				$order="price_yh desc";
 				break;
 			default:
 				$order="addtime desc";
 				break;
 		}
 		//条件
 		$where="1=1 AND pro_type=1 AND del=0 AND is_down=0";
 		if($catid){
 			$where.=" AND cid in(".$this->cid_tree($catid).")";
 		}

 		$product=M('product')->where($where)->order($order)->limit($limit.',20')->select();
 		//echo M('product')->_sql();exit;
 		$json = array();$json_arr = array();
 		foreach ($product as $k => $v) {
 			$json['id']=$v['id'];
 			$json['name']=$v['name'];
 			$json['v_type']=$v['v_type'];
 			$json['photo_x']=__DATAURL__.$v['photo_x'];
 			$json['price']=$v['price'];
 			$json['price_yh']=$v['price_yh'];
 			$json['shiyong']=$v['shiyong'];
 			$json['renqi']=$v['renqi'];
 			$json['company']=$v['company'];
 			$json_arr[] = $json;
 		}
 		echo json_encode(array('status'=>1,'pro'=>$json_arr));
 		exit();
    }
	//***************************
	//  获取商品属性价格接口
	//***************************
	public function jiage(){
		$buff = trim($_POST['buff'],',');
		$buff_arr = trim($_POST['buff_arr'],',');
		$pid = intval($_POST['pid']);
		$pro_info = M('product')->where('id='.intval($pid))->find();
		if ($buff_arr && $pro_info) {
			$arr = explode(',', $buff_arr);
			$str = 0;
			foreach ($arr as $k => $v) {
				$price[] = M('guige')->where('id='.intval($v))->getField('price');
				$stock[] = M('guige')->where('id='.intval($v))->getField('stock');
			}

			rsort($price);
			sort($stock);
			//$price = implode(',', $price);
			echo json_encode(array('status'=>1,'price'=>$price[0],'stock'=>$stock[0]));
			exit();	
		}

		echo json_encode(array('status'=>0));
		exit();	
	}

	//***************************
	//  会员商品收藏接口
	//***************************
	public function shop_collect(){
		$uid = intval($_REQUEST['uid']);
		$pid = intval($_REQUEST['pid']);
		if (!$uid || !$pid) {
			echo json_encode(array('status'=>0,'err'=>'系统错误，请稍后再试.'));
			exit();
		}

		$check = M('product_sc')->where('uid='.intval($uid).' AND pid='.intval($pid))->getField('id');
		if ($check) {
			$res = M('product_sc')->where('id='.intval($check))->delete();
		}else{
			$data = array();
			$data['uid'] = intval($uid);
			$data['pid'] = intval($pid);
			$res = M('product_sc')->add($data);
		}
		
		if ($res) {
			echo json_encode(array('status'=>1));
			exit();
		}else{
			echo json_encode(array('status'=>0,'err'=>'网络错误..'));
			exit();
		}
	}

	//***************************
	//  获取抢购商品接口
	//***************************
	public function panic(){
		$json="";
 		$id=intval($_POST['cat_id']);//获得分类id 这里的id是pro表里的cid
 		// $id=44;
 		$type=I('post.type');//排序类型

 		$page= intval($_POST['page']) ? intval($_POST['page']) : 0;
 		$keyword=I('post.keyword');
 		//排序
 		$order="addtime desc";//默认按添加时间排序
 		//条件
 		$where="1=1 AND pro_type=2 AND del=0 AND is_down=0";
 		if(intval($id)){
 			$where.=" AND cid=".intval($id);
 		}

 		if($keyword) {
            $where.=' AND name LIKE "%'.$keyword.'%"';
        }

        //计算总页数
        $count = M('product')->where($where)->count();
        $the_page = ceil($count/8);
        $eachpage = 8;

 		$product=M('product')->where($where)->order($order)->limit($page.',8')->select();
 		//echo M('product')->_sql();exit;
 		$json = array();$json_arr = array();
 		foreach ($product as $k => $v) {
 			$json['id']=$v['id'];
 			$json['name']=$v['name'];
 			$json['photo_x']=__DATAURL__.$v['photo_x'];
 			$json['price']=$v['price'];
 			$json['price_yh']=$v['price_yh'];
 			$json['shiyong']=$v['shiyong'];
 			if ($v['start_time']>time()) {
 				$json['state'] = 1;
 				if ($v['start_time']<=strtotime(date("Y-m-d 23:59:59"))) {
 					$json['desc'] = date("H:i",$v['start_time']).'开启';
 				}else{
 					$json['desc'] = date("n月j日",$v['start_time']).'开启';
 				}
 			}elseif ($v['end_time']<time()) {
 				$json['state'] = 2;
 				$json['desc'] = '已结束';
 			}elseif (intval($v['num'])<1) {
 				$json['state'] = 3;
 				$json['desc'] = '已抢完';
 			}else{
 				$json['state'] = 4;
 				$json['desc'] = '立即抢购';
 			}
 			$json_arr[] = $json;
 		}

 		echo json_encode(array('status'=>1,'pro'=>$json_arr,'eachpage'=>$eachpage));
 		exit();
	}
	//查找二级分类下的所有子分类id，用逗号拼接 包含自身
    public function cid_tree($id=1){
		$Category = M('category');
		$list=$Category->where("tid=".$id)->order('sort desc,id asc')->select();
		//dump($list);exit;
		$cidstr='';
		foreach($list as $v){
			$json[]=$v['id'];
			$num=$Category->where("tid=".$v['id'])->field('id')->count();
			if($num>0){
				$json[]=$this->catid_tree($v['id']);
			}
		}
		$json[]=$id;
		$cidstr.=implode(',',$json);
		return $cidstr;		
	}
}