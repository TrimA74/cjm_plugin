jQuery(function($){
$(document).ready(function(){
	/*
* Envoie du formulaire en AJAX sur ajaxController.php pour réserver un voyage/escapade
*
*/
$(".resa_form").submit(function(){

      $.ajax({
        type : "POST",
        dataType: "json",
        url : cjm_object.ajax_url,
        data : $(this).serialize()+"&action=ins_resa",
        success : function(msg){
            console.log(msg);
             switch(msg[0]){
             	case 1 : $("#resa_div_id").empty().html("<p>Votre Inscription a bien été prise en compte !</p>");
             	       
             	        $("#resa_div_id").append("<input type=\"button\" id=\"btn_voir_reza\" onclick=\"window.location.href='"+cjm_object.site_url_js+"/mon-profil';\" value=\"Voir ma réservation\"/>");
             			break;
             	case 2 :$(".p_resa_div").remove();
             	        $("#resa_div_id").append("<p class='p_resa_div'>Veuillez réserver au moins une place !</p>");
             			break;
             	case 3 : $(".p_sup_resa_div").remove();
             	         $("#resa_div_id").append("<p class='p_sup_resa_div'>Nombre de places réservées supérieures au nombre de places disponibles!</p>").css('color','bleue');
             		     break;
             	default :$("#resa_div_id").append("<p id='testp'>Veuillez réserver au moins une place !</p>");
             			 $("#resa_div_id").css('width','420px');
             			break;
             }
        }

      });
      return false;

  });
/*
* Envoie du formulaire en AJAX sur ajaxController.php pour annuler une réservation
*
*/

$(".annul_resa_form").submit(function(){
      $.ajax({
        type : "POST",
        dataType: "json",
        url : cjm_object.ajax_url,
        data : $(this).serialize()+"&action=del_resa",
        success : function(msgd){
             switch(msgd){
             	case 1 : $(".annul_resa_div").empty().html("<p>Votre réservation a bien été annulée ! </p>").css('color','orange');
             			 $(".annul_resa_div").css('margin','auto');
             			 setTimeout(window.location.reload(),10000);
             			break;
             	default : $(".annul_resa_div").append("<p>Veuillez contacter l'administrateur du site via l'onglet \"Contact\"</p>").css('color','red');
                        $(".annul_resa_div").append("<input type=\"button\" id=\"btn_modif_reza\" onclick=\"window.location.href='"+cjm_object.site_url_js+"/contact';\" value=\"Contact\"/>");
             			break;
             }
        }

      });
      return false;

  });
  if($("#annul_resa_div").is(":visible")){
  	$("#modif_resa_div").hide();
  }
  $("#resa_modif").click(function(){
  		$("#annul_resa_div").hide();
  		$("#modif_resa_div").show();

  });
 /*
* Envoie du formulaire en AJAX sur ajaxController.php pour modifier une réservation
*
*/

  $(".modif_resa_form").submit(function(){


      $.ajax({
        type : "POST",
        dataType: "json",
        url : cjm_object.ajax_url,
        data : $(this).serialize()+"&action=upd_resa",
        success : function(upd){
             switch(upd){

             	case 1 : $("#modif_resa_div").empty().html("<p>Votre réservation a bien été modifiée! </p>").css('color','orange');
             			 $("#modif_resa_div").append("<input type=\"button\" id=\"btn_voir_reza\" onclick=\"window.location.href='"+cjm_object.site_url_js+"/mon-profil';\" value=\"Voir ma réservation\"/>");
             			break;
             	case 2 : $("#modif_resa_div").append("<p>Veuillez réserver au moins une place !</p>");
             			break;
             	case 3 : $("#modif_resa_div").append("<p>Nombre de places réservées supérieures au nombre de places disponibles!</p>").css('color','bleue');
             		     break;
             	default : $("#modif_resa_div").append("<p>Veuillez contacter l'administrateur du site via l'onglet \"Contact\"</p>").css('color','red');
             	          $("#modif_resa_div").append("<input type=\"button\" id=\"btn_modif_reza\" onclick=\"window.location.href='"+cjm_object.site_url_js+"/contact';\" value=\"Contact\"/>");
             			  $("#btn_modif_reza").css('margin-right','50px');
             			  $("#resa_submit_upd").hide();
             			break;
             }
        }

      });
      return false;

  });
 });
});
