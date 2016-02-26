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
class CRM_Contact_Form_Search_Custom_ICARegion extends CRM_Contact_Form_Search_Custom_Base implements CRM_Contact_Form_Search_Interface {
  function __construct(&$formValues) {
    parent::__construct($formValues);

    $this->_columns = array(
      ts('Contact ID') => 'contact_id',
      ts('Name') => 'sort_name',
      ts('Position') => 'position',
      ts('Organisation Name') => 'organisation_name',
      ts('Address') => 'contact_address',
      ts('Email') => 'email',
      ts('Country') => 'country_name',
      ts('Issues') => 'issues',
    );
  }

  function buildForm(&$form) {
    $form->add('text', 'sort_name', ts('Name'));
    CRM_Core_BAO_CustomField::addQuickFormElement($form, 'region', '10', TRUE, FALSE, TRUE, 'Region');
    CRM_Core_BAO_CustomField::addQuickFormElement($form, 'superregion', '9', TRUE, FALSE, TRUE, 'Super-region');
    CRM_Core_BAO_CustomField::addQuickFormElement($form, 'member', '5', TRUE, FALSE, TRUE, 'Is ICA Member?');
    CRM_Core_BAO_CustomField::addQuickFormElement($form, 'status', '14', TRUE, FALSE, TRUE, 'Status');
    // add select for categories
    $tag = array('' => ts('- any tag -')) + CRM_Core_PseudoConstant::tag();
    $form->addElement('select', 'tag', ts('Tagged'), $tag);
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
    $form->add('select', 'children', ts('Include children? Number of levels'), $children_options);
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
    CRM_Core_BAO_CustomField::addQuickFormElement($form, 'issues', '20', TRUE, FALSE, TRUE, 'Issues');
    $contact_type_options = array(
      'all' => 'All - Organisations and Individuals',
      'organisations' => 'Organisations only',
      //'individuals' => 'Individuals only',
      //'org_fallback' => 'Organisation as last resource',
    );
    $form->add('select', 'types', ts('Contacts to include'), $contact_type_options);
    /**
     * You can define a custom title for the search form
     */
    $this->setTitle('ICA Search');

    /**
     * if you are using the standard template, this array tells the template what elements
     * are part of the search criteria
     */
    $form->assign('elements', array('sort_name', 'superregion', 'region', 'member', 'status', 'tag', 'language', 'children', 'issues', 'types'));
  }

  function summary() {
    $summary = array();
    return $summary;
  }


  function all($offset = 0, $rowcount = 0, $sort = NULL, $includeContactIDs = FALSE, $justIDs = FALSE) {
    $types = CRM_Utils_Array::value('types',
      $this->_formValues
    );
    if ($types == 'org_fallback') {
      $group_by = "GROUP BY contact_a.id";
      $selectClause = "
contact_a.id           as contact_id ,
contact_a.sort_name    as sort_name  ,
email.email            as email   ,
address.postal_code    as postal_code
";
    }
    else {
      $group_by = '';
      $selectClause = "
DISTINCT(contact_a.id)           as contact_id ,
contact_a.sort_name    as sort_name  ,
rel_issues.position_3 as position,
IF(contact_a.contact_type LIKE 'Individual', rel_org_contact.organization_name, contact_a.organization_name) as organisation_name ,
CONCAT_WS('<br />', address.street_address , address.supplemental_address_1 , CONCAT( address.city , ' ' , address.supplemental_address_2 , ' ' , address.postal_code ) , address_country.name) as contact_address ,
email.email            as email   ,
country.name    as country_name ,
rel_issues.issues_20 as issues 
";
    }
    return $this->sql($selectClause,
      $offset, $rowcount, $sort,
      $includeContactIDs, $group_by
    );
  }

