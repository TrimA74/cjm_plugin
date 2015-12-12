
jQuery(function($){
$(document).ready(function(){	
	var get_param = modGestionCJM.GET();
	if(get_param.post_type=="reservation")
		{
			$('#categorychecklist li ').each(function(i,e){
				$(e).hide();
				if(  $(e).attr('id')=="category-32" || $(e).attr('id')=="category-16"){
					$(e).show();
				}
			});	
			$("#publish").prop('disabled', true);
			$("#in-category-32,#in-category-16").change(function () {
				if($("#in-category-32").is(':checked') || $("#in-category-16").is(':checked'))
				{
					$("#publish").prop('disabled', false);
				}
				else {
					$("#publish").prop('disabled', true);
				}
			});
		}
	
});
});