<?php /* 
Plugin Name: Show Only Per Category 
Plugin URI: http://coderspress.com/forum/hide-custom-fields/
Description: Shows only selected fields on Add Listing template, based on Category. PremiumPress 6.6.5 | 8.6
Version: 2015.0915
Updated: 15th September 2015 
Author: sMarty
Author URI: http://coderspress.com
WP_Requires: 3.8.1
WP_Compatible: 4.3.0
License: http://creativecommons.org/licenses/GPL/2.0
*/ 
add_action( 'init', 'chf_plugin_updater' );
function chf_plugin_updater() {
	if ( is_admin() ) { 
	include_once( dirname( __FILE__ ) . '/updater.php' );
		$config = array(
			'slug' => plugin_basename( __FILE__ ),
			'proper_folder_name' => 'cat-hide-fields',
			'api_url' => 'https://api.github.com/repos/CodersPress/hide-custom-fields',
			'raw_url' => 'https://raw.github.com/CodersPress/hide-custom-fields/master',
			'github_url' => 'https://github.com/CodersPress/hide-custom-fields',
			'zip_url' => 'https://github.com/CodersPress/hide-custom-fields/zipball/master',
			'sslverify' => true,
			'access_token' => 'bfc28380ba54a471c2dc7bd2211abbb5cdf76cd5',
		);
		new WP_CHF_UPDATER( $config );
	}
}
add_action('admin_menu', 'show_only_create_menu'); 

function show_only_create_menu() { 
	add_menu_page('SHOW Field Settings', 'Fields IF-Category', 'administrator', __FILE__, 'show_only_setup_page',plugins_url('/images/list.gif', __FILE__)); 
	add_action( 'admin_init', 'register_show_only_setup_settings' );
} 

function register_show_only_setup_settings() {
   	register_setting("show-only-settings-group", "show_only_alert_message");
}

register_activation_hook( __FILE__, 'dbtable_install' ); 

function dbtable_install() { 
global $wpdb; 
$table_name = $wpdb->prefix . "show_field_only"; 
$charset_collate = ''; 
if ( ! empty( $wpdb->charset ) ) { 
$charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}"; 
} 
if ( ! empty( $wpdb->collate ) ) { 
$charset_collate .= " COLLATE {$wpdb->collate}"; 
} 
$sql = "CREATE TABLE $table_name ( id mediumint(9) NOT NULL AUTO_INCREMENT, field_label text NOT NULL, category text NOT NULL, UNIQUE KEY id (id) ) $charset_collate;"; 
require_once( ABSPATH . 'wp-admin/includes/upgrade.php' ); dbDelta( $sql ); 

show_only_setup_defaults();
} 

function show_only_setup_defaults()
{
    $option = array(
        "show_only_alert_message" => "Please complete Category Selection"
    );
  foreach ( $option as $key => $value )
    {
       if (get_option($key) == NULL) {
        update_option($key, $value);
       }
    }
    return;
}


