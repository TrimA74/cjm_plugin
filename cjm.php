<?php
/*
Plugin Name: Réservation
Description: Plugin de réservation de voyages et d'escapades
Author: SEBIRE Florian & SAUSSIER Julien
Version: 1.0
*/
add_action('admin_menu', 'plugin_setup_menu');
add_action( 'admin_enqueue_scripts', 'enqueuescript_back' );
add_action( 'wp_enqueue_scripts', 'enqueuescript_front' );
function enqueuescript_back(){
				/*
				**** Scripts chargés partout sur la partie admin
				*/
				wp_enqueue_script( 'cjm_library',plugins_url('cjm/js/cjm_library.js'),'1.0');
				wp_enqueue_script( 'cjm2',plugins_url('cjm/js/customAdmin.js'),'1.0');
				/*
				* Scripts chargées sur chaque page du plugin en back-office sauf la page de stats
				*/
        if($_GET["page"]!="reservation-stats")
        {
          wp_enqueue_style('style',plugins_url('cjm/css/style.css'),'2.0');
        }
				/*
				Scipts qui ne sont chargés que sur Réservation
				*/
				if(isset($_GET["post_type"]) && $_GET["post_type"]=="reservation"){
					/*
					**** Scipts qui ne sont chargés que sur Réservation=>Gestion des participants
					**** Plugin JavaScript pour exportation en excel et pdf
					*/
					if($_GET["page"]=="gestion-participants"){
		        wp_enqueue_script( 'cjm',plugins_url('cjm/js/cjm_main.js'),'1.0');
		        wp_enqueue_script( 'cjm_export_excel',plugins_url('cjm/js/jquery.table2excel.js'),'1.0');
		        wp_enqueue_script( 'cjm_export_pdf1',plugins_url('cjm/js/html2pdf/tableExport.js'),'1.0');
		        wp_enqueue_script( 'cjm_export_pdf2',plugins_url('cjm/js/html2pdf/jquery.base64.js'),'1.0');
		        wp_enqueue_script( 'cjm_export_pdf3',plugins_url('cjm/js/html2pdf/sprintf.js'),'1.0');
		        wp_enqueue_script( 'cjm_export_pdf4',plugins_url('cjm/js/html2pdf/jspdf.js'),'1.0');
		        wp_enqueue_script( 'cjm_export_pdf5',plugins_url('cjm/js/html2pdf/base64.js'),'1.0');
					}
					if($_GET["page"]=="reservation-stats")
					{
						wp_enqueue_style('style','https://cdn.datatables.net/1.10.10/css/jquery.dataTables.min.css','2.0');
						wp_enqueue_script( 'datables_js','https://cdn.datatables.net/1.10.10/js/jquery.dataTables.min.js','1.0');
						wp_enqueue_script( 'cjm_stats',plugins_url('cjm/js/stats/main.js'),'1.0');
					}
	        if($_GET["page"]=="templates-mails")
	        {
	          wp_enqueue_script( 'cjm_mails',plugins_url('cjm/js/mails/main.js'),'1.0');
	          wp_enqueue_style('mail',plugins_url('cjm/css/mail.css'),'2.0');
	        }
					/*
					**** Création d'un objet JavaScript global en partie back-office
					**** @ajax_object --> nom de la variable global
					**** Exemple en JS : console.log("ajax_object.ajax_url"); Affiche l'url pour faire les appels AJAX sur WordPress
					*/
	       
				}
         wp_localize_script(
          'cjm_library',
          'ajax_object',
          array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
             'user' => "true" ));

}
include_once("functions.php");
/*
**** Scripts JS & feuille CSS chargées en front-office
*/
//scripts js de gestions des forms crud resa
function enqueuescript_front(){
        wp_enqueue_script( 'cjm_gestion_form_client',plugins_url('cjm/js/cjm_gestion_form_client.js'),'1.0');
        wp_enqueue_style('style',plugins_url('cjm/css/style_client.css'),'2.0');
}
/*
**** Modification du type des mails pour le passer en HTML
*/
add_filter( 'wp_mail_content_type', 'set_content_type' );
function set_content_type( $content_type ) {return 'text/html';}
function my_retrieve_password_subject_filter($old_subject)

