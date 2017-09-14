var app = getApp();

//index.js
Page({
  data: {
    indicatorDots: true,
    autoplay: true,
    interval: 5000,
    duration: 1000,
    circular: true,
    productData: [],
    page: 2,
  },
toprocat: function (e) {
  console.log(e.currentTarget.dataset.id)
  var catid =e.currentTarget.dataset.id;
   wx.navigateTo({
     url: '../listdetail/listdetail?catid='+catid
  });
}, 
totuijian:function(e){
    console.log(e.currentTarget.dataset.id)
    wx.navigateTo({
      url: '../listdetail/listdetail?catid='+e.currentTarget.dataset.id,
    })
  },
 dangtian:function(e){
    console.log(e.currentTarget.dataset.title)
    wx.navigateTo({
      url: '../ritual/ritual?title='+e.currentTarget.dataset.title,
      success: function(res){
        // success
      },
      fail: function() {
        // fail
      },
      complete: function() {
        // complete
      }
    })
  },
 inpu: function (e) {
   console.log(e.currentTarget.dataset.title)
   wx.navigateTo({
     url: '../search/search?title=' + e.currentTarget.dataset.title,
     success: function (res) {
       // success
     },
     fail: function () {
       // fail
     },
     complete: function () {
       // complete
     }
   })
 },
 changeIndicatorDots: function (e) {
    this.setData({
      indicatorDots: !this.data.indicatorDots
    })
  },
  changeAutoplay: function (e) {
    this.setData({
      autoplay: !this.data.autoplay
    })
  },
  intervalChange: function (e) {
    this.setData({
      interval: e.detail.value
    })
  },
  durationChange: function (e) {
    this.setData({
      duration: e.detail.value
    })
  },
  //商品连接数据 
  initProductData: function (data){
    for(var i=0; i<data.length; i++){
      console.log(data[i]);
      var item = data[i];
      item.Price = item.Price/100;
      // item.Price = 100;
      item.ImgUrl = app.d.hostImg + item.ImgUrl;   
    }
  },
  onLoad: function (options) {
    var that = this;
    wx.request({
      url: app.d.myUrl + '/Api/Index/index',
      method: 'post',
      data: {},
      header: {
        'content-type': 'application/json',
      },
      success: function (res) {
        var logo=res.data.logo;
        var topcat = res.data.topcat;
        var midcat = res.data.midcat;
        var prolist = res.data.prolist;
        var ggtop=res.data.ggtop;
        
        //that.initProductData(data);
        that.setData({
          imgUrls: ggtop,
          logo:logo,
          topcat: topcat,
          midcat: midcat,
          productData: prolist,
        });
        //endInitData
      },
      fail: function (e) {
        wx.showToast({
          title: '网络异常！',
          duration: 30000
        });
      },
    })
  },
  //点击加载更多
  getMore: function (e) {
    var that = this;
    var page = that.data.page;
    wx.request({
      url: app.d.myUrl + '/Api/Index/getlist',
      method: 'post',
      data: { page: page },
      header: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      success: function (res) {
        var prolist = res.data.prolist;
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
          productData: that.data.productData.concat(prolist)
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
  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {
    return {
      title: '户外手电筒',
      desc: '精品内容尽在这里!',
      path: '/pages/index/index'
    }
  },


});