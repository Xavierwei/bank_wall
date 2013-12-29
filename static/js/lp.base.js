/*
 * page base action
 */
LP.use(['jquery' , 'api'] , function( $ , api ){
    'use strict'

    // get english month
    // TODO .... need I18
    var getMonth = (function(){
        var aMonth = ["January","February","March","April","May","June","July","August","September","October","November","December"];
        return function( date ){
            date = date || new Date;
            return aMonth[ date.getMonth() ];
        }
    })();

    // live for pic-item hover event
    $(document.body)
        .delegate('.pic-item' , 'mouseenter' , function(){
            $(this).find('.item-info')
                //.stop( true , false )
                .fadeIn( 500 );
        })
        .delegate('.pic-item' , 'mouseleave' , function(){
            $(this).find('.item-info')
                //.stop( true , false )
                .fadeOut( 500 );
        })

    // for select options
    .delegate('.select-option p' , 'click' , function(){
        $(this).closest('.select-pop')
            .prev()
            .html( $(this).html() );
    })

    // click to hide select options
    .click(function( ev ){
        $('.select-pop').hide()
            .prev()
            .show();
        if( $(ev.target).hasClass('select-box') ){
            $(ev.target).hide()
                .next()
                .show();
        }
        
    });


    var $main = $('.main');
    var minWidth = 170;
    var itemWidth = minWidth;
    var winWidth = $(window).width();
    // fix one day animation. It is start animate from the day which is not trigger the animation
    // After the day trigger the animation , it would be added 'opened' class.
    // Fix animation day by day
    // events: 
    //      item-reversal   : fix image reversal effect
    //      item-width      : fix item width
    //      item-isotope    : isotope effect init and invoke
    $main.bind('item-reversal' , function(){
        // fix all the items , set position: relative
        $main.children()
            .css('position' , 'relative');
        if( $main.children('.isotope-item').length )
            $main.isotope('destroy')
        // get first time item , which is not opend
        // wait for it's items prepared ( load images )
        // run the animate
        var $timeItem = $('.time-item:not(.opened)').eq(0)
            .width( itemWidth )
            .height( itemWidth );
        var $itemPics = $timeItem.nextUntil('.time-item');

        var startAnimate = function(){

            $timeItem.addClass('opened');

            $itemPics.each(function( index ){
                setTimeout(function(){
                    var $item = $itemPics.eq( index )
                        .addClass('reversal')
                        .width( itemWidth )
                        .height( itemWidth );

                    // set the position
                    // var itemIndex = $item.index();
                    // var cols = ~~( winWidth / itemWidth );
                    // $item
                    //     .hide()
                    //     .css({
                    //         width: 0,
                    //         top: ~~( itemIndex / cols ) * itemWidth,
                    //         left: itemIndex % cols * itemWidth
                    //     });
                    // setTimeout(function(){
                    //     $item
                    //         .show();
                    //     setTimeout(function(){
                    //         $item.addClass('reversal')
                    //     .width( itemWidth )
                    //     .height( itemWidth );;
                    //     });

                    // });


                    // play next pic items
                    if( index == $itemPics.length - 1 ){
                        setTimeout(function(){$main.trigger('item-reversal')} , 1000);
                    }
                } , index * 400 );
            });
        }
        var imgLoadedNum = 0;
        var $imgs = $itemPics.find('img')
            .each(function(){
                // it means the img loaded complete
                if( this.complete ){
                    imgLoadedNum++;
                }

                $(this).load(function(){
                    imgLoadedNum++;
                    if( imgLoadedNum == $itemPics.length ){
                        startAnimate();
                    }
                });
            });
        if( imgLoadedNum == $itemPics.length ){
            startAnimate();
        }
    })
    .bind('item-width' , function(){
        var mainWidth = $(this).width();

        var min = ~~( mainWidth / minWidth );
        // save itemWidth and winWidth 
        itemWidth = ~~( mainWidth / min );
        winWidth = $(window).width();

        $('.time-item, .main-item.reversal , .main-item.reversal img')
            .width( itemWidth )
            .height( itemWidth );
    })
    // isotope effect init and invoke
    .bind('item-isotope' , function(){

        // if the page has unreversaled node
        if( $('.main .main-item:not(.time-item,.reversal)').length ) return;

        if( $main.children('.isotope-item').length ){
            $main.isotope('reLayout');
            return;
        }

        LP.use('isotope' , function(){
            // first init isotope , render no animate effect
            $main
                .addClass('no-animate')
                .isotope({
                    resizable: false
                });

            // after first isotope init
            // remove no animate class
            setTimeout(function(){
                $main.removeClass('no-animate');
            } , 100);
        });
    })
    .trigger('item-width')
    .trigger('item-reversal');


    // fix window resize event
    // resize item width
    var _resizeTimer = null;
    var _scrollAjax = false;
    $(window).resize(function(){
        clearTimeout( _resizeTimer );

        _resizeTimer = setTimeout(function(){
            $main.trigger('item-width');
            // run isotope after item width fixed
            setTimeout(function(){
                $main.trigger('item-isotope');
            } , 500);
            
        } , 200);
    })
    .scroll(function(){
        // if is ajaxing the scroll data
        if( _scrollAjax ) return;
        // if the page has unreversaled node
        if( $('.main .main-item:not(.time-item,.reversal)').length ) return;
        _scrollAjax = true;
        // if scroll to the botton of the window 
        // ajax the next datas
        var st = $(window).scrollTop();
        var bodyHeight = $(document.body).height();
        var winHeight = $(window).height();
        if( bodyHeight - winHeight - st < 100 ){
            //TODO ajax next nodes
            api.ajax('nodeList' , {nid: 10} , function( result ){
                var aHtml = [];
                var lastDate = null;
                // filte for date
                $.each( result.data || [] , function( index , node ){
                    // get date
                    var jsDate = new Date( node.datetime * 1000 );
                    var date = ~~(+jsDate / 86400000);
                    if( lastDate != date ){
                        LP.compile( 'time-item-template' , 
                            {day: jsDate.getDate() , month: getMonth( jsDate )} , 
                            function( html ){
                                aHtml.push( html );
                            } );
                        lastDate = date;
                    }
                    LP.compile( 'node-item-template' , 
                        node , 
                        function( html ){
                            aHtml.push( html );
                        } );

                } );

                // render html
                $('.main').append(aHtml.join(''))
                    .trigger('item-reversal');

                _scrollAjax = false;
            });
        }
    });





    // ================== page actions ====================
    // language select btn event
    LP.action('lang' , function( data ){
        $(this)
            .addClass('language-item-on')
            .siblings()
            .removeClass('language-item-on');

        // set lang tag to cookie
        LP.setCookie('lang' , data.lang , 1e10 );

        // reload document
        LP.reload();
    });

    // view node action

    var _silderWidth = 120;
    var _animateTime = 600;
    var _animateEasing = 'linear';
    var _nodeCache = [];
    LP.action('node' , function( data ){
        // ajax the node info , then compile it to html
        // get the 21 nodes data , before current
        LP.use('api' , function( api ){
            // TODO.. change request url
            var nid = data.nid;
            api.ajax( 'nodeNext' , data , function( result ){
                // get current node data
                $.each( result.data , function( index , node ){
                    if( node.nid == nid ){
                        renderNode( node );
                        // save node cache
                        _nodeCache = _nodeCache.concat( result.data.slice( index ) );
                        return false;
                    }
                } );
            } );
            
        });
        function renderNode ( nodeData ){
            LP.compile( 'inner-template' , nodeData , function( html ){
                var $main = $('.main');
                var mainWidth = winWidth - _silderWidth;

                // inner animation
                $(html).insertBefore( $main )
                    .css({
                        left: - mainWidth ,
                        position: 'relative'
                    })
                    .animate({
                        left: 0
                    } , _animateTime , _animateEasing , function(){
                        // show up node info
                        $(this).find('.inner-info')
                            .animate({
                                bottom: 0
                            } , 300);
                    });

                // main animation
                $main
                    .css({
                        position: 'fixed',
                        width: mainWidth,
                        left: 0,
                        top: 0
                    })
                    .animate({
                        left: winWidth
                    } , _animateTime , _animateEasing);

                // loading comments

                // loading image
            } );
        }
       

        return false;
    });

    // for back action
    LP.action('back' , function( data ){
        var $inner = $('.inner');
        var $main = $('.main');
        var infoTime = 300;
        // hide the inner info node
        $inner.find('.inner-info')
            .animate({
                bottom: -32
            } , infoTime);
        // back $inner and remove it
        $inner
            .delay(infoTime)
            .animate({
                left: - ( $(window).width() - _silderWidth )
            } , _animateTime , _animateEasing , function(){
                $inner.remove();
            });

        // back $main
        $main
            .delay(infoTime)
            .animate({
                left: 0
            } , _animateTime , _animateEasing , function(){
                $main.css({
                    position: 'relative',
                    width: 'auto'
                })
            });
    });

    //for prev action
    LP.action('prev' , function( data ){
        // TODO..

    });

    //for next action
    LP.action('next' , function( data ){
        // TODO..
    });

});