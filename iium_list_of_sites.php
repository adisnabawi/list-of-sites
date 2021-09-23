<?php
/**
 * Plugin Name: List of Sites
 * Plugin URI: https://github.com/adisnabawi/list-of-sites
 * Description: Display content using a shortcode to insert a list of sites
 * Version: 0.1
 * Text Domain: list_of_sites_adis
 * Author: Adis Nabawi, Azizan
 * Author URI: https://adisazizan.xyz
 */


function iium_list_sites_plugin($atts) {
    $a = shortcode_atts( array(
		'id' => 1
	), $atts );
    global $wpdb;
    $table_name = $wpdb->prefix . 'iium_site_list_url';
    $sql = "SELECT * FROM ". $table_name . " where id=%d";
  
    $row = $wpdb->get_results($wpdb->prepare($sql, array($a['id'])));
    $content = '';
    $url = $row[0]->url;  
    $response = wp_remote_get($url);
    $body = [];
    if ( is_array( $response ) && ! is_wp_error( $response ) ) {
        $body    = $response['body']; 
    }
    $result = json_decode($body);
    $content .= '<div class="row">';
    foreach($result as $key => $res) {
        if($key != 0 ){
            $content .= '<div class="col-md-4">';
            $content .= '<div class="card iiumcard">';
            $content .= '<div class="card-body">';
            $content .= '<h5>' . $res->name . '</h5>';
            $content .= '<p>' . $res->description . '</p>';
            $content .= '<a href="' . $res->url . '" class="btn btn-success iiumcolor">Go to Site</a>';
            $content .= '<br><br><p class="text-right"><small>Last updated: <i>' . $res->last_updated . '</i></small></p>';
            $content .= '</div></div></div>';
        }   
    }
    $content .= '</div';
    
    return $content;
        
}

add_shortcode('iium_list_of_sites', 'iium_list_sites_plugin');


function iiumlistsites_styles() {
    wp_enqueue_style( 'styles',  plugin_dir_url( __FILE__ ) . 'css/iiumlistsite.css' );                      
}

function iiumlistsites_styles_script() {
    wp_enqueue_style( 'styles',  plugin_dir_url( __FILE__ ) . 'css/iiumlistsite.css' );     
    wp_enqueue_script( 'script',  plugin_dir_url( __FILE__ ) . 'js/iiumlistsite.js' );                     
}

function iium_list_of_sites_main() {
  
    global $wpdb;
    $table_name = $wpdb->prefix . 'iium_site_list_url';

    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table_name (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
    url varchar(255) DEFAULT '' NOT NULL,
    PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
    $content = '<div id="wpbody-content">';
    $content .= "<h2>Listing of sites in page</h2>";
    
    $sqllist = "SELECT * FROM ". $table_name;
    $all= $wpdb->get_results($wpdb->prepare($sqllist));
    
    $content .= '<table class="iiumlistingform">';
    $content .= '<tr>';
    $content .= '<th>URL</th>';
    $content .= '<th>Short Code</th>';
    $content .= '<th>Action</th>';
    $content .= '</tr>';
    foreach($all as $li){
        $content .= "<tr>";
        $content .= "<td>";
        $content .= "<input type=\"text\" value=\"".$li->url."\" disabled>";
        $content .= "</td>";
        $content .= "<td>";
        $content .= "<input type=\"text\" value=\"[iium_list_of_sites id=" . $li->id . "]\" id='clipiium-". $li->id ."'>";
        $content .= "</td>";
        $content .= "<td class='center'>";
        $content .= "<button class=\"clipiiumbtn\" onclick=\"copyclip(" .$li->id . ")\">Copy Short Code</button>";
        $content .= "<form action=\"" . $_SERVER['REQUEST_URI'] ."\" method=\"post\">";
        $content .= "<input type=\"hidden\" value=\"".$li->id."\" name='deleteAPI'>";
        $content .= "<input type='submit' class=\"deleteiiumlist\" value='Delete'>";
        $content .= "</form>";
        $content .= "</td>";
        $content .= "</tr>";
        
    }
    $content .= '</table>';

    $content .= "<p>Please enter API URL: </p>";
    $content .= "<form action=\"" . $_SERVER['REQUEST_URI'] ."\" method=\"post\">";
    $content .= "<input type=\"text\" placeholder=\"Enter API URL\" name=\"apiUrl\">";
    $content .= "<input type=\"submit\" value=\"Save\" style=\"padding:5px\">";
    $content .= "</form>";
    $content .= "</div>";
    echo $content;
    if (isset($_POST['apiUrl'])) {

        $url = sanitize_text_field($_POST['apiUrl']);
        
        $errors = [];
        $msgs = [];
        
        $wpdb->insert($table_name, array(
            'url' => $url
        ));
        $ils_lastInsert_id = $wpdb->insert_id;
        
        if (!empty($ils_lastInsert_id)) {
            $msgs[] = "Shortcode inserted succesfully";
            echo "<meta http-equiv='refresh' content='0'>";
        } else {
            $errors[] = "DB insert failed";
        }
    }

    if(isset($_POST['deleteAPI'])){
        $id = sanitize_text_field($_POST['deleteAPI']);
        
        $wpdb->delete($table_name, array(
            'id' => $id
        ));
        echo "<meta http-equiv='refresh' content='0'>";
    }
   
  }
  add_action('admin_menu', 'iium_list_of_sites_menu');
  function iium_list_of_sites_menu() {
    add_menu_page( 'List of Sites', 'List Sites', 'manage_options', 'iium-list-of-sites', 'iium_list_of_sites_main' );
  }

add_action( 'wp_enqueue_scripts', 'iiumlistsites_styles' );
add_action( 'admin_enqueue_scripts', 'iiumlistsites_styles_script' );


