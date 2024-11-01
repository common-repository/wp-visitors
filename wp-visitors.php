<?php
/*  
  Plugin Name: wp-visitors
  Version: 1.0
  Plugin URI: http://sgowtham.net/blog/2008/12/06/wordpress-plugin-to-keep-track-of-visitors/
  Description: This plugin will keep track of visitors' information. Check <a href="index.php?page=wp-visitors/wp-visitors.php">this page</a> for details. Requires at least 2.6.3 and tested upto 2.7.
  Author: Gowtham
  Author URI: http://sgowtham.net/
*/
?>

<?php
/*  Copyright 2007  Gowtham (email: mail@sgowtham.net)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
?>


<?php 
function initialize_wpvisitors() {
?>  

  <div class="wrap">  
  <h2>Who Came From Where, When &amp; What Did They See?</h2>
  <?php show_wpvisitors(); ?>
  </div>  

<?php 
}
?>

<?php
# Add to submenu in index.php
function submenu_wpvisitors() {  
  add_submenu_page('index.php', 'Visitor Details Page', 'Visitor Details', 8, __FILE__, 'initialize_wpvisitors');  
}  

# The Hook  
add_action('admin_menu', 'submenu_wpvisitors');
?>


<?php
# Create Table
function createtable_wpvisitors() {

  global $table_prefix, $wpdb;

  $wpvisitors_table = $table_prefix . "wpvisitors";

  if($wpdb->get_var("show tables like '$wpvisitors_table'") != $wpvisitors_table) {

    $sql0  = "CREATE TABLE `". $wpvisitors_table . "` ( ";
    $sql0 .= "  `visitor_id`       int(11)      NOT NULL auto_increment, ";
    $sql0 .= "  `visitor_date`     date         NOT NULL default '0000-00-00', ";
    $sql0 .= "  `visitor_time`     time         NOT NULL default '00:00:00', ";
    $sql0 .= "  `visitor_browser`  varchar(255) NOT NULL default '', ";
    $sql0 .= "  `visitor_requrl`   varchar(255) NOT NULL default '', ";
    $sql0 .= "  `visitor_refurl`   varchar(255)          default '', ";
    $sql0 .= "  `visitor_ip`       varchar(255) NOT NULL default '', ";
    $sql0 .= "  `visitor_hostname` varchar(255) NOT NULL default '', ";
    $sql0 .= "  `visitor_country`  varchar(255)     NULL default '', ";
    $sql0 .= "  `visitor_state`    varchar(255)     NULL default '', ";
    $sql0 .= "  `visitor_city`     varchar(255)     NULL default '', ";
    $sql0 .= "  `visitor_zipcode`  varchar(20)      NULL default '', ";
    $sql0 .= "  UNIQUE KEY `visitor_id` (`visitor_id`) ";
    $sql0 .= ") ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ; ";

    require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
    dbDelta($sql0);
  }
}

# The Hook
add_action('wp_footer', 'createtable_wpvisitors');
?>


<?php
# Record Visitors
function record_wpvisitors() {

  global $table_prefix, $wpdb;

  $wpvisitors_table = $table_prefix . "wpvisitors";
  $today            = date("Y-m-d");
  $time             = date("H:i:s");
  $agent            = $_SERVER['HTTP_USER_AGENT'];
  $requrl           = $_SERVER['REQUEST_URI'];
  $refurl           = $_SERVER['HTTP_REFERER'];
  $ip               = $_SERVER['REMOTE_ADDR'];
  $hostname         = gethostbyaddr($ip);

  $sql1  = "INSERT INTO `" . $wpvisitors_table ."` VALUES ('', '$today', ";
  $sql1 .= "'$time', '$agent', '$requrl', '$refurl', '$ip', '$hostname', ";
  $sql1 .= "'', '', '', '')";

  $result1 = $wpdb->get_results($sql1);
}

# The Hook
add_action('wp_footer', 'record_wpvisitors');
?>


<?php
# Show Visitors
function show_wpvisitors() {

  global $table_prefix, $wpdb;

  $wpvisitors_table = $table_prefix . "wpvisitors";
  $today            = date("Y-m-d");

  # Total number of hits so far
  $total_visitors = $wpdb->get_var("SELECT COUNT(visitor_id) FROM $wpvisitors_table ;");

  # Total number of hits today
  $sql1  = "SELECT * FROM ". $wpvisitors_table ." WHERE ";
  $sql1 .= "visitor_date LIKE \"%$today%\" AND ";
  $sql1 .= "visitor_requrl NOT LIKE \"%favicon%\" ORDER BY ";
  $sql1 .= "visitor_time DESC ";

  $visitor = $wpdb->get_results($sql1);

  if ( !empty($visitor) ) {
?>

  <p>So far, there have been <b><?php echo $total_visitors ?></b> hits and 
  the following were recorded today.</p>

  <table align="center" width="100%" cellpadding="5" cellspacing="5" border="0">
  <tr>
    <th score="col"><?php _e('#') ?></th>
    <th score="col"><?php _e('Date') ?></th>
    <th score="col"><?php _e('Time') ?></th>
    <th score="col"><?php _e('Details/Where From') ?><br><?php _e('ReqURL | RefURL | IP | Hostname') ?></th>
    <th score="col"><?php _e('Browser Details') ?></th>
  </tr>

<?php
    $i = 1;
    foreach ($visitor as $vdetails ) {
      $i = str_pad($i, 5, "0", STR_PAD_LEFT);

      if ($i % 2 == 0) {
        $bgcolor = '#ffffff';
      } else {
        $bgcolor = '#f4f4f4';
      }
?>

  <tr>
    <td bgcolor="<?php echo $bgcolor ?>" valign="top">
    <?php echo $i ?>
    </td>

    <td bgcolor="<?php echo $bgcolor ?>" valign="top">
    <?php echo $vdetails->visitor_date ?>
    </td>

    <td bgcolor="<?php echo $bgcolor ?>" valign="top">
    <?php echo $vdetails->visitor_time ?>
    </td>

    <td bgcolor="<?php echo $bgcolor ?>" valign="top">
    <?php echo $vdetails->visitor_requrl ?>
    <br>
    <?php echo $vdetails->visitor_refurl ?>
    <br>
    <?php echo "<a href=\"http://ws.arin.net/cgi-bin/whois.pl?queryinput=$vdetails->visitor_ip\" target=\"_blank\">$vdetails->visitor_ip</a>" ?> 
    <br>
    <?php echo $vdetails->visitor_hostname ?>
    </td>

    <td bgcolor="<?php echo $bgcolor ?>" valign="top">
    <?php echo $vdetails->visitor_browser ?>
    </td>
  </tr>

<?php
      $i++;
    }
?>
  </table>
<?php
  }
}
?>
