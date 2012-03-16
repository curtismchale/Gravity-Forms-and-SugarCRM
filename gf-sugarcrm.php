<?php
/*
Plugin Name: SugarCRM Gravity Forms
Plugin URI: 
Description: Adds Support for SugarCRM to a Gravity Forms Form
Version: 0.1
Author: WP Theme Tutorial Curis McHale
Author URI: http://wpthemetutorial.com
License: GPLv2 or later
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

/**
 * Including the SOAP SugarCRM toolkit
 */
require_once( plugin_dir_path( __FILE__ ) . '/nusoap/lib/nusoap.php' );

/**
 * Wrapper class for SugarCRM Web Services
 *
 * @link http://kovshenin.com/2010/05/lead-generation-forms-with-wordpress-and-sugarcrm-2287/
 */
class SugarCRMWebServices {
  // Let's define a place to store our access credentials
  var $username;
  var $password;

  // We'll store the session ID here for later use
  var $session;

  // We'll initialize a new NuSOAP Client object into this one
  var $soap;

  // Constructor (PHP4-style)
  function SugarCRMWebServices($username, $password)
  {
    // Copy the credentials into our containers and initialize NuSOAP
    $this->username = $username;
    $this->password = $password;
    // Replace the URL with your copy of SugarCRM
    $this->soap = new nusoap_client('younusoapclient');
  }

  // Login function which stores our session ID
  function login()
  {
    $result = $this->soap->call('login', array(
      'user_auth' => array(
        'user_name' => $this->username,
        'password'  => md5($this->password),
        'version'   => '.01'
        ),
      'application_name' => 'My Application'));
    $this->session = $result['id'];
  }

  // Create a new Lead, return the SOAP result
  function createLead($data)
  {
    // Parse the data and store it into a name/value list
    // which will then pe passed on to Sugar via SOAP
    $name_value_list = array();
    foreach($data as $key => $value)
      array_push($name_value_list, array('name' => $key, 'value' => $value));

    // Fire the set_entry call to the Leads module
    $result = $this->soap->call('set_entry', array(
      'session'         => $this->session,
      'module_name'     => 'Leads',
      'name_value_list' => $name_value_list
    ));

    return $result;
  }
}

/**
 * Sends any submitted Gravite form to Sugar CRM.
 * You can make it work with only one form by adding
 * the from ID to the end of the action. So form 4
 * would be 'gform_after_submission_4'
 *
 * @param     array  $entry  req  The whole form entry from Gravity Forms
 * @uses      createLead
 *
 * @return    null
 *
 * @author    WP Theme Tutorial, Curtis McHale
 * @since     SugarCRM Gravity Forms 0.1
 *
 * @link      http://wp.me/p1Fud2-6P
 *
 */
function theme_t_wp_send_info_to_sugar( $entry ){

  $sugar = new SugarCRMWebServices( $your_username, $your_password );

  // Login  and create a new lead
  $sugar->login();
  $result = $sugar->createLead(array(
    'lead_source'             => 'Form Name',
    'lead_source_description' => 'Description',
    'lead_status'             => 'New',
    'first_name'              => $entry[1],
    'last_name'               => $entry[2],
    'account_name'            => $entry[3],
    'website'                 => $entry[4],
    'email'                   => $entry[5],
    'phone_work'              => $entry[6],
    'number_of_clients'       => $entry[7],
    'description'             => $entry[8],
  ));

}
add_action( 'gform_after_submission', 'theme_t_wp_send_info_to_sugar', 10, 2 );
?>
