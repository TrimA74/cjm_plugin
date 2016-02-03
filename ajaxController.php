<?php
add_action( 'wp_ajax_send_email_confirm', 'send_email_confirm' );
function send_email_confirm() {
    global $wpdb;
    $res = $wpdb->get_results("select * from cjm_mail where id=".$_POST["id"].";");
    $title = stripslashes($res[0]->title);
    $content = stripslashes($res[0]->content);
    foreach ($_POST["users"] as $key => $value) {
      $infos = explode("&",$value);
      $user = get_user_by( "login", $infos[0]);
      $user_id = $user->ID;
      $user_infos = $wpdb->get_results("select nbplace,nbplace_enf,prix_total from cjm_reservation where id_participant=".$user_id);
      $tarif_adulte = get_post_meta($infos[1],"_tarif_adulte",true);
      $tarif_enf = get_post_meta($infos[1],"_tarif_enfant",true);
      $tarif_adh = get_post_meta($infos[1],"_tarif_adherent",true);
      $event_name = get_post_meta($infos[1],"_nom_voyage",true);
      $title = str_replace("%prix_total%",$user_infos[0]->prix_total,$title);
      $content = str_replace("%prix_total%",$user_infos[0]->prix_total,$content);
      $content = str_replace("%USERNAME%",$user->display_name,$content);
      $content = str_replace("%evenement%",$event_name,$content);
      $content = str_replace("%nbplace_enf%",$user_infos[0]->nbplace_enf,$content);
      $content = str_replace("%nbplace%",$user_infos[0]->nbplace,$content);
      $content = str_replace("%prix_place%",$tarif_adulte,$content);
      $content = str_replace("%prix_place_enf%",$tarif_enf,$content);
      $content = str_replace("%prix_place_adh%",$tarif_adh,$content);
      $content = str_replace("%lien%",get_site_url()."/?p=".$infos[1],$content);
      $isSent = wp_mail($infos[0],$title,$content);
    }
    if($isSent)
    {
      $last_query = $wpdb->update('cjm_reservation',
      array("mail_confirm"=>1),
      array("id_evenement"=>$infos[1],
      "id_participant"=>$user_id),
      array("%d"),
      array("%d","%d"));
    }
    echo json_encode($last_query);
}
add_action( 'wp_ajax_nopriv_get_logged_user', 'get_logged_user_callback');
function get_logged_user_callback() {
  global $wpdb;
  $user = wp_get_current_user();
  echo json_encode($user);
  wp_die();
}
add_action('wp_ajax_get_evenements','get_evenements_callback');
function get_evenements_callback () {
if(isset($_POST["get_evenements"]) && isset($_POST["type_evenement"]))
{
  global $wpdb;
  $res = $wpdb->get_results("select ID from cjm_posts where post_type=\"reservation\" and post_status not in (\"auto-draft\",\"trash\")");
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
  echo json_encode($res);
}
wp_die();
}
add_action('wp_ajax_get_resas','get_resas_callback');
function get_resas_callback () {
  global $wpdb;
  if(isset($_POST["get_resas"]))
  {
    $res = $wpdb->get_results("select r.prix_total,r.date_resa,r.id_evenement,r.id_participant,r.id_resa,r.nbplace,r.nbplace_enf,u.user_login,r.paiement,u.display_name,r.liste_attente from cjm_reservation r
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
          if($role=="adherent")
          {
            $value->role="Adhérent";
          }
          else if($role=="noadherent")
          {
            $value->role="Abonné  ";
          }
          else if($role="administrator")
          {
            $value->role="Administrateur";
          }
    		}

  echo json_encode(
  array(
    "draw" => 1,
    "recordsTotal"=> 57,
    "recordsFiltered"=> 57,
    "data" => array_merge($res,$res_ext)
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
add_action('wp_ajax_change_att_resa','change_att_resa_callback');
function change_att_resa_callback () {
  global $wpdb;

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
    }
    $return = $wpdb->update(
    $table,
    array(
      'liste_attente' => $val,
    ),
    array( 'id_resa' => $_POST["id_resa"])
    );
  }
  wp_die();
}
add_action('wp_ajax_sup_resa','sup_resa_callback');
function sup_resa_callback () {
  global $wpdb;
  $values = array($_POST["id_resa"],$_POST["table"]);
  if(isset($_POST["delete_resa"])) {
    if(isset($_POST["id_resa"]) && isset($_POST["table"])
    && !empty($_POST["id_resa"])) {
      $table="cjm_reservation";
      if(!empty($_POST["table"]))
      {
        $table="cjm_reservation_".$_POST["table"];
      }
      $query = $wpdb->delete($table,array('id_resa'=>intval($_POST["id_resa"])),array('%d'));
      // $query = $wpdb->prepare("delete from %s where id_resa=%d",$table,$_POST["id_resa"]);
      // $res = $wpdb->query($query);
      echo json_encode(array($query,$values));
    }
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
add_action('wp_ajax_modif_resa','modif_resa_callback');
function modif_resa_callback () {
  global $wpdb;
  $error="";
  $values= array($_POST["id"],$_POST["nbplace"],$_POST["nbplace_enf"],$_POST["tel"],$_POST["id_user"],$_POST["table"]);
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
      $resquery = $wpdb->update('cjm_reservation_ext',
      array(
        'tel' => $_POST["tel"],
        'nbplace' => intval($_POST["nbplace"]),
        'nbplace_enf'=> intval($_POST["nbplace_enf"])
      ),
      array('id_resa' => intval($_POST["id"])),
      array('%s','%d','%d'),
      array('%d'));
      if(!$resquery){$error.="Modif sur cjm_reservation_ext a echoue";}
    }
    else {
      $query = $wpdb->prepare("update cjm_reservation set nbplace=%d , nbplace_enf=%d  where id_resa=%d",$_POST["nbplace"],$_POST["nbplace_enf"],$_POST["id"]);
      $resquery = $wpdb->query($query);
      if(!$resquery){
          $error.="Modif sur cjm_reservation a echoue\n";
        }
      $query = $wpdb->prepare("update cjm_usermeta set meta_value=%s where meta_key='tel' and user_id=%d",$_POST["tel"],$_POST["id_user"]);
      $resquery = $wpdb->query($query);
      if(!$resquery){
          $error.="Modif sur cjm_usermeta a echoue";
        }
    }
    $res=array("error"=> $error,
      "resQuery" => $resquery,
      "valeurs"=> $values);
    echo json_encode($res);
  }
  else {
    echo json_encode($values);
  }
  wp_die();
}
add_action('wp_ajax_send_mail_confirm','send_mail_confirm');
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
add_action('wp_ajax_add_resa','add_resa');
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
      'role' => $_POST["role"],
      'id_evenement' => intval($_POST["id_evenement"]))
    );
    $data = array(
      'nom' => $_POST["nom"],
      'tel'=> $_POST["tel"],
      'nbplace'=>intval($_POST["nbplace"]) ,
      'nbplace_enf'=> intval($_POST["nbplace_enf"]),
      'paiement' => $_POST["paiement"],
      'attente' => $_POST["liste_attente"],
      'prix_total' => $_POST["role"],
      'id_evenement' => intval($_POST["id_evenement"])
    );
    echo json_encode(array($data,$res));

  }
  wp_die();
}
/* CLIENT GESTION RESERVATIONS */
add_action('wp_ajax_ins_resa','ins_resa');
function ins_resa () {
  global $wpdb;
  if(isset($_POST["pl_adulte"]) && isset($_POST["pl_enfant"]) && isset($_POST["post_id"]) )
  {
     if(!is_numeric($_POST["post_id"])){
         $tab = z;
    }else{
      if($_POST["pl_adulte"] == 0 && $_POST["pl_enfant"] == 0)
        $tab[0] = 2;
      else{

            $post_id = $_POST["post_id"];
            $current_user = wp_get_current_user();
            $id_user = $current_user->ID;
            $user = new WP_User( $id_user );
            $user_rank =  $user->roles[0];

            $nb_place_total = get_post_meta($post_id,'_nb_place',true);
            if($user_rank =='adherent_user'){
                $prix_total = $_POST["pl_adulte"] * get_post_meta($post_id,'_tarif_adherent', true) + $_POST["pl_enfant"] * get_post_meta($post_id,'_tarif_enfant',true);
            }else{
              $prix_total = $_POST["pl_adulte"] * get_post_meta($post_id,'_tarif_adulte', true) + $_POST["pl_enfant"] * get_post_meta($post_id,'_tarif_enfant',true);
            }

            if($nb_place_total < ($_POST["pl_adulte"] + $_POST["pl_enfant"]) && get_post_meta($post_id,'_etat_resa',true) != 'file_attente'){
              $tab[0] =3;
            }else{

              if(get_post_meta($post_id,'_etat_resa',true) == 'file_attente'){
                $bool = 1;
              }else{
                $bool = 0;
              }

             $query =$wpdb->insert( cjm_reservation,
                      array(
                              'id_participant' => $id_user,
                      'id_evenement' => $_POST["post_id"],
                      'nbplace' => esc_attr($_POST["pl_adulte"]),
                      'nbplace_enf' => esc_attr($_POST["pl_enfant"]),
                      'paiement' => 0,
                      'liste_attente' => $bool
                       ));
              $tab[0] = 1;
            }
      }
    }
    echo json_encode($tab);
  }
   wp_die();
}
add_action('wp_ajax_upd_resa','upd_resa');
function upd_resa () {
  global $wpdb;
  if(isset($_POST["pl_adulte_upd"])  && isset($_POST["pl_enfant_upd"]))
  {
    if(!is_numeric($_POST["post_id_upd"])){
         $upd = z;
    }else{

        if($_POST["pl_adulte_upd"] == 0 && $_POST["pl_enfant_upd"] == 0){
          $upd = 2;
        }else{


          $post_id = $_POST["post_id_upd"];
          $current_user = wp_get_current_user();
          $id_user = $current_user->ID;
          $user_rank =  $user->roles[0];
          $nb_place_total = get_post_meta($post_id,'_nb_place',true);
          $query2 = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}reservation WHERE id_evenement = $post_id AND id_participant = $id_user");
          $ancien_nb_place = $query2[0]->nbplace + $query2[0]->nbplace_enf;
          if($user_rank =='adherent_user'){
             $prix_total = $_POST["pl_adulte_upd"] * get_post_meta($post_id,'_tarif_adherent', true) + $_POST["pl_enfant_upd"] * get_post_meta($post_id,'_tarif_enfant',true);
          }else{
            $prix_total = $_POST["pl_adulte_upd"] * get_post_meta($post_id,'_tarif_adulte', true) + $_POST["pl_enfant_upd"] * get_post_meta($post_id,'_tarif_enfant',true);
          }
          if($nb_place_total+$ancien_nb_place < ($_POST["pl_adulte_upd"] + $_POST["pl_enfant_upd"])){
                $upd = 3;
          }else{
            if(get_post_meta($post_id,'_etat_resa',true) == 'file_attente'){
                      $bool = 1;
            }else{
                      $bool = 0;
            }
            $query = $wpdb->update( cjm_reservation,
                    array(
                            'nbplace' => esc_attr($_POST["pl_adulte_upd"]),
                            'nbplace_enf' => esc_attr($_POST["pl_enfant_upd"]),
                            'paiement' => 0,
                            'prix_total' => $prix_total,
                            'liste_attente' => $bool
                    )
                    ,array( 'id_participant' => $id_user , 'id_evenement' => $_POST["post_id_upd"]));
              $nb_place_maj = $nb_place_total+$ancien_nb_place - esc_attr($_POST["pl_adulte_upd"]) - esc_attr($_POST["pl_enfant_upd"]);
            update_post_meta($post_id,'_nb_place',$nb_place_maj);
            $upd = 1;

          }
        }
    }
    echo json_encode($upd);
  }
  wp_die();
}
add_action('wp_ajax_del_resa','del_resa');
function del_resa () {
  if(isset($_POST["post_id_annul"]) && !empty($_POST["post_id_annul"]))
    {
      if(!is_numeric($_POST["post_id_annul"])){
         $msgd = z;
      }else{
        global $wpdb;
        $post_id = $_POST["post_id_annul"];
        $current_user = wp_get_current_user();
        $id_user = get_current_user_id();
        $user = new WP_User( $id_user );
        $nbplace = get_post_meta($post_id,'_nb_place',true);
        $query = $wpdb->delete( cjm_reservation, array( 'id_participant' => $id_user , 'id_evenement' =>  $post_id));
        $msgd = 1;
      }

      echo json_encode($msgd);
    }
 wp_die();
}
