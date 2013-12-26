/*
 * page base action
 */
LP.use(['jquery' , 'util'] , function( $ , util ){
    'use strict'

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
    // fix one day animation. It is start animate from the day which is not trigger the animation
    // After the day trigger the animation , it would be added 'opened' class.
    // Fix animation day by day
    $main.bind('item-reversal' , function(){
    //function picItemAnimate(){
        // get first time item , which is not opend
        // wait for it's items prepared ( load images )
        // run the animate
        var $timeItem = $('.time-item:not(.opened)').eq(0);
        var $itemPics = $timeItem.nextUntil('.time-item');

        var startAnimate = function(){

            $timeItem.addClass('opened');

            $itemPics.each(function( index ){
                setTimeout(function(){
                    $itemPics.eq( index )
                        .addClass('reversal')
                        .width( itemWidth )
                        .height( itemWidth );

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
    .trigger('item-reversal');



    var minWidth = 170;
    var itemWidth = minWidth;
    
    // isotope effect init and invoke
    var isIsotoped = false;
    function fixIsotope(){
        if( isIsotoped ) {
            $main.isotope('reLayout');
            return;
        };
        isIsotoped = true;
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
    }

    function fixItemWidth(){
        var mainWidth = $('.main').width();
        var min = ~~( mainWidth / minWidth );
        itemWidth = ~~( mainWidth / min );

        $('.time-item, .pic-item.reversal , .pic-item.reversal img')
            .width( itemWidth )
            .height( itemWidth );
    }

    // fix window resize event
    // resize item width
    var resizeTimer = null;
    
    $(window).resize(function(){
        clearTimeout( resizeTimer );
        resizeTimer = setTimeout(function(){
            fixItemWidth();
            // run isotope after item width fixed
            setTimeout(fixIsotope , 500);
            
        } , 200);
    })
    .scroll(function(){

    });

    fixItemWidth();
    //picItemAnimate();






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

});