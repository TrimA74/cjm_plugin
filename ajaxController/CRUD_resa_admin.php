<?php
/*
*************************** CRUD Réservation ADMIN ***************************
*/
class CRUD_resa_admin {
  function __construct() {
      add_action('wp_ajax_add_resa',array($this,'add_resa'));
      add_action('wp_ajax_get_resas',array($this,'get_resas'));
      add_action('wp_ajax_sup_resa',array($this,'sup_resa'));
      add_action('wp_ajax_modif_resa',array($this,'modif_resa'));
    }
  /********************** CREATE **********************
  ***** Réservation d'un événement par une personne extérieur
  */
  public function add_resa () {
    global $wpdb;
    $error = "test ";
    if(isset($_POST["nom"])
      && isset($_POST["nbplace"])
      && isset($_POST["nbplace_enf"])
      && isset($_POST["tel"])
      && isset($_POST["paiement"])
      && isset($_POST["liste_attente"])
      && isset($_POST["id_evenement"])
      && isset($_POST["role"])
      && !empty($_POST["nom"])
      && !empty($_POST["tel"])
      && !empty($_POST["paiement"])
      && !empty($_POST["liste_attente"])
      && !empty($_POST["id_evenement"]))
    {
      $list=0;
      $paiement = 0;
      if($_POST["paiement"]=='true')
      {
        $paiement=1;
      }
      if($_POST["liste_attente"]=='true')
      {
        $list=1;
      }
      $res= $wpdb->insert(
      cjm_reservation_ext,
      array(
        'nom' => $_POST["nom"],
        'tel'=> $_POST["tel"],
        'nbplace'=>intval($_POST["nbplace"]) ,
        'nbplace_enf'=> intval($_POST["nbplace_enf"]),
        'paiement' => $paiement,
        'liste_attente' => $list,
        'prix_total' =>getPrixTotal($_POST["id_evenement"],$_POST["nbplace"],$_POST["nbplace_enf"],$_POST["role"]),
        'role' => $_POST["role"],
        'id_evenement' => intval($_POST["id_evenement"]),
        'date_resa'=>date("d-m-Y H:i:s"))
      );
      /* MAJ nb_place_dispo
      */
      if($res==1 && $list==0) {
        $curr_nbplace = intval(get_post_meta(intval($_POST["id_evenement"]),"_nb_place",true));
        $nbplace_maj = $curr_nbplace-intval($_POST["nbplace"])-intval($_POST["nbplace_enf"]);
        $res = update_post_meta(intval($_POST["id_evenement"]),"_nb_place",$nbplace_maj);
        if($res==false){
          $error .=" Nb Places pas MAJ";
        }
      }
      else {
        $error .=" Pas de maj";
      }
      $data = array(
        'nom' => $_POST["nom"],
        'tel'=> $_POST["tel"],
        'nbplace_adl'=>intval($_POST["nbplace"]) ,
        'nbplace_enf'=> intval($_POST["nbplace_enf"]),
        'paiement' => $_POST["paiement"],
        'attente' => $_POST["liste_attente"],
        'role' => $_POST["role"],
        'id_evenement' => intval($_POST["id_evenement"]),
        'nouveau_nb_place' => $nbplace_maj,
        'current_nb_place' => $curr_nbplace ,
        "liste_attente" => $list,
        "error" => $error,
        "res" => $res
      );
      echo json_encode(array(
        "data" => $data,
        "error" => $error));
    }
    wp_die();
  }
  /********************** READ **********************
  ***** Récupération de toutes les réservations
  */
  public function get_resas() {
    $error = array (
    "value" => 0 ,
    "message" => ""
    );
    global $wpdb;
    if(isset($_POST["get_resas"]))
    {
      $res = $wpdb->get_results("select r.prix_total,r.date_resa,r.id_evenement,r.id_participant,r.id_resa,r.nbplace,r.nbplace_enf,u.user_login,r.paiement,u.display_name,r.liste_attente from cjm_reservation r
      	join cjm_users u on u.ID=r.id_participant
      	order by r.liste_attente asc,u.display_name asc
      	;");
        if(is_null($res))
        {
          $error["value"]= 1;$error["message"]= "Les réservations n'ont pas été récupérer (erreur dans la requête sql)";
        }
      	foreach ($res as $key => $value) {
          $nomv = get_post_meta($value->id_evenement,"_nom_voyage",true);
          $value->nom_voyage=$nomv;
          $tel = get_user_meta($value->id_participant,"tel",true);
          $value->tel=$tel;
          $cat = get_the_category($value->id_evenement);
          $value->category=$cat[0]->category_nicename;
          $role = get_user_by('id',$value->id_participant)->roles[0];
          $value->ext="";
          if($role=="subscriber")
          {
            $value->role="Abonné";
          }
          else if ($role=="adherent_user")
          {
            $value->role="Adhérent";
          }
          else if($role="administrator")
          {
            $value->role="Administrateur";
          }
        }

      	$res_ext = $wpdb->get_results("select role,prix_total,date_resa,nom as 'display_name',id_resa,tel,nbplace,nbplace_enf,paiement,liste_attente,id_evenement from cjm_reservation_ext
      		order by liste_attente asc,nom asc
      		;");
          if(is_null($res_ext))
          {
            $error["value"]= 1;$error["message"]= "Les réservations extérieures n'ont pas été récupérer (erreur dans la requête sql)";
          }
      		foreach ($res_ext as $key => $value) {
      			$nomv = get_post_meta($value->id_evenement,"_nom_voyage",true);
      			$value->nom_voyage=$nomv;
      			$cat = get_the_category($value->id_evenement);
      	    $value->category=$cat[0]->category_nicename;
            $id_resa = $value->id_resa;
      			$value->id_participant=$id_resa."ext";
            $value->id_resa=$id_resa."ext";
            $role = $value->role;
            $value->ext="user_ext";
            if($role=="adherent_user") {
              $value->role="Adhérent";
            }
            else {
              $value->role="Non adhérent";
            }
      		}

       if(isset($_POST["is_stat"])){
      echo json_encode(array("data" => array_merge($res,$res_ext)));
    }
    else {
    echo json_encode(
    array(
      "draw" => 1,
      "recordsTotal"=> 57,
      "recordsFiltered"=> 57,
      "data" => array_merge($res,$res_ext),
      "error" => $error
    ));
  }
  }
  else {
    $error["value"]= 1;$error["message"]= "Erreur dans la requête AJAX";
    echo json_encode(array("error" => $error));
  }
    wp_die();
  }
  /********************** DELETE **********************
  ***** Suppresion d'une réservation
  */
  public function sup_resa () {
    $error ="";
    global $wpdb;
    if(isset($_POST["delete_resa"])) {
      if(isset($_POST["id_resa"]) && isset($_POST["table"])
      && !empty($_POST["id_resa"])) {
        $table="cjm_reservation";
        if(!empty($_POST["table"]))
        {
          $table="cjm_reservation_".$_POST["table"];
        }
        $select = $wpdb->get_results("select nbplace,nbplace_enf,id_evenement,liste_attente from ".$table." where id_resa=".$_POST["id_resa"]);
        $nbplace = get_post_meta($select[0]->id_evenement,'_nb_place',true);
        $query = $wpdb->delete($table,array('id_resa'=>intval($_POST["id_resa"])),array('%d'));
        if($query==1 && $select[0]->liste_attente==0)
        {
          $maj_nb_place = intval($nbplace)+intval($select[0]->nbplace)+intval($select[0]->nbplace_enf);
          $query = update_post_meta($select[0]->id_evenement,"_nb_place",(string)$maj_nb_place);
          if($query==false) {
            $error .= "Pas de MAJ nb_place";
          }
        }
        else {
          $error .= "Delete didnt works";
        }
        $values = array(
          "id_resa" => $_POST["id_resa"],
          "nom_table" =>$_POST["table"],
          "maj_nb_place" => $maj_nb_place,
          "delete_works?"=> $query,
          "select" => $select);
        echo json_encode(array($error,$values));
      }
    }
    else {
      echo json_encode("marche po");
    }
    wp_die();
  }
  /********************** UPDATE **********************
  ***** Modification d'une réservation
  */
  public function modif_resa () {
    global $wpdb;
    $error="";
    if(isset($_POST["id"])
      && isset($_POST["nbplace"])
      && isset($_POST["nbplace_enf"])
      && isset($_POST["tel"])
      && isset($_POST["id_user"])
      && isset($_POST["table"])
      && !empty($_POST["id"])
      && !empty($_POST["tel"])
      && !empty($_POST["id_user"]))
    {
      $table="";
      if(!empty($_POST["table"]))
      {
        $select = $wpdb->get_results("SELECT id_evenement,nbplace,nbplace_enf,role,liste_attente FROM cjm_reservation_ext WHERE id_resa=".$_POST["id"]);
        $resquery = $wpdb->update('cjm_reservation_ext',
        array(
          'tel' => $_POST["tel"],
          'nbplace' => intval($_POST["nbplace"]),
          'nbplace_enf'=> intval($_POST["nbplace_enf"]),
          "prix_total" => getPrixTotal($select[0]->id_evenement,intval($_POST["nbplace"]),intval($_POST["nbplace_enf"]),$select[0]->role)
        ),
        array('id_resa' => intval($_POST["id"])),
        array('%s','%d','%d'),
        array('%d'));
        if($resquery==1 && $select[0]->liste_attente==0) {
          $curr_nbplace = intval(get_post_meta(intval($select[0]->id_evenement),"_nb_place",true));
          $nbplace_maj = $curr_nbplace + intval($select[0]->nbplace) + intval($select[0]->nbplace_enf) - intval($_POST["nbplace"]) - intval($_POST["nbplace_enf"]);
          $resquery = update_post_meta(intval($select[0]->id_evenement),"_nb_place",$nbplace_maj);
        }
        else {
          $error .= "pas d'update ";
        }
        if($resquery==false){$error.="nbplace pas MAJ ";}
      }
      else {
        $select = $wpdb->get_results("SELECT id_evenement,nbplace,nbplace_enf,id_participant,liste_attente
          FROM cjm_reservation
          WHERE id_resa=".$_POST["id"]);
        $user = get_userdata($select[0]->id_participant);
        $resquery = $wpdb->update("cjm_reservation",
        array(
          "nbplace" =>  intval($_POST["nbplace"]) ,
          "nbplace_enf" => intval($_POST["nbplace_enf"]),
          "prix_total" => getPrixTotal($select[0]->id_evenement,intval($_POST["nbplace"]),intval($_POST["nbplace_enf"]),$user->roles[0])
        ),
        array(
          "id_resa" => intval($_POST["id"])
        ));
        if($resquery==1 && $select[0]->liste_attente==0) {
          $curr_nbplace = intval(get_post_meta(intval($select[0]->id_evenement),"_nb_place",true));
          $nbplace_maj = $curr_nbplace + intval($select[0]->nbplace) + intval($select[0]->nbplace_enf) - intval($_POST["nbplace"]) - intval($_POST["nbplace_enf"]);
          $resquery = update_post_meta(intval($select[0]->id_evenement),"_nb_place",$nbplace_maj);
        }
        else {
          $error .= "pas d'update nbplace";
        }
        if($resquery==false){
            $error.="MAJ nbplace didnt work";
          }
        $resquery = $wpdb->update("cjm_usermeta",
        array(
          "meta_value" => $_POST["tel"] ,
        ),
        array(
          "meta_key" => "tel",
          "user_id" => intval($_POST["id_user"])
        ),
        array('%s'),array('%s','%d'));
        if($resquery==false){
            $error.="MAJ du tel didnt work";
        }
      }
      $values= array(
        "id_resa" => $_POST["id"],
        "nbplace_adl" => $_POST["nbplace"],
        "nbplace_enf" => $_POST["nbplace_enf"],
        "tel" => $_POST["tel"],
        "id_user" => $_POST["id_user"],
        "nom_table"  => $_POST["table"],
        "select" => $select[0],
        "query" => $resquery,
        "nbplace_maj" => $nbplace_maj,
        "current_nbplace" => $curr_nbplace,
        "user" => $user,
        );
      $res=array("error"=> $error,
        "valeurs"=> $values);
      echo json_encode($res);
    }
    else {
      echo json_encode($values);
    }
    wp_die();
  }
}
new CRUD_resa_admin();
