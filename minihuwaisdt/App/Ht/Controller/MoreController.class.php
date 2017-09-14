<?php
namespace Ht\Controller;
use Think\Controller;
class MoreController extends PublicController{
	/*
	*
	* 构造函数，用于导入外部文件和公共方法
	*/
	public function _initialize(){
		$this->category = M('category');
		// 获取所有分类，进行关系划分
		$list = $this->category->where('tid=0 AND bz_4<2 AND tid!=10 AND id!=10')->order('sort desc,id asc')->field('id,tid,name,bz_2,bz_4')->select();
		foreach ($list as $k1 => $v1) {
			$list[$k1]['list2'] = $this->category->where('tid='.intval($v1['id']))->field('id,tid,name,bz_2')->select();
			foreach ($list[$k1]['list2'] as $k2 => $v2) {
				$list[$k1]['list2'][$k2]['list3'] = $this->category->where('tid='.intval($v2['id']))->field('id,tid,name,bz_2')->select();
			}
		}

		$this->assign('procat',$list);// 赋值数据集
	}
	//*************************
	//单页设置
	//*************************
	public function pweb_gl(){
		//获取web表的数据进行输出
		$model=M('web');
		$list=$model->select();
		//dump($list);exit;
		//=================
		//将变量进行输出
		//=================
		$this->assign('list',$list);	
		$this->display();
	}

	//*************************
	//单页设置修改
	//*************************
	public function pweb(){
		if(IS_POST){
			if(intval($_POST['id'])){
				$data = array();
				$data['content'] = $_POST['content'];
				$data['sort'] = intval($_POST['sort']);
				$data['addtime'] = time();
				$up = M('web')->where('id='.intval($_POST['id']))->save($data);
				if ($up) {
					$this->success('保存成功！');
					exit();
				}else{
					$this->error('操作失败！');
					exit();
				}

			}else{
				$this->error('系统错误！');
				exit();
			}
		}else{
			$this->assign('datas',M('web')->where(M('web')->getPk().'='.I('get.id'))->find());
			$this->display();
		}
	}


	//*************************
	// 小程序配置 设置页面
	//*************************
	public function setup(){
		if(IS_POST){
			//构建数组
			M('program')->create();
			//上传产品分类缩略图
			if (!empty($_FILES["file2"]["tmp_name"])) {
				//文件上传
				$info2 = $this->upload_images($_FILES["file2"],array('jpg','png','jpeg'),"logo");
			    if(!is_array($info2)) {// 上传错误提示错误信息
			        $this->error($info2);
			    }else{// 上传成功 获取上传文件信息
				    M('program')->logo = 'UploadFiles/'.$info2['savepath'].$info2['savename'];
			    }
			}
			M('program')->uptime=time();

			$check = M('program')->where('id=1')->getField('id');
			if (intval($check)) {
				$up = M('program')->where('id=1')->save();
			}else{
				M('program')->id=1;
				$up = M('program')->add();
			}

			if ($up) {
				$this->success('保存成功！');
				exit();
			}else {
				$this->error('操作失败！');
				exit();
			}
			
		}else{
			$this->assign('info',M('program')->where('id=1')->find());
			$this->display();
		}

	}
	//*************************
	// 首页图标 设置
	//*************************
	public function indextopcat(){
		$list = M('index_topcat')->select();

		$this->assign('list',$list);
		$this->display();
	}

	//*************************
	// 首页图标 设置
	//*************************
	public function addtopcat(){
		$info = M('index_topcat')->where('id='.intval($_REQUEST['id']))->find();

		$this->assign('info',$info);
		$this->display();
	}

	//*************************
	// 首页图标 设置
	//*************************
	public function savetopcat(){
		$id = intval($_REQUEST['id']);
		if (!$id) {
			$this->error('参数错误');
			exit();
		}

		$data = array();
		//上传产品分类缩略图
		if (!empty($_FILES["file"]["tmp_name"])) {
			//文件上传
			$info = $this->upload_images($_FILES["file"],array('jpg','png','jpeg'),"category/".date(Ymd));
			if(!is_array($info)) {// 上传错误提示错误信息
				$this->error($info);
				exit();
			}else{// 上传成功 获取上传文件信息
				$data['photo'] = 'UploadFiles/'.$info['savepath'].$info['savename'];
				// $xt = $this->news->where('id='.intval($id))->field('photo')->find();
				// if (intval($_POST['news_id']) && $xt['photo']) {
				// 	$img_url = "Data/".$xt['photo'];
				// 	if(file_exists($img_url)) {
				// 		@unlink($img_url);
				// 	}
				// }
			}
		}

		$data['cid'] = intval($_REQUEST['cid']);
		$data['name'] = M('category')->where('id='.intval($_REQUEST['cid']))->getField('name');
		$res = M('index_topcat')->where('id='.intval($id))->save($data);
		if ($res) {
			$this->success('保存成功！','indextopcat');
			exit();
		}else{
			$this->error('操作失败！');
			exit();
		}
	}




	//*************************
	// 首页图标 设置
	//*************************
	public function indexmidcat(){
		$list = M('index_midcat')->where('1=1')->select();

		$this->assign('list',$list);
		$this->display();
	}

	//*************************
	// 首页图标 设置
	//*************************
	public function addmidcat(){
		$info = M('index_midcat')->where('id='.intval($_REQUEST['id']))->find();

		$this->assign('info',$info);
		$this->display();
	}

	//*************************
	// 首页图标 设置
	//*************************
	public function savemidcat(){
		$id = intval($_REQUEST['id']);
		if (!$id) {
			$this->error('参数错误');
			exit();
		}

		$data = array();
		//上传产品分类缩略图
		if (!empty($_FILES["file"]["tmp_name"])) {
			//文件上传
			$info = $this->upload_images($_FILES["file"],array('jpg','png','jpeg'),"category/".date(Ymd));
			if(!is_array($info)) {// 上传错误提示错误信息
				$this->error($info);
				exit();
			}else{// 上传成功 获取上传文件信息
				$data['photo'] = 'UploadFiles/'.$info['savepath'].$info['savename'];
			}
		}

		$data['cid'] = intval($_REQUEST['cid']);
		$data['digest'] = $_REQUEST['digest'];
		$data['name'] = M('category')->where('id='.intval($_REQUEST['cid']))->getField('name');
		$res = M('index_midcat')->where('id='.intval($id))->save($data);
		if ($res) {
			$this->success('保存成功！','indexmidcat');
			exit();
		}else{
			$this->error('操作失败！');
			exit();
		}
	}

}