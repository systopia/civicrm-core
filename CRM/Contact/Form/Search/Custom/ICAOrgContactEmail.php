<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2012                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2012
 * $Id$
 *
 */
class CRM_Contact_Form_Search_Custom_ICAOrgContactEmail extends CRM_Contact_Form_Search_Custom_Base implements CRM_Contact_Form_Search_Interface {
  function __construct(&$formValues) {
    parent::__construct($formValues);
    $type = CRM_Utils_Array::value('type', $this->_formValues);
    if ($type == 'individuals') {
      $this->_columns = array(
      ts('Contact ID') => 'contact_id',
      ts('Name') => 'sort_name',
      ts('Primary email') => 'email_address',
      ts('Organisation') => 'org_name',
      ts('Status') => 'membership_status',
      ts('Structure Type') => 'structure_type',
      ts('Org Languages') => 'org_languages',
      ts('Country') => 'country_name',
      );
    }
    else {
    $this->_columns = array(
      ts('Contact ID') => 'contact_id',
      ts('Name') => 'sort_name',
      ts('Primary email') => 'email_address',
      //ts('Org Email1') => 'org_email1',
      //ts('Org Email2') => 'org_email2',
      //ts('Org Email3') => 'org_email3',
      //ts('Org Email4') => 'org_email4',
      ts('Status') => 'membership_status',
      ts('Structure Type') => 'structure_type',
      ts('Org Languages') => 'org_languages',
      ts('Country') => 'country_name',
      //ts('Prefix') => 'individual_prefix',
      //ts('First Name') => 'individual_firstname',
      //ts('Surname') => 'individual_lastname',
      //ts('Position') => 'individual_position',
      //ts('Native Position') => 'individual_nativeposition',
      //ts('Email 1') => 'individual_email1',
      //ts('Email 2') => 'individual_email2',
      //ts('Email 3') => 'individual_email3',
      //ts('Email 4') => 'individual_email4',
      //ts('Indiv Address') => 'individual_address',
      //ts('English greeting') => 'english_greeting',
      //ts('French greeting') => 'french_greeting',
      //ts('Spanish greeting') => 'spanish_greeting',
      //ts('Address') => 'contact_address',
      //ts('Email') => 'email', 
      //ts('Country') => 'country_name',
      //ts('Issues') => 'issues',
    );
  }
      
  }

  function buildForm(&$form) {
    $form->add('text', 'sort_name', ts('Name'));
    //CRM_Core_BAO_CustomField::addQuickFormElement($form, 'region', '10', TRUE, FALSE, TRUE, 'Region');
    CRM_Core_BAO_CustomField::addQuickFormElement($form, 'superregion', '9', TRUE, FALSE, TRUE, 'Super-region');
    CRM_Core_BAO_CustomField::addQuickFormElement($form, 'member', '5', TRUE, FALSE, TRUE, 'Is ICA Member?');
    CRM_Core_BAO_CustomField::addQuickFormElement($form, 'status', '14', TRUE, FALSE, TRUE, 'Status');
    CRM_Core_BAO_CustomField::addQuickFormElement($form, 'structype', '15', TRUE, FALSE, TRUE, 'Structure type');
    // add select for categories
    $tag = array('' => ts('- any tag -')) + CRM_Core_PseudoConstant::tag();
    //$form->addElement('select', 'tag', ts('Tagged'), $tag);
    CRM_Core_BAO_CustomField::addQuickFormElement($form, 'language', '7', TRUE, FALSE, TRUE, 'Language');

    $children_options = array(
      0 => '0',
      1 => '1',
      2 => '2',
      3 => '3',
      4 => '4',
      5 => '5',
      //6 => '6',
    );
    //$form->add('select', 'children', ts('Include children? Number of levels'), $children_options);
    /*
    $rel_types = CRM_Contact_BAO_Relationship::getContactRelationshipType(NULL, NULL, NULL, 'Individual', FALSE, 'label', FALSE);
    $rel_options = array();
    foreach ($rel_types as $key => $value) {
      $key_parts = explode('_', $key);Main contactLeader contact
      if (($key_parts[0] == 4) || ($key_parts[0] >= 12 && $key_parts[0] <= 20)) {
        $rel_options[$key_parts[0]] = $value;
      }
    }
    ksort($rel_options, SORT_NUMERIC);
    //$rel_types = CRM_Contact_BAO_Relationship::getRelationType('Individual');
    //CRM_Core_Error::debug_var('rel_options', $rel_options);
    $form->addElement('select', 'relationships', ts('Include related individuals'), $rel_options, array('size' => '10', 'multiple'));
    */
    //CRM_Core_BAO_CustomField::addQuickFormElement($form, 'issues', '20', TRUE, FALSE, TRUE, 'Issues');
    
    $contact_type = array(
      //'all' => 'All - Organisations and Individuals',
      'organisations' => 'Organisations only',
      'individuals' => 'Main contacts only',
      'orgs_wo_contacts' => 'Only organisations without any main contacts',
    );
    $form->add('select', 'type', ts('Contacts to include'), $contact_type);
    
    /**
     * You can define a custom title for the search form
     */
    $this->setTitle('ICA Orgs with Main Contacts for Emailing');

    /**
     * if you are using the standard template, this array tells the template what elements
     * are part of the search criteria
     */
    $form->assign('elements', array('sort_name', 'member', 'status', 'structype', 'language', 'superregion', 'type'));
    //$form->setDefaults(array('issues' => array('Main contact' => '1', 'CiviCRM_OP_OR' => '1')));
    //krumo($form);
  }

