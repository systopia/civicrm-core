<?php

function contact_civitoken_declare($token){
  return array(
    $token . '.country_iso' => 'Country ISO code',
    $token . '.main_contact_name' => 'Main contact name',
    $token . '.main_contact_position' => 'Main contact position',
    $token . '.leader_contact_name' => 'Leader contact name',
    $token . '.leader_contact_position' => 'Leader contact position',
    $token . '.communications_contact_name' => 'Communications contact name',
    $token . '.communications_contact_position' => 'Communications contact position',
    $token . '.development_contact_name' => 'Development contact name',
    $token . '.development_contact_position' => 'Development contact position',
    $token . '.address_block' => 'Address Block',
   );
}

/*
 * based on Michael McAndrew's code but updated to use v3 & to only query for required fields
*https://github.com/michaelmcandrew/civicrm-addressblocktoken/blob/master/addressblocktoken.module
*/
function contact_civitoken_get($cid, &$value, $html_out){
  //$contact = civicrm_api('contact','getsingle', array('version' =>3, 'id' => $cid, 'return' => 'country'));
  $result = civicrm_api('Contact', 'get', array('version' => 3, 'sequential' => 1, 'id' => $cid));
  if ($result['is_error'] == 0) {
    $contact = $result['values'][0];
  }
  $value['contact.address_block'] = CRM_Utils_Address::format($contact, NULL, FALSE, FALSE, FALSE, NULL, TRUE);
  if ($html_out) {
    $value['contact.address_block'] = nl2br($value['contact.address_block']);
  }  
  require_once 'CRM/Core/PseudoConstant.php';
  $countries = CRM_Core_PseudoConstant::countryIsoCode();
  $value['contact.country_iso'] = $countries[$contact['country_id']];
  $relationships = civicrm_api("Relationship","get", array('version' => '3', 'contact_id' => $cid));
  foreach ($relationships['values'] as $rid => $relationship) {
    if ($relationship['relationship_type_id'] == '11' && $relationship['rtype'] == 'b_a') {
      if (!empty($relationship['issues'])) {
        foreach ($relationship['issues'] as $issue) {
          if ($issue == 'Main contact') {
            $value['contact.main_contact_name'] = $relationship['display_name'];
            $value['contact.main_contact_position'] = $relationship['position'];
          }
          if ($issue == 'Communications contact') {
            $value['contact.communications_contact_name'] = $relationship['display_name'];
            $value['contact.communications_contact_position'] = $relationship['position'];
          }
          if ($issue == 'Leader contact') {
            $value['contact.leader_contact_name'] = $relationship['display_name'];
            $value['contact.leader_contact_position'] = $relationship['position'];
          }
          if ($issue == 'Development contact') {
            $value['contact.development_contact_name'] = $relationship['display_name'];
            $value['contact.development_contact_position'] = $relationship['position'];
          }
        }
      }
    }
  }
  return $value;
}