  function from() {
    $types = CRM_Utils_Array::value('types',
      $this->_formValues
    );
    $tag = CRM_Utils_Array::value('tag',
      $this->_formValues
    );
    $from_clauses = "
FROM      civicrm_contact contact_a
LEFT JOIN civicrm_address address ON ( address.contact_id       = contact_a.id AND
                                       address.is_primary       = 1 )
LEFT JOIN civicrm_country address_country ON ( address.country_id = address_country.id )
LEFT JOIN civicrm_email   email   ON ( email.contact_id = contact_a.id AND
                                       email.is_primary = 1 )
LEFT JOIN civicrm_value_location_and_language_3 locationp0 ON ( locationp0.entity_id = contact_a.id )
LEFT JOIN civicrm_country country ON (locationp0.country_8 = country.id)
LEFT JOIN civicrm_value_structure__status_4 statusp0 ON ( statusp0.entity_id = contact_a.id )";
if (!empty($tag)) {
  $from_clauses .= "LEFT JOIN civicrm_entity_tag tagp0 ON (contact_a.id = tagp0.entity_id AND
					                                                 tagp0.entity_table LIKE 'civicrm_contact' )";
}
$from_clauses .= "
LEFT JOIN civicrm_relationship relationship1 ON ( contact_a.id = relationship1.contact_id_b AND
                                                relationship1.relationship_type_id = '10' AND
                                                relationship1.is_active = 1 )
LEFT JOIN civicrm_value_location_and_language_3 locationp1 ON ( relationship1.contact_id_a = locationp1.entity_id )
LEFT JOIN civicrm_value_structure__status_4 statusp1 ON ( relationship1.contact_id_a = statusp1.entity_id ) ";
if (!empty($tag)) {
  $from_clauses .= " LEFT JOIN civicrm_entity_tag tagp1 ON (relationship1.contact_id_a = tagp1.entity_id AND
					                               tagp1.entity_table LIKE 'civicrm_contact') ";
}
$from_clauses .= "
LEFT JOIN civicrm_contact contactp1 ON ( relationship1.contact_id_a = contactp1.id )
LEFT JOIN civicrm_relationship relationship2 ON ( relationship1.contact_id_a = relationship2.contact_id_b AND
                                                relationship2.relationship_type_id = '10' AND
                                                relationship2.is_active = 1 )
LEFT JOIN civicrm_value_location_and_language_3 locationp2 ON ( relationship2.contact_id_a = locationp2.entity_id )
LEFT JOIN civicrm_value_structure__status_4 statusp2 ON ( relationship2.contact_id_a = statusp2.entity_id )
LEFT JOIN civicrm_entity_tag tagp2 ON (relationship2.contact_id_a = tagp2.entity_id AND
					tagp2.entity_table LIKE 'civicrm_contact')
LEFT JOIN civicrm_contact contactp2 ON ( relationship2.contact_id_a = contactp2.id )
LEFT JOIN civicrm_relationship relationship3 ON ( relationship2.contact_id_a = relationship3.contact_id_b AND
                                                relationship3.relationship_type_id = '10' AND
                                                relationship3.is_active = 1 )
LEFT JOIN civicrm_value_location_and_language_3 locationp3 ON ( relationship3.contact_id_a = locationp3.entity_id )
LEFT JOIN civicrm_value_structure__status_4 statusp3 ON ( relationship3.contact_id_a = statusp3.entity_id ) ";
if (!empty($tag)) {
  $from_clauses .= "LEFT JOIN civicrm_entity_tag tagp3 ON (relationship3.contact_id_a = tagp3.entity_id AND
					                                                 tagp3.entity_table LIKE 'civicrm_contact')";
}
$from_clauses .= "
LEFT JOIN civicrm_contact contactp3 ON ( relationship3.contact_id_a = contactp3.id )
LEFT JOIN civicrm_relationship relationship4 ON ( relationship3.contact_id_a = relationship4.contact_id_b AND
                                                relationship4.relationship_type_id = '10' AND
                                                relationship4.is_active = 1 )
LEFT JOIN civicrm_value_location_and_language_3 locationp4 ON ( relationship4.contact_id_a = locationp4.entity_id )
LEFT JOIN civicrm_value_structure__status_4 statusp4 ON ( relationship4.contact_id_a = statusp4.entity_id )
LEFT JOIN civicrm_entity_tag tagp4 ON (relationship4.contact_id_a = tagp4.entity_id AND
					tagp4.entity_table LIKE 'civicrm_contact')
