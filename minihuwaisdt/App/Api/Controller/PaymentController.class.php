<?php
// 本类由系统自动生成，仅供测试用途
namespace Api\Controller;
use Think\Controller;
class PaymentController extends PublicController {


	//***************************
	//  会员立即购买获取数据接口
	//***************************
	public function buy_now(){
		$uid = intval($_REQUEST['uid']);
		if (!$uid) {
			echo json_encode(array('status'=>0,'err'=>'系统错误.'));
			exit();
		}
		//单件商品结算
		//地址管理
		$address=M("address");
		$city=M("china_city");
		$add=$address->where('uid='.intval($uid))->select();
		$citys=$city->where('tid=0')->field('id,name')->select();
		$shopping=M('shopping_char');
		$product=M("product");
		//运费
		$post = M('post');
        
        //立即购买数量
        $num=intval($_REQUEST['num']);
        if (!$num) {
        	$num=1;
        }

        //购物车id
        $cart_id = intval($_REQUEST['cart_id']);
        //检测购物车是否有对应数据
		$check_cart = $shopping->where('id='.intval($cart_id).' AND num>='.intval($num))->getField('pid');
		if (!$check_cart) {
			echo json_encode(array('status'=>0,'err'=>'购物车信息错误.'));
			exit();
		}
		//判断基本库存
		$pro_num = $product->where('id='.intval($check_cart))->getField('num');
		if ($num>intval($pro_num)) {
			echo json_encode(array('status'=>0,'err'=>'库存不足.'));
			exit();
		}
        
		$qz=C('DB_PREFIX');//前缀

		$pro=$shopping->where(''.$qz.'shopping_char.uid='.intval($uid).' and '.$qz.'shopping_char.id='.intval($cart_id))->join('LEFT JOIN __PRODUCT__ ON __PRODUCT__.id=__SHOPPING_CHAR__.pid')->join('LEFT JOIN __SHANGCHANG__ ON __SHANGCHANG__.id=__SHOPPING_CHAR__.shop_id')->field(''.$qz.'product.num as pnum,'.$qz.'shopping_char.id,'.$qz.'shopping_char.pid,'.$qz.'shangchang.name as sname,'.$qz.'product.name,'.$qz.'product.shop_id,'.$qz.'product.photo_x,'.$qz.'product.price_yh,'.$qz.'shopping_char.num,'.$qz.'shopping_char.buff,'.$qz.'shopping_char.price,'.$qz.'shangchang.alipay,'.$qz.'shangchang.alipay_pid,'.$qz.'shangchang.alipay_key')->find();
		//获取运费
		$yunfei = $post->where('pid='.intval($pro['shop_id']))->find();

		if($pro['buff']!=''){
		    $pro['zprice']=$pro['price']*$num;
		}else{
			$pro['price']=$pro['price_yh'];
		    $pro['zprice']=$pro['price']*$num;
		}

		//如果需要运费
		if ($yunfei) {
			if ($yunfei['price_max']>0 && $yunfei['price_max']<=$pro['zprice']) {
				$yunfei['price']=0;
			}
		}

		$buff_text='';
		if($pro['buff']){
			//获取属性名称
			$buff = explode(',',$pro['buff']);
			if(is_array($buff)){
				foreach($buff as $keys => $val){
					$ggid=M("guige")->where('id='.intval($val))->getField('name');
					//$buff_text .= select('name','aaa_cpy_category','id='.$val['id']).':'.select('name','aaa_cpy_category','id='.$val['val']).' ';
					$buff_text .=' '.$ggid.' ';
				}
			}
		}
		$pro['buff']=$buff_text;
		$pro['photo_x']='http://'.$_SERVER['SERVER_NAME'].__UPLOAD__.'/'.$pro['photo_x'];

		echo json_encode(array('status'=>1,'citys'=>$citys,'yun'=>$yunfei,'adds'=>$add,'pro'=>$pro,'num'=>$num,'buff'=>$buff_text));
		exit();
		//$this->assign('citys',$citys);
	}

