/*
 * page base action
 */
LP.use(['jquery' , 'api'] , function( $ , api ){
    'use strict'



	// language select btn event
	LP.action('lang' , function( data ){
		$(this)
			.addClass('language-item-on')
			.siblings()
			.removeClass('language-item-on');

		// set lang tag to cookie
		LP.setCookie('lang' , data.lang );

		// reload document
		LP.reload();
	});

	var lang = LP.getCookie('lang') || 'fr';
	$('.language-item').removeClass('language-item-on')
		.filter('[data-d="lang=' + lang + '"]')
		.addClass('language-item-on');


});