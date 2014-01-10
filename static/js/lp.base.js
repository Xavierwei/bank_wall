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
            var month;
            if( typeof date == 'object' ){
                month = date.getMonth();
            } else {
                month = date - 1;
            }
            return aMonth[ month ];
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
        var filter = $(this).data('param');
        $(this).closest('.select-pop')
            .prev()
            .html( $(this).html() );
        //TODO: loading animation

        $main.fadeOut(400,function(){
            LP.triggerAction('close_user_page');
            $main.html('');
            $main.data('nodes','');
            api.ajax(filter , function( result ){
                $main.fadeIn().trigger('item-insert' , [result.data] );
            });
        });

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

                    // fix it's img width and height
                    $item.find('img')
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
    .bind('item-insert' , function( ev , nodes ){
        var aHtml = [];
        var lastDate = null;
        nodes = nodes || [];

        // save nodes to cache
        var cache = $main.data('nodes') || [];
        $main.data('nodes' , cache.concat( nodes ) );

        // filte for date
        $.each( nodes , function( index , node ){
            // get date
            var match = node.datetime.match(/^\d+-(\d+)-(\d+)/);
            if( lastDate != match[0] ){
                LP.compile( 'time-item-template' , 
                    {day: parseInt(match[2]) , month: getMonth( parseInt(match[1]))} , 
                    function( html ){
                        aHtml.push( html );
                    } );
                lastDate = match[0];
            }
            if(node.type == 'video') {
                node.image = node.file.replace('mp4','jpg');
            }
            else
            {
                node.image = node.file;
            }
            node.formatDate = match[0].replace(/-/g , '/');

            LP.compile( 'node-item-template' , 
                node , 
                function( html ){
                    aHtml.push( html );

                    if( index == nodes.length - 1 ){
                        // render html
                        $main.append(aHtml.join(''))
                            .trigger('item-width')
                            .trigger('item-reversal');
                    }
                } );

        } );
    });


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

        // if has inner element 
        if( $('.inner').length ) return;

        _scrollAjax = true;
        // if scroll to the botton of the window 
        // ajax the next datas
        var st = $(window).scrollTop();
        var bodyHeight = $(document.body).height();
        var winHeight = $(window).height();
        if( bodyHeight - winHeight - st < 100 ){
            //TODO ajax next nodes
            api.ajax('nodeList' , {nid: 10} , function( result ){
                $main.trigger('item-insert' , [result.data] );
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
    var _currentNodeIndex = 0;
    LP.action('node' , function( data ){
        _currentNodeIndex = $(this).prevAll(':not(.time-item)').length;
        var nodes = $main.data('nodes');
        var node = nodes[ _currentNodeIndex ];

        var match = node.datetime.match(/\d+-(\d+)-(\d+)/);
        node.date = parseInt(match[2]);
        node.month = getMonth( parseInt(match[1]));
        LP.compile( 'inner-template' , node , function( html ){
            var mainWidth = winWidth - _silderWidth;
            // inner animation
            var $inner = $(html).insertBefore( $main )
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
            // set inner-info bottom css
            var $info = $inner.find('.inner-info');
            $info.css( 'bottom' , - $info.height() );

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
                } , _animateTime , _animateEasing , function(){
                    $main.hide();
                });

            // loading comments
            getCommentList(node.nid);

            LP.use(['jscrollpane' , 'mousewheel'] , function(){
                $('.com-list').jScrollPane({autoReinitialise:true}).bind(
                    'jsp-scroll-y',
                    function(event, scrollPositionY, isAtTop, isAtBottom)
                    {
                        if(isAtBottom) {
                            //getCommentList(node.nid);
                            console.log('Append next page');
                        }
                    }
                );
            });

            // loading image
        } );
    
        // preload before and after images
        preLoadSiblings();
        // // ajax the node info , then compile it to html
        // // get the 21 nodes data , before current
        // LP.use('api' , function( api ){
        //     // TODO.. change request url
        //     var nid = data.nid;
        //     api.ajax( 'getNode' , data , function( result ){
        //         // get current node data
        //         // $.each( result.data , function( index , node ){
        //         //     if( node.nid == nid ){
        //         //         renderNode( node );
        //         //         // save node cache
        //         //         _nodeCache = _nodeCache.concat( result.data.slice( index ) );
        //         //         return false;
        //         //     }
        //         // } );
        //         var node = result.data;
                
        //     } );
            
        // });

        return false;
    });

    // for back action
    LP.action('back' , function( data ){
        var $inner = $('.inner');
        var infoTime = 300;
        // hide the inner info node
        var $info = $inner.find('.inner-info');

        $info.animate({
                bottom: -$info.height()
            } , infoTime);
        // back $inner and remove it
        $inner.delay(infoTime)
            .animate({
                left: - ( $(window).width() - _silderWidth )
            } , _animateTime , _animateEasing , function(){
                $inner.remove();
            });

        // back $main
        $main.show()
            .css('position' , 'fixed')
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

    /**
     * @desc: 立方体旋转inner node
     * @date:
     * @param node {node object}
     * @param direction { 'right' or 'left' }
     * @author: hdg1988@gmail.com
     */
    function cubeInnerNode( node , direction ){

        var cubeDir = 'cube-' + direction;
        var rotateDir = 'rotate-' + direction;

        var match = node.datetime.match(/\d+-(\d+)-(\d+)/);
        node.date = parseInt(match[2]);
        node.month = getMonth( parseInt(match[1]));

        var $inner = $('.inner');
        LP.compile( 'inner-template' , node , function( html ){
            var $comment = $inner.find('.comment');
            // comment animation
            var $nextComment = $(html).find('.comment')
                .addClass(cubeDir)
                .insertBefore( $comment );

            var $cube = $comment.parent();
            $cube.addClass(rotateDir);

            setTimeout(function(){
                // reset css
                $cube.addClass( 'no-animate' )
                    .removeClass( rotateDir );
                $comment.remove();
                $nextComment
                    .removeClass(cubeDir);
                setTimeout(function(){
                    $cube.removeClass( 'no-animate' )
                        ;
                },0);

                $inner.removeClass('disabled');
                
            } , 1000);

            // picture animation,
            // append or prepend image
            // set image width
            // set .image-wrap's margin-right
            // animate the first image's margin-left style
            var $imgWrap = $inner.find('.image-wrap');
            var wrapWidth = $imgWrap.width();
            $imgWrap.css('margin-right' , - wrapWidth );

            // append dom
            var $oriImage = $imgWrap.children('img');
            var $newImage = $('<img/>')[ direction == 'left' ? 'insertBefore' : 'insertAfter' ]( $oriImage )
                    .attr('src' , node.image);
            // set style and animation
            $imgWrap.children('img').css({
                width: wrapWidth
            })
            .eq(0)
            .css('marginLeft' , direction == 'left' ? - wrapWidth : 0 )
            .animate({
                marginLeft: direction == 'left' ? 0 : - wrapWidth 
            } , 1000)
            // after animation
            .promise()
            .done(function(){
                $imgWrap.css({
                    'margin-right': 0
                });
                $newImage.css('width' , '100%')
                    .siblings('img')
                    .remove();
            });

            // desc animation
            var $info = $inner.find('.inner-info');
            $info.animate({
                    bottom: -$info.height()
                } , 500 )
                .promise()
                .done(function(){
                    $inner.find('.inner-infocom')
                        .html( node.description );
                    $info.animate({
                            bottom: 0
                        } , 500 );
                });
            
            // load comment
            getCommentList(node.nid);
        });
    }

    /**
     * @desc: preload sibling images
     * @date:
     * @author: hdg1988@gmail.com
     */
    function preLoadSiblings(){
        var nodes = $main.data('nodes');
        // preload before and after images
        for( var i = 0 ; i < 5 ; i++ ){
            if( nodes[ _currentNodeIndex - i ] ){
                $('<img/>').attr('src' , nodes[ _currentNodeIndex - i ].image);
            }
            if( nodes[ _currentNodeIndex + i ] ){
                $('<img/>').attr('src' , nodes[ _currentNodeIndex + i ].image);
            }
        }
    }
    //for prev action
    LP.action('prev' , function( data ){
        if( _currentNodeIndex == 0 )
            return false;

        // lock the animation
        if( $('.inner').hasClass('disabled') ) return;
        $('.inner').addClass('disabled');

        _currentNodeIndex -= 1;

        var node = $main.data('nodes')[ _currentNodeIndex ];

        cubeInnerNode( node , 'left' );

        preLoadSiblings();
    });
    
    //for next action
    LP.action('next' , function( data ){
        // lock the animation
        if( $('.inner').hasClass('disabled') ) return;
        $('.inner').addClass('disabled');

        _currentNodeIndex++;
        var nodes = $main.data('nodes');
        var node = nodes[ _currentNodeIndex ];
        if( !node ){
            // TODO..  ajax to get more node
            api.ajax('nodeList' , {nid: nodes[ _currentNodeIndex - 1 ].nid} , function( result ){
                $main.trigger('item-insert' , [result.data] );
                cubeInnerNode( $main.data('nodes')[ _currentNodeIndex ] , 'right' );
                preLoadSiblings();
            });
            return;
        }
        cubeInnerNode( node , 'right' );
        preLoadSiblings();
    });

    //for like action
    LP.action('like' , function( data ){
        var _this = $(this);
        var _likeWrap = _this.find('span');
        if(_this.data('liked')) {
            //TODO.. if current user already liked this node, invoke the unlike function
            return;
        }
        else {
            api.ajax('like', {nid:data.nid}, function( result ){
                _likeWrap.animate({opacity:0},function(){
                    _likeWrap.html(result.data.like_count);
                    _this.data('liked',true);
                    $(this).animate({opacity:1});
                });
            });
        }
    });

    //for comment action
    LP.action('comment' , function( data ){
        // TODO.. comment action here
    });

    //for flag node action
    LP.action('flag_node' , function( data ){
        // if this node already flagged, return the action
        if($(this).hasClass('flagged')) {
            return false;
        }
        // display the modal before submit flag
        if(!$('.confirm-modal').is(':visible')) {
            $('.confirm-modal').fadeIn();
            $('.confirm-modal .modal-header span').html(data.type);
            $('.confirm-modal .ok').attr('data-a','flag_node');
            $('.confirm-modal .ok').attr('data-d','nid='+data.nid);
        }
        else {
            api.ajax('flag', {nid:data.nid});
            LP.triggerAction('cancel_confirm_modal');
            $('.flag-node').addClass('flagged');
        }
    });

    //for flag comment action
    LP.action('flag_comment' , function( data ){
        console.log(data);
        if(!$('.confirm-modal').is(':visible')) {
            $('.confirm-modal').fadeIn();
            $('.confirm-modal .modal-header span').html(data.type);
            $('.confirm-modal .ok').attr('data-a','flag_comment');
            $('.confirm-modal .ok').attr('data-d','cid='+data.cid);
        }
        else {
            api.ajax('flag', {cid:data.cid});
            LP.triggerAction('cancel_confirm_modal');
            $('.comlist-item-'+data.cid).find('.comlist-flag').addClass('flagged');
        }
    });

    //cancel confirm modal
    LP.action('cancel_confirm_modal' , function(){
        $('.confirm-modal').fadeOut();
    });

    //upload photo
    LP.action('pop_upload_photo' , function( data ){
        $('.overlay').fadeIn();
        $('.pop').fadeIn();
        $('.pop-inner').hide();
        $('.pop-file').show();
        $('.pop-file .step1-btns').show();
        $('.pop-file .step2-btns').hide();
        $('.pop .poptit').html(data.title);
    });

    //close pop
    LP.action('close_pop' , function(){
        $('.overlay').fadeOut();
        $('.pop').fadeOut();
    });

    //select photo
    LP.action('select_photo' , function(){
        $('#file-photo').trigger('click');
    });

    //select photo
    LP.action('upload_photo' , function(){
        $('.pop-inner').fadeOut(400);
        //TODO uploading
        $('.pop-load').delay(300).fadeIn(400);
        $('.pop-load').delay(300).fadeOut(400);
        $('.pop-txt').delay(300).fadeIn(400);
    });

    //toggle user page
    LP.action('toggle_user_page' , function(){
        if(!$('.user-page').is(':visible')) {
            $('.inner').fadeOut(400);
            $('.main').fadeOut(400);
            $('.user-page').delay(400).fadeIn(400);
            $('.close-user-page').fadeIn();
        }
        else {
            LP.triggerAction('close_user_page');
        }
    });

    //close user page
    LP.action('close_user_page' , function(){
        $('.user-page').fadeOut(400);
        $('.close-user-page').fadeOut();
        $('.inner').delay(400).fadeIn(400);
        $('.main').delay(400).fadeIn(400);
    });

    //open user edit page
    LP.action('open_user_edit_page' , function(){
        $('.count-com').fadeOut(400);
        $(this).fadeOut();
        $('.user-edit-page').delay(400).fadeIn(400);
        $('.avatar-file').fadeIn();
        $('.count-userinfo').addClass('count-userinfo-edit');
    });

    //save user updates
    LP.action('save_user' , function(){
        $('.user-edit-page').fadeOut(400);
        $('.avatar-file').fadeOut();
        $('.count-com').delay(400).fadeIn(400);
        $('.count-edit').fadeIn();
        $('.count-userinfo').removeClass('count-userinfo-edit');
    });

    //after selected photo
    $('#file-photo').change(function(){
        $('.pop-file .step1-btns').fadeOut(400);
        $('.pop-file .step2-btns').delay(400).fadeIn(400);
    });

    // bind document key event for back , prev , next actions
    $(document).keydown(function( ev ){
        switch( ev.which ){
            case 37: // left
                LP.triggerAction('prev');
                break;
            case 39: // right
                LP.triggerAction('next');
                break;
            case 27: // esc
                LP.triggerAction('back');
                break;
        }
    });

    // after page load , load the recent data from server
    api.ajax('recent' , function( result ){
        $main.trigger('item-insert' , [result.data] );
    });

    // after page load , load the current user information data from server
    api.ajax('user' , function( result ){
        result.data.count_by_day = parseInt(result.data.photos_count_by_day) + parseInt(result.data.videos_count_by_day);
        result.data.count_by_month = parseInt(result.data.photos_count_by_month) + parseInt(result.data.videos_count_by_month);
        LP.compile( 'user-page-template' , result.data , function( html ){
            $('.content').append(html);
        });
    });


    /**
     * Get node comments
     * @param cid
     */
    var getCommentList = function(cid) {
        api.ajax('commentList', {cid: cid}, function( result ){
            // TODO: 异常处理
            var comments = result.data;
            // filte for date
            $.each( comments , function( index , comment ){
                // get date
                var match = comment.datetime.match(/\d+-(\d+)-(\d+)/);
                comment.date = parseInt(match[2]);
                comment.month = getMonth( parseInt(match[1]));

                LP.compile( 'comment-item-template' ,
                    comment ,
                    function( html ){
                        // render html
                        $('.com-list-inner').append(html);
                    } );
            });


        });
    }
});