  function summary() {
    $summary = array();
    return $summary;
  }
  
  /*
  IF( 
        LOCATE(',', SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT indiv_email.email SEPARATOR ','), ',', 2)) != 0,
        SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT indiv_email.email SEPARATOR ','), ',', 2), ',', -1),
        ''
      )
      */
  
  function all($offset = 0, $rowcount = 0, $sort = NULL, $includeContactIDs = TRUE, $justIDs = FALSE) {
    //$group_by = "GROUP BY contact_a.id, indiv_contact.id";
    $sort = "sort_name ASC";
    $selectClause = "
      contact_a.id			as contact_id,
      email.email                       as email_address,
      contact_a.sort_name 		as sort_name,
      REPLACE(TRIM(CHAR(1) FROM org_location.languages_7), CHAR(1), ', ')		as org_languages,
      status.structure_type_15		as structure_type,
      REPLACE(TRIM(CHAR(1) FROM status.statuses_14 ), CHAR(1), ', ') as membership_status,
      country.name as country_name
      ";
    $type = CRM_Utils_Array::value('type', $this->_formValues);
    if ($type == 'individuals') {
      $selectClause .= ", org_contact.sort_name as org_name";
    }
    /*
    $selectClause = "
      contact_a.id			as contact_id ,
      contact_a.sort_name 		as sort_name,
      SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT org_email.email SEPARATOR ','), ',', 1) as org_email1,
      IF( 
        LOCATE(',', SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT org_email.email SEPARATOR ','), ',', 2)) != 0,
        SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT org_email.email SEPARATOR ','), ',', 2), ',', -1),
        ''
      ) as org_email2,
      IF(
        SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT org_email.email SEPARATOR ','), ',', 3)
          NOT LIKE SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT org_email.email SEPARATOR ','), ',', 2),
        SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT org_email.email SEPARATOR ','), ',', 3), ',', -1),
        ''
      ) as org_email3,
      IF(
        SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT org_email.email SEPARATOR ','), ',', 4)
          NOT LIKE SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT org_email.email SEPARATOR ','), ',', 3),
        SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT org_email.email SEPARATOR ','), ',', 4), ',', -1),
        ''
      ) as org_email4,
      REPLACE(TRIM(CHAR(1) FROM org_location.languages_7), CHAR(1), ', ')		as org_languages,
      status.structure_type_15		as structure_type,
      indiv_prefix.name			as individual_prefix,
      indiv_contact.first_name		as individual_firstname,
      indiv_contact.last_name		as individual_lastname,
      rel_issues.position_3		as individual_position,
      rel_issues.native_position_4	as individual_nativeposition,
      REPLACE(TRIM(CHAR(1) FROM rel_issues.issues_20), CHAR(1), ', ') as issues,
      SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT indiv_email.email SEPARATOR ','), ',', 1) as individual_email1,
      IF( 
        LOCATE(',', SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT indiv_email.email SEPARATOR ','), ',', 2)) != 0,
        SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT indiv_email.email SEPARATOR ','), ',', 2), ',', -1),
        ''
      ) as individual_email2,
      IF(
        SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT indiv_email.email SEPARATOR ','), ',', 3)
          NOT LIKE SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT indiv_email.email SEPARATOR ','), ',', 2),
        SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT indiv_email.email SEPARATOR ','), ',', 3), ',', -1),
        ''
      ) as individual_email3,
      IF(
        SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT indiv_email.email SEPARATOR ','), ',', 4)
          NOT LIKE SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT indiv_email.email SEPARATOR ','), ',', 3),
        SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT indiv_email.email SEPARATOR ','), ',', 4), ',', -1),
        ''
      ) as individual_email4,
      CONCAT_WS('\n', indiv_maddorg.display_name, REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(IF(indiv_addformat.format IS NOT NULL, indiv_addformat.format, TRIM(TRAILING '\";' FROM SUBSTRING(setting.value, 8))), '{contact.street_address}', IF(indiv_address.street_address IS NOT NULL, indiv_address.street_address, '')), '{contact.supplemental_address_1}', IF(indiv_address.supplemental_address_1 IS NOT NULL, indiv_address.supplemental_address_1, '')), '{contact.supplemental_address_2}', IF(indiv_address.supplemental_address_2 IS NOT NULL, indiv_address.supplemental_address_2, '')), '{contact.city}', IF(indiv_address.city IS NOT NULL, indiv_address.city, '')), '{contact.postal_code}', IF(indiv_address.postal_code IS NOT NULL, indiv_address.postal_code, '')), '{contact.country}', IF(indiv_country.name IS NOT NULL, indiv_country.name, '')), '{contact.country_iso}', IF(indiv_country.iso_code IS NOT NULL, indiv_country.iso_code, '')), '{, }{ }', ' '), '{contact.addressee}', ''), '{ }\n', '\n'), '{, }', ', '), '{ }', ' '), '\r', ''), '\n\n', '\n')) as individual_address,
      indiv_contact.email_greeting_display as english_greeting,
      indiv_greetings.french_greeting_32 as french_greeting,
      indiv_greetings.spanish_greeting_33 as spanish_greeting
    ";
    */
    return $this->sql($selectClause,$offset, $rowcount, $sort, $includeContactIDs, $group_by);
  }
  
  
  