{



    $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

    $subject = sprintf( __('[%s] Password Reset'), $blogname );



    return $subject;

}



function my_retrieve_password_message_filter($old_message, $key)

{



    if ( strpos( $_POST['user_login'], '@' ) )

    {

        $user_data = get_user_by( 'email', trim( $_POST['user_login'] ) );



    }

    else

    {

        $login = trim($_POST['user_login']);

        $user_data = get_user_by('login', $login);

    }



    $user_login = $user_data->user_login;





    $custom = get_option('forgot_mail_cwd');

    $reset_url = network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login');



    $message .= str_replace("%reseturl%",$reset_url,(str_replace("%username%",$user_login,$custom))); //. "\r\n";





    return $message;

}



// To get these filters up and running:

add_filter ( 'retrieve_password_title', 'my_retrieve_password_subject_filter', 10, 1 );

add_filter ( 'retrieve_password_message', 'my_retrieve_password_message_filter', 10, 2 );

/*
**** Function qui créer les submenus 'Gestion des participants','Statistique','Mails' sur le menu 'Evénements'
*/
function plugin_setup_menu(){
        add_submenu_page( 'edit.php?post_type=reservation', 'Gestion des participants', 'Gestion des participants', 'manage_options', 'gestion-participants', 'display_cjm_content' );
        add_submenu_page( 'edit.php?post_type=reservation', 'Statistique', 'Statistiques', 'manage_options', 'reservation-stats', 'display_cjm_stats' );
        add_submenu_page( 'edit.php?post_type=reservation', 'Mails', 'Mails', 'manage_options', 'templates-mails', 'display_cjm_mails' );
	}
/*
**** @include Controller AJAX
*/
include_once("ajaxController/ajaxController.php");
/*
**** Controller qui mets à jour les templates de mails en BDD
*/
add_action( 'admin_post_save_email', 'prefix_admin_save_email' );
function prefix_admin_save_email() {
    global $wpdb;
    $res = $wpdb->update('cjm_mail',array(
      "title"=>stripslashes($_POST["post_title"]),
      "content"=> stripslashes($_POST["mail_content".$_POST["id"]])
      ),array("id"=>$_POST["id"]));
    header('Location: edit.php?post_type=reservation&page=templates-mails');
}
include_once("views.php");
/*
**** Définition du nouveau type de post 'réservation'
*/
add_action( 'init', 'register_cpt_resa' );
function register_cpt_resa() {

    $labels = array(
        'name' => _x( 'Réservation', 'reservation' ),
        'singular_name' => _x( 'Evénements', 'reservation' ),
        'add_new' => _x( 'Ajouter', 'reservation' ),
        'add_new_item' => _x( 'Ajouter un événement', 'reservation' ),
        'edit_item' => _x( 'Editer un événement', 'reservation' ),
        'new_item' => _x( 'Nouvelle événement', 'reservation' ),
        'view_item' => _x( 'Voir l\'événement', 'reservation' ),
        'search_items' => _x( 'Rechercher un événement', 'reservation' ),
        'not_found' => _x( 'Aucun événement trouvé', 'reservation' ),
        'not_found_in_trash' => _x( 'Aucun événement dans la corbeille', 'reservation' ),
        'parent_item_colon' => _x( 'Evenement parente :', 'reservation' ),
        'menu_name' => _x( 'Evénements', 'reservation' ),
    );
    $args = array(
        'labels' => $labels,
        'hierarchical' => false,
        'description' => 'Les Evénements',
        'supports' => array( 'title', 'editor', 'thumbnail', 'custom-fields', 'revisions' ),
        'taxonomies' => array( 'category'),
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 5,
        'menu_icon'=> 'dashicons-palmtree',
        'show_in_nav_menus' => true,
        'publicly_queryable' => true,
        'exclude_from_search' => false,
        'has_archive' => true,
        'query_var' => true,
        'can_export' => true,
        'rewrite' => true,
        'capability_type' => 'post'
    );
    register_post_type( 'reservation', $args );
}
/*
**** Ajout des meta-boxes pour avoir des informations supplémentaires à ajouter sur le post concernent l'événement
**** Données stockées dans cjm_postmeta
*/
 add_action('add_meta_boxes','init_metabox');
