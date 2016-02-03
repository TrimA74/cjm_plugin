<?php
global $wpdb;
$res = $wpdb->get_results("select * from cjm_mail where id=1");
// $query=$wpdb->get_results("select mail as 'user_login',id_resa as 'ID',id_evenement from cjm_reservation_ext where paiement=1");
$query1=$wpdb->get_results("select u.user_login,u.ID,r.id_evenement from cjm_users u
	join cjm_reservation r on r.id_participant=u.ID
	where r.paiement=1 and r.mail_confirm=0");
// foreach ($query as $key => $value) {
// 	$value->ID=$value->ID."ext";
// 	$value->nom_voyage=get_post_meta($value->id_evenement,"_nom_voyage",true);
// }
foreach ($query1 as $key => $value) {
	$value->nom_voyage=get_post_meta($value->id_evenement,"_nom_voyage",true);
}
// $users = array_merge($query,$query1);
$users = $query1;
