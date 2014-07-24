<?php
//
// Description
// ===========
// This method will remove a tax location from a business, but only if it's not
// currently being used by any tax types or invoices.
//
// Arguments
// ---------
// 
// Returns
// -------
// <rsp stat='ok' id='34' />
//
function ciniki_taxes_locationDelete(&$ciniki) {
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
    $rc = ciniki_taxes_checkAccess($ciniki, $args['business_id'], 'ciniki.taxes.locationDelete'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }
	$modules = $rc['modules'];

	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbCount');

	//
	// Check if any tax types are currently using this tax location
	//
	// FIXME: Convert to check all modules <mod>/taxes/locationUsed.php, which will check if location is still being used by any records.
	$num_invoices = 0;
	if( isset($modules['ciniki.sapos']) ) {
		$strsql = "SELECT 'invoices', COUNT(*) "
			. "FROM ciniki_sapos_invoice_taxes "
			. "WHERE location_id = '" . ciniki_core_dbQuote($ciniki, $args['location_id']) . "' "
			. "AND business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "";
		$rc = ciniki_core_dbCount($ciniki, $strsql, 'ciniki.sapos', 'num');
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( isset($rc['num']['invoices']) && $rc['num']['invoices'] > 0 ) {
			return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1804', 'msg'=>'Invoices are still using this tax location, it cannot be deleted.'));
		}
	}

	//
	// Check if any invoices are currently using this tax location
	//
	$strsql = "SELECT 'rates', COUNT(*) "
		. "FROM ciniki_tax_rates "
		. "WHERE ciniki_tax_rates.location_id = '" . ciniki_core_dbQuote($ciniki, $args['location_id']) . "' "
		. "AND ciniki_tax_rates.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "";
	$rc = ciniki_core_dbCount($ciniki, $strsql, 'ciniki.taxes', 'num');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( isset($rc['num']['rates']) && $rc['num']['rates'] > 0 ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1824', 'msg'=>'There are still tax types using this tax location, it cannot be deleted.'));
	}

	//
	// Delete the tax location
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectDelete');
	$rc = ciniki_core_objectDelete($ciniki, $args['business_id'], 'ciniki.taxes.location', 
		$args['location_id'], NULL, 0x07);
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	
	return array('stat'=>'ok');
}
?>
