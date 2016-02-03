<?php
/*
Plugin Name: Réservation
Description: Plugin de réservation de voyages et d'escapades
Author: SEBIRE Florian & SAUSSIER Julien
Version: 1.0
*/
add_action('admin_menu', 'plugin_setup_menu');
add_action( 'admin_enqueue_scripts', 'enqueuescript' );
add_action( 'wp_enqueue_scripts', 'enqueuescriptclient' );
add_shortcode('my_test', "shortcode");
function shortcode () {
	ob_start();
	add_voyage();
}
function enqueuescript(){
        if($_GET["page"]!="my-custom-submenu-page2")
        {
          wp_enqueue_style('style',plugins_url('cjm/style.css'),'2.0');
        }
        wp_enqueue_script( 'cjm_library',plugins_url('cjm/js/cjm_library.js'),'1.0');
        wp_enqueue_script( 'cjm2',plugins_url('cjm/js/customAdmin.js'),'1.0');
				/*
				Scipts qui ne sont chargés que sur Réservation
				*/
				if(isset($_GET["post_type"]) && $_GET["post_type"]=="reservation"){

				/*
				Scipts qui ne sont chargés que sur Réservation=>Gestion des participants
				*/
				if($_GET["page"]=="my-custom-submenu-page"){
        wp_enqueue_script( 'cjm',plugins_url('cjm/js/cjm_main.js'),'1.0');
        wp_enqueue_script( 'cjm_export_excel',plugins_url('cjm/js/jquery.table2excel.js'),'1.0');
        wp_enqueue_script( 'cjm_export_pdf1',plugins_url('cjm/js/html2pdf/tableExport.js'),'1.0');
        wp_enqueue_script( 'cjm_export_pdf2',plugins_url('cjm/js/html2pdf/jquery.base64.js'),'1.0');
        wp_enqueue_script( 'cjm_export_pdf3',plugins_url('cjm/js/html2pdf/sprintf.js'),'1.0');
        wp_enqueue_script( 'cjm_export_pdf4',plugins_url('cjm/js/html2pdf/jspdf.js'),'1.0');
        wp_enqueue_script( 'cjm_export_pdf5',plugins_url('cjm/js/html2pdf/base64.js'),'1.0');
				}
				if($_GET["page"]=="my-custom-submenu-page2")
				{
					wp_enqueue_style('style','https://cdn.datatables.net/1.10.10/css/jquery.dataTables.min.css','2.0');
					wp_enqueue_script( 'datables_js','https://cdn.datatables.net/1.10.10/js/jquery.dataTables.min.js','1.0');
					wp_enqueue_script( 'cjm_stats',plugins_url('cjm/js/stats/main.js'),'1.0');
				}
        if($_GET["page"]=="my-custom-submenu-page3")
        {
          wp_enqueue_script( 'cjm_mails',plugins_url('cjm/js/mails/main.js'),'1.0');
          wp_enqueue_style('mail',plugins_url('cjm/mail.css'),'2.0');
        }
        wp_localize_script( 'cjm_library', 'ajax_object',
        array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'user' => "true" ));

				}

}
//scripts js de gestions des forms crud resa
function enqueuescriptclient(){
        wp_enqueue_script( 'cjm_gestion_form_client',plugins_url('cjm/js/cjm_gestion_form_client.js'),'1.0');
        wp_enqueue_style('style',plugins_url('cjm/style_client.css'),'2.0');
}
/*
Modification du type des mails pour le passer en HTML
*/
add_filter( 'wp_mail_content_type', 'set_content_type' );
function set_content_type( $content_type ) {
  return 'text/html';
}

function plugin_setup_menu(){
        add_submenu_page( 'edit.php?post_type=reservation', 'Gestion des participants', 'Gestion des participants', 'manage_options', 'my-custom-submenu-page', 'display_cjm_content' );
        add_submenu_page( 'edit.php?post_type=reservation', 'Statistique', 'Statistique', 'manage_options', 'my-custom-submenu-page2', 'display_cjm_stats' );
        add_submenu_page( 'edit.php?post_type=reservation', 'Mails', 'Mails', 'manage_options', 'my-custom-submenu-page3', 'display_cjm_mails' );
	}
include_once("ajaxControllerv2.php");
add_action( 'admin_post_send_email', 'prefix_admin_send_email' );

