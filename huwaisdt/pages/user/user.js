// pages/user/user.js
var app = getApp()
Page( {
  data: {
    userInfo: {},
    orderInfo: {},
    projectSource: 'https://github.com/liuxuanqiang/wechat-weapp-mall',
    userListInfo: ''
  },
  onLoad: function () {
    var that = this
    //调用应用实例的方法获取全局数据
    app.getUserInfo(function (userInfo) {
      //更新数据
      that.setData({
        userInfo: userInfo,
        loadingHidden: true
      })
    });
    this.loadOrderStatus();
  },
  onShow: function () {
    var that = this;
    this.loadOrderStatus();
  },
  loadOrderStatus: function () {
    //获取用户订单数据
    var that = this;
    console.log(app.d.userId);
    console.log(app.globalData.userInfo);
    wx.request({
      url: app.d.myUrl + '/Api/User/getorder',
      method: 'post',
      data: {
        userId: app.globalData.userInfo.id,
      },
      header: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      success: function (res) {
        //--init data        
        var status = res.data.status;
        if (status == 1) {
          that.setData({
            orderInfo: res.data.orderInfo,
            shop_status: res.data.shop_status
          });
        } else {
          wx.showToast({
            title: res.data.err,
            duration: 2000
          });
        }
      },
      error: function (e) {
        wx.showToast({
          title: '网络异常！',
          duration: 2000
        });
      }
    });
  },

})