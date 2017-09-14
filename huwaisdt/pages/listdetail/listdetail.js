// pages/user/dingdan.js
//index.js  
//获取应用实例  
var app = getApp();
var common = require("../../utils/common.js");
Page( {  
  data: {  
    winWidth: 0,  
    winHeight: 0,  
    // tab切换  
    currentTab: 0,  
    isStatus:1,//1待付款，2待收货，3已完成
    page:2
  },  
  onLoad: function(options) { 
    var catid = options.catid;
    var that=this;
    that.initSystemInfo();
    that.setData({
      catid: catid,
      currentTab: 0,
      isStatus: that.getOrderStatus(),
    });
    that.loadProList();
  },  
  getOrderStatus:function(){
    return this.data.currentTab == 0 ? 1 : this.data.currentTab == 2 ?2 :this.data.currentTab == 3 ? 3:0;
  },
  loadProList: function(){
    var that = this;
    var catid=that.data.catid;
    wx.request({
      url: app.d.myUrl + '/Api/Product/lists',
      method: 'post',
      data: {
        sort: that.data.currentTab,
        catid: catid
      },
      header: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      success: function (res) {
        //--init data        
        var status = res.data.status;
        var pro = res.data.pro;
        var catname=res.data.cat_name;
        wx.setNavigationBarTitle({
          title: catname,
        })
        that.setData({
          prolist:pro,
          page:2,
        })
      },
      fail: function () {
        // fail
        wx.showToast({
          title: '网络异常！',
          duration: 2000
        });
      }
    });
  },
  initSystemInfo:function(){
    var that = this;  
  
    wx.getSystemInfo( {
      success: function( res ) {  
        that.setData( {  
          winWidth: res.windowWidth,  
          winHeight: res.windowHeight  
        });  
      }    
    });  
  },
  bindChange: function( e ) {  
  
    var that = this;  
    that.setData( { currentTab: e.detail.current });  
  
  },  
  swichNav: function (e) {
    var that = this;
    if (that.data.currentTab === e.target.dataset.current) {
      return false;
    } else {
      var current = e.target.dataset.current;
      that.setData({
        page: 2,
        currentTab: parseInt(current),
        isStatus: e.target.dataset.otype,
      });
      that.loadProList();
    };
  },
  //点击加载更多
  getMore: function (e) {
    var that = this;
    var page = that.data.page;
    wx.request({
      url: app.d.myUrl + '/Api/Product/getlist',
      method: 'post',
      data: { 
        sort: that.data.currentTab,
        catid: that.data.catid,
        page: page
      },
      header: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      success: function (res) {
        var prolist = res.data.pro;
        if (prolist == '') {
          wx.showToast({
            title: '没有更多数据！',
            duration: 2000
          });
          return false;
        }
        //that.initProductData(data);
        that.setData({
          page: page + 1,
          prolist: that.data.prolist.concat(prolist)
        });
        //endInitData
      },
      fail: function (e) {
        wx.showToast({
          title: '网络异常！',
          duration: 30000
        });
      }
    })
  },
})