	//***************************
	//  会员立即购买下单接口
	//***************************
	public function pay_now(){
		$product=M("product");
		//运费
		$post = M('post');
		$order=M("order");
		$order_pro=M("order_product");

		$uid = intval($_REQUEST['uid']);
		if (!$uid) {
			echo json_encode(array('status'=>0,'err'=>'登录状态异常.'));
			exit();
		}

		//下单
			try {	
				$data = array();
				$data['shop_id']=intval($_POST['sid']);
				$data['uid']=intval($uid);
				$data['addtime']=time();
				$data['del']=0; 
				$data['type']=trim($_POST['paytype']);
				//订单状态 10未付款20代发货30确认收货（待收货）40交易关闭50交易完成
				$data['status']=10;//未付款

				//dump($_POST);exit;
				$_POST['yunfei'] ? $yunPrice = $post->where('id='.intval($_POST['yunfei']))->find() : NULL;
				//dump($yunPrice);exit;
				if(!empty($yunPrice)){
	                $data['post'] = $yunPrice['id'];
	                $data['price']=$_POST['price']+$yunPrice['price'];
				}else{
	                $data['post'] = 0;
	                $data['price']=$_POST['price'];
				}

				$adds_id = intval($_POST['aid']);
				if (!$adds_id) {
					echo json_encode(array('status'=>0,'err'=>'请选择收货地址.'.__LINE__));
					exit();
				}

				$adds_info = M('address')->where('id='.intval($adds_id))->find();
				$data['receiver']=$adds_info['name'];
				$data['tel']=$adds_info['tel'];
				$data['address_xq']=$adds_info['address_xq'];
				$data['code']=$adds_info['code'];
				$data['product_num']=intval($_POST['num']);
				$data['remark']=$_POST['remark'];
				/*******解决屠涂同一订单重复支付问题 lisa**********/
				$data['order_sn']=$this->build_order_no();//生成唯一订单号

				if (!$data['product_num'] || !$data['price']) {
					throw new \Exception("System Error !");
				}

				/**************************************************/
				//dump($data);exit;
				$result = $order->add($data);
				if($result){
					$date =array();
					$date['pid']=intval($_POST['pid']);//商品id
					$date['order_id']=$result;//订单id
					$date['name']=$product->where('id='.intval($date['pid']))->getField('name');//商品名字
					$date['price']=$product->where('id='.intval($date['pid']))->getField('price_yh');
					$date['pro_buff']=$_POST['buff'];
					$date['photo_x']=$product->where('id='.intval($date['pid']))->getField('photo_x');
					$date['pro_buff']=$_POST['buff'];
					$date['addtime']=time();
					$date['num']=intval($_POST['num']);
					//$date['pro_guige']=$_REQUEST['guige'];
					$res = $order_pro->add($date);
					if(!$res){
						throw new \Exception("下单 失败！".__LINE__);
					}

	            	//检查产品是否存在，并修改库存
					$check_pro = $product->where('id='.intval($date['pid']).' AND del=0 AND is_down=0')->field('num,shiyong')->find();
					if (!$check_pro) {
						throw new \Exception("商品不存在或已下架！");
					}
					$up = array();
					$up['num'] = intval($check_pro['num'])-intval($date['num']);
					$up['shiyong'] = intval($check_pro['shiyong'])+intval($date['num']);
					$product->where('id='.intval($date['pid']))->save($up);

					$url=$_SERVER['HTTP_REFERER'];
					
				}else{
					throw new \Exception("下单 失败！");
				}
			} catch (Exception $e) {
				echo json_encode(array('status'=>0,'err'=>$e->getMessage()));
				exit();
			}
			//把需要的数据返回
			$arr = array();
			$arr['order_id'] = $result;
			$arr['order_sn'] = $data['order_sn'];
			$arr['pay_type'] = $_POST['paytype'];
			echo json_encode(array('status'=>1,'arr'=>$arr));
			exit();
	}

