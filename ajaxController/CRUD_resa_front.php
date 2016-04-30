<?php
/*
********************** CRUD Réservation FRONT **********************
*/
class CRUD_resa_front {
  function __construct() {
      add_action('wp_ajax_ins_resa',array($this,'ins_resa'));
      add_action('wp_ajax_upd_resa',array($this,'upd_resa'));
      add_action('wp_ajax_del_resa',array($this,'del_resa'));
    }
  /********************** CREATE **********************
  ***** Réservation d'un événement par une personne
  */
  public function ins_resa () {
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
              // $user = new WP_User( $id_user );
              $user_rank =  $current_user->roles[0];
              $nb_place_dispo = get_post_meta($post_id,'_nb_place',true);
              $prix_total = getPrixTotal($post_id,intval($_POST["pl_adulte"]),intval($_POST["pl_enfant"]),$user_rank);
              if($nb_place_dispo < ($_POST["pl_adulte"] + $_POST["pl_enfant"]) && get_post_meta($post_id,'_etat_resa',true) != 'file_attente'){
                $tab[0] =3;
              }else{

                if(get_post_meta($post_id,'_etat_resa',true) == 'file_attente'){
                  $bool = 1;
                }else{
                  $bool = 0;
                  $nbplace_maj = $nb_place_dispo - intval($_POST["pl_adulte"]) - intval($_POST["pl_enfant"]);
                  $res = update_post_meta(intval($post_id),"_nb_place",$nbplace_maj);
                }

               $query =$wpdb->insert( cjm_reservation,
                        array(
                        'id_participant' => $id_user,
                        'id_evenement' => $_POST["post_id"],
                        'nbplace' => esc_attr($_POST["pl_adulte"]),
                        'nbplace_enf' => esc_attr($_POST["pl_enfant"]),
                        'paiement' => 0,
                        'liste_attente' => $bool,
                        'prix_total'=>$prix_total,
                        'date_resa'=>date("d-m-Y H:i:s")
                         ));
                $tab[0] = 1;
                if($query==1) {
                  $isSent = MailSender::send_email_ins_modif($current_user->user_login,false,$_POST["post_id"]);
                }
              }
        }
      }
      echo json_encode($tab);
    }
     wp_die();
  }
  /********************** UPDATE **********************
  ***** Modification d'une réservation
  */
  public function upd_resa () {
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
            $nb_place_dispo = get_post_meta($post_id,'_nb_place',true);
            $query2 = $wpdb->get_results("SELECT * FROM cjm_reservation WHERE id_evenement = $post_id AND id_participant = $id_user");
            $ancien_nb_place = $query2[0]->nbplace + $query2[0]->nbplace_enf;
            $prix_total = getPrixTotal($post_id,intval($_POST["pl_adulte_upd"]),intval($_POST["pl_enfant_upd"]),$user_rank);
            if($nb_place_dispo+$ancien_nb_place < ($_POST["pl_adulte_upd"] + $_POST["pl_enfant_upd"])){
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
                              'liste_attente' => $bool,
                              'date_resa'=>date("d-m-Y H:i:s")
                      )
                      ,array( 'id_participant' => $id_user , 'id_evenement' => $_POST["post_id_upd"]));
              if($query==1) {
                $nb_place_maj = $nb_place_dispo+$ancien_nb_place - intval(esc_attr($_POST["pl_adulte_upd"])) - intval(esc_attr($_POST["pl_enfant_upd"]));
                $query = update_post_meta($post_id,'_nb_place',$nb_place_maj);
              }
              if($query==1) {
                $isSent = MailSender::send_email_ins_modif($current_user->user_login,true,$post_id);
              }
              $upd = 1;
            }
          }
      }
      echo json_encode($upd);
    }
    wp_die();
  }
  /********************** DELETE **********************
  ***** Annulation d'une réservation
  */
  public function del_resa () {
    if(isset($_POST["post_id_annul"]) && !empty($_POST["post_id_annul"]))
      {
        if(!is_numeric($_POST["post_id_annul"])){
           $msgd = z;
        }else{
          global $wpdb;
          $post_id = $_POST["post_id_annul"];
          $id_user = get_current_user_id();
          $nbplace = get_post_meta($post_id,'_nb_place',true);
          $select = $wpdb->get_results("select nbplace,nbplace_enf from cjm_reservation where id_participant=".$id_user." and id_evenement=".$post_id);
          $query = $wpdb->delete( 'cjm_reservation', array( 'id_participant' => $id_user , 'id_evenement' =>  $post_id));
          if($query==1)
          {
            $maj_nb_place = intval($nbplace)+intval($select[0]->nbplace)+intval($select[0]->nbplace_enf);
            $query = update_post_meta($post_id,"_nb_place",$maj_nb_place);
          }
          $msgd = 1;
        }
        echo json_encode($msgd);
      }
   wp_die();
  }
}
new CRUD_resa_front();
