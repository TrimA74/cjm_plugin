<?php
function getPrixTotal ($post_id,$nbplace_adl,$nbplace_enf,$user_rank) {
  if($user_rank =='adherent_user'){
     $prix_total = $nbplace_adl * intval(get_post_meta($post_id,'_tarif_adherent', true)) + $nbplace_enf * intval(get_post_meta($post_id,'_tarif_enfant',true));
  }else{
    $prix_total = $nbplace_adl * intval(get_post_meta($post_id,'_tarif_adulte', true)) + $nbplace_enf * intval(get_post_meta($post_id,'_tarif_enfant',true));
  }
  return $prix_total;
}

/*
**** @my_admin_notice
**** Petite Fonction bien cool pour afficher des messages (succès,erreur,informatif) en PHP !
*/
function my_admin_notice($class,$message) {
    echo"<div class=\"$class is-dismissible notice\"> <p>$message</p>
    <button type='button' class='notice-dismiss'>
    <span class='screen-reader-text'>Ne pas tenir compte de ce message </span>
    </button>
    </div>";
}
