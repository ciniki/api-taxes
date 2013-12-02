<?php
//
// Description
// ===========
// This method will remove a tax type from a business, but only if it's not
// currently being used by any tax types or invoices.
//
// Arguments
// ---------
// 
// Returns
// -------
// <rsp stat='ok' id='34' />
//
function ciniki_taxes_typeDelete(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        'type_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tax Type'), 
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
    $rc = ciniki_taxes_checkAccess($ciniki, $args['business_id'], 'ciniki.taxes.typeDelete'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }

	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbCount');

	//
	// Check if any tax types are currently using this tax type
	//
	$num_invoices = 0;
	if( isset($modules['ciniki.products']) ) {
	}

	//
	// Check to make sure none of the default settings use this type
	//
	$strsql = "SELECT detail_key "
		. "FROM ciniki_tax_settings "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND detail_key LIKE 'default-type-%' "
		. "AND detail_value = '" . ciniki_core_dbQuote($ciniki, $args['type_id']) . "' "
		. "";
	$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.taxes', 'default');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( isset($rc['rows']) && count($rc['rows']) > 0 ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1388', 'msg'=>'There are module defaults set to using this tax type, they must be changed before the tax type can be removed.'));
	}
	
	//
	// Check if any invoices are currently using this tax rate
	//
	$strsql = "SELECT 'types', COUNT(*) "
		. "FROM ciniki_tax_type_rates "
		. "WHERE ciniki_tax_type_rates.type_id = '" . ciniki_core_dbQuote($ciniki, $args['type_id']) . "' "
		. "AND ciniki_tax_type_rates.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "";
	$rc = ciniki_core_dbCount($ciniki, $strsql, 'ciniki.taxes', 'num');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( isset($rc['num']['types']) && $rc['num']['types'] > 0 ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1389', 'msg'=>'There are still tax rates using this tax type, it cannot be deleted.'));
	}

	//
	// Delete the tax type
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectDelete');
	$rc = ciniki_core_objectDelete($ciniki, $args['business_id'], 'ciniki.taxes.type', 
		$args['type_id'], NULL, 0x07);
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	
	return array('stat'=>'ok');
}
?>