function init_metabox(){
  add_meta_box('info_crea', 'Informations Evénement', 'info_crea', 'reservation', 'normal');
}
/*
**** Définition des meta-boxes
*/
function info_crea($post){
  $date_debut = get_post_meta($post->ID,'_date_debut',true);
  $date_fin = get_post_meta($post->ID,'_date_fin',true);
  $nb_place = get_post_meta($post->ID,'_nb_place',true);
  $nb_place_total = get_post_meta($post->ID,'_nb_place_total',true);
  $tarif_adulte = get_post_meta($post->ID,'_tarif_adulte',true);
  $tarif_enfant = get_post_meta($post->ID,'_tarif_enfant',true);
  $tarif_adherent = get_post_meta($post->ID,'_tarif_adherent',true);
  $nom_voyage = get_post_meta($post->ID,'_nom_voyage',true);
  $etat_resa = get_post_meta($post->ID,'_etat_resa',true);
  echo '<label class="lbl_resa" for="nom_voyage">Nom de l\'évènement: </label>';
  echo '<input class="ipt_resa" id="nom_voyage_meta" type="text" name="nom_voyage" value="'.esc_attr($nom_voyage).'" />';
  echo '<label class="lbl_resa" for="date_debut">Date début : </label>';
  echo '<input class="ipt_resa" placeholder="JJ-MM-AAAA" id="date_debut_meta" type="text" name="date_debut" value="'.esc_attr($date_debut).'" />';
  echo '<label class="lbl_resa" for="date_fin">Date fin : </label>';
  echo '<input class="ipt_resa" placeholder="JJ-MM-AAAA" id="date_fin_meta" type="text" name="date_fin" value="'.esc_attr($date_fin).'" />';
  echo '<label class="lbl_resa" for="nb_place">Places Disponibles : </label>';
  echo '<input class="ipt_resa" id="nb_place_meta" type="text" name="nb_place" value="'.esc_attr($nb_place).'" />';
  echo '<label class="lbl_resa" for="nb_place_total">Places Totales : </label>';
  echo '<input class="ipt_resa" id="nb_place_total_meta" type="text" name="nb_place_total" value="'.esc_attr($nb_place_total).'" />';
  echo '<label class="lbl_resa" for="tarif_adulte">Tarifs Adultes : </label>';
  echo '<input class="ipt_resa" id="tarif_adulte_meta" type="text" name="tarif_adulte" value="'.esc_attr($tarif_adulte).'" />';
  echo '<label class="lbl_resa" for="tarif_enfant">Tarifs Enfants : </label>';
  echo '<input class="ipt_resa" id="tarif_enfant_meta" type="text" name="tarif_enfant" value="'.esc_attr($tarif_enfant).'" />';
  echo '<label class="lbl_resa" for="tarif_adherent">Tarifs Adhérent : </label>';
  echo '<input class="ipt_resa" id="tarif_adherent_meta" type="text" name="tarif_adherent" value="'.esc_attr($tarif_adherent).'" />';
  echo '<label class="lbl_resa" for="etat_resa">Etat Réservation : </label>';
  echo '<select name="etat_resa">';
     echo '<option value="ouvert" ';
     if($etat_resa == 'ouvert'){echo 'selected = "selected"';};
     echo '>Ouvert</option>';
     echo '<option value="file_attente" ';
     if($etat_resa == 'file_attente'){echo 'selected = "selected"';}
     echo '>File d\'attente</option>';
     echo '<option value="cloture" ';
     if($etat_resa == 'cloture'){echo 'selected = "selected"';}
      echo '>Cloturé</option>';
  echo '</select>';
}
/*
**** Fonction pour sauvegarder le nouveau post avec les meta-boxes
*/
add_action('save_post','save_metabox');
function save_metabox($post_id){
	global $wpdb;
  if($_POST["post_type"]!="reservation")
  {
    return;
  }
  else if(isset($_POST['date_debut'])
  &&isset($_POST['date_fin'])
  &&isset($_POST['nom_voyage'])
  &&isset($_POST['nb_place'])
  &&isset($_POST['nb_place_total'])
  &&isset($_POST['etat_resa'])
  &&isset($_POST['tarif_adulte'])
  &&isset($_POST['tarif_adherent'])
  &&isset($_POST['tarif_enfant']))
  {
		$tarif_adulte = get_post_meta($post_id,"_tarif_adulte",true);
		$tarif_enfant = get_post_meta($post_id,"_tarif_enfant",true);
		$tarif_adherent = get_post_meta($post_id,"_tarif_adherent",true);
		if($tarif_adulte != $_POST['tarif_adulte'] || $tarif_enfant != $_POST['tarif_enfant'] || $tarif_adherent != $_POST['tarif_adherent'])
		{
			update_post_meta($post_id, '_tarif_adulte', $_POST['tarif_adulte']);
	    update_post_meta($post_id, '_tarif_enfant', $_POST['tarif_enfant']);
	    update_post_meta($post_id, '_tarif_adherent', $_POST['tarif_adherent']);
			$select = $wpdb->get_results("select id_resa,nbplace,nbplace_enf,id_participant,mail_confirm from cjm_reservation where id_evenement=".$post_id);
			foreach ($select as $key => $value) {
				$user = get_user_by('ID',$value->id_participant);
				$value->role = $user->roles[0];
			}
			$select1 = $wpdb->get_results("select id_resa,nbplace,nbplace_enf,role from cjm_reservation_ext where id_evenement=".$post_id);
			$select = array_merge($select,$select1);
			foreach ($select as $key => $value) {
				if(!is_null($value->mail_confirm))
				{
					$query = $wpdb->update("cjm_reservation",
					array(
						"prix_total" => getPrixTotal($post_id,$value->nbplace,$value->nbplace_enf,$value->role)
					),array("id_resa" => $value->id_resa),array("%d"),array('%d'));
				}
				else {
					$query = $wpdb->update("cjm_reservation_ext",
					array(
						"prix_total" => getPrixTotal ($post_id,$value->nbplace,$value->nbplace_enf,$value->role)
					),array("id_resa" => $value->id_resa),array("%d"),array('%d'));
				}

			}
		}
		update_post_meta($post_id, '_date_debut', $_POST['date_debut']);
    update_post_meta($post_id, '_date_fin', $_POST['date_fin']);
    update_metadata ('post', $post_id, '_nb_place', $_POST['nb_place']); // fait la même chose,juste pour le test :)
    update_post_meta($post_id, '_nb_place_total', $_POST['nb_place_total']);
    update_post_meta($post_id, '_nom_voyage', $_POST['nom_voyage']);
    update_post_meta($post_id, '_etat_resa', $_POST['etat_resa']);
  }
}
/*
**** Remove des meta-boxes pour tous les posts qui ne sont pas des événements
*/
add_action('admin_init','customize_meta_boxes');
function customize_meta_boxes() {
     remove_meta_box('postcustom','reservation','normal');
}
/*
Notification quand il y a des mails à envoyer
*/
add_filter( 'add_menu_classes', 'add_plugin_bubble_so_17525062');

function add_plugin_bubble_so_17525062( $menu )
{
    foreach( $menu as $menu_key => $menu_data )
    {
        if( 'edit.php?post_type=reservation' != $menu_data[2] )
        {
          continue;
        }
        else {
          global $wpdb;
          $query = $wpdb->get_results("select count(id_resa) from cjm_reservation where paiement=1 and mail_confirm=0;","ARRAY_N");
          $pending_count=$query[0][0];
          $menu[$menu_key][0] .= " <span id='notif_event_admin' class='update-plugins count-$pending_count'><span class='plugin-count'>" . number_format_i18n($pending_count) . '</span></span>';
         }
    }
    return $menu;
}