  function from() {
    $sort_name = CRM_Utils_Array::value('sort_name', $this->_formValues);
    $region = CRM_Utils_Array::value('region', $this->_formValues);
    $sregion = CRM_Utils_Array::value('superregion', $this->_formValues);
    $member = CRM_Utils_Array::value('member', $this->_formValues);
    $status = CRM_Utils_Array::value('status', $this->_formValues);
    $children = CRM_Utils_Array::value('children', $this->_formValues);
    $issues = CRM_Utils_Array::value('issues', $this->_formValues);
    $tag = CRM_Utils_Array::value('tag', $this->_formValues);
    $languages = CRM_Utils_Array::value('language', $this->_formValues);
    $type = CRM_Utils_Array::value('type', $this->_formValues);
    
    $from_clauses = array();
    if ($type == 'organisations') {
      $from_clauses[] = "
        FROM civicrm_contact contact_a
        LEFT JOIN civicrm_value_structure__status_4 status ON status.entity_id = contact_a.id
	LEFT JOIN civicrm_value_location_and_language_3 org_location ON org_location.entity_id = contact_a.id
	LEFT JOIN civicrm_country country ON country.id = org_location.country_8";
    }
    elseif ($type == 'individuals') {
      $from_clauses[] = "
        FROM civicrm_contact contact_a
        LEFT JOIN civicrm_relationship rel ON contact_a.id = rel.contact_id_a
          AND rel.relationship_type_id = 11
          AND rel.is_active = 1
        LEFT JOIN civicrm_value_position_2 rel_issues ON ( rel.id = rel_issues.entity_id )
        LEFT JOIN civicrm_contact org_contact ON org_contact.id = rel.contact_id_b
        LEFT JOIN civicrm_value_structure__status_4 status ON status.entity_id = org_contact.id
	LEFT JOIN civicrm_value_location_and_language_3 org_location ON org_location.entity_id = org_contact.id
	LEFT JOIN civicrm_country country ON country.id = org_location.country_8";
    }
    else {
      $from_clauses[] = "
        FROM civicrm_contact contact_a
        LEFT JOIN civicrm_value_structure__status_4 status ON status.entity_id = contact_a.id
	LEFT JOIN civicrm_value_location_and_language_3 org_location ON org_location.entity_id = contact_a.id
	LEFT JOIN civicrm_country country ON country.id = org_location.country_8";
    }
    $from_clauses[] = "LEFT JOIN civicrm_email email ON email.contact_id = contact_a.id
                         AND email.is_primary = 1
                         AND email.on_hold = 0";
    /*
    $from_clauses[] = "
      FROM      civicrm_contact contact_a 
      LEFT JOIN civicrm_value_structure__status_4 status ON (status.entity_id = contact_a.id)
      LEFT JOIN civicrm_relationship rel ON contact_a.id = rel.contact_id_b
        AND rel.relationship_type_id = 11
        AND rel.is_active = 1
      LEFT JOIN civicrm_contact indiv_contact ON rel.contact_id_a = indiv_contact.id
      LEFT JOIN civicrm_value_position_2 rel_issues ON ( rel.id = rel_issues.entity_id )
      LEFT JOIN civicrm_email org_email ON contact_a.id = org_email.contact_id
        AND org_email.location_type_id NOT IN (8,9)
      LEFT JOIN civicrm_option_value indiv_prefix ON indiv_contact.prefix_id = indiv_prefix.value
        AND indiv_prefix.option_group_id = 6
      LEFT JOIN civicrm_email indiv_email ON indiv_contact.id = indiv_email.contact_id
        AND indiv_email.location_type_id NOT IN (8,9)
      LEFT JOIN civicrm_address indiv_address ON indiv_contact.id = indiv_address.contact_id
        AND indiv_address.is_primary = 1
      LEFT JOIN civicrm_address indiv_maddress ON indiv_address.master_id = indiv_maddress.id
      LEFT JOIN civicrm_contact indiv_maddorg ON indiv_maddress.contact_id = indiv_maddorg.id
      LEFT JOIN civicrm_country indiv_country ON indiv_address.country_id = indiv_country.id
      LEFT JOIN civicrm_address_format indiv_addformat ON indiv_country.address_format_id = indiv_addformat.id
      INNER JOIN civicrm_setting setting ON setting.name = 'address_format'
      LEFT JOIN civicrm_value_location_and_language_3 org_location ON contact_a.id = org_location.entity_id
      LEFT JOIN civicrm_value_custom_greetings_6 indiv_greetings ON indiv_contact.id = indiv_greetings.entity_id";
     */
    return implode(' ', $from_clauses);
  }

