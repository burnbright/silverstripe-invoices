(function($) {
	
	$.entwine('ss', function($){
		
		$('.Actions a.newwindow').entwine({
			
			onclick: function(e){
				e.preventDefault();
				window.open($(this).attr("href"),"_blank");
				
				
			}
			
		});
	
		$('.Actions a.reloader').entwine({
		
			onclick: function(e) {
				e.preventDefault();
				var url = $(this).attr("href");
				//$(".cms-container").loadPanel();
				//$('from .Actions').redraw();
				//$(".cms-container").splitViewMode();
				jQuery.ajax({
					url: url,
					success: function(){
						$//('form .Actions').redraw();
						$('.cms-edit-form').redraw();
					},
					failure: function(){
						alert("something went wrong");
					}
				});
			},
		
		});
	
	});
	
})(jQuery);