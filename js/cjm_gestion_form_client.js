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
        url : "https://srv-prj.iut-acy.local/jumelage/Best_wordpress_ever/wordpress/wp-content/plugins/cjm/ajaxController.php",
        data : $(this).serialize(),
        success : function(msg){     
             switch(msg[0]){
             	case 1 : $("#resa_div_id").empty().html("<p>Votre Inscription a bien été prise en compte !</p>").css('color','green');
             	         $("#resa_div_id").css('width','420px');
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
        url : "https://srv-prj.iut-acy.local/jumelage/Best_wordpress_ever/wordpress/wp-content/plugins/cjm/ajaxController.php",
        data : $(this).serialize(),
        success : function(msgd){    
            console.log(msgd);    
             switch(msgd){
             	case 1 : $(".annul_resa_div").empty().html("<p>Votre réservation a bien été annulée ! </p>").css('color','orange');
             			 $(".annul_resa_div").css('margin','auto');
             			 $(".annul_resa_div").css('width','350px');
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
        url : "https://srv-prj.iut-acy.local/jumelage/Best_wordpress_ever/wordpress/wp-content/plugins/cjm/ajaxController.php",
        data : $(this).serialize(),
        success : function(upd){  
        console.log(upd);      
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
             			  console.log(upd);
             			break; 
             }   
        }
        
      }); 
      return false;

  });
 });
});