LEFT JOIN civicrm_contact contactp4 ON ( relationship4.contact_id_a = contactp4.id )
LEFT JOIN civicrm_relationship relationship5 ON ( relationship4.contact_id_a = relationship5.contact_id_b AND
                                                relationship5.relationship_type_id = '10' AND
                                                relationship5.is_active = 1 )
LEFT JOIN civicrm_value_location_and_language_3 locationp5 ON ( relationship5.contact_id_a = locationp5.entity_id )
LEFT JOIN civicrm_value_structure__status_4 statusp5 ON ( relationship5.contact_id_a = statusp5.entity_id )
LEFT JOIN civicrm_entity_tag tagp5 ON (relationship5.contact_id_a = tagp5.entity_id AND
					tagp5.entity_table LIKE 'civicrm_contact') 
LEFT JOIN civicrm_contact contactp5 ON ( relationship5.contact_id_a = contactp5.id )";
//if ($types != 'organisations') {
$from_clauses .= "LEFT JOIN civicrm_relationship rel_org ON ( contact_a.id = rel_org.contact_id_a AND
                                            rel_org.is_active = 1 AND
                                            rel_org.relationship_type_id = 11 )
LEFT JOIN civicrm_contact rel_org_contact ON ( rel_org_contact.id = rel_org.contact_id_b )
LEFT JOIN civicrm_value_position_2 rel_issues ON ( rel_org.id = rel_issues.entity_id )
LEFT JOIN civicrm_value_location_and_language_3 rel_org_locationp0 ON ( rel_org.contact_id_b = rel_org_locationp0.entity_id )
LEFT JOIN civicrm_value_structure__status_4 rel_org_statusp0 ON ( rel_org.contact_id_b = rel_org_statusp0.entity_id )
LEFT JOIN civicrm_entity_tag rel_org_tagp0 ON (rel_org.contact_id_b = rel_org_tagp0.entity_id AND
                                               rel_org_tagp0.entity_table LIKE 'civicrm_contact')
LEFT JOIN civicrm_relationship rel_orgp1 ON ( rel_org.contact_id_b = rel_orgp1.contact_id_b AND
                                                rel_orgp1.relationship_type_id = '10' AND
                                                rel_orgp1.is_active = 1 )
LEFT JOIN civicrm_value_location_and_language_3 rel_org_locationp1 ON ( rel_orgp1.contact_id_a = rel_org_locationp1.entity_id )
LEFT JOIN civicrm_value_structure__status_4 rel_org_statusp1 ON ( rel_orgp1.contact_id_a = rel_org_statusp1.entity_id ) 
LEFT JOIN civicrm_entity_tag rel_org_tagp1 ON ( rel_orgp1.contact_id_a = rel_org_tagp1.entity_id AND
                                                rel_org_tagp1.entity_table LIKE 'civicrm_contact' )
LEFT JOIN civicrm_contact rel_org_contactp1 ON ( rel_orgp1.contact_id_a = rel_org_contactp1.id )
LEFT JOIN civicrm_relationship rel_orgp2 ON ( rel_orgp1.contact_id_a = rel_orgp2.contact_id_b AND
                                                rel_orgp2.relationship_type_id = '10' AND
                                                rel_orgp2.is_active = 1 )
LEFT JOIN civicrm_value_location_and_language_3 rel_org_locationp2 ON ( rel_orgp2.contact_id_a = rel_org_locationp2.entity_id )
LEFT JOIN civicrm_value_structure__status_4 rel_org_statusp2 ON ( rel_orgp2.contact_id_a = rel_org_statusp2.entity_id )
LEFT JOIN civicrm_entity_tag rel_org_tagp2 ON (rel_orgp2.contact_id_a = rel_org_tagp2.entity_id AND
					       rel_org_tagp2.entity_table LIKE 'civicrm_contact' )
