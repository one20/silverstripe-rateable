(function($){
	var rateables = $('.rateable-ui');
	rateables.each(function(){
		var self = $(this);
		setRaty(self);
	});

	function setRaty(instance){
		instance.raty('destroy');
		var userHasRated = instance.hasClass('disabled');
		var scoreDisplayed = (instance.data('userrating') != '')?instance.data('userrating'):instance.data('averagescore');
		var imagePath = (userHasRated)?'rateable/images/rated':'rateable/images/default';

		instance.raty({
			readOnly: userHasRated,
			score: scoreDisplayed,
			path: imagePath
		});

		if(!userHasRated){
			$('img', instance).click(function(){
				$.getJSON(instance.data('ratepath') + '?score=' + $(this).attr('alt'), function(data) {
					if(data.status == 'error'){
						alert(data.message);
						return;
					}
					instance.addClass('disabled');
					instance.data('averagescore', data.averagescore);
					instance.data('userrating', data.userrating);
					setRaty(instance);
				});
			});
		} else {
			var clearBtn = $('<a href="">Clear</a>');
			instance.append(clearBtn);
			clearBtn.click(function(){
				$.getJSON(instance.data('clearratepath'), function(data) {
					if(data.status == 'error'){
						alert(data.message);
						return;
					}
					instance.removeClass('disabled');
					instance.data('averagescore', data.averagescore);
					instance.data('userrating', '');
					setRaty(instance);
				});
				return false;
			});
		}
	}
})(jQuery);