
jQuery(function($){
$(document).ready(function(){	
	var data = {
		'action' : 'get_logged_user'
	};
	$.get(ajax_object.ajax_url,data,function (data) {
		if(data.roles[Object.keys(data.roles)[0]]=="admin_comite"){
			$("#toplevel_page_WP-Lightbox-2").remove();
			$("#toplevel_page_srp-free-settings").remove();
		}
	},"json")
	;

	console.log($("#toplevel_page_wysija_campaigns a div:last-child").text("Newsletter"));
	var get_param = modGestionCJM.GET();
	if(get_param.post_type=="reservation")
		{
			$('#categorychecklist li ').each(function(i,e){
				$(e).hide();
				if(  $(e).attr('id')=="category-3" || $(e).attr('id')=="category-4"){
					$(e).show();
				}
			});	
			$("#publish").prop('disabled', true);
			$("#in-category-3,#in-category-4").change(function () {
				if($("#in-category-3").is(':checked') || $("#in-category-4").is(':checked'))
				{
					$("#publish").prop('disabled', false);
				}
				else {
					$("#publish").prop('disabled', true);
				}
			});
		}
	
	var elem = $("#notif_event_admin").clone();
	$("#les_mails_titre").append(elem.clone().css({"background-color" : "#00B9EB" ,
													"font-size" : "15px"	,
													"line-weight" :"17px" ,
													"border-radius" : "10px",
													"font-weight" : "600" ,
													"margin" : "1px 0 0 2px",
													"z-index" : "26",
													"color" : "#FFF",
													"display":"inline-block",
													"width" :"20px",
													"padding-right":"4px",
													"text-align":"center"}));
	$("#menu-posts-reservation ul li a").each(function (i,e) {
		if($(e).attr('href')=="edit.php?post_type=reservation&page=gestion-participants") {
			$(e).append(elem);
			}
		});
	});
});