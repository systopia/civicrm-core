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
class CRM_Contact_Form_Search_Custom_ICAOrgContacts extends CRM_Contact_Form_Search_Custom_Base implements CRM_Contact_Form_Search_Interface {
  function __construct(&$formValues) {
    parent::__construct($formValues);
    $this->_columns = array(
      ts('Org ID') => 'contact_id',
      ts('Org Name') => 'sort_name',
      ts('Org Email1') => 'org_email1',
      ts('Org Email2') => 'org_email2',
      ts('Org Email3') => 'org_email3',
      ts('Org Email4') => 'org_email4',
      ts('Org Languages') => 'org_languages',
      ts('Structure Type') => 'structure_type',
      ts('Prefix') => 'individual_prefix',
      ts('First Name') => 'individual_firstname',
      ts('Surname') => 'individual_lastname',
      ts('Position') => 'individual_position',
      ts('Native Position') => 'individual_nativeposition',
      ts('Email 1') => 'individual_email1',
      ts('Email 2') => 'individual_email2',
      ts('Email 3') => 'individual_email3',
      ts('Email 4') => 'individual_email4',
      ts('Indiv Address') => 'individual_address',
      ts('English greeting') => 'english_greeting',
      ts('French greeting') => 'french_greeting',
      ts('Spanish greeting') => 'spanish_greeting',
      //ts('Address') => 'contact_address',
      //ts('Email') => 'email', 
      //ts('Country') => 'country_name',
      //ts('Issues') => 'issues',
    );
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
    
    $contact_type_options = array(
      'all' => 'All - Organisations and Individuals',
      'organisations' => 'Organisations only',
      'individuals' => 'Individuals only',
      //'org_fallback' => 'Organisation as last resource',
    );
    //$form->add('select', 'types', ts('Contacts to include'), $contact_type_options);
    
    /**
     * You can define a custom title for the search form
     */
    $this->setTitle('ICA Orgs with Main Contacts Search');

    /**
     * if you are using the standard template, this array tells the template what elements
     * are part of the search criteria
     */
    $form->assign('elements', array('sort_name', 'member', 'status', 'structype', 'language', 'superregion'));
    $form->setDefaults(array('issues' => array('Main contact' => '1', 'CiviCRM_OP_OR' => '1')));
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
    $group_by = "GROUP BY contact_a.id, indiv_contact.id";
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

    $issues_no_op = $issues;
    unset($issues_no_op['CiviCRM_OP_OR']);

    $types = CRM_Utils_Array::value('types', $this->_formValues);
    $from_clauses = array();
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
      /*
      LEFT JOIN civicrm_address address ON ( address.contact_id       = contact_a.id AND
                                       address.is_primary       = 1 )
      LEFT JOIN civicrm_country address_country ON ( address.country_id = address_country.id )
      LEFT JOIN civicrm_email   email   ON ( email.contact_id = contact_a.id AND
                                       email.is_primary = 1)
      LEFT JOIN civicrm_value_location_and_language_3 locationp0 ON ( locationp0.entity_id = contact_a.id )
      LEFT JOIN civicrm_country country ON (locationp0.country_8 = country.id)
      LEFT JOIN civicrm_value_structure__status_4 statusp0 ON ( statusp0.entity_id = contact_a.id ) "; */
    /*
    if (!empty($tag)) {
      $from_clauses[] = "LEFT JOIN civicrm_entity_tag tagp0 ON ( contact_a.id = tagp0.entity_id AND
					                                                       tagp0.entity_table LIKE 'civicrm_contact' )";
    }
    if ($types != 'Individuals') {
			if ($children > 0) {
				$from_clauses[] = "
          LEFT JOIN civicrm_relationship relationship1 ON ( contact_a.id = relationship1.contact_id_b AND
																									          relationship1.relationship_type_id = '10' AND
																								           	relationship1.is_active = 1 )
          LEFT JOIN civicrm_value_location_and_language_3 locationp1 ON ( relationship1.contact_id_a = locationp1.entity_id )
          LEFT JOIN civicrm_value_structure__status_4 statusp1 ON ( relationship1.contact_id_a = statusp1.entity_id ) ";
			}
			if (!empty($tag) && $children > 0) {
				$from_clauses[] = "LEFT JOIN civicrm_entity_tag tagp1 ON (relationship1.contact_id_a = tagp1.entity_id AND
																																	tagp1.entity_table LIKE 'civicrm_contact')";
	    }
			if (!empty($sort_name) && $children > 0) {
				$from_clauses[] = "LEFT JOIN civicrm_contact contactp1 ON ( relationship1.contact_id_a = contactp1.id )";
			}
			if ($children > 1) {
				$from_clauses[] = "
       	  LEFT JOIN civicrm_relationship relationship2 ON ( relationship1.contact_id_a = relationship2.contact_id_b AND
					          																				relationship2.relationship_type_id = '10' AND
										          															relationship2.is_active = 1 )
         	LEFT JOIN civicrm_value_location_and_language_3 locationp2 ON ( relationship2.contact_id_a = locationp2.entity_id )
        	LEFT JOIN civicrm_value_structure__status_4 statusp2 ON ( relationship2.contact_id_a = statusp2.entity_id ) ";
			}
			if (!empty($tag) && $children > 1) {
				$from_clauses[] = "LEFT JOIN civicrm_entity_tag tagp2 ON (relationship2.contact_id_a = tagp2.entity_id AND
																																	tagp2.entity_table LIKE 'civicrm_contact')";
			}
			if (!empty($sort_name) && $children > 1) {
				$from_clauses[] = "LEFT JOIN civicrm_contact contactp2 ON ( relationship2.contact_id_a = contactp2.id )";
			}
			if ($children > 2) {
				$from_clauses[] = "
	        LEFT JOIN civicrm_relationship relationship3 ON ( relationship2.contact_id_a = relationship3.contact_id_b AND
					          																				relationship3.relationship_type_id = '10' AND
										          															relationship3.is_active = 1 )
        	LEFT JOIN civicrm_value_location_and_language_3 locationp3 ON ( relationship3.contact_id_a = locationp3.entity_id )
        	LEFT JOIN civicrm_value_structure__status_4 statusp3 ON ( relationship3.contact_id_a = statusp3.entity_id ) ";
			}
			if (!empty($tag) && $children > 2) {
				$from_clauses[] = "LEFT JOIN civicrm_entity_tag tagp3 ON (relationship3.contact_id_a = tagp3.entity_id AND
																																tagp3.entity_table LIKE 'civicrm_contact')";
			}
			if (!empty($sort_name) && $children > 2) {
				$from_clauses[] = "LEFT JOIN civicrm_contact contactp3 ON ( relationship3.contact_id_a = contactp3.id )";
			}
			if ($children > 3) {
				$from_clauses[] = "
	        LEFT JOIN civicrm_relationship relationship4 ON ( relationship3.contact_id_a = relationship4.contact_id_b AND
					          																				relationship4.relationship_type_id = '10' AND
										          															relationship4.is_active = 1 )
	        LEFT JOIN civicrm_value_location_and_language_3 locationp4 ON ( relationship4.contact_id_a = locationp4.entity_id )
        	LEFT JOIN civicrm_value_structure__status_4 statusp4 ON ( relationship4.contact_id_a = statusp4.entity_id ) ";
			}
			if (!empty($tag) && $children > 3) {
				$from_clauses[] = "LEFT JOIN civicrm_entity_tag tagp4 ON (relationship4.contact_id_a = tagp4.entity_id AND
																																	tagp4.entity_table LIKE 'civicrm_contact')";
			}
			if (!empty($sort_name) && $children > 3) {
				$from_clauses[] = "LEFT JOIN civicrm_contact contactp4 ON ( relationship4.contact_id_a = contactp4.id )";
			}
			if ($children > 4) {
				$from_clauses[] = "
         	LEFT JOIN civicrm_relationship relationship5 ON ( relationship4.contact_id_a = relationship5.contact_id_b AND
				        																						relationship5.relationship_type_id = '10' AND
								        																		relationship5.is_active = 1 )
	        LEFT JOIN civicrm_value_location_and_language_3 locationp5 ON ( relationship5.contact_id_a = locationp5.entity_id )
        	LEFT JOIN civicrm_value_structure__status_4 statusp5 ON ( relationship5.contact_id_a = statusp5.entity_id ) ";
      }
			if (!empty($tag) && $children > 4) {
				$from_clauses[] = "LEFT JOIN civicrm_entity_tag tagp5 ON (relationship5.contact_id_a = tagp5.entity_id AND
																																	tagp5.entity_table LIKE 'civicrm_contact')";
      }
			if (!empty($sort_name) && $children > 4) {
				$from_clauses[] = "LEFT JOIN civicrm_contact contactp5 ON ( relationship5.contact_id_a = contactp5.id )";
			}
    }
    if ($types != 'organisations') {
      $from_clauses[] = "
        LEFT JOIN civicrm_relationship rel_org ON ( contact_a.id = rel_org.contact_id_a AND
                                                    rel_org.is_active = 1 AND
                                                    rel_org.relationship_type_id = 11 )
        LEFT JOIN civicrm_contact rel_org_contact ON ( rel_org_contact.id = rel_org.contact_id_b )
        LEFT JOIN civicrm_value_position_2 rel_issues ON ( rel_org.id = rel_issues.entity_id )
        LEFT JOIN civicrm_value_location_and_language_3 rel_org_locationp0 ON ( rel_org.contact_id_b = rel_org_locationp0.entity_id )
        LEFT JOIN civicrm_value_structure__status_4 rel_org_statusp0 ON ( rel_org.contact_id_b = rel_org_statusp0.entity_id ) ";
      if (!empty($tag)) {
        $from_clauses[] = "LEFT JOIN civicrm_entity_tag rel_org_tagp0 ON ( rel_org.contact_id_b = rel_org_tagp0.entity_id AND
                                                                          rel_org_tagp0.entity_table LIKE 'civicrm_contact' )";
      }
      if ($children > 0) {
        $from_clauses[] = "
          LEFT JOIN civicrm_relationship rel_orgp1 ON ( rel_org.contact_id_b = rel_orgp1.contact_id_b AND
                                                        rel_orgp1.relationship_type_id = '10' AND
                                                        rel_orgp1.is_active = 1 )
          LEFT JOIN civicrm_value_location_and_language_3 rel_org_locationp1 ON ( rel_orgp1.contact_id_a = rel_org_locationp1.entity_id )
          LEFT JOIN civicrm_value_structure__status_4 rel_org_statusp1 ON ( rel_orgp1.contact_id_a = rel_org_statusp1.entity_id ) ";
      }
      if (!empty($tag) && $children > 0) {
        $from_clauses[] = "LEFT JOIN civicrm_entity_tag rel_org_tagp1 ON ( rel_orgp1.contact_id_a = rel_org_tagp1.entity_id AND
                                                                           rel_org_tagp1.entity_table LIKE 'civicrm_contact' ) ";
      }
      if (!empty($sort_name) && $children > 0) {
        $from_clauses[] = "LEFT JOIN civicrm_contact rel_org_contactp1 ON ( rel_orgp1.contact_id_a = rel_org_contactp1.id )";
      }
      if ($children > 1) {
        $from_clauses[] = "
          LEFT JOIN civicrm_relationship rel_orgp2 ON ( rel_orgp1.contact_id_a = rel_orgp2.contact_id_b AND
                                                        rel_orgp2.relationship_type_id = '10' AND
                                                        rel_orgp2.is_active = 1 )
          LEFT JOIN civicrm_value_location_and_language_3 rel_org_locationp2 ON ( rel_orgp2.contact_id_a = rel_org_locationp2.entity_id )
          LEFT JOIN civicrm_value_structure__status_4 rel_org_statusp2 ON ( rel_orgp2.contact_id_a = rel_org_statusp2.entity_id ) ";
      }
      if (!empty($tag) && $children > 1) {
        $from_clauses[] = "LEFT JOIN civicrm_entity_tag rel_org_tagp2 ON (rel_orgp2.contact_id_a = rel_org_tagp2.entity_id AND
					                                                                rel_org_tagp2.entity_table LIKE 'civicrm_contact' )";
      }
      if (!empty($sort_name) && $children > 1) {
        $from_clauses[] = "LEFT JOIN civicrm_contact rel_org_contactp2 ON ( rel_orgp2.contact_id_a = rel_org_contactp2.id )";
      }
      if ($children > 2) {
        $from_clauses[] = "
          LEFT JOIN civicrm_relationship rel_orgp3 ON ( rel_orgp2.contact_id_a = rel_orgp3.contact_id_b AND
                                                        rel_orgp3.relationship_type_id = '10' AND
                                                        rel_orgp3.is_active = 1 )
          LEFT JOIN civicrm_value_location_and_language_3 rel_org_locationp3 ON ( rel_orgp3.contact_id_a = rel_org_locationp3.entity_id )
          LEFT JOIN civicrm_value_structure__status_4 rel_org_statusp3 ON ( rel_orgp3.contact_id_a = rel_org_statusp3.entity_id ) ";
      }
      if (!empty($tag) && $children > 2) {
        $from_clauses[] = "LEFT JOIN civicrm_entity_tag rel_org_tagp3 ON (rel_orgp3.contact_id_a = rel_org_tagp3.entity_id AND
					                                                                rel_org_tagp3.entity_table LIKE 'civicrm_contact' )";
      }
      if (!empty($sort_name) && $children > 2) {
        $from_clauses[] = "LEFT JOIN civicrm_contact rel_org_contactp3 ON ( rel_orgp3.contact_id_a = rel_org_contactp3.id )";
      }
      if ($children > 3) {
        $from_clauses[] = "
          LEFT JOIN civicrm_relationship rel_orgp4 ON ( rel_orgp3.contact_id_a = rel_orgp4.contact_id_b AND
                                                        rel_orgp4.relationship_type_id = '10' AND
                                                        rel_orgp4.is_active = 1 )
          LEFT JOIN civicrm_value_location_and_language_3 rel_org_locationp4 ON ( rel_orgp4.contact_id_a = rel_org_locationp4.entity_id )
          LEFT JOIN civicrm_value_structure__status_4 rel_org_statusp4 ON ( rel_orgp4.contact_id_a = rel_org_statusp4.entity_id ) ";
      }
      if (!empty($tag) && $children > 3) {
        $from_clauses[] = "LEFT JOIN civicrm_entity_tag rel_org_tagp4 ON (rel_orgp4.contact_id_a = rel_org_tagp4.entity_id AND
					       rel_org_tagp4.entity_table LIKE 'civicrm_contact' )";
      }
      if (!empty($sort_name) && $children > 3) {
        $from_clauses[] = "LEFT JOIN civicrm_contact rel_org_contactp4 ON ( rel_orgp4.contact_id_a = rel_org_contactp4.id )";
      }
      if ($children > 4) {
        $from_clauses[] = "
          LEFT JOIN civicrm_relationship rel_orgp5 ON ( rel_orgp4.contact_id_a = rel_orgp5.contact_id_b AND
                                                        rel_orgp5.relationship_type_id = '10' AND
                                                        rel_orgp5.is_active = 1 )
          LEFT JOIN civicrm_value_location_and_language_3 rel_org_locationp5 ON ( rel_orgp5.contact_id_a = rel_org_locationp5.entity_id )
          LEFT JOIN civicrm_value_structure__status_4 rel_org_statusp5 ON ( rel_orgp5.contact_id_a = rel_org_statusp5.entity_id ) ";
      }
      if (!empty($tag) && $children > 4) {
        $from_clauses[] = "LEFT JOIN civicrm_entity_tag rel_org_tagp5 ON (rel_orgp5.contact_id_a = rel_org_tagp5.entity_id AND
					                                                                rel_org_tagp5.entity_table LIKE 'civicrm_contact' )";
      }
      if (!empty($sort_name) && $children > 4) {
        $from_clauses[] = "LEFT JOIN civicrm_contact rel_org_contactp5 ON ( rel_orgp5.contact_id_a = rel_org_contactp5.id )";
      }
    }
    if ($types == 'organisations') {
      if (array_sum($issues_no_op) > 0) {
        $use_or = FALSE;
        if ($issues['CiviCRM_OP_OR'] == TRUE) {
          $use_or = TRUE;
        }
        $issue_values = array();
        foreach ($issues_no_op as $issue => $value) {
          if ($value == '1') {
            $issue_values[] = "rel_indiv.issues_20 LIKE '%$issue%'";
          }
        }
        if ($use_or) {
		      $issue_clause = ' ( ' . implode(' OR ', $issue_values) . ' ) ';
		    }
		    else {
			    $issue_clause = ' ( ' . implode(' AND ', $issue_values) . ' ) ';
			  }
        $from_clauses[] = "
          LEFT JOIN
            ( SELECT r.id, r.contact_id_a, r.contact_id_b, r.relationship_type_id, r.is_active, p.issues_20 FROM civicrm_relationship r INNER JOIN civicrm_value_position_2 p ON r.id = p.entity_id) rel_indiv
            ON ( contact_a.id = rel_indiv.contact_id_b AND
                 rel_indiv.is_active = 1 AND
							   rel_indiv.relationship_type_id = 11 AND
                 $issue_clause )
          LEFT JOIN civicrm_contact rel_indiv_contact ON ( rel_indiv.contact_id_a = rel_indiv_contact.id )
          LEFT JOIN civicrm_value_position_2 rel_issues ON ( rel_indiv.id = rel_issues.entity_id )";
      }
    }
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
    $clauses[] = "contact_a.is_deleted = 0";
    $clauses[] = "(indiv_contact.is_deleted = 0 OR indiv_contact.is_deleted IS NULL)";
    $placeholder = 1;
    if (strlen($member) == 1) {
      $clauses[] = "status.is_ica_member_5 = %$placeholder";
      $params[$placeholder] = array($member, 'Integer');
      $placeholder++;
    }
    $clauses[] = "rel_issues.issues_20 LIKE '%Main contact%'";
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
      $clauses[] = "contact_a.sort_name LIKE %$placeholder";
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
    /*
    //$params = array();
    $sort_name = CRM_Utils_Array::value('sort_name', $this->_formValues);
    $region = CRM_Utils_Array::value('region', $this->_formValues);
    $sregion = CRM_Utils_Array::value('superregion', $this->_formValues);
    $member = CRM_Utils_Array::value('member', $this->_formValues);
    $children = CRM_Utils_Array::value('children', $this->_formValues);
    $issues = CRM_Utils_Array::value('issues', $this->_formV $clauses[] = ' ( ' . implode(' OR ', $sregion_clauses) . ' ) ';alues);
    $tag = CRM_Utils_Array::value('tag', $this->_formValues);
    $languages = CRM_Utils_Array::value('language', $this->_formValues);

    $issues_no_op = $issues;
    unset($issues_no_op['CiviCRM_OP_OR']);

    $types = CRM_Utils_Array::value('types', $this->_formValues);
    //CRM_Core_Error::debug_var('languages', $languages);
    $clauses = array();
    $params = array();
    $placeholder = 1;
    if (!empty($sort_name)) {
      for ($i = 0; $i <= $children ; $i++) {
        if ($i == 0) {
          $name_clauses[] = "contact_a.sort_name LIKE %$placeholder";
        }
        else {
          $name_clauses[] = "contactp$i.sort_name LIKE %$placeholder";
        }
        $params[$placeholder] = array("%$sort_name%", 'String');
        $placeholder++;
        if ($types != 'organisations') {
          if ($i == 0) {
            $name_clauses[] = "rel_org_contact.sort_name LIKE %$placeholder";
          }
          else {
            $name_clauses[] = "rel_org_contactp$i.sort_name LIKE %$placeholder";
          }
          $params[$placeholder] = array("%$sort_name%", 'String');
          $placeholder++;
        }
      }
      $clauses[] = ' ( ' . implode(' OR ', $name_clauses) . ' ) ';
    }
    if ($region) {
      for ($i = 0; $i <= $children ; $i++) {
        $region_clauses[] = "locationp$i.region_10 LIKE %$placeholder";
        $params[$placeholder] = array($region, 'String');
        $placeholder++;
        if ($types != 'organisations') {
          $region_clauses[] = "rel_org_locationp$i.region_10 LIKE %$placeholder";
          $params[$placeholder] = array($region, 'String');
          $placeholder++;
        }
      }
      $clauses[] = ' ( ' . implode(' OR ', $region_clauses) . ' ) ';
    }
    if ($sregion) {
      for ($i = 0; $i <= $children ; $i++) {
        $sregion_clauses[] = "locationp$i.super_region_9 LIKE %$placeholder";
        $params[$placeholder] = array($sregion, 'String');
        $placeholder++;
        if ($types != 'organisations') {
          $sregion_clauses[] = "rel_org_locationp$i.super_region_9 LIKE %$placeholder";
          $params[$placeholder] = array($sregion, 'String');
          $placeholder++;
        }
      }
      $clauses[] = ' ( ' . implode(' OR ', $sregion_clauses) . ' ) ';
    }
    //$type = gettype($member);
    //CRM_Core_Error::debug_var('type', $type);
    if (strlen($member) == 1) {
      for ($i = 0; $i <= $children ; $i++) {
        $member_clauses[] = "statusp$i.is_ica_member_5 = %$placeholder";
        $params[$placeholder] = array($member, 'Integer');
        $placeholder++;
        if ($types != 'organisations') {
          $member_clauses[] = "rel_org_statusp$i.is_ica_member_5 = %$placeholder";
          $params[$placeholder] = array($member, 'Integer');
          $placeholder++;
        }
      }
      //CRM_Core_Error::debug_var('member_clauses', $member_clauses);
      $clauses[] = ' ( ' . implode(' OR ', $member_clauses) . ' ) ';
    }
    //CRM_Core_Error::debug_var('status', $status);
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
      for ($i = 0; $i <= $children ; $i++) {
				$status_values = array();
				foreach ($status as $value) {
					$status_values[] = "statusp$i.statuses_14 LIKE CONCAT('%', CHAR(1),'$value', CHAR(1), '%')";
				}
				if ($use_or) {
					$status_clauses[] = ' ( ' . implode(' OR ', $status_values) . ' ) ';
				}
				else {
					$status_clauses[] = ' ( ' . implode(' AND ', $status_values) . ' ) ';
				}
        if ($types != 'organisations') {
          $status_values = array();
          foreach ($status as $value) {
			      $status_values[] = "rel_org_statusp$i.statuses_14 LIKE CONCAT('%', CHAR(1),'$value', CHAR(1), '%')";
				  }
				  if ($use_or) {
					  $status_clauses[] = ' ( ' . implode(' OR ', $status_values) . ' ) ';
				  }
				  else {
					  $status_clauses[] = ' ( ' . implode(' AND ', $status_values) . ' ) ';
				  }
        }
      }
      $clauses[] = ' ( ' . implode(' OR ', $status_clauses) . ' ) ';
    }
    if (!empty($tag)) {
      for ($i = 0; $i <= $children ; $i++) {
        $tag_clauses[] = "tagp$i.tag_id = %$placeholder";
        $params[$placeholder] = array($tag, 'Integer');
        $placeholder++;
        if ($types != 'organisations') {
          $tag_clauses[] = "rel_org_tagp$i.tag_id = %$placeholder";
          $params[$placeholder] = array($tag, 'Integer');
          $placeholder++;
        }
      }
    $clauses[] = ' ( ' . implode(' OR ', $tag_clauses) . ' ) ';
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
      for ($i = 0; $i <= $children ; $i++) {
				$language_values = array();
				foreach ($languages as $value) {
					$language_values[] = "locationp0.languages_7 LIKE '%$value%'";
				}
				if ($use_or) {
					$language_clauses[] = ' ( ' . implode(' OR ', $language_values) . ' ) ';
				}
				else {
					$language_clauses[] = ' ( ' . implode(' AND ', $language_values) . ' ) ';
				}
        if ($types != 'organisations') {
          $language_values = array();
          foreach ($languages as $value) {
			      $language_values[] = "rel_org_locationp0.languages_7 LIKE '%$value%'";
				  }
				  if ($use_or) { $clauses[] = ' ( ' . implode(' OR ', $sregion_clauses) . ' ) ';
					  $language_clauses[] = ' ( ' . implode(' OR ', $language_values) . ' ) ';
				  }
				  else {
					  $language_clauses[] = ' ( ' . implode(' AND ', $language_values) . ' ) ';
				  }
					CRM_Core_Error::debug_var('language_clauses', $language_clauses);
        }
      }
      $clauses[] = ' ( ' . implode(' OR ', $language_clauses) . ' ) ';
    }
    if (array_sum($issues) > 0 && $types != 'organisations') {
      $use_or = FALSE;
      if ($issues['CiviCRM_OP_OR'] == TRUE) {
        $use_or = TRUE;
      }
      $issue_values = array();
      foreach ($issues_no_op as $issue => $value) {
        if ($value == '1') {
          $issue_values[] = "rel_issues.issues_20 LIKE '%$issue%'";
        }
      }
      if ($use_or) {
		    $issue_clause = ' ( ' . implode(' OR ', $issue_values) . ' ) ';
		  }
		  else {
			  $issue_clause = ' ( ' . implode(' AND ', $issue_values) . ' ) ';
			}

      if ($types == 'all' ) {
        $clauses[] = "( contact_a.contact_type LIKE 'Organization' OR $issue_clause )";
      }
      //elseif ($types == 'organisations') {
      //  $clauses[] = " ( $issue_clause OR rel_issues.issues_20 IS NULL ) ";
      //} 
      else {
        $clauses[] = $issue_clause;
      }
    }
    if ($types == 'organisations') {
      $clauses[] = "contact_a.contact_type LIKE 'Organization'";
    }
    elseif ($types == 'individuals') {
      $clauses[] = "contact_a.contact_type LIKE 'Individual'";
    }
    $conditions = implode(' AND ', $clauses);
    */
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

