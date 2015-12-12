<?php

function get_logged_user_callback() {
  global $wpdb;
  if(isset($_POST["user"]))
  {
    if($_POST["user"]=="true")
    {
      $user = wp_get_current_user();
      echo json_encode($user);
    }
  }
  if(isset($_POST["whatever"]))
  {
     $whatever = intval( $_POST['whatever'] );
      $whatever = 10;
        echo json_encode($whatever);
  }
  
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
  $res = $wpdb->get_results("select r.date_resa,r.id_evenement,r.id_participant,r.id_resa,r.nbplace,r.nbplace_enf,r.paiement from cjm_reservation r
    join cjm_users u on u.ID=r.id_participant
    
    ;");
  foreach ($res as $key => $value) {
    $prenom = get_user_meta($value->id_participant,"first_name", true); 
    $nom = get_user_meta($value->id_participant,"last_name", true); 
    $value->user_name=$nom." ".$prenom;
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

  if(isset($_POST["paiement"]) && !empty($_POST["paiement"])
  && isset($_POST["id_resa"]) && !empty($_POST["id_resa"]))
  {
  if($_POST["paiement"]=='true')
  {
    $val=1;
  }
  else
  {
    $val=0;
  }
  $return = $wpdb->update(
  'cjm_reservation', 
  array( 
    'paiement' => $val, 
  ), 
  array( 'id_resa' => $_POST["id_resa"])
  );
  }
  wp_die();
}
add_action('wp_ajax_change_paiement_resa','change_paiement_resa_callback');

function sup_resa_callback () {
  global $wpdb;
  if(isset($_POST["delete_resa"]))
  {
  if(isset($_POST["id_resa"])
    && !empty($_POST["id_resa"]))
  {
    $query = $wpdb->prepare("delete from cjm_reservation where id_resa=%d",$_POST["id_resa"]);
    $wpdb->query($query);
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