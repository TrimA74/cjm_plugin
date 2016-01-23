<?php

function get_logged_user_callback() {
  global $wpdb;
  $user = wp_get_current_user();
  echo json_encode($user);
  wp_die();
}
add_action( 'wp_ajax_get_logged_user', 'get_logged_user_callback' );
add_action( 'wp_ajax_nopriv_get_logged_user', 'get_logged_user_callback');

function get_evenements_callback () {

if(isset($_POST["get_evenements"]) && isset($_POST["type_evenement"]))
{
  global $wpdb;
  $res = $wpdb->get_results("select ID from cjm_posts where post_type=\"reservation\" and post_status not in (\"auto-draft\",\"trash\")");
  foreach ($res as $key => $value) {
    $nom = get_post_meta($value->ID,"_nom_voyage",true);
    $value->post_title=$nom;
    $etat_resa = get_post_meta($value->ID,"_etat_resa",true);
    $value->etat_resa=$etat_resa;
    $dated= get_post_meta($value->ID,"_date_debut",true);
    $value->dated = $dated;
    $datef = get_post_meta($value->ID,"_date_fin",true);
    $value->datef = $datef;
    $nbplace = get_post_meta($value->ID,"_nb_place",true);
    $value->nbplace = $nbplace;
    $tarifa = get_post_meta($value->ID,"_tarif_adulte",true);
    $value->tarifa=$tarifa;
    $tarife = get_post_meta($value->ID,"_tarif_enfant",true);
    $value->tarife=$tarife;
    $cat = get_the_category($value->ID);
    $value->category=$cat[0]->category_nicename;

  }
  echo json_encode($res);
}
wp_die();
}

add_action('wp_ajax_get_evenements','get_evenements_callback');

