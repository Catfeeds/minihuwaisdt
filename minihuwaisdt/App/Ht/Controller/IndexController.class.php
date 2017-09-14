<?php
namespace Ht\Controller;
use Think\Controller;

class IndexController extends PublicController{
	//***********************************
	// iframe式显示菜单和index页
	//**********************************
	public function index(){
	    $menu="";
	    $index="";
      $menu="<include File='Page/adminusermenu'/>";
      $index="<iframe src='".U('Page/adminindex')."' id='iframe' name='iframe'></iframe>";

       //版权
       $copy=M('admin_app')->where('id=1')->getField('name');
       $this->assign('copy',$copy);
       $this->assign('menu',$menu);
       $this->assign('index',$index);
	   $this->display();
	}
/**
	 * [welcome 首页]
	 * @return [type] [description]
	 */
	public function welcome(){
		$ip=get_client_ip();
		$todaytime=mktime(0,0,0,date('m'),date('d'),date('Y'));
		$beginThismonth=mktime(0,0,0,date('m'),1,date('Y'));
		// 模型
		$order=M('order');
		$user=M('user');
		$product=M("product");
		$news=M("news");

		//订单数、用户数、交易额、资讯数、产品数 总数
		$ordernum=$order->where("del=0")->count();
		$usernum=$user->where("del=0")->count();
		$productnum=$product->where("del=0")->count();
		$newsnum=$news->count();

		$paylist=$order->where("del=0 AND status>10 AND back!=2")->field("price")->select();
		$money=0;
		foreach ($paylist as $k => $v) {
			$money=$money+$v['price'];
		}

		//订单数、用户数、交易额、品牌数、产品数 今日
		$today_ordernum=$order->where("del=0 AND addtime>=".$todaytime)->count();
		$today_usernum=$user->where("del=0 AND addtime>=".$todaytime)->count();
		$today_productnum=$product->where("del=0 AND addtime>=".$todaytime)->count();
		$today_newsnum=$news->where("addtime>=".$todaytime)->count();
		$today_paylist=$order->where("del=0 AND status>10 AND back!=2 AND addtime>=".$todaytime)->field("price")->count();
		$today_money=0;
		foreach ($today_paylist as $k => $v) {
			$today_money=$today_money+$v['price'];
		}

		$productnum=$product->where("del=0")->count();
		$today_productnum=$product->where("del=0 AND addtime>=".$todaytime)->count();

		//订单数、用户数、交易额、品牌数、产品数 本月 
		//订单绘图
		$thismonthorder=$order->where("del=0 AND back!=2 AND addtime>=".$beginThismonth)->count();
		$thismonth_usernum=$user->where("del=0 AND addtime>=".$beginThismonth)->count();
		$thismonth_productnum=$product->where("del=0 AND addtime>=".$beginThismonth)->count();
		$thismonth_newsnum=$news->where("addtime>=".$beginThismonth)->count();
		$thismonthpaylist=$order->where("del=0 AND back!=2 AND addtime>=".$beginThismonth)->field("price")->select();
		$thismonthmoney=0;
		foreach ($thismonthpay as $k => $v) {
			$thismonthmoney=$thismonthmoney+$v['price'];
		}

		$this->assign("usernum",$usernum);
		$this->assign("money",$money);
		$this->assign("ordernum",$ordernum);
		$this->assign("productnum",$productnum);
		$this->assign("newsnum",$newsnum);

		$this->assign("today_usernum",$today_usernum);
		$this->assign("today_money",$today_money);
		$this->assign("today_ordernum",$today_ordernum);
		$this->assign("today_productnum",$today_productnum);
		$this->assign("today_newsnum",$today_newsnum);

		$this->assign("thismonth_usernum",$thismonth_usernum);
		$this->assign("thismonth_productnum",$thismonth_productnum);
		$this->assign("thismonth_newsnum",$thismonth_newsnum);
		$this->assign("thismonthmoney",$thismonthmoney);//本月的销售额
		$this->assign("thismonthorder",$thismonthorder);//本月订单数
		$this->assign("ip",$ip);
		$this->display();
	}	
}