	//**********************************
    // 购物车结算 获取数据
    //***********************************
	public function buy_cart(){
		$uid = intval($_REQUEST['uid']);
		if (!$uid) {
			echo json_encode(array('status'=>0,'err'=>'登录状态异常.'));
			exit();
		}

		$address=M("address");
		//运费
		$post = M('post');
		$qz=C('DB_PREFIX');
		$add=$address->where('uid='.intval($uid))->order('is_default desc,sort desc')->select();
		$product=M("product");
		$shopping=M('shopping_char');
		$cart_id = trim($_REQUEST['cart_id'],',');
		$id=explode(',', $cart_id);
		if (!$cart_id) {
			echo json_encode(array('status'=>0,'err'=>'网络异常.'.__LINE__));
			exit();
		}

		$pro=array();
		$pro1=array();
		foreach($id as $k => $v){
			//检测购物车是否有对应数据
			$check_cart = $shopping->where('id='.intval($v))->getField('id');
			if (!$check_cart) {
				echo json_encode(array('status'=>0,'err'=>'非法操作.'.__LINE__));
				exit();
			}

			$pro[$k]=$shopping->where(''.$qz.'shopping_char.uid='.intval($uid).' and '.$qz.'shopping_char.id='.$v)->join('LEFT JOIN __PRODUCT__ ON __PRODUCT__.id=__SHOPPING_CHAR__.pid')->join('LEFT JOIN __SHANGCHANG__ ON __SHANGCHANG__.id=__SHOPPING_CHAR__.shop_id')->field(''.$qz.'product.num as pnum,'.$qz.'shopping_char.id,'.$qz.'shangchang.name as sname,'.$qz.'product.name,'.$qz.'product.shop_id,'.$qz.'product.photo_x,'.$qz.'product.price_yh,'.$qz.'shopping_char.num,'.$qz.'shopping_char.buff,'.$qz.'shopping_char.price,'.$qz.'shangchang.alipay,'.$qz.'shangchang.alipay_pid,'.$qz.'shangchang.alipay_key')->find();
		    //获取运费
		    $yunfei = $post->where('pid='.intval($pro[$k]['shop_id']))->find();
		    //dump($yunfei);
		    if($pro[$k]['buff']!=''){
		    	$pro[$k]['zprice']=$pro[$k]['price']*$pro[$k]['num'];
		    }else{
		    	$pro[$k]['price']=$pro[$k]['price_yh'];
		    	$pro[$k]['zprice']=$pro[$k]['price']*$pro[$k]['num'];
		    }
		    $pro[$k]['photo_x'] = 'http://'.$_SERVER['SERVER_NAME'].__UPLOAD__.'/'.$pro[$k]['photo_x'];
			//$pro['zprice']+=$pro[$k]['zprice'];
		    $buff_text='';
			if($pro[$k]['buff']){
				//验证属性
				$buff = explode(',',$pro[$k]['buff']);
				if(is_array($buff)){
					foreach($buff as $keys => $val){
						$ggid=M("guige")->where('id='.intval($val))->getField('name');
						//$buff_text .= select('name','aaa_cpy_category','id='.$val['id']).':'.select('name','aaa_cpy_category','id='.$val['val']).' ';
						$buff_text .=' '.$ggid.' ';
					}
				}
			}
		 	$pro[$k]['buff']=$buff_text;
		}

		//计算总价
	    foreach($id as $ks => $vs){
			$pro1[$ks]=$shopping->where(''.$qz.'shopping_char.uid='.intval($uid).' and '.$qz.'shopping_char.id='.$vs)->join('LEFT JOIN __PRODUCT__ ON __PRODUCT__.id=__SHOPPING_CHAR__.pid')->join('LEFT JOIN __SHANGCHANG__ ON __SHANGCHANG__.id=__SHOPPING_CHAR__.shop_id')->field(''.$qz.'product.num as pnum,'.$qz.'shopping_char.id,'.$qz.'shangchang.name as sname,'.$qz.'product.name,'.$qz.'product.photo_x,'.$qz.'product.price_yh,'.$qz.'shopping_char.num,'.$qz.'shopping_char.buff,'.$qz.'shopping_char.price')->find();
		    if($pro1[$ks]['buff']){
		    	$pro1[$ks]['zprice']=$pro1[$ks]['price']*$pro1[$ks]['num'];
		    }else{
		    	$pro1[$ks]['price']=$pro1[$ks]['price_yh'];
		    	$pro1[$ks]['zprice']=$pro1[$ks]['price']*$pro1[$ks]['num'];
		    }
			$price+=$pro1[$ks]['zprice'];
		}

		//如果需要运费
		if ($yunfei) {
			if ($yunfei['price_max']>0 && $yunfei['price_max']<=$price) {
				$yunfei['price']=0;
			}
		}
		//如果token为空则生成一个token 
        /* if(!isset($_SESSION['token']) || $_SESSION['token']=='') { 
             $this->set_token(); 
        } 
        if(isset($_POST['submit'])){
		    if(!$this->valid_token()){ 
		    	$this->success('您已成功购买！可以去我的订单查看',U("User/orders",array('key'=>$_GET['key'])));
		        exit();
		    }
        }*/
        $citys=M('china_city')->where('tid=0')->field('id,name')->select();
        echo json_encode(array('status'=>1,'citys'=>$citys,'price'=>$price,'pro'=>$pro,'adds'=>$add,'yun'=>$yunfei));
		exit();
	}

