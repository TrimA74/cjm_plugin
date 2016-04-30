<?php
/*
**** Affichage du contenu dans la page Mails
*/
function display_cjm_mails () {
    /*
    * Les mails
    */
    global $wpdb;
    $res = $wpdb->get_results("select * from cjm_mail");
    echo "<h1>Les mails</h1>";
    echo "<p>NOM Prénom : <strong>%USERNAME%</strong></p>";
    echo "<p>Nom de l'événement : <strong>%evenement%</strong></p>";
    echo "<p>Nombre de place adulte : <strong>%nbplace%</strong></p>";
    echo "<p>Nombre de places enfants : <strong>%nbplace_enf%</strong></p>";
    echo "<p>Prix place enfant : <strong>%prix_place_enf%</strong></p>";
    echo "<p>Prix place adulte : <strong>%prix_place%</strong></p>";
    echo "<p>Prix total : <strong>%prix_total%</strong></p>";
    foreach ($res as $key => $value) {
      $mail_message = stripslashes(stripslashes($value->content));
      $title = stripslashes(stripslashes($value->title));
      echo "<form method='post' action='admin-post.php?action=save_email'>";
      echo "<input style='font-size: x-large;' type='text' name='post_title' size='50' value=\"".$value->title."\" id='title".$value->id."' placeholder='Titre'>";
      wp_editor($mail_message,"mail_content".$value->id,array("wpautop"=>false,"dfw"=>true));
      echo "<input type=\"hidden\" name=\"id\" value='".$value->id."'>";
      echo "<input type=\"hidden\" name=\"action\" value=\"save_email\">";
      submit_button( 'Sauvegarder' ,'primary');
      echo "</form>";
    }
}
/*
**** Affichage du contenu dans la page Statistique
*/
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
/*
**** Affichage du contenu dans la page Gestion des participants
*/
function display_cjm_content() {
			// my_admin_notice("updated","Petite fonction cool :)");
      echo "<div class='icon-cjm-resa'></div>";
      echo "<h1 id='master_titre_resa'>Gestions des participants</h1>";
      echo "<h2 class='nav-tab-wrapper'>";
      echo "<img id='reload_all' src='../wp-content/plugins/cjm/img/refresh.png' style='cursor:pointer;float:right;margin-right:10px;'></img>";
      echo "<a id='les_voyages_titre' class='nav-tab'>Les voyages</a>";
      echo "<a id='les_escapades_titre' class='nav-tab'>Les Escapades</a>";
      echo "<a id='les_mails_titre' class='nav-tab'>Confirmation de paiement</a>";
      echo "</h2>";
      /*
      Les voyages
      */
      echo "<div id='les_voyages' style='display:none;'>";
      echo "<h1>Les Voyages</h1>";
      echo "<input type='submit' class='button action' value='Modifier' id='btn_modif_voyage'>";
      // echo
      // "<select id='voyages_action'>
      //       <option selected=\"selected\">Action</option>
      //       <option value=\"Supprimer\">Supprimer</option>
      //       <option value=\"Modifier\">Modifier</option>
      // </select>";
      // echo "<input type='submit' class='button action' value='Appliquer' id='app_voyage'>";
      echo "<table style='border:solid;' class='wp-list-table widefat fixed posts'>";
      echo "</table>";
      echo "</div>";
      /*
      Les escapades
      */
      echo "<div id='les_escapades' style='display:none;'>";
      echo "<h1>Les Escapades</h1>";
      echo "<input type='submit' class='button action' value='Modifier' id='btn_modif_escapade'>";
      // echo
      // "<select id='escapdes_action'>
      //       <option selected=\"selected\">Action</option>
      //       <option value=\"Supprimer\">Supprimer</option>
      //       <option value=\"Modifier\">Modifier</option>
      // </select>";
      // echo "<input type='submit' class='button action' value='Appliquer' id='app_escapade'>";
      echo "<table style='border:solid;' class='wp-list-table widefat fixed posts'>";
      echo "</table>";
      echo "</div>";
      /*
      Les réservations
      */
      echo "<div id='les_resas' style='display:none;'>";
      echo "<h1>Les Réservations</h1>";
      echo "<input type='submit' class='button action' value='Modifier' id='btn_modif_resa'>";
      echo "<input type='submit' class='button action' value='Supprimer' id='btn_sup_resa'>";
      // echo
      // "<select id='resa_action'>
      //       <option selected=\"selected\">Action</option>
      //       <option value=\"Supprimer\">Supprimer</option>
      //       <option value=\"Modifier\">Modifier</option>
      // </select>";
      // echo "<input type='submit' class='button action' value='Appliquer' id='app_resa'>";
      echo "<table style='border:solid;' class='wp-list-table widefat fixed posts'>";
      echo "</table>";
      echo
      "<select id='export_resas'>

            <option value=\"Excel\">Excel</option>
      </select>";
      //<option  value =\"PDF\" selected=\"selected\">PDF</option>
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
            <option  value =\"adherent_user\" selected=\"selected\">Adhérent</option>
            <option value=\"no_nadherent\">Non Adhérent</option>
      </select>";
      submit_button( 'Enregistrer',"primary","add_resa" );
      echo "</div>";
      echo "</div>";
      /*
    * Les mails
    */
    echo "<div id='les_mails' style='display:none;'>";
    echo "<h1>Confirmation de paiement</h1>";
		echo "<input id='check_all' type='checkbox'><label>Tous</label>";
		// global $wpdb;
		// $res = $wpdb->get_results("select * from cjm_mail where id=1");
		// $users=$wpdb->get_results("select u.user_login,u.ID,r.id_evenement from cjm_users u
		// 	join cjm_reservation r on r.id_participant=u.ID
		// 	where r.paiement=1 and r.mail_confirm=0");
		// foreach ($users as $key => $value) {
		// 	$value->nom_voyage=get_post_meta($value->id_evenement,"_nom_voyage",true);
		// 	}
    // 	foreach ($res as $key => $value) {
    //   $mail_message = stripslashes(stripslashes($value->content));
    //   $title = stripslashes(stripslashes($value->title));
    //   $id= $value->id;
      // echo "<form method='post' action='admin-post.php?action=send_email'>";
      echo "<form id='send_email'>";
      // foreach ($users as $key => $value) {
      //   echo "<input class='checkbox_send_mail' type='checkbox' name='users[]' value ='".$value->user_login."&".$value->id_evenement."'>".$value->user_login." de l'événement <strong>".$value->nom_voyage."</strong></input></br>";
      // }
      // echo "<input type=\"hidden\" name=\"action\" value=\"send_email_confirm\">";
      // echo "<input type=\"hidden\" name=\"id\" value='".$id."''>";
      // echo "<input class='button button-primary' type='button' value='Envoyer' id='send_email_btn'></input>";
      echo "</form>";
    // }
    echo "<div>";
}
