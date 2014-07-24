<?php
//
// Description
// ===========
// This method returns the list of tax rates both current and past for a business.
//
// Arguments
// ---------
// 
// Returns
// -------
// <rsp stat='ok' id='34' />
//
function ciniki_taxes_rateList(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
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
    $rc = ciniki_taxes_checkAccess($ciniki, $args['business_id'], 'ciniki.taxes.rateList'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }
	$modules = $rc['modules'];

//	ciniki_core_loadMethod($ciniki, 'ciniki', 'businesses', 'private', 'timezoneOffset');
//	$utc_offset = ciniki_businesses_timezoneOffset($ciniki);
	ciniki_core_loadMethod($ciniki, 'ciniki', 'businesses', 'private', 'intlSettings');
	$rc = ciniki_businesses_intlSettings($ciniki, $args['business_id']);
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$intl_timezone = $rc['settings']['intl-default-timezone'];
	ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
	$date_format = ciniki_users_dateFormat($ciniki, 'php');

	//
	// Get the list of future taxes
	//
	$strsql = "SELECT ciniki_tax_rates.id, "
		. "ciniki_tax_rates.name, ";
	if( ($modules['ciniki.taxes']['flags']&0x01) > 0 ) {
		$strsql .= "ciniki_tax_rates.location_id, "
			. "IFNULL(ciniki_tax_locations.name, '') AS location_name, ";
	} else {
		$strsql .= "'0' AS location_id, '' AS location_name, ";
	}
	$strsql .= "ciniki_tax_rates.item_percentage, "
		. "ciniki_tax_rates.item_amount, "
		. "ciniki_tax_rates.invoice_amount, "
		. "ciniki_tax_rates.flags, "
		. "ciniki_tax_rates.start_date, "
		. "ciniki_tax_rates.end_date, "
		. "ciniki_tax_type_rates.type_id AS type_ids, "
		. "IFNULL(ciniki_tax_types.name, '') AS types "
		. "FROM ciniki_tax_rates "
		. "LEFT JOIN ciniki_tax_type_rates ON (ciniki_tax_rates.id = ciniki_tax_type_rates.rate_id "
			. "AND ciniki_tax_type_rates.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. ") ";
	if( ($modules['ciniki.taxes']['flags']&0x01) > 0 ) {
		$strsql .= "LEFT JOIN ciniki_tax_locations ON (ciniki_tax_rates.location_id = ciniki_tax_locations.id "
			. "AND ciniki_tax_locations.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. ") ";
	}
	$strsql .= "LEFT JOIN ciniki_tax_types ON (ciniki_tax_type_rates.type_id = ciniki_tax_types.id "
			. "AND ciniki_tax_type_rates.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "AND (ciniki_tax_types.flags&0x01) = 0 "
			. ") "
		. "WHERE ciniki_tax_rates.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND ciniki_tax_rates.start_date > UTC_TIMESTAMP() "
		. "ORDER BY ciniki_tax_rates.start_date DESC, ciniki_tax_rates.end_date DESC"
		. "";
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
	$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.taxes', array(
		array('container'=>'rates', 'fname'=>'id', 'name'=>'rate',
			'fields'=>array('id', 'name', 'location_id', 'location_name',
				'item_percentage', 'item_amount', 'invoice_amount',
				'flags', 'start_date', 'end_date', 'type_ids', 'types'),
			'utctotz'=>array('start_date'=>array('timezone'=>$intl_timezone, 'format'=>$date_format),
				'end_date'=>array('timezone'=>$intl_timezone, 'format'=>$date_format)),
			'idlists'=>array('type_ids'), 'lists'=>array('types')),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( isset($rc['rates']) ) {
		$future = $rc['rates'];
	} else {
		$future = array();
	}

	//
	// Get the list of current taxes
	//
	$strsql = "SELECT ciniki_tax_rates.id, "
		. "ciniki_tax_rates.name, ";
	if( ($modules['ciniki.taxes']['flags']&0x01) > 0 ) {
		$strsql .= "ciniki_tax_rates.location_id, "
			. "IFNULL(ciniki_tax_locations.name, '') AS location_name, ";
	} else {
		$strsql .= "'0' AS location_id, '' AS location_name, ";
	}
	$strsql .= "ciniki_tax_rates.item_percentage, "
		. "ciniki_tax_rates.item_amount, "
		. "ciniki_tax_rates.invoice_amount, "
		. "ciniki_tax_rates.flags, "
		. "ciniki_tax_rates.start_date, "
		. "ciniki_tax_rates.end_date, "
		. "ciniki_tax_type_rates.type_id AS type_ids, "
		. "IFNULL(ciniki_tax_types.name, '') AS types "
		. "FROM ciniki_tax_rates "
		. "LEFT JOIN ciniki_tax_type_rates ON (ciniki_tax_rates.id = ciniki_tax_type_rates.rate_id "
			. "AND ciniki_tax_type_rates.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. ") ";
	if( ($modules['ciniki.taxes']['flags']&0x01) > 0 ) {
		$strsql .= "LEFT JOIN ciniki_tax_locations ON (ciniki_tax_rates.location_id = ciniki_tax_locations.id "
			. "AND ciniki_tax_locations.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. ") ";
	}
	$strsql .= "LEFT JOIN ciniki_tax_types ON (ciniki_tax_type_rates.type_id = ciniki_tax_types.id "
			. "AND ciniki_tax_type_rates.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "AND (ciniki_tax_types.flags&0x01) = 0 "
			. ") "
		. "WHERE ciniki_tax_rates.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND ciniki_tax_rates.start_date < UTC_TIMESTAMP() "
		. "AND (ciniki_tax_rates.end_date = '0000-00-00 00:00:00' OR ciniki_tax_rates.end_date > UTC_TIMESTAMP()) "
		. "ORDER BY ciniki_tax_rates.start_date DESC, ciniki_tax_rates.end_date DESC"
		. "";
	$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.taxes', array(
		array('container'=>'rates', 'fname'=>'id', 'name'=>'rate',
			'fields'=>array('id', 'name', 'location_id', 'location_name', 
				'item_percentage', 'item_amount', 'invoice_amount',
				'flags', 'start_date', 'end_date', 'type_ids', 'types'),
			'utctotz'=>array('start_date'=>array('timezone'=>$intl_timezone, 'format'=>$date_format),
				'end_date'=>array('timezone'=>$intl_timezone, 'format'=>$date_format)),
			'idlists'=>array('type_ids'), 'lists'=>array('types')),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( isset($rc['rates']) ) {
		$current = $rc['rates'];
	} else {
		$current = array();
	}

	//
	// Get the list of past taxes
	//
	$strsql = "SELECT ciniki_tax_rates.id, "
		. "ciniki_tax_rates.name, ";
	if( ($modules['ciniki.taxes']['flags']&0x01) > 0 ) {
		$strsql .= "ciniki_tax_rates.location_id, "
			. "IFNULL(ciniki_tax_locations.name, '') AS location_name, ";
	} else {
		$strsql .= "'0' AS location_id, '' AS location_name, ";
	}
	$strsql .= "ciniki_tax_rates.item_percentage, "
		. "ciniki_tax_rates.item_amount, "
		. "ciniki_tax_rates.invoice_amount, "
		. "ciniki_tax_rates.flags, "
		. "ciniki_tax_rates.start_date, "
		. "ciniki_tax_rates.end_date, "
		. "ciniki_tax_type_rates.type_id AS type_ids, "
		. "IFNULL(ciniki_tax_types.name, '') AS types "
		. "FROM ciniki_tax_rates "
		. "LEFT JOIN ciniki_tax_type_rates ON (ciniki_tax_rates.id = ciniki_tax_type_rates.rate_id "
			. "AND ciniki_tax_type_rates.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. ") ";
	if( ($modules['ciniki.taxes']['flags']&0x01) > 0 ) {
		$strsql .= "LEFT JOIN ciniki_tax_locations ON (ciniki_tax_rates.location_id = ciniki_tax_locations.id "
			. "AND ciniki_tax_locations.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. ") ";
	}
	$strsql .= "LEFT JOIN ciniki_tax_types ON (ciniki_tax_type_rates.type_id = ciniki_tax_types.id "
			. "AND ciniki_tax_type_rates.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "AND (ciniki_tax_types.flags&0x01) = 0 "
			. ") "
		. "WHERE ciniki_tax_rates.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND ciniki_tax_rates.end_date <> '0000-00-00 00:00:00' "
		. "AND ciniki_tax_rates.end_date < UTC_TIMESTAMP() "
		. "ORDER BY ciniki_tax_rates.start_date DESC, ciniki_tax_rates.end_date DESC"
		. "";
	$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.taxes', array(
		array('container'=>'rates', 'fname'=>'id', 'name'=>'rate',
			'fields'=>array('id', 'name', 'location_id', 'location_name', 
				'item_percentage', 'item_amount', 'invoice_amount',
				'flags', 'start_date', 'end_date', 'type_ids', 'types'),
			'utctotz'=>array('start_date'=>array('timezone'=>$intl_timezone, 'format'=>$date_format),
				'end_date'=>array('timezone'=>$intl_timezone, 'format'=>$date_format)),
			'idlists'=>array('type_ids'), 'lists'=>array('types')),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( isset($rc['rates']) ) {
		$past = $rc['rates'];
	} else {
		$past = array();
	}

	return array('stat'=>'ok', 'future'=>$future, 'current'=>$current, 'past'=>$past);
}
?>
