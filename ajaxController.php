<?php
$path = dirname(__FILE__); 
global $wpdb;
require(str_replace("wp-content/plugins/cjm", "wp-load.php", $path));

if(isset($_POST["pl_adulte"]) && isset($_POST["pl_enfant"]) && isset($_POST["post_id"]) )
{	
	if($_POST["pl_adulte"] == null && $_POST["pl_enfant"] == null)
		$msg = 2;
	else{
				$post_id = get_the_ID();
				$current_user = wp_get_current_user();
				$id_user = $current_user->ID;
				$nbplace_adulte = get_post_custom_values('_nb_place', $post_id);
				$nbplace_enfant = get_post_custom_values('_nb_place_enf', $post_id);
				$query =$wpdb->insert( cjm_reservation,
								array( 
				                'id_participant' => $id_user,
								'id_evenement' => $_POST["post_id"], 
								'nbplace' => esc_attr($_POST["pl_adulte"]),
								'nbplace_enf' => esc_attr($_POST["pl_enfant"]),
								'paiement' => 0
								 ));
				$nb_place = $nbplace_adulte-esc_attr($_POST["post_id"]);
				update_post_meta($post_id, '_nb_place', $nb_place);
				update_post_meta($post_id, '_nb_place_enf', $nbplace_enfant-esc_attr($_POST["pl_enfant"]));

		$msg = 1;
	   	echo json_encode($msg);
	}
}

if(isset($_POST["post_id"]) && !empty($_POST["post_id"]) && isset($_POST["user_id"])&& !empty($_POST["user_id"]))
{	
	
	$query = $wpdb->delete( cjm_reservation, array( 'id_participant' => $_POST["user_id"] , 'id_evenement' => $_POST["post_id"]));
	$msgd = 1;
	echo json_encode($msgd);
}

	