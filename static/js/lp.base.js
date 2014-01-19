/*
 * page base action
 */
LP.use(['jquery' , 'api'] , function( $ , api ){
    'use strict'

    var API_FOLDER = "../api";
    var THUMBNAIL_IMG_SIZE = "_250_250";
    var BIG_IMG_SIZE = "_800_800";
    var page_num = 20; //TODO: 初始化的时候需要计算一整个屏幕能显示几个

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
        .delegate('.search-ipt' , 'keypress' , function(ev){
            switch( ev.which ){
                case 13: // enter
                    LP.triggerAction('search');
            }
        })

        // for select options
        .delegate('.select-option p' , 'click' , function(){
            $(this)
                // add selected class
                .addClass('selected')
                // remove sibling class
                .siblings()
                .removeClass('selected')
                .end()
                .closest('.select-pop')
                .prev()
                .html( $(this).html() );

            // refresh main query parameter
            var pageParam = refreshQuery();
            var filter = $(this).data('api');
            //TODO: loading animation

            $main.fadeOut(400,function(){
                LP.triggerAction('close_user_page');
                $main.html('');
                $main.data( 'nodes', [] );
                api.ajax(filter, pageParam , function( result ){
                    nodeActions.inserNode( $main.show() , result.data , pageParam.orderby == 'datetime' );
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
    // After the day trigger the animation 
    // Fix animation day by day
    var nodeActions = {
        prependNode: function( $dom , nodes , bShowDate ){
            var aHtml = [];
            var lastDate = null;
            var pageParm = $main.data('param'); //TODO:  pageParm.orderby == 'like' || pageParm.orderby == 'random' 此时不显示日历
            nodes = nodes || [];

            // save nodes to cache
            var cache = $dom.data('nodes') || [];
            $dom.data('nodes' , nodes.concat( cache ) );

            // if( bShowDate ){
            //     lastDate = $main.find('.time-item').last().data('date');
            // }
            // filte for date
            $.each( nodes , function( index , node ){
                // get date
                if( bShowDate ){

                    var datetime = new Date(node.datetime*1000);
                    var date = datetime.getFullYear() + "/" + (parseInt(datetime.getMonth()) + 1) + "/" + datetime.getDate();
                    if( lastDate != date){
                        LP.compile( 'time-item-template' ,
                            {date: date , day: parseInt(datetime.getDate()) , month: getMonth(parseInt(datetime.getMonth()) + 1)} ,
                            function( html ){
                                aHtml.push( html );
                            } );
                        lastDate = date;
                    }
                }
                // fix video type
                node.image = node.file.replace( node.type == 'video' ? '.mp4' : '.jpg' , THUMBNAIL_IMG_SIZE + '.jpg');
                node.formatDate = date;

                node.str_like = node.likecount > 1 ? 'Likes' : 'Like';
                LP.compile( 'node-item-template' ,
                    node ,
                    function( html ){
                        aHtml.push( html );

                        if( index == nodes.length - 1 ){
                            // render html
                            var $oFirstTimeNode = $dom.children().eq(0);
                            // remove first time item;
                            $dom.prepend(aHtml.join(''));
                            if( bShowDate && 
                                $oFirstTimeNode.prevAll('.time-item').first().data('date')
                                == $oFirstTimeNode.data('date') ){
                                $oFirstTimeNode.remove();
                            }
                            nodeActions.setItemWidth( $dom );
                            nodeActions.setItemReversal( $dom );
                        }
                    } );

            } );
        },
        // when current dom is main , and the recent ajax param orderby is 'like' or
        // 'random' , the datetime would not be showed.
        // pageParm.orderby == 'like' || pageParm.orderby == 'random' 此时不显示日历
        inserNode: function( $dom , nodes , bShowDate ){
            var aHtml = [];
            var lastDate = null;
            var pageParm = $main.data('param'); //TODO:  pageParm.orderby == 'like' || pageParm.orderby == 'random' 此时不显示日历
            nodes = nodes || [];

            // save nodes to cache
            var cache = $dom.data('nodes') || [];
            $dom.data('nodes' , cache.concat( nodes ) );

            // filter for nodes , if there are nodes autoloaded in 5 minutes
            // the page index is not right.  So you should delete the same nodes
            var lastNode = cache[ cache.length - 1 ];
            if( lastNode ){
                var index = null ;
                $.each( nodes , function( i , node ){
                    if( lastNode.nid == node.nid ){
                        index = i;
                        return false;
                    }
                } );
                if( index !== null ){
                    nodes.splice( 0 , index + 1 );
                }
            }

            if( bShowDate ){
                lastDate = $main.find('.time-item').last().data('date');
            }
            // filte for date
            $.each( nodes , function( index , node ){
                // get date
                if( bShowDate ){

                    var datetime = new Date(node.datetime*1000);
                    var date = datetime.getFullYear() + "/" + (parseInt(datetime.getMonth()) + 1) + "/" + datetime.getDate();
                    if( lastDate != date){
                        LP.compile( 'time-item-template' ,
                            {date: date , day: parseInt(datetime.getDate()) , month: getMonth(parseInt(datetime.getMonth()) + 1)} ,
                            function( html ){
                                aHtml.push( html );
                            } );
                        lastDate = date;
                    }
                }
                // fix video type
                node.image = node.file.replace( node.type == 'video' ? '.mp4' : '.jpg' , THUMBNAIL_IMG_SIZE + '.jpg');
                node.formatDate = date;

                node.str_like = node.likecount > 1 ? 'Likes' : 'Like';
                LP.compile( 'node-item-template' ,
                    node ,
                    function( html ){
                        aHtml.push( html );

                        if( index == nodes.length - 1 ){
                            // render html
                            $dom.append(aHtml.join(''));
                            nodeActions.setItemWidth( $dom );
                            nodeActions.setItemReversal( $dom );
                        }
                    } );

            } );
        },
        setItemWidth: function( $dom ){
            var mainWidth = $dom.width();
            var min = ~~( mainWidth / minWidth );
            // save itemWidth and winWidth 
            itemWidth = ~~( mainWidth / min );
            winWidth = $(window).width();

            $dom.find('.time-item, .main-item.reversal , .main-item.reversal img')
                .width( itemWidth )
                .height( itemWidth );
            $dom.find('.main-item').height( itemWidth );
        },
        // start pic reversal animation
        setItemReversal: function( $dom ){
            // fix all the items , set position: relative
            $dom.children()
                .css('position' , 'relative');
            if( $dom.children('.isotope-item').length )
                $dom.isotope('destroy')
            // get first time item , which is not opend
            // wait for it's items prepared ( load images )
            // run the animate

            // if has time items, it means it needs to reversal from last node-item element
            // which is not be resersaled
            var $nodes = $dom.find('.pic-item:not(.reversal)');

            var startAnimate = function( $node ){
                $node.addClass('reversal')
                    .width( itemWidth )
                    .height( itemWidth );
                // fix it's img width and height
                $node.find('img')
                    .width( itemWidth )
                    .height( itemWidth );
                setTimeout(function(){
                    nodeActions.setItemReversal( $dom );
                } , 500);
            }
            // if esist node , which is not reversaled , do the animation
            if( $nodes.length ){
                var $img = $nodes.eq(0)
                    .find('img');
                if( $img[0].complete ){
                    startAnimate( $nodes.eq(0) );
                } else {
                    $img.load(function(){
                        startAnimate( $nodes.eq(0) );
                    });
                }
            } else { // judge if need to load next page 
                $(window).trigger('scroll');
            }
        },
        // set items auto fix it's width
        setItemIsotope: function( $dom ){
            // if the page has unreversaled node
            if( $dom.find('.main-item:not(.time-item,.reversal)').length ) return;

            if( $dom.children('.isotope-item').length ){
                $dom.isotope('reLayout');
                return;
            }

            LP.use('isotope' , function(){
                // first init isotope , render no animate effect
                $dom
                    .addClass('no-animate')
                    .isotope({
                        resizable: false
                    });

                // after first isotope init
                // remove no animate class
                setTimeout(function(){
                    $dom.removeClass('no-animate');
                } , 100);
            });
        }
    }

    // fix window resize event
    // resize item width
    var _resizeTimer = null;
    var _scrollAjax = false;
    $(window).resize(function(){
        clearTimeout( _resizeTimer );

        _resizeTimer = setTimeout(function(){
            if( $main.is(':visible') ){
                nodeActions.setItemWidth( $main );

                // run isotope after item width fixed
                setTimeout(function(){
                    nodeActions.setItemIsotope( $main );
                } , 500);
            }

            var $userPage = $('.user-page');
            var $userCom = $userPage.find('.count-com');
            if( $userPage.is(':visible') && $userCom.is(':visible') ){
                nodeActions.setItemWidth( $userCom );
                // run isotope after item width fixed
                setTimeout(function(){
                    nodeActions.setItemIsotope( $userCom );
                } , 500);
            }
        } , 200);
    })
    .scroll(function(){
        // if is ajaxing the scroll data
        if( _scrollAjax ) return;
        // if scroll to the botton of the window
        // ajax the next datas
        var st = $(window).scrollTop();
        var docHeight = $(document).height();
        var winHeight = document.body.clientHeight;
        if( docHeight - winHeight - st < 100 ){
            
            // fix main element
            // it must visible and in main element has unreversaled node
            if( $main.is(':visible') && !$main.find('.main-item:not(.time-item,.reversal)').length ){
                _scrollAjax = true;
                var param = $main.data('param');
                param.page++;
                $main.data('param' , param);
                api.ajax('recent' , param , function( result ){
                    nodeActions.inserNode( $main , result.data , param.orderby == 'datetime');
                    _scrollAjax = false;

                    // TODO:: no more data tip
                });
            }
            // fix user page element
            var $userCom = $('.user-page .count-com');
            // it must visible and in main element has unreversaled node
            if( $('.user-page').is(':visible') && !$userCom.find('.main-item:not(.time-item,.reversal)').length ){
                _scrollAjax = true;
                var userPageParam = $('.side').data('param');
                userPageParam.page++;
                api.ajax('recent' , userPageParam , function( result ){
                    nodeActions.inserNode( $userCom , result.data , true );
                    _scrollAjax = false;

                    // TODO:: no more data tip
                });
            }
            if( _scrollAjax ){
                // TODO: loading animation
            }
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
        var datetime = new Date(node.datetime*1000);
        node.date = datetime.getDate();
        node.month = getMonth((parseInt(datetime.getMonth()) + 1));
        node.image = node.file.replace( node.type == "video" ? '.mp4' : '.jpg', BIG_IMG_SIZE + '.jpg');
        node.currentUser = $('.side').data('user');
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
            bindCommentSubmisson();
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

            // init vide node
            if( node.type == "video" ){

                LP.use('flash-detect', function(){
                    if(FlashDetect.installed || $('html').hasClass('video')) { // need to validate html5 video as well
                        LP.use('video-js' , function(){
                            videojs( "inner-video-" + node.nid , {}, function(){
                                // Player (this) is initialized and ready.
                            });
                        });
                    }
                    else
                    {
                        LP.compile( 'wmv-template' , {nid:node.nid} , function( html ){
                            $('.image-wrap-inner').html(html);
                        });
                    }
                });
            }

            // change url
            changeUrl('/nid/' + node.nid);
            // loading image
        } );

        // preload before and after images
        preLoadSiblings();
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

        var datetime = new Date(node.datetime*1000);
        node.date = datetime.getDate();
        node.month = getMonth((parseInt(datetime.getMonth()) + 1));
        node.currentUser = $('.side').data('user');

        var $inner = $('.inner');
        LP.compile( 'inner-template' , node , function( html ){
            var $comment = $inner.find('.comment');
            // comment animation
            var $newInner = $(html);
            var $nextComment = $newInner.find('.comment')
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
                    $cube.removeClass( 'no-animate' );
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
            var $oriItem = $imgWrap.children('.image-wrap-inner');
            var $newItem = $newInner.find('.image-wrap-inner')[ direction == 'left' ? 'insertBefore' : 'insertAfter' ]( $oriItem );

            // init video
            if( node.type == "video" ){
                LP.use('video-js' , function(){
                    videojs( "inner-video-" + node.nid , {}, function(){
                      // Player (this) is initialized and ready.
                    });
                });
            }
            // set style and animation
            $imgWrap.children('.image-wrap-inner').css({
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
                    $newItem.css('width' , '100%')
                        .siblings('.image-wrap-inner')
                        .remove();
                });

            // desc animation
            var $info = $inner.find('.inner-info');
            $info.animate({
                    bottom: -$info.height()
                } , 500 )
                .promise()
                .done(function(){
                    var $newInfo = $newInner.find('.inner-info')
                        .insertAfter( $info );
                    $info.remove();
                    $newInfo.css('bottom' , -$newInfo.height() )
                        .animate({
                            bottom: 0
                        } , 500 );
                });

            // load comment
            bindCommentSubmisson();
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
                $('<img/>').attr('src' , API_FOLDER + nodes[ _currentNodeIndex - i ].image);
            }
            if( nodes[ _currentNodeIndex + i ] ){
                $('<img/>').attr('src' , API_FOLDER + nodes[ _currentNodeIndex + i ].image);
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
            var param = $main.data('param');
            param.page++;
            $main.data('param' , param);
            api.ajax('nodeList' , {nid: nodes[ _currentNodeIndex - 1 ].nid} , function( result ){
                nodeActions.inserNode( $main , result.data , param.orderby == 'datetime' );
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
        var _likeWrap = _this.find('span').eq(0);
        if(_this.data('liked')) {
            //TODO.. if current user already liked this node, invoke the unlike function
            return;
        }
        else {
            api.ajax('like', {nid:data.nid}, function( result ){
                if(result.success) {
                    _likeWrap.animate({opacity:0},function(){
                        _likeWrap.html(result.data);
                        _this.data('liked',true);
                        _this.removeClass('clickable');
                        _this.append('<span class="com-unlike clickable" data-d="nid={{nid}}" data-a="unlike">(unlike)</span>');
                        $(this).animate({opacity:1});
                    });
                }
            });
        }
    });

    LP.action('unlike' , function( data ){
        var _this = $(this);
        var _likeWrap = _this.parent().find('span').eq(0);
        api.ajax('unlike', {nid:data.nid}, function( result ){
            if(result.success) {
                _likeWrap.animate({opacity:0},function(){
                    _likeWrap.html(result.data);
                    _this.parent().data('liked',false);
                    _this.fadeOut();
                    $(this).animate({opacity:1});
                });
            }
        });
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
    LP.action('pop_upload' , function( data ){
        var acceptFileTypes;
        var type = data.type;

        LP.compile( "pop-template" , data,  function( html ){
            $(document.body).append( html );
            $('.overlay').fadeIn();
            $('.pop').fadeIn();

            var $fileupload = $('#fileupload');
            if(type == 'video') {
                acceptFileTypes = /(\.|\/)(move|mp4|avi)$/i;
                //$('#select-btn').html(' SELECT VIDEO <input id="file-video" type="file" name="video" />');
                var maxFileSize = 7 * 1024000;
            } else {
                acceptFileTypes = /(\.|\/)(gif|jpe?g|png)$/i;
                //$('#select-btn').html(' SELECT PHOTO <input id="file-photo" type="file" name="photo" />');
                var maxFileSize = 5 * 1024000;
                // init event
                transformMgr.initialize();
            }
            LP.use('fileupload' , function(){
                // Initialize the jQuery File Upload widget:
                $fileupload.fileupload({
                        // Uncomment the following to send cross-domain cookies:
                        //xhrFields: {withCredentials: true},
                        url: '../api/index.php/node/post',
                        maxFileSize: 5000000,
                        acceptFileTypes: acceptFileTypes,
                        autoUpload:false
                    })
                    .bind('fileuploadadd', function (e, data) {
                        console.log(data);
                        //TODO: 当用户选择图片后跳转到处理图片的流程
                        if(data.files[0].size > maxFileSize) {
                            $('#fileupload .error').fadeIn().html('Maximum file size is ' + parseInt(maxFileSize/1024000) + "MB");
                        }
                        else {
                            $('#fileupload .error').hide();
                            data.submit();
                        }
                    })
                    .bind('fileuploadstart', function (e, data) {
                        $('.pop-inner').fadeOut(400);
                        $('.pop-load').delay(400).fadeIn(400);
                    })
                    .bind('fileuploadprogress', function (e, data) {
                        var rate = data._progress.loaded / data._progress.total * 100;
                        $('.popload-percent p').css({width:rate + '%'});
                    })
                    .bind('fileuploaddone', function (e, data) {
                        if(data.result.data.type == 'video') {
                            $('.poptxt-pic img').attr('src', API_FOLDER + data.result.data.file.replace('.mp4', THUMBNAIL_IMG_SIZE + '.jpg'));
                            setTimeout(function(){
                                var timestamp = new Date().getTime();
                                $('.poptxt-pic img').attr('src',$('.poptxt-pic img').attr('src') + '?' +timestamp );
                            },2000);
                            $('.poptxt-submit').attr('data-d','nid=' + data.result.data.nid+'&type=' + data.result.data.type);
                            $('.pop-inner').delay(400).fadeOut(400);
                            $('.pop-txt').delay(900).fadeIn(400);
                        }
                        else {
                            $('.poptxt-pic img').attr('src', API_FOLDER + data.result.data.file/*.replace('.jpg', THUMBNAIL_IMG_SIZE + '.jpg')*/);
                            $('.poptxt-submit').attr('data-d','nid=' + data.result.data.nid);
//                            $('.pop-file .step1-btns').fadeOut(400);
//                            $('.pop-file .step2-btns').delay(400).fadeIn(400);
                            $('.pop-inner').delay(400).fadeOut(400);
                            $('.pop-txt').delay(1200).fadeIn(400);
                        }
                    });
            });
        } );
//         $('.pop .poptit').html('upload ' + data.type);
//         $('.overlay').fadeIn();
//         $('.pop').fadeIn();
//         $('.pop-inner').hide();
//         $('.pop-file').show();
//         $('.pop-file .step1-btns').show();
//         $('.pop-file .step2-btns').hide();
//         $('#fileupload .error').hide();
//         $('#node-description').val('');
//         $('.poptxt-pic img').attr('src','');
//         $('.poptxt-check input').prop('checked',false);

//         // bind popfile-btn file upload event
//         var $fileupload = $('#fileupload');
//         if( $fileupload.data('init') != type ){

//             if($fileupload.data('init')) {
//                 $fileupload.fileupload('destroy').unbind('fileuploadadd').unbind('fileuploadstart').unbind('fileuploadprogress').unbind('fileuploaddone');
//             }
//             $fileupload.data('init', type );
//             if(type == 'video') {
//                 acceptFileTypes = /(\.|\/)(move|mp4|avi)$/i;
//                 $('#select-btn').html(' SELECT VIDEO <input id="file-video" type="file" name="video" />');
//                 var maxFileSize = 7 * 1024000;
//             }
//             else {
//                 acceptFileTypes = /(\.|\/)(gif|jpe?g|png)$/i;
//                 $('#select-btn').html(' SELECT PHOTO <input id="file-photo" type="file" name="photo" />');
//                 var maxFileSize = 5 * 1024000;
//             }
//             LP.use('fileupload' , function(){
//                 // Initialize the jQuery File Upload widget:
//                 $fileupload.fileupload({
//                         // Uncomment the following to send cross-domain cookies:
//                         //xhrFields: {withCredentials: true},
//                         url: '../api/index.php/node/post',
//                         maxFileSize: 5000000,
//                         acceptFileTypes: acceptFileTypes,
//                         autoUpload:false
//                     })
//                     .bind('fileuploadadd', function (e, data) {
//                         console.log(data);
//                         //TODO: 当用户选择图片后跳转到处理图片的流程
//                         if(data.files[0].size > maxFileSize) {
//                             $('#fileupload .error').fadeIn().html('Maximum file size is ' + parseInt(maxFileSize/1024000) + "MB");
//                         }
//                         else {
//                             $('#fileupload .error').hide();
//                             data.submit();
//                         }
//                     })
//                     .bind('fileuploadstart', function (e, data) {
//                         $('.pop-inner').fadeOut(400);
//                         $('.pop-load').delay(400).fadeIn(400);
//                     })
//                     .bind('fileuploadprogress', function (e, data) {
//                         var rate = data._progress.loaded / data._progress.total * 100;
//                         $('.popload-percent p').css({width:rate + '%'});
//                     })
//                     .bind('fileuploaddone', function (e, data) {
//                         if(data.result.data.type == 'video') {
//                             $('.poptxt-pic img').attr('src', API_FOLDER + data.result.data.file/*.replace('.mp4', THUMBNAIL_IMG_SIZE + '.jpg')*/);
//                             setTimeout(function(){
//                                 var timestamp = new Date().getTime();
//                                 $('.poptxt-pic img').attr('src',$('.poptxt-pic img').attr('src') + '?' +timestamp );
//                             },2000);
//                             $('.poptxt-submit').attr('data-d','nid=' + data.result.data.nid);
//                             $('.pop-inner').delay(400).fadeOut(400);
//                             $('.pop-txt').delay(900).fadeIn(400);
//                         }
//                         else {
//                             $('.poptxt-pic img').attr('src', API_FOLDER + data.result.data.file/*.replace('.jpg', THUMBNAIL_IMG_SIZE + '.jpg')*/);
//                             $('.poptxt-submit').attr('data-d','nid=' + data.result.data.nid);
// //                            $('.pop-file .step1-btns').fadeOut(400);
// //                            $('.pop-file .step2-btns').delay(400).fadeIn(400);
//                             $('.pop-inner').delay(400).fadeOut(400);
//                             $('.pop-txt').delay(1200).fadeIn(400);
//                         }
//                     });
//             });
//         }
    });

    //close pop
    LP.action('close_pop' , function(){
        $('.overlay').fadeOut(function(){$(this).remove();});
        $('.pop').fadeOut(function(){$(this).remove();});
    });

    //select photo
    // LP.action('select_photo' , function(){
    //     $('#file-photo').trigger('click');
    // });

    //select photo
    LP.action('upload_photo' , function(){
        $('.pop-inner').fadeOut(400);
        //TODO uploading
        $('.pop-load').delay(400).fadeIn(400);
        $('.pop-load').delay(400).fadeOut(400);
        $('.pop-txt').delay(800).fadeIn(400);
    });

    //save the content description
    LP.action('save_node' , function(data){
        var description = $('#node-description').val();
        if(description.length == 0) {
            $('.poptxt-preview .error').html('Please write some description.').fadeIn();
            return;
        }
        if(description.length > 140) {
            $('.poptxt-preview .error').html('The description is limited to 140 characters.').fadeIn();
            return;
        }
        if(!$('.poptxt-check input').is(':checked')) {
            $('.poptxt-preview .error').fadeOut();
            $('.poptxt-check .error').fadeIn();
            return;
        }
        $('.poptxt-check .error').fadeOut();

        // get image scale , rotate , zoom arguments
        if(data.type == 'photo') {
            var trsdata = transformMgr.result();
        }

        api.ajax('saveNode' , $.extend( {nid: data.nid, description: description} , trsdata ), function( result ){
            if(result.success) {
                //TODO: insert the content to photo wall instead of refresh
                $main.html('');
                var param = $main.data('param');
                api.ajax('recent' , function( result ){
                    nodeActions.inserNode( $main , result.data , param.orderby == 'datetime' );
                });

                $('.pop-inner').fadeOut(400);
                $('.pop-success').delay(400).fadeIn(400);
                setTimeout(function() {
                    LP.triggerAction('close_pop');
                },1500);
            };
        });
    });

    //toggle user page
    LP.action('toggle_user_page' , function(){
        if(!$('.user-page').is(':visible')) {
            $('.inner').fadeOut(400);
            $('.main').fadeOut(400);
            $('.user-page').delay(400).fadeIn(400 , function(){
                // if first loaded , load user's nodes from server
                var user = $('.side').data('user');
                var param = {page:1, uid:user.uid, orderby:'datetime'};
                $('.side').data('param', param);
                var $countCom = $(this).find('.count-com');
                if( !$countCom.children().length ){
                    api.ajax('recent' , param , function( result ){
                        nodeActions.inserNode( $countCom , result.data , true );
                    });
                }
            });
            $('.close-user-page').fadeIn();
        }
        else {
            LP.triggerAction('close_user_page');
        }
    });

    // List user nodes
    LP.action('list_user_nodes', function(data){
        var type = data.type;
        var param = $('.side').data('param');
        param.page = 1;
        delete param.type;
        delete param.start;
        delete param.mycomment;
        delete param.mylike;
        switch(data.type) {
            case 'photo':
                param.type = 'photo';
                break;
            case 'video':
                param.type = 'video';
                break;
            case 'day':
                var d = new Date();
                param.start = d.getFullYear() + '-' + parseInt(d.getMonth() + 1) + '-' + d.getDate();
                break;
            case 'month':
                var d = new Date();
                param.start = d.getFullYear() + '-' + parseInt(d.getMonth() + 1) + '-' + 1;
                break;
            case 'comment':
                param.mycomment = true;
                break;
            case 'like':
                param.mylike = true;
                break;
        }
        var $countCom = $('.count-com').removeData('nodes').fadeOut(function(){
            $(this).html('').show();
            api.ajax('recent' , param , function( result ){
                nodeActions.inserNode( $countCom , result.data , true );
            });
        });
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

    //close user page
    LP.action('logout' , function(){
        api.ajax('logout', function( result ){
            if(result.success) {
                window.location.reload();
            }
        });
    });

    //save user updates
    LP.action('search' , function(){
        $main.fadeOut(400,function(){
            LP.triggerAction('close_user_page');
            $main.html('');
            $main.data('nodes','');
            var param = refreshQuery();
            api.ajax('recent', param , function( result ){
                $('.search-ipt').val('').blur();
                nodeActions.inserNode( $main.show() , result.data , param.orderby == 'datetime');

            });
        });
    });

    // get last day nodes
    LP.action('by_day' , function(){
        $main.fadeOut(400,function(){
            LP.triggerAction('close_user_page');
            $main.html('');
            $main.data('nodes','');
            //TODO this method need to reset selected items to default value
            var param = refreshQuery();
            var d = new Date();
            param = $.extend(param, {'start': d.getFullYear() + '-' + parseInt(d.getMonth() + 1) + '-' + d.getDate()});

            //TODO save to dom cache date
            api.ajax('recent', param , function( result ){
                $('.search-ipt').val('').blur();
                nodeActions.inserNode( $main.show() , result.data , param.orderby == 'datetime');
            });
        });
    });

    // get last month nodes
    LP.action('by_month' , function(){
        $main.fadeOut(400,function(){
            LP.triggerAction('close_user_page');
            $main.html('');
            $main.data('nodes','');
            //TODO this method need to reset selected items to default value
            var param = refreshQuery();
            var d = new Date();
            param = $.extend(param, {'start': d.getFullYear() + '-' + parseInt(d.getMonth() + 1) + '-' + 1});

            //TODO save to dom cache date
            api.ajax('recent', param , function( result ){
                $('.search-ipt').val('').blur();
                nodeActions.inserNode( $main.show() , result.data , param.orderby == 'datetime');
            });
        });
    });



    //after selected photo
//    $('#file-photo').change(function(){
//        $('.pop-file .step1-btns').fadeOut(400);
//        $('.pop-file .step2-btns').delay(400).fadeIn(400);
//    });


    // bind document key event for back , prev , next actions
    $(document).keydown(function( ev ){
        var $inner = $('.inner');
        if( !$inner.length || !$inner.is(':visible') ) return;
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


    // get all query parameter
    var refreshQuery = function( query ){
        // get search value
        var $searchInput = $('.search-ipt');
        var param = { page: 1 , pagenum: 20 };
        param [ $searchInput.attr('name') ] = $searchInput.val();

        // get select options
        $('.header .select').find('.select-option p.selected')
            .each( function(){
                param = $.extend( param , LP.query2json( $(this).data('param') ) );
            } );

        $main.data('param' , $.extend( param , query || {} ) );

        // change hash
        var param = $main.data('param');
        var str = '';
        $.each( ['orderby' , 'type' , 'country'] , function( i , val){
            if( param[val] ){
                str += '/' + val + '/' + param[val];
            }
        } )
        changeUrl( str );
        return $main.data('param');
    }

    var changeUrl = function( str ){
        location.hash = '#!' + str;
    }

    //-----------------------------------------------------------------------
    // init drag event for image upload
    // after image upload, init it's size to fix the window
    // use raephael js to rotate, scale , and drag the image photo
    var transformMgr = (function(){
        var isDragging      = false;
        var isMousedown     = false;
        var startPos        = null;
        var totalMoveX      = 0;
        var totalMoveY      = 0;
        var lastMoveX       = 0;
        var lastMoveY       = 0;

        var maxDistance = 200;

        $(document).mouseup(function(){
            // reset states
            if( !isMousedown ) return;
            isDragging      = false;
            isMousedown     = false;
            startPos        = null;
            totalMoveX += lastMoveX;
            totalMoveY += lastMoveY;

            lastMoveX = 0;
            lastMoveY = 0;


            // // reset center button
            // $centerBtn.animate({
            //     marginLeft  : oMleft,
            //     marginTop   : oMtop,
            //     opacity     : 1
            // } , 300 );
        });

        // init ps_btn_up
        var perRotate   = 10;
        var perScale    = 1.1;

        var totalScale  = 1;
        var totalRotate = 0;
        var transforms = [];

        var trsReg = /T(-?[0-9.]+),(-?[0-9.]+)/;
        var scaReg = /S(-?[0-9.]+),(-?[0-9.]+),(-?[0-9.]+),(-?[0-9.]+)/;
        var rotReg = /R(-?[0-9.]+),(-?[0-9.]+),(-?[0-9.]+)/;

        var transform = function( x , y , s , r ){
            var left = x === undefined ? totalMoveX : x;
            var top = y === undefined ? totalMoveY : y;
            var scale = s === undefined ? totalScale : s;
            var rotate = r === undefined ? totalRotate : r;
            var transformValue = imgRaphael.transform();

            var match = null;
            // move 
            if( x !== undefined ){
                if( transforms.length && ( match = transforms[transforms.length-1].match( trsReg ) ) ){
                    transforms[transforms.length-1] = "T" + ( x + parseFloat( match[1] ) ) + ',' + ( y + parseFloat( match[2] ) );
                } else {
                    transforms.push( "T" + x + ',' + y );
                }

                 imgRaphael.transform( transforms.join('') );
            }
            if( s !== undefined ){
                if( transforms.length && ( match = transforms[transforms.length-1].match( scaReg ) ) ){
                    transforms[transforms.length-1] = "S" + ( s * parseFloat( match[1] ) ) + ','
                         + ( s * parseFloat( match[2] ) )
                         + "," + match[3]
                         + "," + match[4];
                } else {
                    transforms.push( "S" + s + ',' + s + ',' + (tarWidth/2) + "," + (tarHeight/2) );
                }

                imgRaphael.animate({
                    transform: transforms.join('')
                } , 200);
            }
            if( r !== undefined ) {
                if( transforms.length && ( match = transforms[transforms.length-1].match( rotReg ) ) ){
                    transforms[transforms.length-1] = "R" + ( r + parseFloat( match[1] ) ) 
                        + "," + match[2]
                        + "," + match[3];
                } else {
                    transforms.push( "R" + r + ',' + (tarWidth/2) + "," + (tarHeight/2) );
                }

                imgRaphael.animate({
                    transform: transforms.join('')
                } , 200);
            }
        }

        var $poptxtpic = null;
        var $picInner = null;
        var tarHeight   = null;
        var tarWidth    = null;

        var imgRaphael = null;
        var raphael = null;
        var forExpr = 100;

        function reset(){
            isDragging      = false;
            isMousedown     = false;
            startPos        = null;
            totalMoveX      = 0;
            totalMoveY      = 0;
            lastMoveX       = 0;
            lastMoveY       = 0;

            totalScale  = 1;
            totalRotate = 0;
            transforms  = [];
        }

        var initialize = function(){
            perRotate   = 10;
            perScale    = 1.1;

            totalScale  = 1;
            totalRotate = 0;
            transforms = [];

            $poptxtpic = $('.poptxt-pic');
            $picInner = $('.poptxt-pic-inner');
            tarHeight   = $picInner.height();
            tarWidth    = $picInner.width();

            imgRaphael = null;
            raphael = null;

            $picInner.find('img').load(function(){
                $(this).css({
                    width: 'auto',
                    height: 'auto'
                })
                .show();

                // remove last sav
                var img = this;
                
                LP.use('raphael' , function(){
                    var width   = img.width;
                    var height  = img.height;
                    if( width / height > tarWidth / tarHeight ){
                        width   = width / height * ( tarHeight + forExpr );
                        height  = tarHeight + forExpr;
                    } else {
                        height  = height / width * ( tarWidth + forExpr );
                        width   = tarWidth + forExpr;
                    }
                    if( !raphael ){
                        raphael = Raphael( img.parentNode , tarWidth, tarHeight);
                        imgRaphael = raphael.image( img.src , 0 , 0 , width, height);
                    }
                    raphael.setSize( tarWidth , tarHeight );

                    // reset transform
                    imgRaphael.attr({
                        src     : img.src,
                        width   : width,
                        height  : height
                    })
                    .transform('');
                    transformMgr.reset();
                    transformMgr.transform('T' + parseInt( (tarWidth - width ) / 2) + ',' + parseInt( ( tarHeight - height ) / 2 ) );
                    $(img).css({
                        width: width,
                        height : height
                    })
                    .hide();
                });
            });

            $poptxtpic.mousedown( function( ev ){
                isMousedown = true;
                startPos = {
                    pageX     : ev.pageX
                    , pageY   : ev.pageY
                }
                return false;
            })
            .mousemove( function( ev ){
                if( !isMousedown ) return;
                if( !isDragging ){
                    if( Math.abs( ev.pageX - startPos.pageX ) + Math.abs( ev.pageY - startPos.pageY ) >= 10 ){
                        isDragging = true;
                    } else {
                        return false;
                    }
                }
                // move images
                if( !imgRaphael ) return;

                transform( ev.pageX - startPos.pageX - lastMoveX , ev.pageY - startPos.pageY - lastMoveY );
                lastMoveX = ev.pageX - startPos.pageX;
                lastMoveY = ev.pageY - startPos.pageY;

                // move center icon
                // $centerBtn.css({
                //     marginLeft  : oMleft + lastMoveX / 2
                //     , marginTop : oMtop + lastMoveY / 2
                //     , opacity: 1 - Math.min( 0.5 , ( Math.abs( lastMoveX ) + Math.abs( lastMoveY ) ) / maxDistance )
                // });
            })
            .bind('mousewheel' , function( ev ){
                var deltay = ev.originalEvent.wheelDeltaY || ev.originalEvent.deltaY;
                if( deltay < 0 ){
                    totalScale /= perScale;
                    transform( undefined , undefined , 1/perScale );
                } else {
                    totalScale *= perScale;
                    transform( undefined , undefined , perScale );
                }
            });


            // TODO.. for long click
            // var animateScale = function(  ){
                
            // }
            // var longTimeout = null;
            // var longInterval = null;

            $('.pop-zoomout-btn').mousedown(function(){
                totalScale *= perScale;
                transform( undefined , undefined , perScale );

                // longTimeout = setTimeout(function(){
                //     longInterval = setInterval(function(){
                //         transform( undefined , undefined , perScale );
                //     } , 500 );
                // } , 500);

            });

            $('.pop-zoomin-btn').mousedown(function(){
                totalScale /= perScale;
                transform( undefined , undefined , 1/perScale );

                // longTimeout = setTimeout(function(){
                //     longInterval = setInterval(function(){
                //         transform( undefined , undefined , 1/perScale );
                //     } , 500 );
                // } , 500);
            });
            
            $('.pop-rright-btn').mousedown(function(){
                totalRotate += perRotate
                transform( undefined , undefined , undefined , perRotate);
                // longTimeout = setTimeout(function(){
                //     longInterval = setInterval(function(){
                //         transform( undefined , undefined , undefined , perRotate);
                //     } , 500 );
                // } , 500);
            });

            $('.pop-rleft-btn').mousedown(function(){
                totalRotate -= perRotate;
                transform( undefined , undefined , undefined , -perRotate );
                // longTimeout = setTimeout(function(){
                //     longInterval = setInterval(function(){
                //         transform( undefined , undefined , undefined , -perRotate);
                //     } , 500 );
                // } , 500);
            });

            // $(document)
            //     .mouseup(function(){
            //         clearTimeout( longTimeout );
            //         clearInterval( longInterval );
            //     });
        }


        return {
            reset       : reset
            , initialize: initialize
            , result    : function(){
                var off  = imgRaphael.getBBox();
                var width = parseInt($picInner.find('img').css('width'));
                var height = parseInt($picInner.find('img').css('height'));
                return {
                    width       : width * totalScale,
                    height      : height * totalScale,
                    src         : $picInner.find('img').attr('src'),
                    rotate      : totalRotate,
                    x           : off.x,
                    y           : off.y,
                    cid         : 1
                }
            }
            , transform  : transform
        }
    })();



    var init = function() {
        // after page load , load the recent data from server
        var pageParam = refreshQuery();
        api.ajax('recent', pageParam, function( result ){
            nodeActions.inserNode( $main , result.data , pageParam.orderby == 'datetime' );
        });

        // after page load , load the current user information data from server
        api.ajax('user' , function( result ){
            if(result.success) {
                //bind user data after success logged
                LP.compile( 'user-page-template' , result.data , function( html ){
                    $('.content').append(html);
                });

                LP.compile( 'side-template' , result.data , function( html ){
                    $('.content').append(html);
                    //cache the user data
                    $('.side').data('user',result.data);
                });
                $('.page').addClass('logged');
                $('.header .select').fadeIn();
            }
            else {
                $('.header .login').fadeIn();
            }
        });

        LP.use('uicustom',function(){
            $( ".search-ipt").val('').autocomplete({
                source: function( request, response ) {
                    $.ajax({
                        url: "../api/tag/list",
                        dataType: "json",
                        data: {
                            term: request.term
                        },
                        success: function( data ) {
                            response( $.map( data.data, function( item ) {
                                return {
                                    label: item.tag,
                                    value: item.tag
                                }
                            }));
                        }
                    });
                },
                minLength: 2,
                select: function( event, ui ) {
                    console.log(ui);
                }
            });
        });


        LP.use('handlebars' , function(){
            //Handlebars helper
            Handlebars.registerHelper('ifvideo', function(options) {
                if(this.type == 'video')
                    return options.fn(this);
                else
                    return options.inverse(this);
            });

            Handlebars.registerHelper('ifliked', function(options) {
                if(this.user_liked == true)
                    return options.fn(this);
                else
                    return options.inverse(this);
            });
        });

        $('body').on('mouseenter','.com-like',function(){
            var needLogin = $(this).find('.need-login');
            if(needLogin) {
                needLogin.fadeIn();
            }
        });
        $('body').on('mouseleave','.com-like',function(){
            var needLogin = $(this).find('.need-login');
            if(needLogin) {
                needLogin.fadeOut();
            }
        });


        // every five minutes get the latest nodes
        setInterval( function(){
            // if main element is visible
            if( !$main.is(':visible') ) return;
            api.ajax( 'neighbor' , {nid: 1} , function( r ){
                var nodes = r.data.left;
                if( !nodes.length ) return;
                nodeActions.prependNode( $main , nodes , $main.data('param').orderby == "datetime" );
            } );
        } , 5 * 60 * 1000 );
    }



    var bindCommentSubmisson = function() {
        LP.use('form' , function(){
            $('.comment-form').ajaxForm({
                beforeSubmit:  function($form){
                    $('.comment-msg-error').hide();
                    if($('.com-ipt').val().length == 0) {
                        $('.comment-msg-error').show().html('You should write something.');
                        return false;
                    }
                },
                complete: function(xhr) {
                    var res = xhr.responseJSON;
                    if(res.success) {
                        var comment = res.data;
                        var datetime = new Date(comment.datetime*1000);
                        comment.date = datetime.getDate();
                        comment.month = getMonth((parseInt(datetime.getMonth()) + 1));
                        comment.user = $('.side').data('user');
                        $('.comment-form').fadeOut();
                        $('.comment-msg-success').delay(500).fadeIn();
                        LP.compile( 'comment-item-template' ,
                            comment,
                            function( html ){
                                // render html
                                if($('.com-list-inner .comlist-item').length == 0) {
                                    $('.com-list-inner').html('');
                                }
                                $('.com-list-inner').first().append(html);
                            } );
                    }
                    else {
                        if(res.message == 'need login') {
                            $('.comment-msg-error').html('You need login before comment');
                        }
                    }
                }
            });
        });
    }


    /**
     * Get node comments
     * @param nid
     */
    var getCommentList = function(nid) {
        api.ajax('commentList', {nid: nid}, function( result ){
            // TODO: 异常处理
            var comments = result.data;
            if(comments.length == 0) {
                $('.com-list-inner').html('You will be first one to comment this content.');
            }
            else {
                $.each( comments , function( index , comment ){
                    // get date
                    var datetime = new Date(comment.datetime*1000);
                    comment.date = datetime.getDate();
                    comment.month = getMonth((parseInt(datetime.getMonth()) + 1));

                    LP.compile( 'comment-item-template' ,
                        comment ,
                        function( html ){
                            // render html
                            $('.com-list-inner').first().append(html);
                        } );
                });
            }
        });
    }

    init();

});