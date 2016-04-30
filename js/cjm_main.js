jQuery(function($){
  $(document).ready(function(){
    var les_voyages = $("#les_voyages"),
    les_escapades = $("#les_escapades"),
    les_resas = $("#les_resas"),
    les_mails = $("#les_mails"),
    les_mails_titre = $("#les_mails_titre"),
    les_voyages_titre = $("#les_voyages_titre"),
    les_escapades_titre = $("#les_escapades_titre");
    get_param = modGestionCJM.GET(),
    add_resa_btn = document.getElementById("btn_add_resa");
    /*
    * Récupérer tous les voyages et toutes les réservations
    * dans la page du plugin en AJAX et traitement en JS pour l'affichage
    */
    document.getElementById("check_all").onclick =  function () {
      var bool = false ;
      if(jQuery("#check_all").is(':checked')) {
        bool = true;
      }
      jQuery("#send_email input").each(function(i,e) {
        jQuery(e).prop('checked', bool);
      });
    };
    modGetData.get_evenements("Les voyages");
    modGetData.get_mails_to_confirm();
    add_resa_btn.onclick = function () {
      document.getElementById("add_resa_form").style.display='block';
    };
    /*
    Boutton pour reload les événemnts et les réservations
    */
    jQuery("body").off("click","#reload_all");
    $("body").on("click","#reload_all",function () {
      modGestionCJM.reloadData(get_param);
    });
    /*
    * Par défaut les voyages sont affichés dès l'arrivée sur la page 'Gestion des participants'
    */
    les_voyages.slideDown();
    /*
    * Au clique sur le titre 'Les voyages'
    *
    */
    les_voyages_titre.click(function() {
      les_voyages.removeClass('voyage_clicked');
      $(this).css('background-color','inherit');
      les_escapades_titre.css('background-color','#e4e4e4');
      les_mails_titre.css('background-color','#e4e4e4');
      les_escapades.slideUp();
      les_mails.slideUp();
      les_resas.hide();
      if(les_voyages.css('display')=="none"){
        les_voyages.slideDown();
      }
    });
      /*
      * Au clique sur le titre 'Les escapades'
      *
      */
     $("#les_escapades_titre").click(function() {
      les_voyages.removeClass('voyage_clicked');
      $(this).css('background-color','#e4e4e4');
      les_mails_titre.css('background-color','#e4e4e4');
      les_voyages_titre.css('background-color','#e4e4e4');
      $(this).css('background-color','inherit');
      les_voyages.slideUp();
      les_mails.slideUp();
      les_escapades.slideDown();
      $("#les_escapades table tr").show();
      $("#les_escapades h1").html("Les escapades");
      les_resas.hide();
    });
    $("#les_mails_titre").click(function () {
      les_voyages_titre.css('background-color','#e4e4e4');
      les_escapades_titre.css('background-color','#e4e4e4');
      $(this).css('background-color','inherit');
      if(les_mails.css('display')=="none") {
        les_mails.slideDown();
        les_voyages.slideUp();
        les_escapades.slideUp();
        les_resas.slideUp();
      }
      else {
        les_mails.slideUp();
      }
    });
    /*
    * Suppression d'un voyage en AJAX
    */
    $("#btn_sup_voyage").click(function() {
      // if($('#voyages_action option:selected').val()=="Supprimer")
      // {
         modGestionCJM.sup_voyage();
      // }
    });
     /*
    * Suppression d'une escapade en AJAX
    */
    $("#btn_sup_escapade").click(function() {
      // if($('#escapdes_action option:selected').val()=="Supprimer")
      // {
         modGestionCJM.sup_escapade();
      // }
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
    * Code exécuter dès que les requêtes AJAX qui récupére les événements sont terminées
    *
    */
    $( document ).ajaxStop(function() {
      jQuery("body").off("click","#send_email_btn");
      jQuery("body").on("click","#send_email_btn",function () {
        modSendEmails.send_mail_confirm_paiement();
      });
      /*
      Formulaire d'ajout de réservation au voyage cliqué
      */
      jQuery("body").off("click","#add_resa");
      jQuery("body").on("click","#add_resa",function () {
        modGestionCJM.add_resa();
      });
      /*
      * Exporter des réservations au format .xls sous Excel
      *
      */
      jQuery("body").off("click","#btn_export_resa");
      $("body").on("click","#btn_export_resa",function () {
      if($("#export_resas option:selected").val()=="Excel"){
        var name = "";
        if(!les_voyages.is(":hidden")){
          name += $("#les_resas h1").html().split(":")[1];
        }
        else {
           name += $("#les_resas h1").html().split(":")[1];
        }
        $("#les_resas table").table2excel({
        exclude : '.noExl',
        filename: name
        });
      }
      // else if ($("#export_resas option:selected").val()=="PDF") {
      //   $('#les_resas table').tableExport({type:'pdf',escape:'false',htmlContent:'false',ignoreColumn: []});
      // }
      });

      /*
      * Suppresion ou modification d'une réservaion en AJAX
      */
      jQuery("body").off("click","#app_resa");
      jQuery("body").on("click","#btn_sup_resa",function() {
          modGestionCJM.sup_resa();
      });
      jQuery("body").off("click","#app_resa");
      jQuery("body").on("click","#btn_modif_resa",function() {
          modGestionCJM.modif_resa();
      });
      /*
      * Requete AJAX dès que changement sur la checkbox paiement
      */
      jQuery("body").off("click",".paiement_class");
      $("body").on("click",".paiement_class",function () {
        var table="";
        var patt = new RegExp("ext");
        var id = $(this).attr('id').replace("paiement","");
        var res = patt.test(id);
        if(res)
        {
          table="ext";id.replace("ext","");
        }
        var paiement = $(this).is(':checked');
        modGestionCJM.change_paiement_resa(id,paiement,table);
        // modSendEmails.send_mail_confirm_paiement(modSendEmails.tmce_getContent("test_mail"));
      });
      /*
      * Requete AJAX dès que changement sur la checkbox liste attente
      */
      jQuery("body").off("click",".att_class");
      $("body").on("click",".att_class",function () {
          var table="";
          var patt = new RegExp("ext");
          var id = $(this).attr('id').replace("att","")
          var att = $(this).is(':checked');
          var res = patt.test(id);
          if(res)
          {
            table="ext";id.replace("ext","");
          }
          if(!att)
          {
            $("#reservation"+id+" td").eq(0).attr('class','resa_attente');
          }
          else {
            $("#reservation"+id+" td").eq(0).attr('class','');
          }
          modGestionCJM.change_att_resa(id,att,table);
      });
        /*
        * Au clique sur un événement, affichage des réservations de l'événement cliqué
        */
        var ligne = $("#les_voyages table tbody > tr , #les_escapades table tbody > tr");
        ligne.each(function (i,e) {
           $(e).children().eq(1).click(function () {
              ligne.children().removeClass('voyage_clicked');
              $(this).addClass('voyage_clicked');
              $("#add_resa_form").attr('voyage',$(e).attr('id'));
        if(les_voyages.css('display')=="block")
          {
            $("#les_resas h1").html("Les réservations pour le voyage : "+$(e).children().eq(1).text());
            les_resas.show();
            $("#les_resas table tr").hide().addClass('noExl');
            $("#les_resas table tr:eq(0)").show().removeClass('noExl');
            $("#les_resas table tbody > tr > #"+$(e).attr("id")+"").parent().show().removeClass('noExl');
          }

        if(les_escapades.css('display')=="block")
          {
            $("#les_resas h1").html("Les réservations pour l'Escapade : "+$(e).children().eq(1).text());
            les_resas.show();
            $("#les_resas table tr").hide().addClass('noExl');
            $("#les_resas table tr:eq(0)").show().removeClass('noExl');
            $("#les_resas table tbody > tr > #"+$(e).attr("id")+"").parent().show().removeClass('noExl');
          }
          });
        });
    });
  });
});
