<?php
//
// Description
// ===========
// This method returns the list of tax types both current and past for a business.
//
// Arguments
// ---------
// 
// Returns
// -------
// <rsp stat='ok' id='34' />
//
function ciniki_taxes_typeList(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        'locations'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Locations'), 
        )); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    $args = $rc['args'];

    //  
    // Make sure this module is activated, and
    // check permission to run this function for this business
    //  
	ciniki_core_loadMethod($ciniki, 'ciniki', 'taxes', 'private', 'checkAccess');
    $rc = ciniki_taxes_checkAccess($ciniki, $args['business_id'], 'ciniki.taxes.typeList'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }

	ciniki_core_loadMethod($ciniki, 'ciniki', 'businesses', 'private', 'timezoneOffset');
	$utc_offset = ciniki_businesses_timezoneOffset($ciniki);

	$rsp = array('stat'=>'ok', 'active'=>array(), 'inactive'=>array());

	//
	// Get the list of active tax types, with current tax rates
	//
	$strsql = "SELECT ciniki_tax_types.id, "
		. "ciniki_tax_types.name, "
		. "ciniki_tax_types.flags, "
		. "ciniki_tax_type_rates.rate_id AS rate_ids, "
		. "ciniki_tax_rates.name AS rates "
		. "FROM ciniki_tax_types "
		. "LEFT JOIN ciniki_tax_type_rates ON (ciniki_tax_types.id = ciniki_tax_type_rates.type_id "
			. "AND ciniki_tax_type_rates.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. ") "
		. "LEFT JOIN ciniki_tax_rates ON (ciniki_tax_type_rates.rate_id = ciniki_tax_rates.id "
			. "AND ciniki_tax_rates.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "AND ciniki_tax_rates.start_date < UTC_TIMESTAMP "
			. "AND (ciniki_tax_rates.end_date = '0000-00-00 00:00:00' "
				. "OR ciniki_tax_rates.end_date > UTC_TIMESTAMP()) "
			. ") "
		. "WHERE ciniki_tax_types.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND (ciniki_tax_types.flags&0x01) = 0 "
		. "";
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
	$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.taxes', array(
		array('container'=>'types', 'fname'=>'id', 'name'=>'type',
			'fields'=>array('id', 'name', 'flags', 'rate_ids', 'rates'),
			'idlists'=>array('rate_ids'), 'lists'=>array('rates')),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( isset($rc['types']) ) {
		$rsp['active'] = $rc['types'];
	}

	//
	// Get the list of inactive tax types
	//
	$strsql = "SELECT ciniki_tax_types.id, "
		. "ciniki_tax_types.name, "
		. "ciniki_tax_types.flags, "
		. "ciniki_tax_type_rates.rate_id AS rate_ids, "
		. "ciniki_tax_rates.name AS rates "
		. "FROM ciniki_tax_types "
		. "LEFT JOIN ciniki_tax_type_rates ON (ciniki_tax_types.id = ciniki_tax_type_rates.type_id "
			. "AND ciniki_tax_type_rates.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. ") "
		. "LEFT JOIN ciniki_tax_rates ON (ciniki_tax_type_rates.rate_id = ciniki_tax_rates.id "
			. "AND ciniki_tax_rates.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "AND ciniki_tax_rates.start_date < UTC_TIMESTAMP "
			. "AND (ciniki_tax_rates.end_date = '0000-00-00 00:00:00' "
				. "OR ciniki_tax_rates.end_date > UTC_TIMESTAMP()) "
			. ") "
		. "WHERE ciniki_tax_types.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND (ciniki_tax_types.flags&0x01) = 1 "
		. "";
	$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.taxes', array(
		array('container'=>'types', 'fname'=>'id', 'name'=>'type',
			'fields'=>array('id', 'name', 'flags', 'rate_ids', 'rates'),
			'idlists'=>array('rate_ids'), 'lists'=>array('rates')),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( isset($rc['types']) ) {
		$rsp['inactive'] = $rc['types'];
	}

	//
	// If locations specified, get the list of known locations
	//
	if( isset($args['locations']) && $args['locations'] == 'yes' ) {
		$strsql = "SELECT id, name "
			. "FROM ciniki_tax_locations "
			. "WHERE ciniki_tax_locations.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "";
		$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.taxes', array(
			array('container'=>'locations', 'fname'=>'id', 'name'=>'location',
				'fields'=>array('id', 'name')),
			));
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( isset($rc['locations']) ) {
			$rsp['locations'] = $rc['locations'];
		}
	}

	return $rsp;
}
?>
