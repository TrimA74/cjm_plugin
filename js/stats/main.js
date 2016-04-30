jQuery(document).ready(function() {
    var data_to_send = {
    'action' : 'get_resas',
    'get_resas' : true ,
    'is_stat' : true
  }
    jQuery('#resas').DataTable( {
        "language" : {
            "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/French.json"
        },
        "processing": true,
        "serverSide": false,
        "ajax": {
            "url": ajax_object.ajax_url,
            "type": "POST",
            "data" : data_to_send
        },
        "columnDefs": [
            { 
                "data": "display_name",
                "targets": 0
            },
            { "data": "nom_voyage",
                "targets": 1 },
            { "data": "nbplace",
                "targets": 2 },
            { "data": "nbplace_enf",
                "targets": 3 },
            { "data": "prix_total",
                "targets": 4 },
            { "data": "tel",
                "targets":5 },
            { 
                "data": function (source, type, val) {
                    if(source.paiement=="0"){val="OUI";}else {val="NON";}
                    if(type=="sort"){ return source.paiement;}
                    if(type=="filter"){ return val; }
                    if (type=="display") { return val; }
                    return source.paiement;
                },
                "targets": 6
            },
            { "data": function (source, type, val) {
                    if(type=="sort"){
                        return source.liste_attente;
                    }
                    if(type=="filter"){
                        if(source.liste_attente=="0"){
                            val="Réservé";
                        }
                        else {
                            val="²";
                        }
                        return val; 
                    }
                    if (type=="display")
                    {
                         if(source.liste_attente=="0"){
                            val="Réservé";
                        }
                        else {
                            val="Attente";
                        }
                        return val;
                    }
                    return source.liste_attente;
                } ,
                "targets": 7},
            { "data": "role",
                "targets": 8 },
                {"data":"date_resa",
                "targets":9}
        ]
    } );
} );
