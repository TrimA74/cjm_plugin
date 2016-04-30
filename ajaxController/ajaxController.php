<?php
include_once("mailSender.php");
include_once("CRUD_resa_admin.php");
include_once("CRUD_resa_front.php");
add_action('wp_ajax_get_mails_to_confirm','get_mails_to_confirm');
function get_mails_to_confirm () {
  global $wpdb;
  $res = $wpdb->get_results("select * from cjm_mail where id=1");
  $users=$wpdb->get_results("select u.user_login,u.ID,r.id_evenement from cjm_users u
    join cjm_reservation r on r.id_participant=u.ID
    where r.paiement=1 and r.mail_confirm=0");
  foreach ($users as $key => $value) {
    $value->nom_voyage=get_post_meta($value->id_evenement,"_nom_voyage",true);
    }
  echo json_encode($users);
  wp_die();
}

add_action( 'wp_ajax_get_logged_user', 'get_logged_user_callback');
function get_logged_user_callback() {
  global $wpdb;
  $user = wp_get_current_user();
  echo json_encode($user);
  wp_die();
}
add_action('wp_ajax_get_evenements','get_evenements_callback');
function get_evenements_callback () {
  $error = array (
  "value" => 0,
  "message" => "");
if(isset($_POST["get_evenements"]) && isset($_POST["type_evenement"]))
{
  global $wpdb;
  $res = $wpdb->get_results("select ID from cjm_posts where post_type=\"reservation\" and post_status not in (\"auto-draft\",\"trash\")");
  if(is_null($res) || empty($res)) {
    $error["value"] = 1;$error["message"] = "Les événements n'ont pas été récupérer (erreur dans la requête sql)";
  }
  foreach ($res as $key => $value) {
    $nom = get_post_meta($value->ID,"_nom_voyage",true);
    $value->post_title=$nom;
    $place_total =get_post_meta($value->ID,"_nb_place_total",true);
    $value->place_total = $place_total;
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
    $tarifadh = get_post_meta($value->ID,"_tarif_adherent",true);
    $value->tarifadh=$tarifadh;
    $cat = get_the_category($value->ID);
    $value->category=$cat[0]->category_nicename;
  }
  echo json_encode(array(
    "data" => $res,
    "error" => $error
  ));
}
wp_die();
}

add_action('wp_ajax_get_resa_by_voyage','get_resa_by_voyage_callback');
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
add_action('wp_ajax_get_user_by_id' , 'get_user_by_id_callback');
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
add_action('wp_ajax_change_paiement_resa','change_paiement_resa_callback');
function change_paiement_resa_callback () {
  global $wpdb;

  if(isset($_POST["paiement"])
  && !empty($_POST["paiement"])
  && isset($_POST["id_resa"])
   && !empty($_POST["id_resa"])
   && isset($_POST["table"])) {
  if($_POST["paiement"]=='true'){
    $val=1;
  }
  else{
    $val=0;
  }
  $table="cjm_reservation";
  if($_POST["table"]=="ext")
  {
    $table=$table."_ext";
  }

  $return = $wpdb->update(
    $table,
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
/*
**** MODIF liste_attente
* MAJ nbplace OK
* Tags : @reserve @liste_attente @attente
*/
add_action('wp_ajax_change_att_resa','change_att_resa');
function change_att_resa () {
  global $wpdb;
  $error ="";
  if(isset($_POST["att"]) && !empty($_POST["att"]) && isset($_POST["table"])
  && isset($_POST["id_resa"]) && !empty($_POST["id_resa"])){
    if($_POST["att"]=='true'){
      $val=0;
    }
    else{
      $val=1;
    }
    $table="cjm_reservation";
    if($_POST["table"]=="ext")
    {
      $table=$table."_ext";
      $_POST["id_resa"] = str_replace('ext','',$_POST["id_resa"]);
    }
    $select = $wpdb->get_results("SELECT id_evenement,nbplace,nbplace_enf FROM ".$table." WHERE id_resa=".$_POST["id_resa"]);
    $resquery = $wpdb->update(
    $table,
    array(
      'liste_attente' => $val,
    ),
    array( 'id_resa' => $_POST["id_resa"])
    );
    if($resquery==1) {
      $curr_nbplace = intval(get_post_meta(intval($select[0]->id_evenement),"_nb_place",true));
      if($val==0)
      {
        $nbplace_maj = $curr_nbplace - intval($select[0]->nbplace) - intval($select[0]->nbplace_enf);
      }
      else {
        $nbplace_maj = $curr_nbplace + intval($select[0]->nbplace) + intval($select[0]->nbplace_enf);
      }
      $resquery = update_post_meta(intval($select[0]->id_evenement),"_nb_place",$nbplace_maj);
      if($resquery==false) {$error .="pas de maj nbplace";}
    }
    else {
      $error .= "pas de maj liste_attente";
    }
    echo json_encode(array($error,$curr_nbplace,$select,$table,$_POST["id_resa"]));
  }
  wp_die();
}

add_action('wp_ajax_sup_voyage','sup_voyage_callback');
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
add_action('wp_ajax_sup_escapade','sup_escapade_callback');
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
