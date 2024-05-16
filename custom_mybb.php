<?php
if (!defined("IN_MYBB")) {
  die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

// einkommentieren um Fehler angezeigt zu bekommen
// error_reporting ( -1 );
// ini_set ( 'display_errors', true );


/***
 * Funktion für die Anzeige im ACP Reiter Plugins
 */
function custom_mybb_info()
{
  return array(
    "name"    => "Eigene Anpassungen",
    "description"  => "Dieses Plugin stellt das Grundgerüst für eine Datei bereit, in der eigene Änderungen vorgenommen werden können. Keine eigene Funktion.",
    "website"  => "",
    "author"  => "risuena",
    "authorsite"  => "https://github.com/katjalennartz",
    "version"  => "1.0.0",
    "compatibility" => "18*"
  );
}

/**
 * Diese Funktion testet, ob das Plugin schon installiert ist oder nicht. 
 * In dem Fall fragen wir ab, ob es die Einstellungsgruppe schon gibt.
 * Man könnte auch testen ob es eine bestimmte Tabelle in der Datenbank gibt oder ein bestinmmtes Feld in einer Tabelle
 */

function custom_mybb_is_installed()
{
  global $db, $mybb;

  //existiert die einstellung? Dann ist das Plugin installiert
  if (isset($mybb->settings['custom_mybb_active'])) {
    return true;
  }
  // if ($db->table_exists("TABELLENNAME")) {
  //   return true;
  // }
  //Feld abfragen 
  // if ($db->field_exists("FELDNAME", "TABELLENNAME")) {
  //   return true;
  // }
  return false;
}


/**
 * Installations Funktion - wird ausgeführt bei dem Klick  auf 'installieren' im ACP bei Plugins
 * 
 * Was sollte hier rein?
 * - anlegen von Templategruppen / Templates / Styles
 * - anlegen von Einstellungsgruppe und Einstellungen
 * - anlegen von Tabellen und Spalten in der Datenbank
 */
function custom_mybb_install()
{
  //Das global brauchen wir um in dieser Funktion auf Funktionen von MyBB zugreifen zu können.
  global $db, $mybb;

  //falls es Fehler gab, rufen wir hier erst einmal die Uninstall funktion auf.
  custom_mybb_uninstall();

  // Einstellungsgruppe Erstellen ACP
  $setting_group = array(
    'name' => 'custom_mybb',
    'title' => 'Anpassungen MyBB',
    'description' => 'Hier findest du deine eigenen Einstellungen.', // Beschreibungstext
    'disporder' => 1, // Reihenfolge 
    'isdefault' => 0
  );
  //einfügen in die DB und direkt die ID der neu angelegten Gruppe in der Variablen $gid speichern
  $gid = $db->insert_query("settinggroups", $setting_group);

  //Wir legen ein Array an in dem wir unsere Einstellungen anlegen 
  $setting_array = array(
    'custom_mybb_active' => array(
      'title' => 'Aktiv',
      'description' => 'Ist das Plugin gerade aktiv?',
      'optionscode' => 'yesno',
      'value' => '1', // Default
      'disporder' => 1
    ),
    //Beispiel für einen weiteren eintrag 
    //man könnte sich hier auch den ingamezeitraum speichern
    // 'arealiste_forumdisplay' => array(
    //   'title' => 'Aktiv',
    //   'description' => 'Ist das Plugin gerade aktiv?',
    //   'optionscode' => 'yesno',  //möglich z.B: text, numeric, groupselect siehe https://docs.mybb.com/1.8/development/plugins/basics/#plugin-settings
    //   'value' => '1', // Default 1 für yes 0 für no
    //   'disporder' => 2
    // ),
  );

  //Das Plugin ist schon installiert und ihr wollt neue Einstellungen ohne das plugin zu deinstallieren und neu zu installieren?
  //Ihr könnt euch neue Einstellungen im ACP selber einfügen
  // https://forenadreesse/admin/index.php?module=config-settings
  //Oben habt ihr einenReiter mit 
  // 'Einstellungen ändern' 'Neue Einstellung' 'Neue Einstellungsgruppe' 'Existierende Einstellungen ändern'

  //Jetzt gehen wir das Array durch und fügen die Einstellungen hinzu
  foreach ($setting_array as $name => $setting) {
    $setting['name'] = $name;
    $setting['gid'] = $gid;
    $db->insert_query('settings', $setting);
  }

  //Wichtig! Damit die Einstellungen auch im ACP angezeigt werden. 
  rebuild_settings();

  //Templates erstellen
  // Wir erstellen eine Templategruppe, weil es übersichtlicher ist. 
  // $templategrouparray = array(
  //   'prefix' => 'custom_mybb',
  //   'title'  => $db->escape_string('Eigene Anpassungen'),
  //   'isdefault' => 1
  // );
  // $db->insert_query("templategroups", $templategrouparray);

  //Ein Beispiel für ein ganz einfaches Template einer Seite
  //für weitere kopieren und die 0 hochzählen 
  // $template[0] = array( //achtung 0 anpassen und hochzählen! 
  //   "title" => 'custom_mybb_main',
  //   "template" => '
  //   <html>
  //   <head>
  //   <title>{$mybb->settings[\\\'bbname\\\']} - Custom Mybb</title>
  //   {$headerinclude}
  //   </head>
  //   <body>
  //   {$header}

  //   <table width="100%" border="0" align="center" class="tborder">
  //   <tr>

  //     <td valign="top">
  //       <h1>Beispiel</h1>
  //       <div class="custom-con">
  //       ganz einfacher Text
  //       </div>
  //     </td>
  //   </tr>
  //   </table>

  //   {$footer}
  //   </body>
  //   </html>
  //   ',
  //   "sid" => "-2",  // -2 -> Template pro Style  // -1 globale Templates
  //   "version" => "1.0",
  //   "dateline" => TIME_NOW
  // );

  //weitere templates bei Bedarf (kopieren, 0 anpassen, einfügen)

  // alle templates einfügen
  // foreach ($template as $row) {
  //   $db->insert_query("templates", $row);
  // }


  //CSS einfügen
  // $css = array(
  //   'name' => 'custom_mybb.css',
  //   'tid' => 1,
  //   'attachedto' => '',
  //   "stylesheet" =>    '

  //   /*Beispiel*/
  //   .custom-con {
  //       padding: 10px;
  //       text-align: center;
  //   }

  //   ',
  //   'cachefile' => $db->escape_string(str_replace('/', '', 'custom_mybb.css')),
  //   'lastmodified' => time()
  // );

  // require_once MYBB_ADMIN_DIR . "inc/functions_themes.php";

  // $sid = $db->insert_query("themestylesheets", $css);
  // $db->update_query("themestylesheets", array("cachefile" => "css.php?stylesheet=" . $sid), "sid = '" . $sid . "'", 1);

  // $tids = $db->simple_select("themes", "tid");
  // while ($theme = $db->fetch_array($tids)) {
  //   update_theme_stylesheet_list($theme['tid']);
  // }

  //Tabellen:
  // spalte hinzufügen:
  // $db->add_column("TABELLENNAMEN", "FELDNAME", "varchar(200) NOT NULL DEFAULT ''");

  //Beispiel Table hinzufügen:
  // Neue Tabelle um Szenen zu speichern und informationen, wie die benachrichtigungen sein sollen.
  // if (!$db->table_exists("scenetracker")) {
  //   $db->write_query("CREATE TABLE `" . TABLE_PREFIX . "scenetracker` (
  //       `id` int(10) NOT NULL AUTO_INCREMENT,
  //       `uid` int(10) NOT NULL,
  //       `tid` int(10) NOT NULL,
  //       `alert` int(1) NOT NULL DEFAULT 0,
  //       `type` varchar(50) NOT NULL DEFAULT 'always',
  //       `inform_by` int(10) NOT NULL DEFAULT 1,
  //       `index_view` int(1) NOT NULL DEFAULT 1,
  //       `profil_view` int(1) NOT NULL DEFAULT 1,
  //       PRIMARY KEY (`id`)
  //   ) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;");
  // }


  //Tabellen / Spalten erstellen könnt ihr euch außerdem auch in phpmyadmin, 
  //wenn ihr das Plugin nicht neu installieren wollt wenn ihr nach der installation welche haben wollt
}

/**
 * Deinstallationsfunktion - wird bei dem Klick auf Deinstallieren im ACP bei Plugins
 *  
 * Was sollte hier rein?
 * - prinzipiell alles was man im Install installiert.
 * - löschen von Templates / Templategruppen  / Styles - jenachem was man angelegt hat
 * - löschen von Tabellen und Spalten im MyBB
 * - löschen Einstellungen / Einstellungsgruppe
 */ function custom_mybb_uninstall()
{
  //Das global brauchen wir um in dieser Funktion auf Funktionen von MyBB zugreifen zu können.
  global $db, $mybb;

  //Spalten löschen 
  //erst überprüfen ob Feld überhaupt existiert dann löschen
  // if ($db->field_exists("FELDNAME", "TABELLE")) {
  //   $db->drop_column("TABELLE", "FELDNAME");
  // }

  //eine Tabelle löschen
  // if ($db->table_exists("TABELLE")) {
  //   $db->drop_table("TABELLE");
  // }

  // Templates löschen
  //alle templates löschen die mit dem namen custom_mybb beginnen
  // $db->delete_query("templates", "title LIKE 'custom_mybb%'");
  //Templategruppe löschen mit dem namen custom_mybb löschen
  // $db->delete_query("templategroups", "prefix = 'custom_mybb'");

  //Einstellungen löschen
  //alle einstellungen löschen die mit dem namen custom_mybb beginnen
  // $db->delete_query('settings', "name LIKE 'custom_mybb%'");
  //einstellungsgruppe custom_mybb löschen
  // $db->delete_query('settinggroups', "name = 'custom_mybb'");

  // CSS löschen
  // require_once MYBB_ADMIN_DIR . "inc/functions_themes.php";
  // $db->delete_query("themestylesheets", "name = 'custom_mybb.css'");
  // $query = $db->simple_select("themes", "tid");
  // while ($theme = $db->fetch_array($query)) {
  //   update_theme_stylesheet_list($theme['tid']);
  // }

  rebuild_settings();
}

/**
 * Aktivieren des Plugins - wird auch automatisch bei 'installieren' aufgerufen
 * Hier am besten Änderungen einfügen wie das einfügen von Variablen in Templates
 */

function custom_mybb_activate()
{
  //Variablen einfügen
  include MYBB_ROOT . "/inc/adminfunctions_templates.php";

  //beispiel für forumdisplay 
  // -> templatename in dem gesucht werden soll  // 
  // {$rules} - was soll gesucht werden
  // {$rules} {$arealist_forumdisplay} - womit es ersetzt werden soll
  //Hier wird also im Template nach {$rules} gesucht und dann mit '{$rules} {$arealist_forumdisplay}' ersetzt 
  // find_replace_templatesets("forumdisplay", "#" . preg_quote('{$rules}') . "#i", '{$rules} {$arealist_forumdisplay}');
}

/**
 * Dektivieren des Plugins - wird auch automatisch bei 'installieren' aufgerufen
 * Hier am besten Änderungen einfügen wie das löschen von Variablen in Templates
 */
function custom_mybb_deactivate()
{
  //Variablen einfügen
  include MYBB_ROOT . "/inc/adminfunctions_templates.php";

  //beispiel für forumdisplay 
  // -> templatename in dem gesucht werden soll  // 
  // {$rules} - was soll gesucht werden
  // {$rules} {$arealist_forumdisplay} - womit es ersetzt werden soll
  //Hier wird also im Template nach {$arealist_forumdisplay} gesucht und dann mit '' ersetzt, also quasi gelöscht 
  // find_replace_templatesets("forumdisplay", "#" . preg_quote('{$arealist_forumdisplay}') . "#i", '');
}


/**
 * Hier folgen ein paar der wichtigsten Hooks - eine Auflistung aller Hooks findet ihr hier:
 * https://docs.mybb.com/1.8/development/plugins/hooks/
 * Das Plugin führt die Funktion die ihr schreibt dann genau an dieser Stelle aus.
 * Es ist also so, als würdet ihr den Code quasi in der Datei an diese Stelle schreiben.
 * An diesen 'Haken' setzt das Plugin den Code.
 */

$plugins->add_hook('global_start', 'custom_mybb_global');
//  $plugins->add_hook('global_intermediate', 'custom_mybb_global');
//  $plugins->add_hook('global_end', 'custom_mybb_global');
function custom_mybb_global()
{
  /*Der Global Hook macht Variablen überall im Forum verfügbar.  
  Sinnvoll für Header Tabellen. Lasst zum Beispiel etwas nur für Benutzer, oder nur für Gäste anzeigen
  Wenn ihr hier Templates einfügt kann es sein, dass das beim global_start nur mit 
  globalen Templates funktioniert - versucht in dem Fall global_intermediate oder global_end*/
  global $db, $mybb; //evt. müsst ihr hier weitere Variablen hinzufügen, oft z.b. $templates, $header
}

$plugins->add_hook('member_profile_start', 'custom_mybb_member_profile');
//  $plugins->add_hook('member_profile_end', 'custom_mybb_member_profile');
function custom_mybb_member_profile()
{
  //globale variablen evt. ergänzen. 
  global $mybb, $templates, $db;
  /* Profilenazeige verbergen z.B. Avatare vor Gästen
  Beispiel für Anzeige angehangenene Accounts des Accountsswitchers*/
  $thisuser = intval($mybb->user['uid']);

  //Fetch all Characters cause, we like it better, when we can design it like we want
  $otherfaces = mybb_custom_get_allchars($thisuser);
  $face = "";

  foreach ($otherfaces as $uid => $username) {
    if ($uid != $thisuser) {
      $user = get_user($uid);
      // var_dump($user);
      $userlink = build_profile_link($user['username'], $user['uid']);
      if ($user['avatar'] != "" && $mybb->user['uid'] != 0) {

        $avatar =  "<div class=\"profil_ava_other\" style=\"background-image: url({$user['avatar']});\"></div>";
      } else {
        $avatar = "<i class=\"fas fa-user-circle\"></i>";
      }

      $face .= "
        <div class=\"profil_faces\">
          <div class=\"profil_face__ava\">{$avatar}</div>
          <div class=\"profil_face__item\">{$userlink}</div>
        </div>";

      //allternativ ein template anlegen  und html code von $face dort einfügen
      // eval("\$custom_mybb_as_bit .= \"" . $templates->get("custom_mybb_as_bit") . "\";");
    }
  }
  if (count($otherfaces) == 1) {
    $face = "keine Mehrfachcharaktere";
  }

  //Beispiel um sich die anzahl Ingameposts ausgeben zu lassen FID not in 
  $ingameposts = $db->fetch_field($db->write_query("SELECT count(*) anzahl from mybb_posts p
    INNER JOIN mybb_users u ON u.uid = p.uid
    INNER JOIN
          (SELECT fid as fff FROM mybb_forums WHERE concat(',',parentlist,',') LIKE '%,14,%' OR concat(',',parentlist,',') LIKE '%,20,%') as f
          ON fff = fid
          and p.uid = {$thisuser}
          "), "anzahl");

// wenn man bestimmte foren (z.B sms / mail / telefon) ausschließen will kann man sie mit fid not in ausschließen:

// $ingameposts = $db->fetch_field($db->write_query("SELECT count(*) anzahl from mybb_posts p
// INNER JOIN mybb_users u ON u.uid = p.uid
// INNER JOIN
//       (SELECT fid as fff FROM mybb_forums WHERE concat(',',parentlist,',') LIKE '%,14,%' OR concat(',',parentlist,',') LIKE '%,20,%') as f
//       ON fff = fid
//       and p.uid = {$thisuser}
//       and fid not in (24,49)"), "anzahl");

//ausgabe mit {$ingamepost} im member_profile tpl.
}

/**
 * misc -> grift auf misc.php zu, hier kann man sich eigene Seiten definieren,
 * die dann z.B. mit /misc.php?action=seitenname zu erreichen sind
 * zum Beispiel auch eigene Listen etc.
 */

$plugins->add_hook("misc_start", "custom_mybb_member_misc", 1);
function custom_mybb_member_misc()
{
  global $db, $mybb, $templates, $headerinclude, $header, $footer, $page;

  //Bsp für Teamseite
  if ($mybb->get_input('action') == 'teampage') {
    eval("\$page = \"" . $templates->get("custom_mybb_teampage") . "\";");
    output_page($page);
    die();
  }
}

/**
 * Dieser Hook greift auf dem Index und hier könnt ihr Infos z.B. 
 * über dem Ingamebereich anzeigen lassen. 
 * Wichtig ist hier der Parameter in der Klammer der Funktion
 * Außerdem könnt ihr sachen nur über das Array ausgeben lassen
 */
$plugins->add_hook('build_forumbits_forum', 'custom_mybb_forumbit');
function custom_mybb_forumbit(&$forum)
{
  global $db, $templates;
  //für 1 die id des Forums einsetzen, über dem es angezeigt werden soll
  if ($forum['fid'] == 1) {
    $forum['bezeichner'] = eval($templates->render('wanted_forumbit'));
  }
  // {$forum['bezeichner']} dann ins template einfügen
}

/**
 * Hilfsfunktion um alle Charaktere zu bekommen, die zu einer UID gehören
 * Diese funktion verwendet keine Haken, aber kann in anderen Funktionen in der Datei benutzt werden
 * gibt array mit key = uid und value = username zurück 
 * @param int 
 * @return array
 */
function mybb_custom_get_allchars($uid)
{
  global $mybb, $db;

  //Array initialisieren
  $charas = array();
  //testen ob der accountswitcher installiert ist
  $user = get_user($uid);
  if ($db->field_exists("as_uid", "users")) {
    if ($uid != 0) {
      $as_uid = $user['as_uid'];

      if ($as_uid == 0) {
        // as_uid = 0 wenn hauptaccount oder keiner angehangen
        $get_all_users = $db->query("SELECT uid,username FROM " . TABLE_PREFIX . "users WHERE ( (as_uid = $uid) OR (uid = $uid) ) ORDER BY username");
      } else if ($as_uid != 0) {
        //id des users holen wo alle an gehangen sind 
        $get_all_users = $db->query("SELECT uid,username FROM " . TABLE_PREFIX . "users WHERE ((as_uid = $as_uid) OR (uid = $uid) OR (uid = $as_uid)) ORDER BY username");
      }
      //Wir speichern jetzt alle Charas in einem Array
      while ($users = $db->fetch_array($get_all_users)) {
        $uid = $users['uid'];
        $charas[$uid] = $users['username'];
      }
    }
  } else {
    //wenn der switcher nicht installiert ist nur array mit uid und username der übergebenen uid
    $charas[$uid] = $user['username'];
  }
  //und geben es zurück
  return $charas;
}
