(function ($) {

	$(function(){

		// fix sub nav on scroll
		var $win = $(window),
				$nav = $('.subnav'),
				navHeight = $('.navbar').first().height(),
				navTop = $('.subnav').length && $('.subnav').offset().top - navHeight,
				isFixed = 0;

		processScroll();

		$win.on('scroll', processScroll);

		function processScroll() {
			var i, scrollTop = $win.scrollTop();
			if (scrollTop >= navTop && !isFixed) {
				isFixed = 1;
				$nav.addClass('subnav-fixed');
			} else if (scrollTop <= navTop && isFixed) {
				isFixed = 0;
				$nav.removeClass('subnav-fixed');
			}
		}

	});


})(window.jQuery);

var bootstrapAlert = function(message, type) {
	if(typeof(type) == 'undefined')
		type = 'info';

	$('#alert_placeholder').css({
		position: 'fixed',
		bottom: 0,
		right: 0
	}).
		html('<div class="alert alert-' + type + '"><a class="close" data-dismiss="alert">×</a><span>'+message+'</span></div>').
		fadeIn(500)
	;

	setTimeout(function(){
		$('#alert_placeholder').fadeOut(500)
	}, 3000);
};

function getParameterByName(name) {
	name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
	var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
		results = regex.exec(location.search);
	return results == null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}