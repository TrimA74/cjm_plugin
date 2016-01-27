<?php
$path = dirname(__FILE__);
require(str_replace("wp-content/plugins/cjm", "wp-load.php", $path));
if(isset($_POST["pl_adulte"]) && isset($_POST["pl_enfant"]) && isset($_POST["post_id"]) )
{
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
									 //maj nbplce remplacer par un trigger sur cjm_reservation
                    /*if($bool == 0){
	                    $nb_place_maj = $nb_place_total - esc_attr($_POST["pl_adulte"]) - esc_attr($_POST["pl_enfant"]);
					    update_post_meta($post_id,'_nb_place',$nb_place_maj);
						}*/
					$tab[0] = $query;
					// $affiche = number_format($prix_total, 2);
					// $tab[1] = $affiche;
					$values= array($_POST["pl_adulte"],$_POST["pl_enfant"]);
					$tab[3] = $values;

				}


	}
	echo json_encode($tab);
}

if(isset($_POST["post_id_annul"]) && !empty($_POST["post_id_annul"]) && isset($_POST["user_id_annul"])&& !empty($_POST["user_id_annul"]))
{	global $wpdb;
	$post_id = $_POST["post_id_annul"];
	$current_user = wp_get_current_user();
	$id_user = get_current_user_id();
	$user = new WP_User( $id_user );
	$nbplace = get_post_meta($post_id,'_nb_place',true);
	$query2 = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}reservation WHERE id_evenement = $post_id AND id_participant = $id_user");
	// plus nécessaire, fait part un trigger
	// $nb_place_maj = $nbplace + $query2[0]->nbplace + $query2[0]->nbplace_enf;
	// update_post_meta($post_id,'_nb_place',$nb_place_maj);
	$query = $wpdb->delete( cjm_reservation, array( 'id_participant' => $_POST["user_id_annul"] , 'id_evenement' => $_POST["post_id_annul"]));
	$msgd = 1;
	echo json_encode($msgd);
}

if(isset($_POST["pl_adulte_upd"])  && isset($_POST["pl_enfant_upd"]))
{


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
	echo json_encode($upd);
}

if(isset($_GET["delete_resa"]) && isset($_GET["id_resa"]))
{
	$nbplace = $wpdb->get_results("select nbplace,nbplace_enf from cjm_reservation where id_resa=".$_GET["id_resa"].";");
	var_dump($nbplace);
}