function show_only_setup_page() { ?>

<script type="text/javascript" src="<?php echo plugins_url('/cat-hide-fields/js/jquery-ui.min.js');?>"></script>
<script type="text/javascript" src="<?php echo plugins_url('/cat-hide-fields/js/ui.dropdownchecklist.js');?>"></script>
<style>
    .odd {
        background-color: #f5f5f5;
    }
    p {
        width: 474px;
        background-color: #fff;
        padding: 6px 12px;
        border: 1px solid #DDD;
    }
    .widefat thead tr th {
        color: #fff;
        font-size: 16px;
    }
    .widefat td {
        font-size: 16px;
        padding: 6px 12px;
        line-height: 21px;

    }
.ui-dropdownchecklist {
	font-size: medium;
	color: black;
}
.ui-dropdownchecklist-selector {
	height: 20px;
	border: 1px solid #ddd;
	background: #fff;
}
.ui-state-hover, .ui-state-active {
	border-color: #5794bf;
}
.ui-dropdownchecklist-dropcontainer {
	background-color: #fff;
	border: 1px solid #999;
}
.ui-dropdownchecklist-item {
}
.ui-state-hover {
	background-color: #2EA2CC;
}
.ui-state-disabled label {
	color: #ccc;
}
.ui-dropdownchecklist-group {
	font-weight: bold;
	font-style: italic;
}
.ui-dropdownchecklist-indent {
	padding-left: 17px;
}
/* Font size of 0 on the -selector and an explicit medium on -text required to eliminate 
   descender problems within the containers and still have a valid size for the text */
.ui-dropdownchecklist-selector-wrapper {
	vertical-align: middle;
	font-size: 14px;
}
.ui-dropdownchecklist-selector {
	padding: 1px 2px 2px 2px;
	font-size: 14px;
}
.ui-dropdownchecklist-text {
	font-size: 14px;
	/* line-height: 20px; */
}
.ui-dropdownchecklist-group {
	padding: 2px 2px 2px 2px;
}

</style>
<div class="wrap">
    <h2>Show Only Setup Page</h2>
    <hr />

<?php
if ($_REQUEST['settings-updated']=='true') {
echo '<div id="message" class="updated fade"><p><strong>MESSAGE SAVED</strong></p></div>';
}
?>

<form method="post" action="options.php">
    <?php settings_fields("show-only-settings-group");?>
    <?php do_settings_sections("show-only-settings-group");?>
    <table class="widefat" style="width:500px;">

<h3>Alert Message Settings</h3>
                <p style="background:#2EA2CC;color:#fff;font-size:16px;">Appears when a users clicks Details before selecting a Category.</p>

<tr>
<td><input type="text" size="40" id="show_only_alert_message" name="show_only_alert_message" value="<?php echo get_option("show_only_alert_message");?>"/></td>
<td><input type="submit" value="SET" /></td>
</tr>

  </table>
</form>
</div>
<br />
<hr>

    <h3>Show Field if Category</h3>
    <p>Assign field labels multiple times to different categories. Once a field label is assigned, it will not show on any other category. You must insure you assign to all desired categories, including child_of parent categories.</p>
    <table class="widefat" style="width:500px;">
        <thead style="background:#2EA2CC;">
            <tr>
                <th>Field Label</th>
                <th>Show Only On Category</th>
                <th>SET Condition</th>
            </tr>
        </thead>
        <form id="set_condtion" name="set_condtion" method="post" action="">
            <tr>
                <td>
                    <select name="field_name" id="field_name" class="postform">
                        <?php $submissionfields=get_option( "submissionfields"); foreach($submissionfields as $key=>$field){ ?>
                        <option value="<?php echo $field['name']; ?>">
                            <?php echo $field[ 'name']; ?>
                        </option>
                        <?php } ?>
                    </select>
                </td>
                <td>
                    <?php $args=array( 'show_option_all'=>'', 
                                'show_option_none' => '', 
                                'orderby' => 'id', 
                                'order' => 'ASC', 
                                'hide_empty' => 0, 
                                'child_of' => 0, 
                                'exclude' => '', 
                                'echo' => 1, 
                                'selected' => 0, 
                                'hierarchical' => 1, 
                                'name' => 'cat[]', 
                                'id' => 'cat', 
                                'class' => 'postform', 
                                'depth' => 0, 
                                'tab_index' => 0, 
                                'taxonomy' => THEME_TAXONOMY, 
                                'hide_if_empty' => false, 
                                ); 
                               wp_dropdown_categories($args); ?>
                </td>
                <td>
                    <input id="set" type="submit" value="SET" />
        </form>
        </td>
        </tr>
<script>
jQuery(document).ready(function( $ ) {
$("#cat").dropdownchecklist( { width: 220, maxDropHeight: 250, forceMultiple: true, onComplete: function(selector) {
        var values = "";
        for( i=0; i < selector.options.length; i++ ) {
            if (selector.options[i].selected && (selector.options[i].value != "")) {
                if ( values != "" ) values += ",";
                values += selector.options[i].value;
            }
        }
    } });
});
</script>
    </table>
    <?php global $wpdb; 
                if (!isset($_POST[ 'field_name']) ) { 
                   echo "<p></p>"; 
                } else {
                   $field=$_POST[ 'field_name']; 
                   $cat_number = $_POST[ 'cat'];
                foreach($cat_number as $key) {    
                   $table_name=$wpdb->prefix . "show_field_only"; 
                   $wpdb->insert( $table_name, array( 'field_label' => $field, 'category' => $key ) );   
                }
                echo "<p style='color:green;'>Success: Field Label set to Category below...</p>"; 
                } 
               if (isset($_POST['field_id']) ) { global $wpdb; $table_name = $wpdb->prefix . "show_field_only"; $wpdb->query($wpdb->prepare( "DELETE FROM " . $table_name . " WHERE id=%d",$_POST['field_id'] )); } ?>
    <br />
    <hr />
    <h3>Current: Set Conditions</h3>
    <p>Click DELETE to remove a Field Condition.</p>
    <table class="widefat" style="width:500px;">
        <thead style="background:#2EA2CC;">
            <tr>
                <th>Field Label</th>
                <th>Only Shows On Category</th>
                <th>Remove Condition</th>
            </tr>
        </thead>
        <?php global $wpdb;
            $table_name=$wpdb->prefix . "show_field_only"; 
               $sql = "SELECT * FROM " . $table_name . " "; 
                  $field_conditions = $wpdb->get_results($sql); 
                      foreach($field_conditions as $conditions) { 
                      $field_id = $conditions->id; 
                      $field_label = $conditions->field_label; 
                      $category = $conditions->category; 
                      $term = get_term_by('id', $category, THEME_TAXONOMY); 
                      $name = $term->name; 
         ?>
        <tr class="<?=($c++%2==1)?'odd':NULL?>">
            <td>
                <?php echo $field_label ?>
            </td>
            <td>
                <?php echo $name ?>
            </td>
            <td>
                <form name="delete" id="delete" method="post" action="#">
                    <input type="hidden" value="<?php echo $field_id ?>" name="field_id">
                    <input type="submit" value="DELETE" />
                </form>
            </td>
        </tr>
        <?php } ?>
    </table>
</div>
<?php } 
add_action( 'wp_footer', 'hide_fields');

