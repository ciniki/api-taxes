<?php
//
// Description
// ===========
// This method will return the details for a tax rate.
//
// Arguments
// ---------
// 
// Returns
// -------
// <rsp stat='ok' id='34' />
//
function ciniki_taxes_rateGet(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        'rate_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tax Rate'), 
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
    $rc = ciniki_taxes_checkAccess($ciniki, $args['business_id'], 'ciniki.taxes.taxGet'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }

	ciniki_core_loadMethod($ciniki, 'ciniki', 'businesses', 'private', 'timezoneOffset');
	$utc_offset = ciniki_businesses_timezoneOffset($ciniki);
	ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
	$date_format = ciniki_users_dateFormat($ciniki);
	ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'datetimeFormat');
	$datetime_format = ciniki_users_datetimeFormat($ciniki);

	//
	// Get the details about a tax
	//
	$strsql = "SELECT ciniki_tax_rates.id, ciniki_tax_rates.name, "
		. "ciniki_tax_rates.item_percentage, ciniki_tax_rates.item_amount, ciniki_tax_rates.invoice_amount, "
		. "ciniki_tax_rates.flags, "
		. "DATE_FORMAT(CONVERT_TZ(ciniki_tax_rates.start_date, '+00:00', '" . ciniki_core_dbQuote($ciniki, $utc_offset) . "'), "
			. "'" . ciniki_core_dbQuote($ciniki, $date_format) . " %H:%i:%S') AS start_date, "
		. "DATE_FORMAT(CONVERT_TZ(ciniki_tax_rates.end_date, '+00:00', '" . ciniki_core_dbQuote($ciniki, $utc_offset) . "'), "
			. "'" . ciniki_core_dbQuote($ciniki, $date_format) . " %H:%i:%S') AS end_date, "
		. "ciniki_tax_type_rates.type_id AS type_ids "
		. "FROM ciniki_tax_rates "
		. "LEFT JOIN ciniki_tax_type_rates ON (ciniki_tax_rates.id = ciniki_tax_type_rates.rate_id "
			. "AND ciniki_tax_type_rates.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. ") "
		. "WHERE ciniki_tax_rates.id = '" . ciniki_core_dbQuote($ciniki, $args['rate_id']) . "' "
		. "AND ciniki_tax_rates.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "";
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
	$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.taxes', array(
		array('container'=>'rates', 'fname'=>'id', 'name'=>'rate',
			'fields'=>array('id', 'name', 'item_percentage', 'item_amount', 'invoice_amount',
				'flags', 'start_date', 'end_date', 'type_ids'),
			'idlists'=>array('type_ids')),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( !isset($rc['rates']) || !isset($rc['rates'][0]['rate']) ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1386', 'msg'=>'Unable to find the tax rate'));
	}
	$rate = $rc['rates'][0]['rate'];

	return array('stat'=>'ok', 'rate'=>$rate);
}
?>
