seajs.config({
  // 配置 shim 信息，这样我们就可以通过 require("jquery") 来获取 jQuery
  //plugins: ['shim']

  shim: {
    // for jquery
    jquery: {
      src: "../jquery/jquery-1.102.js"
      , exports: "jQuery"
    }
    // , upload: {
    //   src: '../uploader/ajaxUpload'
    //   , exports: 'AjaxUpload'
    // }
    // , bootstrap: {
    //   src: '../bootstrap3/js/bootstrap.min.js',
    //   deps: ['jquery']
    // }
    // , datepicker: {
    //   src: '../bootstrap3/datepicker/js/bootstrap-datepicker.js',
    //   deps: ['bootstrap']
    // }
    // , angular: {
    //   src: '../angular/angular.js'
    //   , exports: "angular"
    // }
  }
  , alias: {
    api: '../api'
    , api4sjht: '../api4sjht'
    , util: '../util/util'
    , panel: "../panel/panel"
    , autoComplete: '../autocomplete/autoComplete'
    , validator: '../validator/validator'
    , html2json: '../com/html2json'
    , tooltip: '../util/tooltip'
  }
});