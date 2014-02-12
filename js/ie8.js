var closeUploadPop = function() {
    LP.triggerAction('close_pop');
}

var close_pop = function() {
    LP.triggerAction('close_pop');
}

var closeAvatarPop = function(url) {
    LP.triggerAction('close_pop');
    $('.user-pho , .count-userpho').find('img')
        .attr('src' , './api' + url+'?'+ new Date().getTime() );
}