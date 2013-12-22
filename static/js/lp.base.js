/*
 * page base action
 */
LP.use(['jquery' , 'util'] , function( exports , util ){
    'use strict'
    var $ = exports;
    // // extent jquery , rewrite serialize method , for it 
    // // would replace blank space to '+'
    // var _tmpSerialize = $.fn.serialize;

    // $.fn.serialize = function(){
    //     var data = _tmpSerialize.call(this);
    //     return data.replace(/\+/g , ' ');
    // }

    // dom ready
    $(function(){
        /* 
         * 自动插入HTML 
         */
        $('[data-autoload]').each(function(){
            if( $(this).attr('run-auto-load') ) return;
            $(this).attr( 'run-auto-load' , 1 );
            var $self = $(this);
            var data  = LP.query2json( $self.data('autoload') );
            var api   = data.api;
            if ( api ) {
                delete data.api;
                LP.ajax(api, data, function(e){ 
                    e = e || ''; 
                    var html = e.html !== undefined ? e.html : e;
                    $self.html(html); 
                });
            }
        });
    });

});