function hide_fields(){

$my_theme = wp_get_theme();
$theme_version = $my_theme->get( 'Version' );

if ( is_page() ) {

if ( $theme_version <= "8.4" ) {

if ( is_user_logged_in() ) {
 if (stripos($_SERVER['REQUEST_URI'],'eid=') !== false) {$step = '3';} else {$step = '4';}
} else { $step = '5'; } 

} else {

$step = '2'; 

}
?>
<script> 
jQuery(document).ready(function () {

var estep = '<?php echo $step;?>';

 var category = jQuery(".tcbox input:checked").val();
  if (category === undefined) {
            jQuery('.astep'+estep).removeAttr('href');
        } else {
            jQuery('.astep'+estep).attr('href', '#step'+estep);
        }
    jQuery('.astep'+estep).click(function () {
       var category = jQuery(".tcbox input:checked").val();
       if(category === undefined){
        jQuery('.astep'+estep).removeAttr('href');
         } else { jQuery('.astep'+estep).attr('href') } 
        if (jQuery('.astep'+estep).attr('href') === undefined) {
            alert('<?php echo get_option("show_only_alert_message");?>');
        } else { <?php global $wpdb;
            $table_name = $wpdb->prefix."show_field_only";
            $sql = "SELECT * FROM ".$table_name." ";
            $category_conditions = $wpdb->get_results($sql); ?>
            var db_conditions = jQuery.parseJSON('<?php echo json_encode($category_conditions); ?>');
			var fieldNames = "";
            jQuery.each(db_conditions, function (i, val) {
                var check_cat = val.category;
                var FieldToTreat = val.field_label;
                if (category == check_cat) {
                    jQuery(".customfield").filter(":contains(" + FieldToTreat + ")").show();
					fieldNames += val.field_label;
				} 
				if (fieldNames.indexOf(val.field_label) < 0){
				jQuery(".customfield").filter(":contains(" + FieldToTreat + ")").hide().find("input, textarea").attr('id','');
                jQuery('form .btn-primary').attr('disabled', false);
				}
            });
        }
    });
    jQuery('.tcbox').click(function () {
     var category = jQuery(".tcbox input:checked").val();
        if (category === undefined) {
            jQuery('.astep'+estep).removeAttr('href');
        } else {
            jQuery('.astep'+estep).attr('href', '#step'+estep);
        }
    });
});
</script>
<?php
}
}
?>