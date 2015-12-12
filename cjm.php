<?php
/*
Plugin Name: Réservation
Description: Plugin de réservation de voyages et d'escapades
Author: SEBIRE Florian & SAUSSIER Julien
Version: 1.0
*/
add_action('admin_menu', 'plugin_setup_menu');
add_action( 'admin_enqueue_scripts', 'enqueuescript' );
add_shortcode('my_test', "shortcode");
function shortcode () {
	ob_start();
	add_voyage();
}
function enqueuescript(){
        wp_enqueue_script( 'cjm_library',plugins_url('cjm/js/cjm_library.js'),'1.0');
        wp_enqueue_script( 'cjm',plugins_url('cjm/js/cjm_main.js'),'1.0');
        wp_enqueue_script( 'cjm2',plugins_url('cjm/js/customAdmin.js'),'1.0');
        wp_enqueue_script( 'cjm_export_excel',plugins_url('cjm/js/jquery.table2excel.js'),'1.0');
                wp_localize_script( 'cjm_library', 'ajax_object',
            array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'user' => "true" ) );
        wp_enqueue_style('style',plugins_url('cjm/style.css'),'2.0');

}
 
function plugin_setup_menu(){
        add_submenu_page( 'edit.php?post_type=reservation', 'Gestion des participants', 'Gestion des participants', 'manage_options', 'my-custom-submenu-page', 'test_init' );

	}
include_once("ajaxControllerv2.php");
function test_init(){
      echo "<div class='icon-cjm-resa'></div>";
      echo "<h1>Gestions des participants</h1>;";
      echo "<h2 class='nav-tab-wrapper'>";
      echo "<a id='les_voyages_titre' class='nav-tab'>Les voyages</a>";
      echo "<a id='les_escapades_titre' class='nav-tab'>Les Escapades</a>";
      echo "</h2>";
      /*
      
      */
      echo "<div id='les_voyages' style='display:none;'>";
      echo "<h1>Les voyages</h1>";
      echo 
      "<select id='voyages_action'>
            <option selected=\"selected\">Action</option>
            <option value=\"Supprimer\">Supprimer</option>
            <option value=\"Modifier\">Modifier</option>
      </select>";
      echo "<input type='submit' class='button action' value='Appliquer' id='app_voyage'>";
      echo "<table style='border:solid;' class='wp-list-table widefat fixed striped posts'>";
      echo "</table>";
      echo "</div>"; 

      echo "<div id='les_escapades' style='display:none;'>";
      echo "<h1>Les Escapades</h1>";
      echo 
      "<select id='escapdes_action'>
            <option selected=\"selected\">Action</option>
            <option value=\"Supprimer\">Supprimer</option>
            <option value=\"Modifier\">Modifier</option>
      </select>";
      echo "<input type='submit' class='button action' value='Appliquer' id='app_escapade'>";
      echo "<table style='border:solid;' class='wp-list-table widefat fixed striped posts'>";
      echo "</table>";
      echo "</div>"; 
      /*
      
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
      echo "<table style='border:solid;' class='wp-list-table widefat fixed striped posts'>";
      echo "</table>";
       echo 
      "<select id='export_resas'>
            <option  value =\"PDF\" selected=\"selected\">PDF</option>
            <option value=\"Excel\">Excel</option>
      </select>";
      echo "<input type='button' class='button action' value='Exporter' id='btn_export_resa'>";
      echo "</div>";

       

}

add_action( 'init', 'register_cpt_resa' );

function register_cpt_resa() {

    $labels = array( 
        'name' => _x( 'Réservation', 'reservation' ),
        'singular_name' => _x( 'Réservation', 'reservation' ),
        'add_new' => _x( 'Ajouter', 'reservation' ),
        'add_new_item' => _x( 'Ajouter une réservation', 'reservation' ),
        'edit_item' => _x( 'Editer une réservation', 'reservation' ),
        'new_item' => _x( 'Nouvelle réservation', 'reservation' ),
        'view_item' => _x( 'Voir la réservation', 'reservation' ),
        'search_items' => _x( 'Rechercher une réservation', 'reservation' ),
        'not_found' => _x( 'Aucune réservation trouvée', 'reservation' ),
        'not_found_in_trash' => _x( 'Aucune réservation dans la corbeille', 'reservation' ),
        'parent_item_colon' => _x( 'Réservation parente :', 'reservation' ),
        'menu_name' => _x( 'Réservation', 'reservation' ),
    );

    $args = array( 
        'labels' => $labels,
        'hierarchical' => false,
        'description' => 'Les Réservations',
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
  add_meta_box('info_crea', 'Informations Réservations', 'info_crea', 'reservation', 'normal');
}

function info_crea($post){
  $date_debut = get_post_meta($post->ID,'_date_debut',true);
  $date_fin = get_post_meta($post->ID,'_date_fin',true);
  $nb_place = get_post_meta($post->ID,'_nb_place',true);
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
if(isset($_POST['date_debut'])
  &&isset($_POST['date_fin'])
  &&isset($_POST['nom_voyage'])
  &&isset($_POST['nb_place'])
  &&isset($_POST['nb_place_enf'])
  &&isset($_POST['tarif_adulte'])
  &&isset($_POST['tarif_enfant']))
  update_post_meta($post_id, '_date_debut', $_POST['date_debut']);
  update_post_meta($post_id, '_date_fin', $_POST['date_fin']);
  update_post_meta($post_id, '_nb_place', $_POST['nb_place']);
  update_post_meta($post_id, '_tarif_adulte', $_POST['tarif_adulte']);
  update_post_meta($post_id, '_tarif_enfant', $_POST['tarif_enfant']);
  update_post_meta($post_id, '_tarif_adherent', $_POST['tarif_adherent']);
  update_post_meta($post_id, '_nom_voyage', $_POST['nom_voyage']);
  update_post_meta($post_id, '_etat_resa', $_POST['etat_resa']);
  $res = add_post_meta($post_id,'_date_debut', $_POST['date_debut'],true);
  if(!$res)
    update_post_meta($post_id, '_date_debut', $_POST['date_debut']);
}

add_action('admin_init','customize_meta_boxes');

function customize_meta_boxes() {
     remove_meta_box('postcustom','reservation','normal');
}