LEFT JOIN civicrm_contact rel_org_contactp2 ON ( rel_orgp2.contact_id_a = rel_org_contactp2.id )
LEFT JOIN civicrm_relationship rel_orgp3 ON ( rel_orgp2.contact_id_a = rel_orgp3.contact_id_b AND
                                                rel_orgp3.relationship_type_id = '10' AND
                                                rel_orgp3.is_active = 1 )
LEFT JOIN civicrm_value_location_and_language_3 rel_org_locationp3 ON ( rel_orgp3.contact_id_a = rel_org_locationp3.entity_id )
LEFT JOIN civicrm_value_structure__status_4 rel_org_statusp3 ON ( rel_orgp3.contact_id_a = rel_org_statusp3.entity_id )
LEFT JOIN civicrm_entity_tag rel_org_tagp3 ON (rel_orgp3.contact_id_a = rel_org_tagp3.entity_id AND
					       rel_org_tagp3.entity_table LIKE 'civicrm_contact' )
LEFT JOIN civicrm_contact rel_org_contactp3 ON ( rel_orgp3.contact_id_a = rel_org_contactp3.id )
LEFT JOIN civicrm_relationship rel_orgp4 ON ( rel_orgp3.contact_id_a = rel_orgp4.contact_id_b AND
                                                rel_orgp4.relationship_type_id = '10' AND
                                                rel_orgp4.is_active = 1 )
LEFT JOIN civicrm_value_location_and_language_3 rel_org_locationp4 ON ( rel_orgp4.contact_id_a = rel_org_locationp4.entity_id )
LEFT JOIN civicrm_value_structure__status_4 rel_org_statusp4 ON ( rel_orgp4.contact_id_a = rel_org_statusp4.entity_id )
LEFT JOIN civicrm_entity_tag rel_org_tagp4 ON (rel_orgp4.contact_id_a = rel_org_tagp4.entity_id AND
					       rel_org_tagp4.entity_table LIKE 'civicrm_contact' )
LEFT JOIN civicrm_contact rel_org_contactp4 ON ( rel_orgp4.contact_id_a = rel_org_contactp4.id )
LEFT JOIN civicrm_relationship rel_orgp5 ON ( rel_orgp4.contact_id_a = rel_orgp5.contact_id_b AND
                                                rel_orgp5.relationship_type_id = '10' AND
                                                rel_orgp5.is_active = 1 )
LEFT JOIN civicrm_value_location_and_language_3 rel_org_locationp5 ON ( rel_orgp5.contact_id_a = rel_org_locationp5.entity_id )
LEFT JOIN civicrm_value_structure__status_4 rel_org_statusp5 ON ( rel_orgp5.contact_id_a = rel_org_statusp5.entity_id )
LEFT JOIN civicrm_entity_tag rel_org_tagp5 ON (rel_orgp5.contact_id_a = rel_org_tagp5.entity_id AND
					       rel_org_tagp5.entity_table LIKE 'civicrm_contact' )
