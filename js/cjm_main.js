jQuery(function($){
  $(document).ready(function(){
    // $(document).ajaxComplete(function (e,ajax,options) {
    //   console.log(ajax);
    // });
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
    $("#les_mails_titre").click(function () {
      if($("#les_mails").css('display')=="none")
      {
        $("#les_mails").slideDown();
        $("#les_voyages").slideUp();
        $("#les_escapades").slideUp();
      }
      else {
        $("#les_mails").slideUp();
      }
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
     /*
    * Suppression d'une escapade en AJAX
    */
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
    jQuery("body").off("click","#btn_export_resa");
    $("body").on("click","#btn_export_resa",function () {
    if($("#export_resas option:selected").val()=="Excel"){
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
    else if ($("#export_resas option:selected").val()=="PDF") {
      $('#les_resas table').tableExport({type:'pdf',escape:'false',htmlContent:'false',ignoreColumn: [0,7,8]});
    }
    });

    /*
    * Suppresion ou modification d'une réservaion en AJAX
    */
    jQuery("body").off("click","#app_resa");
    jQuery("body").on("click","#app_resa",function() {
      // Suppresion
      if($("#resa_action option:selected").val()=="Supprimer")
      {
        modGestionCJM.sup_resa();
      }
      // Modification
      if($("#resa_action option:selected").val()=="Modifier")
      {
        modGestionCJM.modif_resa();
      }
    });
    /*
    * Requete AJAX dès que changement sur la checkbox paiement
    */
    jQuery("body").off("click",".paiement_class");
    $("body").on("click",".paiement_class",function () {
      var id = $(this).attr('id').replace("paiement","")
      var paiement = $(this).is(':checked');
      modGestionCJM.change_paiement_resa(id,paiement);
      // modSendEmails.send_mail_confirm_paiement(modSendEmails.tmce_getContent("test_mail"));
    });
    /*
    * Requete AJAX dès que changement sur la checkbox liste attente
    */
    jQuery("body").off("click",".att_class");
    $("body").on("click",".att_class",function () {
        var id = $(this).attr('id').replace("att","")
        var att = $(this).is(':checked');
        if(!att)
        {
          $("#reservation"+id+" td").eq(0).attr('class','resa_attente');
        }
        else {
          $("#reservation"+id+" td").eq(0).attr('class','');
        }
        modGestionCJM.change_att_resa(id,att);
    });
      /*
      * Au clique sur un événement, affichage des réservations de l'événement cliqué
      */
      $("#les_voyages table tbody > tr , #les_escapades table tbody > tr").each(function (i,e) {
         $(e).children().eq(1).click(function () {
            $("#les_voyages table tbody > tr , #les_escapades table tbody > tr").children().removeClass('voyage_clicked');
            $(this).addClass('voyage_clicked');
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
      /**/

});
  });


});