function prefix_admin_send_email() {
    global $wpdb;
    var_dump($_POST);
    foreach ($_POST["users"] as $key => $value) {
      $infos = explode("&",$value);
			$user = get_user_by( "login", $infos[0]);
			$user_id = $user->ID;
			$user_infos = $wpdb->get_results("select nbplace,nbplace_enf,prix_total from cjm_reservation where id_participant=".$user_id);
      $tarif_adulte = get_post_meta($infos[1],"_tarif_adulte",true);
      $tarif_enf = get_post_meta($infos[1],"_tarif_enfant",true);
      $tarif_adh = get_post_meta($infos[1],"_tarif_adherent",true);
			$event_name = get_post_meta($infos[1],"_nom_voyage",true);
      $res = $wpdb->get_results("select * from cjm_mail where id=".$_POST["id"].";");
      $title = stripslashes($res[0]->title);
      $content = stripslashes($res[0]->content);
      $title = str_replace("%prix_total%",$user_infos[0]->prix_total,$title);
      $content = str_replace("%prix_total%",$user_infos[0]->prix_total,$content);
      $content = str_replace("%USERNAME%",$user->display_name,$content);
      $content = str_replace("%evenement%",$event_name,$content);
      $content = str_replace("%nbplace_enf%",$user_infos[0]->nbplace_enf,$content);
      $content = str_replace("%nbplace%",$user_infos[0]->nbplace,$content);
      $content = str_replace("%prix_place%",$tarif_adulte,$content);
      $content = str_replace("%prix_place_enf%",$tarif_enf,$content);
      $content = str_replace("%prix_place_adh%",$tarif_adh,$content);
      $content = str_replace("%espace%","</br>",$content);
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
    header('Location: edit.php?post_type=reservation&page=my-custom-submenu-page');
}
add_action( 'admin_post_save_email', 'prefix_admin_save_email' );

function prefix_admin_save_email() {
    global $wpdb;
    $res = $wpdb->update('cjm_mail',array(
      "title"=>stripslashes($_POST["post_title"]),
      "content"=> stripslashes($_POST["test_mail"])
      ),array("id"=>1));
    header('Location: edit.php?post_type=reservation&page=my-custom-submenu-page3');
}
function display_cjm_mails () {
    /*
    * Les mails
    */
    global $wpdb;
    $res = $wpdb->get_results("select * from cjm_mail where id=1");
    echo "<h1>Les mails</h1>";
    foreach ($res as $key => $value) {
      $mail_message = stripslashes(stripslashes($value->content));
      $title = stripslashes(stripslashes($value->title));
      echo "<form method='post' action='admin-post.php?action=save_email'>";
      echo "<input style='font-size: x-large;' type='text' name='post_title' size='50' value='".$title."'' id='title' placeholder='Titre'>";
      wp_editor($mail_message,"test_mail");
      echo "<input type=\"hidden\" name=\"id\" value='".$id."'>";
      echo "<input type=\"hidden\" name=\"action\" value=\"save_email\">";
      submit_button( 'Sauvegarder' ,'primary');
      echo "</form>";
    }
}
function display_cjm_stats () {
  echo "<h1>Stats</h1>";
  echo "<table id='resas' class='hover row-border' cellspacing='0' width='100%'>
        <thead>
            <tr>
                <th>Nom Prénom</th>
                <th>Nom Evenement</th>
                <th>Nombre place</th>
                <th>Nombre place enfants</th>
                <th>Prix total</th>
                <th>Téléphone</th>
								<th>Paiement</th>
								<th>Priorité</th>
                <th>Role</th>
                <th>Date</th>
            </tr>
        </thead>
        <tfoot>
            <tr>
							<th>Nom Prénom</th>
							<th>Nom Evenement</th>
							<th>Nombre place</th>
							<th>Nombre place enfants</th>
							<th>Prix total</th>
							<th>Téléphone</th>
							<th>Paiement</th>
							<th>Priorité</th>
              <th>Role</th>
              <th>Date</th>
            </tr>
        </tfoot>
    </table>";
}
function display_cjm_content() {
        echo "<div class='icon-cjm-resa'></div>";
      echo "<h1 id='master_titre_resa'>Gestions des participants</h1>";
      echo "<h2 class='nav-tab-wrapper'>";
      echo "<img id='reload_all' src='../wp-content/plugins/cjm/img/refresh.png' style='cursor:pointer;float:right;margin-right:10px;'></img>";
      echo "<a id='les_voyages_titre' class='nav-tab'>Les voyages</a>";
      echo "<a id='les_escapades_titre' class='nav-tab'>Les Escapades</a>";
      echo "<a id='les_mails_titre' class='nav-tab'>Les Mails</a>";
      echo "</h2>";
      /*
      Les voyages
      */
      echo "<div id='les_voyages' style='display:none;'>";
      echo "<h1>Les Voyages</h1>";
      echo
      "<select id='voyages_action'>
            <option selected=\"selected\">Action</option>
            <option value=\"Supprimer\">Supprimer</option>
            <option value=\"Modifier\">Modifier</option>
      </select>";
      echo "<input type='submit' class='button action' value='Appliquer' id='app_voyage'>";
      echo "<table style='border:solid;' class='wp-list-table widefat fixed posts'>";
      echo "</table>";
      echo "</div>";
      /*
      Les escapades
      */
      echo "<div id='les_escapades' style='display:none;'>";
      echo "<h1>Les Escapades</h1>";
      echo
      "<select id='escapdes_action'>
            <option selected=\"selected\">Action</option>
            <option value=\"Supprimer\">Supprimer</option>
            <option value=\"Modifier\">Modifier</option>
      </select>";
      echo "<input type='submit' class='button action' value='Appliquer' id='app_escapade'>";
      echo "<table style='border:solid;' class='wp-list-table widefat fixed posts'>";
      echo "</table>";
      echo "</div>";
      /*
      Les réservations
      */
      echo "<div id='les_resas' style='display:none;'>";
      echo "<h1>Les Réservations</h1>";
      echo
      "<select id='resa_action'>
            <option selected=\"selected\">Action</option>
            <option value=\"Supprimer\">Supprimer</option>
            <option value=\"Modifier\">Modifier</option>
      </select>";
      echo "<input type='submit' class='button action' value='Appliquer' id='app_resa'>";
      echo "<table style='border:solid;' class='wp-list-table widefat fixed posts'>";
      echo "</table>";
      echo
      "<select id='export_resas'>
            <option  value =\"PDF\" selected=\"selected\">PDF</option>
            <option value=\"Excel\">Excel</option>
      </select>";
      echo "<input type='button' class='button action' value='Exporter' id='btn_export_resa'>";
      echo "</br><input type='button' class='button action' value='Ajouter une Réservation' id='btn_add_resa'>";
      echo "<div id='add_resa_form' style='display:none;'>";
      echo "<h2>Ajouter une réservation</h2>";
      echo "<input name='add_name' type='text' placeholder='NOM Prénom'></input></br>";
      echo "<input placeholder='Places adultes' type='number'></input></br>";
      echo "<input placeholder='Places enfants' type='number'></input></br>";
      echo "<input name='add_tel' type='text' placeholder='Téléphone'></input></br>";
      echo "<label>Paiement : </label><input name='add_paiement' type='checkbox'></input></br>";
      echo "<label>Liste attente : </label><input name='add_list' type='checkbox'></input></br>";
      echo
      "<select id='add_role'>
            <option  value =\"adherent\" selected=\"selected\">Adhérent</option>
            <option value=\"noadherent\">Non Adhérent</option>
      </select>";
      submit_button( 'Enregistrer',"primary","add_resa" );
      echo "</div>";
      echo "</div>";
      /*
    * Les mails
    */
    echo "<div id='les_mails' style='display:none;'>";
    echo "<h1 onclick='window.location.href=\"&mails=true\"'>Confirmation de paiement</h1>";
    include("_mail.php");
    foreach ($res as $key => $value) {
      $mail_message = stripslashes(stripslashes($value->content));
      $title = stripslashes(stripslashes($value->title));
      $id= $value->id;
      echo "<form method='post' action='admin-post.php?action=send_email'>";
      foreach ($users as $key => $value) {
        echo "<input type='checkbox' name='users[]' value ='".$value->user_login."&".$value->id_evenement."'>".$value->user_login." de l'événement <strong>".$value->nom_voyage."</strong></input></br>";
      }
      echo "<input type=\"hidden\" name=\"action\" value=\"send_email\">";
      echo "<input type=\"hidden\" name=\"id\" value='".$id."''>";
      submit_button( 'Envoyer' ,'primary');
      echo "</form>";
    }
    echo "<div>";
}

add_action( 'init', 'register_cpt_resa' );
function my_admin_notice($class,$message) {
    echo"<div class=\"$class is-dismissible notice\"> <p>$message</p>
          <button type='button' class='notice-dismiss'>
      <span class='screen-reader-text'>Ne pas tenir compte de ce message </span>
      </button>
    </div>";
}

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

//  meta box reservation
 add_action('add_meta_boxes','init_metabox');
function init_metabox(){
  add_meta_box('info_crea', 'Informations Evénement', 'info_crea', 'reservation', 'normal');
}

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

add_action('save_post','save_metabox');
function save_metabox($post_id){
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
    update_post_meta($post_id, '_date_debut', $_POST['date_debut']);
    update_post_meta($post_id, '_date_fin', $_POST['date_fin']);
    update_metadata ('post', $post_id, '_nb_place', $_POST['nb_place']);
    update_post_meta($post_id, '_nb_place_total', $_POST['nb_place_total']);
    update_post_meta($post_id, '_tarif_adulte', $_POST['tarif_adulte']);
    update_post_meta($post_id, '_tarif_enfant', $_POST['tarif_enfant']);
    update_post_meta($post_id, '_tarif_adherent', $_POST['tarif_adherent']);
    update_post_meta($post_id, '_nom_voyage', $_POST['nom_voyage']);
    update_post_meta($post_id, '_etat_resa', $_POST['etat_resa']);

  }
}

add_action('admin_init','customize_meta_boxes');

function customize_meta_boxes() {
     remove_meta_box('postcustom','reservation','normal');
}
