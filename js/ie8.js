var API_FOLDER = "./api";

var closeUploadPop = function() {
    LP.triggerAction('get_fresh_nodes');
    $('.pop-inner').fadeOut(400);
    $('.pop-success').delay(400).fadeIn(400);
    setTimeout(function() {
        LP.triggerAction('close_pop');
    },1500);
}


var uploadBusy = function(tmp_file) {
    setTimeout(function(){
        LP.use(['api'] , function(api){
            var data = {};
            data.type = 'video';
            data.description = $('#node-description').val();
            data.tmp_file = tmp_file;
            data.iframe = true;
            data.iframeRepost = true;
            api.ajax('saveNode' , data , function( data ){
                if(typeof data.message.error != "undefined" && data.message.error == 508) {
                    uploadBusy(data.message.tmp_file);
                }
                if(data.success) {
                    closeUploadPop();
                }
            });
        })
    }, 1000*15);
}

var uploadPopError = function(code) {
    switch(code){
        case 502:
            var errorIndex = 0;
            break;
        case 501:
            var errorIndex = 2;
            break;
        case 503:
            var errorIndex = 1;
            break;
        case 509:
            var errorIndex = 3;
            break;
    }
    $('.pop-inner').fadeOut(400);
    $('.pop-file').delay(800).fadeIn(400);
    $('.step1-tips li').removeClass('error');
    $('.step1-tips li').eq(errorIndex).addClass('error');
}

var close_pop = function() {
    LP.triggerAction('get_fresh_nodes');
    $('#node_post_flash object').fadeOut(400);
    $('.pop-success').delay(400).fadeIn(400);
    setTimeout(function() {
        LP.triggerAction('close_pop');
    },1500);
}

var cancel_pop = function() {
	LP.triggerAction('close_pop');
}

var closeAvatarPop = function(url) {
    LP.triggerAction('close_pop');
    $('.user-pho , .count-userpho').find('img')
        .attr('src' , './api' + url+'?'+ new Date().getTime() );
}


var fileDialogComplete = function(numFilesSelected, numFilesQueued) {
    if (numFilesQueued > 0) {
        this.startUpload(this.getFile(0).id);
    }
}

var uploadStart = function(file) {
    //$('.pop-file').fadeOut(400);
    $('.pop-load').fadeIn(400);
}

var uploadProgress = function(file, bytesLoaded, bytesTotal) {
    var rate = bytesLoaded / bytesTotal * 100;
    $('.popload-percent p').css({width:rate + '%'});
}


var uploadDoneMsg = function(data) {
    if(!data.success) {
        if(typeof data.message.error != "undefined" && data.message.error == 508) {
            setTimeout(function(){
                var type = 'video';
                LP.use(['api'] , function(api){
                    api.ajax('upload' , {'type':type, 'tmp_file': data.message.tmp_file} , function( result ){
                        uploadDoneMsg(result);
                    });
                });
            }, 1000*15);
            return;
        }

        switch(data.message){
            case 502:
                var errorIndex = 0;
                break;
            case 501:
                var errorIndex = 2;
                break;
            case 503:
                var errorIndex = 1;
                break;
            case 509:
                var errorIndex = 3;
                break;
        }
        $('.pop-load').fadeOut(400);
        $('.pop-txt').fadeOut(400);
        $('.pop-file').fadeIn(400);
        $('.step1-tips li').removeClass('error');
        $('.step1-tips li').eq(errorIndex).addClass('error');
    }
    else {
        $('.pop-txt').fadeIn(400);
        $('.poptxt-pic-inner').fadeIn();
        $('.poptxt-pic img').attr('src', API_FOLDER + data.data.file.replace('.mp4', '.jpg'));
        $('.poptxt-submit').attr('data-d','file='+ data.data.file +'&type=' + data.data.type);
    }
}

var uploadSuccess = function(file, serverData) {
    var data = JSON.parse(serverData);
    uploadDoneMsg(data);
    //$('.pop-load').fadeOut(400);
}

var uploadError = function(object, error, message){
    $('.pop-load').fadeOut(400);
    //$('.pop-file').delay(800).fadeIn(400);
}

var wmvPlaying = function() {
    $('.inner-infoicon .video').addClass('pause');
}

var wmvPause = function() {
    $('.inner-infoicon .video').removeClass('pause');
}
