<?php
//
// Description
// ===========
// This method will add a new tax location for a business.  It will need to be assigned
// to tax types before it can be utilized anywhere.
//
// Arguments
// ---------
// 
// Returns
// -------
// <rsp stat='ok' id='34' />
//
function ciniki_taxes_locationAdd(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
		'name'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Name'),
		'code'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Code'),
		'country_code'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Country Code'),
		'start_postal_zip'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Start Range Postal/Zip Code'),
		'end_postal_zip'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'End Range Postal/Zip Code'),
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
    $rc = ciniki_taxes_checkAccess($ciniki, $args['business_id'], 'ciniki.taxes.locationAdd'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }

	//
	// Add the tax location
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
	$rc = ciniki_core_objectAdd($ciniki, $args['business_id'], 'ciniki.taxes.location', $args, 0x07);
	if( $rc['stat'] != 'ok' ) {
		ciniki_core_dbTransactionRollback($ciniki, 'ciniki.taxes');
		return $rc;
	}
	$location_id = $rc['id'];

	return array('stat'=>'ok', 'id'=>$location_id);
}
?>
