<?php
class MailSender {
  function __construct() {
      add_action( 'wp_ajax_send_email_confirm', array($this,'send_email_confirm') );
    }
    public static function send_email_ins_modif ($dest,$isModif,$post_id) {
      $id_mail  = 2;
      if($isModif)
      {
        $id_mail  = 3;
      }
      global $wpdb;
      $res = $wpdb->get_results("select * from cjm_mail where id=$id_mail;");
      $title = stripslashes($res[0]->title);
      $content = stripslashes($res[0]->content);
        $user = get_user_by( "login", $dest);
        $user_id = $user->ID;
        $user_infos = $wpdb->get_results("select nbplace,nbplace_enf,prix_total from cjm_reservation where id_participant=$user_id and id_evenement=$post_id");
        $tarif_adulte = get_post_meta($post_id,"_tarif_adulte",true);
        $tarif_enf = get_post_meta($post_id,"_tarif_enfant",true);
        $tarif_adh = get_post_meta($post_id,"_tarif_adherent",true);
        $event_name = get_post_meta($post_id,"_nom_voyage",true);
        $title = str_replace("%evenement%",$event_name,$title);
        $content = str_replace("%prix_total%",$user_infos[0]->prix_total,$content);
        $content = str_replace("%USERNAME%",$user->display_name,$content);
        $content = str_replace("%evenement%",$event_name,$content);
        $content = str_replace("%nbplace_enf%",$user_infos[0]->nbplace_enf,$content);
        $content = str_replace("%nbplace%",$user_infos[0]->nbplace,$content);
        $content = str_replace("%prix_place%",$tarif_adulte,$content);
        $content = str_replace("%prix_place_enf%",$tarif_enf,$content);
        $content = str_replace("%prix_place_adh%",$tarif_adh,$content);
        $content = str_replace("%lien%",get_site_url()."/?p=".$post_id,$content);
        $isSent = wp_mail($dest,$title,$content);
        return $isSent;
    }
    public function send_email_confirm() {
        global $wpdb;
        $datas = array($_POST["users"],$_POST["id"]);
        $res = $wpdb->get_results("select * from cjm_mail where id=".$_POST["id"].";");
        $title = stripslashes($res[0]->title);
        $content = stripslashes($res[0]->content);
        foreach ($_POST["users"] as $key => $value) {
          $infos = explode("&",$value);
          $user = get_user_by( "login", $infos[0]);
          $user_id = $user->ID;
          $user_infos = $wpdb->get_results("select nbplace,nbplace_enf,prix_total from cjm_reservation where id_participant=$user_id and id_evenement=$infos[1]");
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
        // echo json_encode(array($last_query,$datas));
        // echo json_encode("test");
    }
}

new MailSender();