	//**********************************
    // 购物车结算 下订单
    //***********************************
    public function payment(){
    	$product=M("product");
		//运费
		$post = M('post');
		$order=M("order");
		$order_pro=M("order_product");
		$shopping=M('shopping_char');

		$uid = intval($_REQUEST['uid']);
		if (!$uid) {
			echo json_encode(array('status'=>0,'err'=>'登录状态异常.'));
			exit();
		}

		$cart_id = trim($_REQUEST['cart_id'],',');
		if (!$cart_id) {
			echo json_encode(array('status'=>0,'err'=>'数据异常.'));
			exit();
		}

		//生成订单
		  try {
		  	$qz=C('DB_PREFIX');//前缀

		  	$cart_id = explode(',', $cart_id);
			$shop=array();
			foreach($cart_id as $ke => $vl){
				$shop[$ke]=$shopping->where(''.$qz.'shopping_char.uid='.intval($uid).' and '.$qz.'shopping_char.id='.$vl)->join('LEFT JOIN __PRODUCT__ ON __PRODUCT__.id=__SHOPPING_CHAR__.pid')->field(''.$qz.'shopping_char.pid,'.$qz.'shopping_char.num,'.$qz.'shopping_char.shop_id,'.$qz.'shopping_char.buff,'.$qz.'product.name,'.$qz.'product.photo_x,'.$qz.'product.logo')->find();
				//$img.=$shop[$ke]['logo'].'|';
				$num+=$shop[$ke]['num'];
				$date['name']=$shop[$ke]['name'];
                $shang=$shop[0]['shop_id'];
			}

			$yunPrice = array();
			if ($_POST['yunfei']) {
				$yunPrice = $post->where('id='.intval($_POST['yunfei']))->find();
			}
			
			$data['shop_id']=$shop[$ke]['shop_id'];
			$data['uid']=intval($uid);

            if(!empty($yunPrice)){
                $data['post'] = $yunPrice['id'];
                $data['price']=$_POST['price']+$yunPrice['price'];
			}else{
                $data['post'] = 0;
                $data['price']=$_POST['price'];
			}
			//$data['price']=$_REQUEST['price']+$yunPrice[0]['price'];
			//$data['post'] = $yunPrice[0]['id'];

			$data['addtime']=time();
			$data['del']=0;
			$data['type']=$_POST['type'];
			$data['status']=10;

			$adds_id = intval($_POST['aid']);
			if (!$adds_id) {
				throw new \Exception("请选择收货地址.".__LINE__);
			}
			$adds_info = M('address')->where('id='.intval($adds_id))->find();
			$data['receiver']=$adds_info['name'];
			$data['tel']=$adds_info['tel'];
			$data['address_xq']=$adds_info['address_xq'];
			$data['code']=$adds_info['code'];

			$data['product_num']=$num;
			//$data['product_img']=rtrim($img,'|');
			$data['remark']=$_REQUEST['remark'];
			$data['order_sn']=$this->build_order_no();//生成唯一订单号

			$result = $order->add($data);
		    if($result){
	            //$prid = explode(",", $_POST['ids']);
			    foreach($cart_id as $key => $var){
					$shops[$key]=$shopping->where(''.$qz.'shopping_char.uid='.intval($uid).' and '.$qz.'shopping_char.id='.intval($var))->join('LEFT JOIN __PRODUCT__ ON __PRODUCT__.id=__SHOPPING_CHAR__.pid')->field(''.$qz.'shopping_char.pid,'.$qz.'shopping_char.num,'.$qz.'shopping_char.shop_id,'.$qz.'shopping_char.buff,'.$qz.'shopping_char.price,'.$qz.'product.name,'.$qz.'product.logo,'.$qz.'product.price_yh,'.$qz.'product.num as pnum')->find();
				    if($shops[$key]['buff']!=''){
				    	$shops[$key]['zprice']=$shops[$key]['price']*$shops[$key]['num'];
			            $guige_list='';
			        }else{
				    	$shops[$key]['price']=$shops[$key]['price_yh'];
				    	$shops[$key]['zprice']=$shops[$key]['price']*$shops[$key]['num'];
				    	$guige_list="";
			        }
			        $buff_text='';
					if($shops[$key]['buff']){
					   //验证属性
						$buff = explode(',',$shops[$key]['buff']);
						if(is_array($buff)){
							foreach($buff as $keys => $val){
								$ggid=M("guige")->where('id='.intval($val))->getField('name');
								$buff_text .= $ggid.' ';
							};
						}
					}
					$date = array();
			        $date['pid']=$shops[$key]['pid'];
					$date['name']=$shops[$key]['name'];
			        $date['order_id']=$result;
					$date['price']=$shops[$key]['price'];
					$date['photo_x']=$shops[$key]['logo'];
					$date['pro_buff']=trim($buff_text,' ');
					$date['addtime']=time();
					$date['num']=$shops[$key]['num'];
					$date['pro_guige']=$guige_list;
					$res = $order_pro->add($date);
					if (!$res) {
						throw new \Exception("下单 失败！".__LINE__);
					}
					//检查产品是否存在，并修改库存
					$check_pro = $product->where('id='.intval($date['pid']).' AND del=0 AND is_down=0')->field('num,shiyong')->find();
					if (!$check_pro) {
						throw new \Exception("商品不存在或已下架！");
					}
					$up = array();
					$up['num'] = intval($check_pro['num'])-intval($date['num']);
					$up['shiyong'] = intval($check_pro['shiyong'])+intval($date['num']);
					$product->where('id='.intval($date['pid']))->save($up);
	            	//echo  $product->getLastSql();
	            	//删除购物车数据
	            	$shopping->where('uid='.intval($uid).' AND id='.intval($var))->delete();
					
				}
				$url=$_SERVER['HTTP_REFERER'];
				/*if($_REQUEST['type']=='alipay'){
					$data = R('Pay/doalipay',array($data['shop_id'],$data['order_sn'],$_GET['key']));
					//$data = R('Pay/doalipay',array($_REQUEST['pid'],$result,$key));
				}*/
				//$data = R('Pay/doalipay',array($shang));
			}else{
				throw new \Exception("下单 失败！");
			}
		  } catch (Exception $e) {
		  	echo json_encode(array('status'=>0,'err'=>$e->getMessage()));
		  	exit();
		  }
		  
		    //把需要的数据返回
			$arr = array();
			$arr['order_id'] = $result;
			$arr['order_sn'] = $data['order_sn'];
			$arr['pay_type'] = $_POST['type'];
			echo json_encode(array('status'=>1,'arr'=>$arr));
			exit();	
    }

	public function ceshi(){
		print_r("adads");die();
	}

	/**针对涂屠生成唯一订单号
	*@return int 返回16位的唯一订单号
	*/
	public function build_order_no(){
		return date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
	}
}