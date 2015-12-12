jQuery(function($){
  $(document).ready(function(){
    /*
    * Récupérer tous les voyages et toutes les réservations 
    * dans la page du plugin en AJAX et traitement en JS pour l'affichage
    */

      var get_param = modGestionCJM.GET();
      if(get_param.post_type=="reservation" && get_param.page=="my-custom-submenu-page")
      {
        modGetData.get_evenements("Les voyages");
        
      }
    if($(this).hasClass('voyage_clicked'))
      {
        $(this).removeClass('voyage_clicked');
      }
    /*
    * Par défaut les voyages sont affichés dès l'arrivée sur la page 'Gestion des participants'
    */
    $("#les_voyages").slideDown();
    /*
    * Au clique sur le titre 'Les voyages'
    * 
    */
    $("#les_voyages_titre").click(function() {
      
      $("#les_voyages").removeClass('voyage_clicked');
      $(this).css('background-color','inherit');
      $("#les_escapades_titre").css('background-color','#e4e4e4');
      $("#les_escapades").slideUp();
      $("#les_resas").hide();
      if($("#les_voyages").css('display')=="none")
      {
        $("#les_voyages").slideDown();
      }
    });
      /*
      * Au clique sur le titre 'Les escapades'
      * 
      */
     $("#les_escapades_titre").click(function() {
    
      $("#les_voyages").removeClass('voyage_clicked');
      $("#les_voyages_titre").css('background-color','#e4e4e4');
      $(this).css('background-color','inherit');
      $("#les_voyages").slideUp();
      $("#les_escapades").slideDown();
      $("#les_escapades table tr").show();
      $("#les_escapades h1").html("Les escapades");
      $("#les_resas").hide();
      

    });
    /*
    * Suppression d'un voyage en AJAX
    */
    $("#app_voyage").click(function() {
      if($('#voyages_action option:selected').val()=="Supprimer")
      {
         modGestionCJM.sup_voyage();
      }
    });

    $("#app_escapade").click(function() {
      if($('#escapdes_action option:selected').val()=="Supprimer")
      {
         modGestionCJM.sup_escapade();
      }
    });
    /**
    ** Simulation de la suppresion en cachant l'élément supprimer
    **/
    $(".sup_resa_class").each(function(e){
      if(!e.is("input:checked"))
      {
        e.hide();
      }
    });
    /*
    * Code exécuter dès que les requêtes AJAX qui récupére les événements sont terminer
    *
    */
    $( document ).ajaxStop(function() {
    /*
    * Exporter des réservations au format .xls sous Excel
    *  
    */

    $("#btn_export_resa").click(function () {
    if($("#export_resas option:selected").val()=="Excel")
    {
          var name = "";
          if(!$("#les_voyages").is(":hidden"))
          {
            name += $("#les_resas h1").html().split(":")[1]

          }
          else {
             name += $("#les_resas h1").html().split(":")[1];
          }
          $("#les_resas table").table2excel({
      exclude : '.noExl',
      filename: name
      });
    } 
    });

    /*
    * Suppresion d'une réservaion en AJAX
    */
    $("#app_resa").click(function() {
      if($("#resa_action option:selected").val()=="Supprimer")
      {
        modGestionCJM.sup_resa();
      }
      if($("#resa_action option:selected").val()=="Modifier")
      {
        modGestionCJM.modif_resa();
      }
    });
      $(".paiement_class").each(function(i,e)
        {
          $(this).change(function () {

          var id = $(this).attr('id').replace("paiement","")
          var paiement = $(this).is(':checked');
          modGestionCJM.change_paiement_resa(id,paiement);


          });
        });
      $("#les_resas table tbody tr").each(function(i,e)
      {
          var ids = $(this).children().eq(2).attr('id');
          var nbplace_enf = $("#"+ids+"> td").eq(6).html();
          var nbplace_a = $("#"+ids+"> td").eq(5).html();
        });


      $("#les_voyages table tbody > tr , #les_escapades table tbody > tr").each(function (i,e) {
         $(e).children().eq(1).click(function () {
            if($(this).hasClass('voyage_clicked'))
                {
                   $(e).children().removeClass('voyage_clicked');
                }
            else {
                  $(this).addClass('voyage_clicked');
                }
          
      if($("#les_voyages").css('display')=="block")
        {
          $("#les_resas h1").html("Les réservations pour le voyage : "+$(e).children().eq(1).text());
          $("#les_resas").show();
          $("#les_resas table tr").hide().addClass('noExl');
          $("#les_resas table tr:eq(0)").show().removeClass('noExl');
          $("#les_resas table tbody > tr > #"+$(e).attr("id")+"").parent().show().removeClass('noExl');
        }

      if($("#les_escapades").css('display')=="block")
        {
          $("#les_resas h1").html("Les réservations pour l'Escapade : "+$(e).children().eq(1).text());
          $("#les_resas").show();
          $("#les_resas table tr").hide().addClass('noExl');
          $("#les_resas table tr:eq(0)").show().removeClass('noExl');
          $("#les_resas table tbody > tr > #"+$(e).attr("id")+"").parent().show().removeClass('noExl');
        }
        });
      });

});
  });


});