  function where($includeContactIDs = TRUE) {
    $member = CRM_Utils_Array::value('member', $this->_formValues);
    $structype = CRM_Utils_Array::value('structype', $this->_formValues);
    $status = CRM_Utils_Array::value('status', $this->_formValues);
    $sort_name = CRM_Utils_Array::value('sort_name', $this->_formValues);
    $languages = CRM_Utils_Array::value('language', $this->_formValues);
    $sregion = CRM_Utils_Array::value('superregion', $this->_formValues);
    $type = CRM_Utils_Array::value('type', $this->_formValues);
    $clauses[] = "contact_a.is_deleted = 0";
    if ($type == 'individuals') {
      $clauses[] = "org_contact.is_deleted = 0";
      $clauses[] = "org_contact.display_name NOT LIKE ''";
      $clauses[] = "contact_a.contact_type LIKE 'Individual'";
    }
    elseif ($type == 'orgs_wo_contacts') {
      $clauses[] = "
        NOT EXISTS 
        (SELECT indiv_contact.id
        FROM civicrm_contact indiv_contact
        LEFT JOIN civicrm_relationship rel ON indiv_contact.id = rel.contact_id_a
          AND rel.relationship_type_id = 11
          AND rel.is_active = 1
        LEFT JOIN civicrm_value_position_2 rel_issues ON ( rel.id = rel_issues.entity_id )
        WHERE rel_issues.issues_20 LIKE '%Main contact%'
          AND rel.contact_id_b = contact_a.id
          AND indiv_contact.is_deleted = 0)";
      $clauses[] = "contact_a.contact_type LIKE 'Organization'";
      $clauses[] = "contact_a.display_name NOT LIKE ''";
    }
    else {
      $clauses[] = "contact_a.contact_type LIKE 'Organization'";
      $clauses[] = "contact_a.display_name NOT LIKE ''";
    }
    $placeholder = 1;
    if (strlen($member) == 1) {
      $clauses[] = "status.is_ica_member_5 = %$placeholder";
      $params[$placeholder] = array($member, 'Integer');
      $placeholder++;
    }
    if ($type == 'individuals') {
      $clauses[] = "rel_issues.issues_20 LIKE '%Main contact%'";
    }
    $structype = CRM_Utils_Array::value('structype', $this->_formValues);
    if (!empty($structype)) {
      $clauses[] = "status.structure_type_15 LIKE %$placeholder";
      $params[$placeholder] = array($structype, 'String');
      $placeholder++;
    }
    if (!empty($status)) {
      $use_or = FALSE;
      foreach ($status as $key => $value) {
        if ($value == 'CiviCRM_OP_OR') {
          $use_or = TRUE;
	  $or_key = $key;
	}
      }
      if ($use_or) {
        unset($status[$or_key]);
      }
      $status_values = array();
      foreach ($status as $value) {
        $status_values[] = "status.statuses_14 LIKE CONCAT('%', CHAR(1),'$value', CHAR(1), '%')";
      }
      if ($use_or) {
        $clauses[] = ' ( ' . implode(' OR ', $status_values) . ' ) ';
      }
      else {
        $clauses[] = ' ( ' . implode(' AND ', $status_values) . ' ) ';
      }
    }
    if (!empty($sort_name)) {
      if ($type == 'individuals') {
        $clauses[] = "org_contact.sort_name LIKE %$placeholder";
      }
      else {
        $clauses[] = "contact_a.sort_name LIKE %$placeholder";
      }
      $params[$placeholder] = array("%$sort_name%", 'String');
      $placeholder++;
    }
    if (!empty($languages)) {  
      $use_or = FALSE;
      foreach ($languages as $key => $value) {
        if ($value == 'CiviCRM_OP_OR') {
	  $use_or = TRUE;
	  $or_key = $key;
	}
      }
      if ($use_or) {
        unset($languages[$or_key]);
      }
      $language_values = array();
      foreach ($languages as $value) {
        $language_values[] = "org_location.languages_7 LIKE '%$value%'";
      }
      if ($use_or) {
        $clauses[] = ' ( ' . implode(' OR ', $language_values) . ' ) ';
      }
      else {
        $clauses[] = ' ( ' . implode(' AND ', $language_values) . ' ) ';
      }
    }
    if ($sregion) {
      $clauses[] = "org_location.super_region_9 LIKE %$placeholder";
      $params[$placeholder] = array($sregion, 'String');
      $placeholder++;
    }
    
    if ($type == 'orgs_wo_contacts') {
      $clauses[] = "
        NOT EXISTS 
        (SELECT indiv_contact.id
        FROM civicrm_contact indiv_contact
        LEFT JOIN civicrm_relationship rel ON indiv_contact.id = rel.contact_id_a
          AND rel.relationship_type_id = 11
          AND rel.is_active = 1
        LEFT JOIN civicrm_value_position_2 rel_issues ON ( rel.id = rel_issues.entity_id )
        WHERE rel_issues.issues_20 LIKE '%Main contact%'
          AND rel.contact_id_b = contact_a.id
          AND indiv_contact.is_deleted = 0)";
    }
     
   
    $where = implode(' AND ', $clauses);

    //CRM_Core_Error::debug_var('where', $where);
    return $this->whereClause($where, $params);
  }

  function setDefaultValues() {
    //$config         = CRM_Core_Config::singleton();
    //$countryDefault = $config->defaultContactCountry;
    $defaults = array();
    $defaults['sort_name'] = array('test');
    return $defaults;
  }


  function templateFile() {
    return 'CRM/Contact/Form/Search/Custom.tpl';
  }

  function setTitle($title) {
    if ($title) {
      CRM_Utils_System::setTitle($title);
    }
    else {
      CRM_Utils_System::setTitle(ts('Search'));
    }
  }
}