function get_resas_callback () {
  global $wpdb;
  if(isset($_POST["get_resas"]))
  {
  $res = $wpdb->get_results("select r.prix_total,r.date_resa,r.id_evenement,r.id_participant,r.id_resa,r.nbplace,r.nbplace_enf,r.paiement,u.display_name,r.liste_attente from cjm_reservation r
    join cjm_users u on u.ID=r.id_participant
    order by r.liste_attente asc,u.display_name asc
    ;");
  foreach ($res as $key => $value) {
    $nomv = get_post_meta($value->id_evenement,"_nom_voyage",true);
    $value->nom_voyage=$nomv;
    $tel = get_user_meta($value->id_participant,"tel",true);
    $value->tel=$tel;
    $cat = get_the_category($value->id_evenement);
    $value->category=$cat[0]->category_nicename;
  }

  echo json_encode($res);
}
  wp_die();
}

add_action('wp_ajax_get_resas','get_resas_callback');

function get_resa_by_voyage_callback () {
  global $wpdb;


if(isset($_POST["get_resa_by_voyage"]) && isset($_POST["id_evenement"]) && !empty($_POST["id_evenement"]))
  {
    $id_evenement=$_POST["id_evenement"];
    $res = $wpdb->get_results("select r.date_resa,r.id_evenement,r.id_participant,r.id_resa,r.nbplace,r.paiement from cjm_reservation r
    join cjm_users u on u.ID=r.id_participant
    where id_evenement=$id_evenement
    ;");
  foreach ($res as $key => $value) {
    $prenom = get_user_meta($value->id_participant,"first_name", true); 
    $nom = get_user_meta($value->id_participant,"last_name", true); 
    $value->user_name=$nom." ".$prenom;
    $nomv = get_post_meta($value->id_evenement,"_nom_voyage",true);
    $value->nom_voyage=$nomv;
    $tel = get_user_meta($value->id_participant,"tel",true);
    $value->tel=$tel;
  }

  echo json_encode($res);

  }
  wp_die();
}

add_action('wp_ajax_get_resa_by_voyage','get_resa_by_voyage_callback');

function get_user_by_id_callback () {
  global $wpdb;
  if(isset($_POST["user_by_id"]) && isset($_POST["id_user"]) && !empty($_POST["id_user"]))
  {
  if($_POST["user_by_id"]==true)
  {
  $user = get_user_by('id',$_POST["id_user"]);
  echo json_encode($user);
  }
  }
  wp_die();
}

add_action('wp_ajax_get_user_by_id' , 'get_user_by_id_callback');

function change_paiement_resa_callback () {
  global $wpdb;

  if(isset($_POST["paiement"]) 
  && !empty($_POST["paiement"])
  && isset($_POST["id_resa"])
   && !empty($_POST["id_resa"])) {
  if($_POST["paiement"]=='true'){
    $val=1;
  }
  else{
    $val=0;
  }
  $return = $wpdb->update(
    'cjm_reservation', 
    array( 
      'paiement' => $val, 
    ), 
    array( 'id_resa' => $_POST["id_resa"]));
  if($return==1)
    {
      $msg="PaiementisOK";
    }
    else {
      $msg="PaiementPasOK";
    }
  echo json_encode($msg);
  }
  wp_die();
}
add_action('wp_ajax_change_paiement_resa','change_paiement_resa_callback');

function change_att_resa_callback () {
  global $wpdb;

  if(isset($_POST["att"]) && !empty($_POST["att"])
  && isset($_POST["id_resa"]) && !empty($_POST["id_resa"])){
    if($_POST["att"]=='true'){
      $val=0;
    }
    else{
      $val=1;
    }
    $return = $wpdb->update(
    'cjm_reservation', 
    array( 
      'liste_attente' => $val, 
    ), 
    array( 'id_resa' => $_POST["id_resa"])
    );
  }
  wp_die();
}
add_action('wp_ajax_change_att_resa','change_att_resa_callback');

function sup_resa_callback () {
  global $wpdb;
  if(isset($_POST["delete_resa"])) {
    if(isset($_POST["id_resa"])
    && !empty($_POST["id_resa"])) {
      $nbplace = $wpdb->get_results("select nbplace,nbplace_enf,id_evenement from cjm_reservation where id_resa=".$_POST["id_resa"].";");
      $query = $wpdb->prepare("delete from cjm_reservation where id_resa=%d",$_POST["id_resa"]);
      $res = $wpdb->query($query);
      $new_nbplace = $nbplace[0]->nbplace + $nbplace[0]->nbplace_enf;
      $id_evenement = $nbplace[0]->id_evenement;
      $current_nbplace = get_post_meta($id_evenement,"_nb_place");
      $res = update_post_meta($id_evenement,"_nb_place",$current_nbplace[0]+$new_nbplace);
      echo json_encode(array(
        "new_nbplace" => $new_nbplace,
        "id_evenement" => $id_evenement,
        "current_nbplace" => $current_nbplace
        ));
    }
  }
  wp_die();
}
add_action('wp_ajax_sup_resa','sup_resa_callback');

function sup_voyage_callback () {
  global $wpdb;
  if(isset($_POST["delete_voyage"]))
  {
  if(isset($_POST["id"])
    && !empty($_POST["id"]))
  {
    $query = $wpdb->prepare("delete from cjm_posts where ID=%d",$_POST["id"]);
    $wpdb->query($query);
    $query = $wpdb->prepare("delete from cjm_postmeta where post_id=%d",$_POST["id"]);
    $wpdb->query($query);
    $query = $wpdb->prepare("delete from cjm_reservation where id_evenement=%d",$_POST["id"]);
    $wpdb->query($query);
  }
  }
  wp_die();
}

add_action('wp_ajax_sup_voyage','sup_voyage_callback');


function sup_escapade_callback () {
  global $wpdb;
  if(isset($_POST["delete_escapade"]))

  {
  if(isset($_POST["id"])
    && !empty($_POST["id"]))
  {
    $query = $wpdb->prepare("delete from cjm_posts where ID=%d",$_POST["id"]);
    $wpdb->query($query);
    $query = $wpdb->prepare("delete from cjm_postmeta where post_id=%d",$_POST["id"]);
    $wpdb->query($query);
    $query = $wpdb->prepare("delete from cjm_reservation where id_evenement=%d",$_POST["id"]);
    $wpdb->query($query);
  }
  }
  wp_die();
}

add_action('wp_ajax_sup_escapade','sup_escapade_callback');

function modif_resa_callback () {
  global $wpdb;
  $error="";
  $values= array($_POST["id"],$_POST["nbplace"],$_POST["nbplace_enf"],$_POST["tel"],$_POST["id_user"]);
  if(isset($_POST["id"])
    && isset($_POST["nbplace"])
    && isset($_POST["nbplace_enf"])
    && isset($_POST["tel"])
    && isset($_POST["id_user"])
    && !empty($_POST["id"])
    && !empty($_POST["tel"])
    && !empty($_POST["id_user"]))
  {   
    $query = $wpdb->prepare("update cjm_reservation set nbplace=%d , nbplace_enf=%d  where id_resa=%d",$_POST["nbplace"],$_POST["nbplace_enf"],$_POST["id"]);
    $resquery = $wpdb->query($query);
    if(!$resquery)
    {
      $error.="Modif sur cjm_reservation a echoue\n";
    } 
    $query = $wpdb->prepare("update cjm_usermeta set meta_value=%s where meta_key='tel' and user_id=%d",$_POST["tel"],$_POST["id_user"]);
    $resquery = $wpdb->query($query);
    if(!$resquery)
    {
      $error.="Modif sur cjm_usermeta a echoue";
    }
    $res=array("error"=> $error,
      "resQuery" => $resquery,
      "valeurs"=> $values);
    echo json_encode($res);
  }
  else {
    echo json_encode($value);
  }
  wp_die();
}
add_action('wp_ajax_modif_resa','modif_resa_callback');

function send_mail_confirm () {
  global $wpdb;
  if(isset($_POST["mail"])
  && !empty($_POST["mail"])
  && isset($_POST["content"])
  && !empty($_POST["content"])
  ){
    wp_mail("florianseb96@hotmail.fr","Confirmation paiement voyage",$_POST["content"]);
    echo json_encode("done");
  }
  wp_die();
}
add_action('wp_ajax_send_mail_confirm','send_mail_confirm');


function add_resa () {
  global $wpdb;
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
    
    // $query = $wpdb->prepare("insert into cjm_reservation_ext (nom,tel,nbplace,nbplace_enf,paiement,liste_attente,prix_total,id_evenement)
    //   values (%s,%s,%d,%d,%d,%d,%d);
    //   ",$_POST["nom"],$_POST["tel"],$_POST["nbplace"],$_POST["nbplace_enf"],$_POST["paiement"],$_POST["liste_attente"],$prix,$_POST["id_evenement"]);
    // $res = $wpdb->exec($query);
    
  }
  wp_die();
}

add_action('wp_ajax_add_resa','add_resa');


