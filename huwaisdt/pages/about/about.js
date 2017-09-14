// pages/user/dingdan.js
//index.js  
//获取应用实例  
var app = getApp();
//引入这个插件，使html内容自动转换成wxml内容
var WxParse = require('../../wxParse/wxParse.js');
var common = require("../../utils/common.js");
Page( {  
  data: {  
    winWidth: 0,  
    winHeight: 0,  
    // tab切换  
    currentTab: 0,  
  },  
  onLoad: function(options) { 
    var that = this;
    wx.request({
      url: app.d.myUrl + '/Api/Web/aboutUs',
      method: 'post',
      data: {},
      header: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      success: function (res) {
        var data1 = res.data.err[0];
        var data2 = res.data.err[1];
        var data3 = res.data.err[2];
        var data4 = res.data.err[3];
        var data5 = res.data.err[4];
        var data6 = res.data.err[5];
        var content1 = res.data.err[0].content;
        var content2 = res.data.err[1].content;
        var content3 = res.data.err[2].content;
        var content4 = res.data.err[3].content;
        var content5 = res.data.err[4].content;
        var content6 = res.data.err[5].content;
        WxParse.wxParse('content1', 'html', content1, that, 3);
        WxParse.wxParse('content2', 'html', content2, that, 3);
        WxParse.wxParse('content3', 'html', content3, that, 3);
        WxParse.wxParse('content4', 'html', content4, that, 3);
        WxParse.wxParse('content5', 'html', content5, that, 3);
        WxParse.wxParse('content6', 'html', content6, that, 3);
        that.setData({
          data1: data1,
          data2: data2,
          data3: data3,
          data4: data4,
          data5: data5,
          data6: data6,
        });
      },
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
  swichNav: function( e ) {  
  
    var that = this;  
  
    if( this.data.currentTab === e.target.dataset.current ) {  
      return false;  
    } else {  
      that.setData({
        currentTab: parseInt(e.target.dataset.current),
      });
    }
  },
})