LEFT JOIN civicrm_contact rel_org_contactp5 ON ( rel_orgp5.contact_id_a = rel_org_contactp5.id )
";
//}

    if ($types == 'org_fallback') {
      $from_clauses .= "
LEFT JOIN civicrm_relationship rel_indiv ON ( contact_a.id = rel_indiv.contact_id_b AND
                                                             rel_indiv.is_active = 1 )
LEFT JOIN civicrm_contact rel_indiv_contact ON ( rel_indiv.contact_id_a = rel_indiv_contact.id )";
    }
    return $from_clauses;

  }

  function where($includeContactIDs = FALSE) {
    //$params = array();
    $sort_name = CRM_Utils_Array::value('sort_name',
      $this->_formValues
    );
    $region = CRM_Utils_Array::value('region',
      $this->_formValues
    );
    $sregion = CRM_Utils_Array::value('superregion',
      $this->_formValues
    );
    $member = CRM_Utils_Array::value('member',
      $this->_formValues
    );
    $status = CRM_Utils_Array::value('status',
      $this->_formValues
    );
    $children = CRM_Utils_Array::value('children',
      $this->_formValues
    );
    $issues = CRM_Utils_Array::value('issues',
      $this->_formValues
    );

    $tag = CRM_Utils_Array::value('tag',
      $this->_formValues
    );

    $languages = CRM_Utils_Array::value('language',
      $this->_formValues
    );

    $issues_no_op = $issues;
    unset($issues_no_op['CiviCRM_OP_OR']);

    $types = CRM_Utils_Array::value('types',
      $this->_formValues
    );
    CRM_Core_Error::debug_var('languages', $languages);
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
        $params[$placeholder] = array($sort_name, 'String');
        $placeholder++;
        if ($i == 0) {
          $name_clauses[] = "rel_org_contact.sort_name LIKE %$placeholder";
        }
        else {
          $name_clauses[] = "rel_org_contactp$i.sort_name LIKE %$placeholder";
        }
        $params[$placeholder] = array($sort_name, 'String');
        $placeholder++;
      }
      $clauses[] = ' ( ' . implode(' OR ', $name_clauses) . ' ) ';
    }
    if ($region) {
      for ($i = 0; $i <= $children ; $i++) {
        $region_clauses[] = "locationp$i.region_10 LIKE %$placeholder";
        $params[$placeholder] = array($region, 'String');
        $placeholder++;
        //if ($types != 'organisations') {
          $region_clauses[] = "rel_org_locationp$i.region_10 LIKE %$placeholder";
          $params[$placeholder] = array($region, 'String');
          $placeholder++;
        //}
      }
      $clauses[] = ' ( ' . implode(' OR ', $region_clauses) . ' ) ';
    }
    if ($sregion) {
      for ($i = 0; $i <= $children ; $i++) {
        $sregion_clauses[] = "locationp$i.super_region_9 LIKE %$placeholder";
        $params[$placeholder] = array($sregion, 'String');
        $placeholder++;
        //if ($types != 'organisations') {
          $sregion_clauses[] = "rel_org_locationp$i.super_region_9 LIKE %$placeholder";
          $params[$placeholder] = array($sregion, 'String');
          $placeholder++;
        //}
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
        //if ($types != 'organisations') {
          $member_clauses[] = "rel_org_statusp$i.is_ica_member_5 = %$placeholder";
          $params[$placeholder] = array($member, 'Integer');
          $placeholder++;
        //}
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
					$status_values[] = "statusp$i.statuses_14 LIKE '%$value%'";
				}
				if ($use_or) {
					$status_clauses[] = ' ( ' . implode(' OR ', $status_values) . ' ) ';
				}
				else {
					$status_clauses[] = ' ( ' . implode(' AND ', $status_values) . ' ) ';
				}
        //if ($types != 'organisations') {
          $status_values = array();
          foreach ($status as $value) {
			      $status_values[] = "rel_org_statusp$i.statuses_14 LIKE '%$value%'";
				  }
				  if ($use_or) {
					  $status_clauses[] = ' ( ' . implode(' OR ', $status_values) . ' ) ';
				  }
				  else {
					  $status_clauses[] = ' ( ' . implode(' AND ', $status_values) . ' ) ';
				  }
        //}
      }
      $clauses[] = ' ( ' . implode(' OR ', $status_clauses) . ' ) ';
    }
    if (!empty($tag)) {
      for ($i = 0; $i <= $children ; $i++) {
        $tag_clauses[] = "tagp$i.tag_id = %$placeholder";
        $params[$placeholder] = array($tag, 'Integer');
        $placeholder++;
        //if ($types != 'organisations') {
          $tag_clauses[] = "rel_org_tagp$i.tag_id = %$placeholder";
          $params[$placeholder] = array($tag, 'Integer');
          $placeholder++;
        //}
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
      //for ($i = 0; $i <= $children ; $i++) {
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
        //if ($types != 'organisations') {
          $language_values = array();
          foreach ($languages as $value) {
			      $language_values[] = "rel_org_locationp0.languages_7 LIKE '%$value%'";
				  }
				  if ($use_or) {
					  $language_clauses[] = ' ( ' . implode(' OR ', $language_values) . ' ) ';
				  }
				  else {
					  $language_clauses[] = ' ( ' . implode(' AND ', $language_values) . ' ) ';
				  }
					CRM_Core_Error::debug_var('language_clauses', $language_clauses);
        //}
      //}
      $clauses[] = ' ( ' . implode(' OR ', $language_clauses) . ' ) ';
    }
    if ((array_sum($issues_no_op) > 0)) {
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
      if ($types != 'individuals') {
        $clauses[] = "( contact_a.contact_type LIKE 'Organization' OR $issue_clause )";
      }
      else {
        $clauses[] = $issue_clause;
      }
    }
    //if ($types == 'organisations') {
    //  $clauses[] = "contact_a.contact_type LIKE 'Organization'";
    //}
    //elseif ($types == 'individuals') {
    //  $clauses[] = "contact_a.contact_type LIKE 'Individual'";
    //}
    $conditions = implode(' AND ', $clauses);
    
    $where = $conditions;
    if ($types == 'organisations') {
      $where .= " AND ( contact_a.contact_type LIKE 'Individual' OR NOT EXISTS (
SELECT 1
FROM      civicrm_contact indiv_contact
LEFT JOIN civicrm_value_location_and_language_3 locationp0 ON ( locationp0.entity_id = indiv_contact.id )
LEFT JOIN civicrm_country country ON (locationp0.country_8 = country.id)
LEFT JOIN civicrm_value_structure__status_4 statusp0 ON ( statusp0.entity_id = indiv_contact.id )
LEFT JOIN civicrm_relationship relationship1 ON ( indiv_contact.id = relationship1.contact_id_b AND
                                                relationship1.relationship_type_id = '10' AND
                                                relationship1.is_active = 1 )
LEFT JOIN civicrm_value_location_and_language_3 locationp1 ON ( relationship1.contact_id_a = locationp1.entity_id )
LEFT JOIN civicrm_value_structure__status_4 statusp1 ON ( relationship1.contact_id_a = statusp1.entity_id )
LEFT JOIN civicrm_relationship relationship2 ON ( relationship1.contact_id_a = relationship2.contact_id_b AND
                                                relationship2.relationship_type_id = '10' AND
                                                relationship2.is_active = 1 )
LEFT JOIN civicrm_value_location_and_language_3 locationp2 ON ( relationship2.contact_id_a = locationp2.entity_id )
LEFT JOIN civicrm_value_structure__status_4 statusp2 ON ( relationship2.contact_id_a = statusp2.entity_id )
LEFT JOIN civicrm_relationship relationship3 ON ( relationship2.contact_id_a = relationship3.contact_id_b AND
                                                relationship3.relationship_type_id = '10' AND
                                                relationship3.is_active = 1 )
LEFT JOIN civicrm_value_location_and_language_3 locationp3 ON ( relationship3.contact_id_a = locationp3.entity_id )
LEFT JOIN civicrm_value_structure__status_4 statusp3 ON ( relationship3.contact_id_a = statusp3.entity_id )
LEFT JOIN civicrm_relationship relationship4 ON ( relationship3.contact_id_a = relationship4.contact_id_b AND
                                                relationship4.relationship_type_id = '10' AND
                                                relationship4.is_active = 1 )
LEFT JOIN civicrm_value_location_and_language_3 locationp4 ON ( relationship4.contact_id_a = locationp4.entity_id )
LEFT JOIN civicrm_value_structure__status_4 statusp4 ON ( relationship4.contact_id_a = statusp4.entity_id )
LEFT JOIN civicrm_relationship relationship5 ON ( relationship4.contact_id_a = relationship5.contact_id_b AND
                                                relationship5.relationship_type_id = '10' AND
                                                relationship5.is_active = 1 )
LEFT JOIN civicrm_value_location_and_language_3 locationp5 ON ( relationship5.contact_id_a = locationp5.entity_id )
LEFT JOIN civicrm_value_structure__status_4 statusp5 ON ( relationship5.contact_id_a = statusp5.entity_id ) 
";
$where .= " LEFT JOIN civicrm_relationship rel_org ON ( indiv_contact.id = rel_org.contact_id_a AND
                                            rel_org.is_active = 1 AND
                                            rel_org.relationship_type_id = 11 )
LEFT JOIN civicrm_contact rel_org_contact ON ( rel_org_contact.id = rel_org.contact_id_b )
LEFT JOIN civicrm_value_position_2 rel_issues ON ( rel_org.id = rel_issues.entity_id )
LEFT JOIN civicrm_value_location_and_language_3 rel_org_locationp0 ON ( rel_org.contact_id_b = rel_org_locationp0.entity_id )
LEFT JOIN civicrm_value_structure__status_4 rel_org_statusp0 ON ( rel_org.contact_id_b = rel_org_statusp0.entity_id )
LEFT JOIN civicrm_relationship rel_orgp1 ON ( rel_org.contact_id_b = rel_orgp1.contact_id_b AND
                                                rel_orgp1.relationship_type_id = '10' AND
                                                rel_orgp1.is_active = 1 )
LEFT JOIN civicrm_value_location_and_language_3 rel_org_locationp1 ON ( rel_orgp1.contact_id_a = rel_org_locationp1.entity_id )
LEFT JOIN civicrm_value_structure__status_4 rel_org_statusp1 ON ( rel_orgp1.contact_id_a = rel_org_statusp1.entity_id )
LEFT JOIN civicrm_relationship rel_orgp2 ON ( rel_orgp1.contact_id_a = rel_orgp2.contact_id_b AND
                                                rel_orgp2.relationship_type_id = '10' AND
                                                rel_orgp2.is_active = 1 )
LEFT JOIN civicrm_value_location_and_language_3 rel_org_locationp2 ON ( rel_orgp2.contact_id_a = rel_org_locationp2.entity_id )
LEFT JOIN civicrm_value_structure__status_4 rel_org_statusp2 ON ( rel_orgp2.contact_id_a = rel_org_statusp2.entity_id )
LEFT JOIN civicrm_relationship rel_orgp3 ON ( rel_orgp2.contact_id_a = rel_orgp3.contact_id_b AND
                                                rel_orgp3.relationship_type_id = '10' AND
                                                rel_orgp3.is_active = 1 )
LEFT JOIN civicrm_value_location_and_language_3 rel_org_locationp3 ON ( rel_orgp3.contact_id_a = rel_org_locationp3.entity_id )
LEFT JOIN civicrm_value_structure__status_4 rel_org_statusp3 ON ( rel_orgp3.contact_id_a = rel_org_statusp3.entity_id )
LEFT JOIN civicrm_relationship rel_orgp4 ON ( rel_orgp3.contact_id_a = rel_orgp4.contact_id_b AND
                                                rel_orgp4.relationship_type_id = '10' AND
                                                rel_orgp4.is_active = 1 )
LEFT JOIN civicrm_value_location_and_language_3 rel_org_locationp4 ON ( rel_orgp4.contact_id_a = rel_org_locationp4.entity_id )
LEFT JOIN civicrm_value_structure__status_4 rel_org_statusp4 ON ( rel_orgp4.contact_id_a = rel_org_statusp4.entity_id )
LEFT JOIN civicrm_relationship rel_orgp5 ON ( rel_orgp4.contact_id_a = rel_orgp5.contact_id_b AND
                                                rel_orgp5.relationship_type_id = '10' AND
                                                rel_orgp5.is_active = 1 )
LEFT JOIN civicrm_value_location_and_language_3 rel_org_locationp5 ON ( rel_orgp5.contact_id_a = rel_org_locationp5.entity_id )
LEFT JOIN civicrm_value_structure__status_4 rel_org_statusp5 ON ( rel_orgp5.contact_id_a = rel_org_statusp5.entity_id )
";
$conditions = str_replace("contact_a.contact_type LIKE 'Organization' OR ", '', $conditions);
$where .= "
 WHERE $conditions AND indiv_contact.contact_type LIKE 'Individual' AND contact_a.id = rel_org_contact.id ) ) ";
}

//CRM_Core_Error::debug_var('where', $where);
    return $this->whereClause($where, $params);
  }

  function setDefaultValues() {
    return array();
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

