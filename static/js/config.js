seajs.config({
  // 配置 shim 信息，这样我们就可以通过 require("jquery") 来获取 jQuery
  //plugins: ['shim']

  shim: {
    // for jquery
    jquery: {
      src: "../jquery/jquery-1.102.js"
      , exports: "jQuery"
    }
    ,handlebars: {
      src: "../handlebars/handlebars-v1.1.2.js"
      , exports: "Handlebars"
    }
    ,isotope:{
      src: "../plugin/jquery.isotope.min.js"
      , deps: ['jquery']
    }
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