
/*
* Module de récupération des données plugin CJM
*/
var modGetData = (function(){
    var self = {};
    function calcul_prix_resa (nbplaceE,nbplaceP,prix_enfant,prix_adulte) {
      var res;
      res = (nbplaceP*prix_adulte)+(nbplaceE*prix_enfant);
      return res;
    };
    /*
    * Aucun paramètre, fonction qui récupère les informations de l'utilisateur qui est conencté en AJAX
    Return : Object de type Promise qui contient les informations de l'utiisateur
    Pour récupérer les informations facilement il faut le faire dans une fonction de callback comme ceci
    Soit user la variable de type Promise : 
    user.then(function (user) {
      console.log(user);
      //Ici user contient toutes les informations de l'utilisateur(rôle,droits,data)
    });
    */
    self.get_logged_user = function () {
       var data = {
      'action': 'get_logged_user',
      'user': ajax_object.user
      };
      return Promise.resolve(jQuery.post(ajax_object.ajax_url, data,function (data) {  
        },"json"));
    };
    /*
    * Fonction qui fait un appel AJAX sur ajaxController.php pour récupérer tous les événéments
    * Param : type (il n'est pas utile pour le moment mais peut permettre de récupérer unique un type de voyage à la fois)
    */
    self.get_evenements = function (type) {
        var data = {
          'action' : 'get_evenements',
          'get_evenements' : true , 
          'type_evenement' : type
        }
          jQuery.post(ajax_object.ajax_url,data,function (data) {
        /*
        * Remplissage du tableau des voyages et des escapades
        * Requete AJAX sur ajaxController pour récupérer tous les événements
        */
        /*
        * Remplissage de l'en-tête du tableau des voyages 
        */
        jQuery("#les_voyages table").append("<tr></tr>");
        jQuery("#les_voyages table tr").append("<th  style='width:5%;' class='manage-column'><input class='sup_voyage_class' id='check_sup_voyage_all' type='checkbox'></th>");
        jQuery("#les_voyages table tr").append("<th class='manage-column'>Nom du voyage</th>");
        jQuery("#les_voyages table tr").append("<th class='manage-column'>Début</th>");
        jQuery("#les_voyages table tr").append("<th class='manage-column'>Fin</th>");
        jQuery("#les_voyages table tr").append("<th class='manage-column'>Nombre places restantes</th>");
        jQuery("#les_voyages table tr").append("<th class='manage-column'>Tarif Adulte</th>");
        jQuery("#les_voyages table tr").append("<th class='manage-column'>Tarif Enfant</th>");
        /*
        * Remplissage de l'en-tête du tableau des escapades 
        */
        jQuery("#les_escapades table").append("<tr></tr>");
        jQuery("#les_escapades table tr").append("<th  style='width:5%;' class='manage-column'><input class='sup_voyage_class' id='check_sup_voyage_all' type='checkbox'></th>");
        jQuery("#les_escapades table tr").append("<th class='manage-column'>Nom escapade</th>");
        jQuery("#les_escapades table tr").append("<th class='manage-column'>Début</th>");
        jQuery("#les_escapades table tr").append("<th class='manage-column'>Fin</th>");
        jQuery("#les_escapades table tr").append("<th class='manage-column'>Nombre places restantes</th>");
        jQuery("#les_escapades table tr").append("<th class='manage-column'>Tarif Adulte</th>");
        jQuery("#les_escapades table tr").append("<th class='manage-column'>Tarif Enfant</th>");
        /*
        * Pour chaque Object événement ajout d'une ligne dans le tableau
        *
        */
        jQuery.each(data,function(i,e){
          var type_evenement = e.category.toLowerCase().substr(0,e.category.toLowerCase().length-1);
          jQuery("#les_"+e.category.toLowerCase()+" table").append("<tr id='"+type_evenement+""+e.ID+"'></tr>");
          var ligne = jQuery("#"+type_evenement+""+e.ID);
          ligne.append("<td><input type='checkbox' id='check_sup_"+type_evenement+""+e.ID+"' class='sup_"+type_evenement+"_class' ></td>");
          ligne.append("<td style='cursor:pointer;color:blue;'>"+e.post_title+"</td>");
          ligne.append("<td>"+e.dated+"</td>");
          ligne.append("<td>"+e.datef+"</td>");
          ligne.append("<td>"+e.nbplace+"</td>");
          ligne.append("<td>"+e.tarifa+"</td>");
          ligne.append("<td>"+e.tarife+"</td>");

        });
        /*
        * Fin du remplissage du tableau
        *
        */
      },"json")
      .done(function() {
         modGetData.get_resas();
        })
      /*
      * Si la requête ajax échoue, petit alert des familles
      */
      .fail(function() {
        alert( "Les événements n'ont pas été chargées." );
        });
    };
    self.get_resas = function () {
          var data = {
          'action' : 'get_resas',
          'get_resas' : true , 
        }
         jQuery.post(ajax_object.ajax_url,data,function (data) {
        jQuery("#les_resas table").append("<tr></tr>");
        jQuery("#les_resas table tr").append("<th style='width:5%;' class='manage-column'><input class='sup_resa_class' id='check_sup_resa_all' type='checkbox'></th>");
        jQuery("#les_resas table tr").append("<th class='manage-column'>Nom Prenom</th>");
        jQuery("#les_resas table tr").append("<th class='manage-column'>Nom voyage</th>");
        jQuery("#les_resas table tr").append("<th class='manage-column'>Nombre de places Adulte</th>");
        jQuery("#les_resas table tr").append("<th class='manage-column'>Nombre de places Enfant</th>");
        jQuery("#les_resas table tr").append("<th class='manage-column'>Paiement</th>");
        jQuery("#les_resas table tr").append("<th class='manage-column'>Prix</th>");
        jQuery("#les_resas table tr").append("<th class='manage-column'>Téléphone</th>");
        jQuery.each(data,function(i,e){
          var type_evenement = e.category.toLowerCase().substr(0,e.category.toLowerCase().length-1);
          jQuery("#les_resas table").append("<tr id='reservation"+e.id_resa+"'></tr>");
          var resa = jQuery("#reservation"+e.id_resa);
          resa.append("<td><input type='checkbox' id='check_sup_resa"+e.id_resa+"' class='sup_resa_class' ></td>");
          resa.append("<td>"+e.user_name+"</td>");
          resa.append("<td id='"+type_evenement+""+e.id_evenement+"'>"+e.nom_voyage+"</td>");
          resa.append("<td name='nb_place_resa'>"+e.nbplace+"</td>");
          resa.append("<td name='nbplace_enf_resa'>"+e.nbplace_enf+"</td>");
          if(e.paiement==0)
          {
            resa.append("<td><input type='checkbox' id='paiement"+e.id_resa+"' class='paiement_class'></td>");
          }
          else {
            resa.append("<td><input type='checkbox' id='paiement"+e.id_resa+"' class='paiement_class' checked ></td>");
          }
          var prix_adulte = parseInt(jQuery("#"+type_evenement+""+e.id_evenement).children().eq(5).text());
          var prix_enfant = parseInt(jQuery("#"+type_evenement+""+e.id_evenement).children().eq(6).text());
          resa.append("<td>"+String(calcul_prix_resa(parseInt(e.nbplace_enf),parseInt(e.nbplace),parseInt(prix_enfant),parseInt(prix_adulte)))+"</td>");
          resa.append("<td name='tel_resa'>"+e.tel+"</td>");
                          });
      },"json")
    .fail(function() {
      alert( "Les réservations n'ont pas été chargées.");
      });      
  

    };
    self.get_resa_by_voyage = function (id_evenement) {
      var data = {
          'action' : 'get_resa_by_voyage',
          'get_resa_by_voyage' : true , 
          'id_evenement' : id_evenement
        }
       return Promise.resolve(jQuery.post(ajax_object.ajax_url,data,function (data) {  
        },"json"));
    };
    /*
    Paramètres : id_user L'ID de l'utilisateur pour lequelle on veut récupérer ses informations
    Return : Object de type Promise qui contient les informations de l'utiisateur
    Pour récupérer les informations facilement il faut le faire dans une fonction de callback comme ceci
    Soit user la variable de type Promise : 
    user.then(function (user) {
      console.log(user);
      //Ici user contient toutes les informations de l'utilisateur(rôle,droits,data)
    });
    */
    self.get_user_by_id = function (id_user) {
      var data = {
          'action' : 'get_user_by_id',
          'user_by_id' : true , 
          'id_user' : id_user
        }
      return Promise.resolve(jQuery.post(ajax_object.ajax_url,data,function (data) {  
      },"json"));
    };
    return self;
})();
/*
* Module de gestion des données (réservation & événements)
* 
*/
var modGestionCJM = (function(){
  var self = {};
  function modif_resa_ajax () {

  };
  self.modif_resa = function () {
    var checks = jQuery(".sup_resa_class:checked").attr('id');
    var res = checks.match(/\d+/);
    jQuery("#reservation"+res[0]).children().each(function(i,e)
    {
      var textElement = jQuery(e).html();
      if(!jQuery(e).children().is("input") && i!=1 && i!=2 && i!=6)
      {
        var name = jQuery(e).attr('name');
        jQuery(e).html("<input name='modif_"+name+"' type='text' value='"+textElement+"'></input>");
      }
    });
    jQuery("<input type='button' value='Enregistrer' class='button action' id='register_modif_resa'>").insertAfter('#app_resa');
    jQuery("#register_modif_resa").click( function () {
      // modif_resa_ajax()
      // console.log(jQuery("#les_resas").find("table").find("tbody").serialize());
      var infos = "";
      jQuery("#les_resas").find("table").find("tbody").find("tr").each(function(i,e)
      {
        // test += jQuery(e).children('td').children('input').val();
        var resa_text = jQuery(e).children('td').children('input:text');
        if(resa_text.length==3)
        {
          jQuery.each(resa_text,function(i,e){
            infos += jQuery(e).attr('name') + "=" +jQuery(e).val() + "&";
          });
        }
        
      });
      });
  };
  self.sup_voyage = function () {
    var checks = jQuery(".sup_voyage_class:checked").attr('id');
    var res = checks.match(/\d+/);
     var data = {
        'action' : 'sup_voyage',
        'id' : res[0] , 
        'delete_voyage' : true
      }
    jQuery.post(ajax_object.ajax_url,data, function (data) {
      jQuery("#voyage"+res).hide();
    },"json");
  }
    self.sup_escapade = function () {
    var checks = jQuery(".sup_escapade_class:checked").attr('id');
    var res = checks.match(/\d+/);
     var data = {
        'action' : 'sup_escapade',
        'id' : res[0] , 
        'delete_escapade' : true
      }
    jQuery.post(ajax_object.ajax_url,data, function (data) {
      jQuery("#escapade"+res).hide();
    },"json");
  }

  self.sup_resa = function () {
    var checks = jQuery(".sup_resa_class:checked").attr('id');
    var res = checks.match(/\d+/);
    var id_evenement = jQuery("#reservation"+res[0]+" td").eq(2).html();
    var data = {
        'action' : 'sup_resa',
        'delete_resa' : true , 
        'id_resa' :  res[0],
        'id_evenement' : id_evenement
      }
    jQuery.post(ajax_object.ajax_url,data, function (data) {
      jQuery("#reservation"+res).hide();
    },"json");
  }

  self.change_paiement_resa = function (id_resa,paiement) {
      var data = {
          'action' : 'change_paiement_resa',
          'paiement' : paiement , 
          'id_resa' : id_resa
        }
      jQuery.post(ajax_object.ajax_url,data, function (data) {

      }, "json"

    );
  };
  self.GET = function (param) {
  var vars = {};
  window.location.href.replace( 
    /[?&]+([^=&]+)=?([^&]*)?/gi, // regexp
    function( m, key, value ) { // callback
      vars[key] = value !== undefined ? value : '';
    }
  );
  if ( param ) {
    return vars[param] ? vars[param] : null;  
      }
    return vars;
  };

  return self;


  
  })();



