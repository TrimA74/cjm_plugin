jQuery(document).ready(function() {
    var data_to_send = {
    'action' : 'get_resas',
    'get_resas' : true ,
  }
    jQuery('#resas').DataTable( {
        "processing": true,
        "serverSide": false,
        "ajax": {
            "url": ajax_object.ajax_url,
            "type": "POST",
            "data" : data_to_send,
            // complete : function (data) {
            //   jQuery("#resas_length label").html(
            //     jQuery("#resas_length label").html()
            //     .replace("Show","Montrez les")
            //     .replace("entries"," premières données"));
            //   jQuery("#resas_filter label").html(
            //     jQuery("#resas_filter label").html()
            //     .replace("Search","Recherche")
            //   );
            //   jQuery("#resas_previous").text("Précedent");
            //   jQuery("#resas_next").text("Suivant");
            // }
        },
        "columns": [
            { "data": "display_name" },
            { "data": "nom_voyage" },
            { "data": "nbplace" },
            { "data": "nbplace_enf" },
            { "data": "prix_total" },
            { "data": "tel" },
            { "data": "paiement" },
            { "data": "liste_attente" }
        ]
    } );
} );
