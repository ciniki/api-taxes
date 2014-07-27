<?php
//
// Description
// ===========
// This method will return the details for a tax location.
//
// Arguments
// ---------
// 
// Returns
// -------
// <rsp stat='ok' id='34' />
//
function ciniki_taxes_locationGet(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        'location_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tax Rate'), 
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
    $rc = ciniki_taxes_checkAccess($ciniki, $args['business_id'], 'ciniki.taxes.locationGet'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }

//	ciniki_core_loadMethod($ciniki, 'ciniki', 'businesses', 'private', 'timezoneOffset');
//	$utc_offset = ciniki_businesses_timezoneOffset($ciniki);
//	ciniki_core_loadMethod($ciniki, 'ciniki', 'businesses', 'private', 'intlSettings');
//	$rc = ciniki_businesses_intlSettings($ciniki, $args['business_id']);
//	if( $rc['stat'] != 'ok' ) {
//		return $rc;
//	}
//	$intl_timezone = $rc['settings']['intl-default-timezone'];
//	ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
//	$date_format = ciniki_users_dateFormat($ciniki, 'php');
//	ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'datetimeFormat');
//	$datetime_format = ciniki_users_datetimeFormat($ciniki, 'php');

	//
	// Get the details about a tax
	//
	$strsql = "SELECT ciniki_tax_locations.id, ciniki_tax_locations.name, "
		. "ciniki_tax_locations.code, "
		. "ciniki_tax_locations.country_code, "
		. "ciniki_tax_locations.start_postal_zip, ciniki_tax_locations.end_postal_zip "
		. "FROM ciniki_tax_locations "
		. "WHERE ciniki_tax_locations.id = '" . ciniki_core_dbQuote($ciniki, $args['location_id']) . "' "
		. "AND ciniki_tax_locations.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "";
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
	$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.taxes', array(
		array('container'=>'locations', 'fname'=>'id', 'name'=>'location',
			'fields'=>array('id', 'name', 'code', 'country_code', 
				'start_postal_zip', 'end_postal_zip')),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( !isset($rc['locations']) || !isset($rc['locations'][0]['location']) ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1505', 'msg'=>'Unable to find the tax location'));
	}
	$location = $rc['locations'][0]['location'];

	return array('stat'=>'ok', 'location'=>$location);
}
?>
