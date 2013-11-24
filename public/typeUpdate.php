<?php
//
// Description
// ===========
// This method will update an existing tax type for a business.
//
// Arguments
// ---------
// 
// Returns
// -------
// <rsp stat='ok' />
//
function ciniki_taxes_typeUpdate(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        'type_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tax Type'), 
		'name'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Name'),
		'flags'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Flags'),
		'rate_ids'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'idlist', 'name'=>'Tax Rates'),
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
    $rc = ciniki_taxes_checkAccess($ciniki, $args['business_id'], 'ciniki.taxes.typeUpdate'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }
	$modules = $rc['modules'];

	//
	// Check if the type name already exists
	//
	if( isset($args['name']) ) {
		$strsql = "SELECT id "
			. "FROM ciniki_tax_types "
			. "WHERE ciniki_tax_types.name = '" . ciniki_core_dbQuote($ciniki, $args['name']) . "' "
			. "AND ciniki_tax_types.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "";
		$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.taxes', 'type');
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( isset($rc['rows']) && count($rc['rows']) > 0 ) {
			return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1392', 'msg'=>'You already have a tax type with this name, please choose another'));
		}
	}

	//
	// Start the transaction
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionStart');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionRollback');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionCommit');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectDelete');
	$rc = ciniki_core_dbTransactionStart($ciniki, 'ciniki.taxes');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	//
	// Update the tax rate
	//
	$rc = ciniki_core_objectUpdate($ciniki, $args['business_id'], 'ciniki.taxes.type', 
		$args['type_id'], $args, 0x04);
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	//
	// Update the tax types
	//
	if( isset($args['rate_ids']) ) {
		//
		// Get the existing type ids for this tax rate
		//
		$strsql = "SELECT id, rate_id "
			. "FROM ciniki_tax_type_rates "
			. "WHERE type_id = '" . ciniki_core_dbQuote($ciniki, $args['type_id']) . "' "
			. "AND business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "";
		ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQueryList2');
		$rc = ciniki_core_dbQueryList2($ciniki, $strsql, 'ciniki.taxes', 'rates');
		if( $rc['stat'] != 'ok' ) {
			return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1391', 'msg'=>'Unable to find tax rates for tax type'));
		}
		$tax_rates = $rc['rates'];

		//
		// Remove rate ids no longer used
		//
		foreach($tax_rates as $trid => $rate_id) {
			// Check for any types that were previously assigned that do not exist in the new type list
			if( $args['rate_ids'] === '' || !in_array($rate_id, $args['rate_ids']) ) {
				$rc = ciniki_core_objectDelete($ciniki, $args['business_id'], 'ciniki.taxes.type_rate',
					$trid, NULL, 0x04);
				if( $rc['stat'] != 'ok' ) {
					ciniki_core_dbTransactionRollback($ciniki, 'ciniki.taxes');
					return $rc;
				}
			}
		}
		//
		// Add new type ids
		//
		foreach($args['rate_ids'] as $rate_id) {
			// Check for types that do not already exist in type list
			if( !in_array($rate_id, $tax_rates) ) {
				$rc = ciniki_core_objectAdd($ciniki, $args['business_id'], 'ciniki.taxes.type_rate', 
					array('type_id'=>$args['type_id'], 'rate_id'=>$rate_id), 0x04);
				if( $rc['stat'] != 'ok' ) {
					ciniki_core_dbTransactionRollback($ciniki, 'ciniki.taxes');
					return $rc;
				}
			}
		}
	}

	//
	// Commit the transaction
	//
	$rc = ciniki_core_dbTransactionCommit($ciniki, 'ciniki.taxes');
	if( $rc['stat'] != 'ok' ) {
		ciniki_core_dbTransactionRollback($ciniki, 'ciniki.taxes');
		return $rc;
	}

	//
	// Update the last_change date in the business modules
	// Ignore the result, as we don't want to stop user updates if this fails.
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'businesses', 'private', 'updateModuleChangeDate');
	ciniki_businesses_updateModuleChangeDate($ciniki, $args['business_id'], 'ciniki', 'taxes');

	return array('stat'=>'ok');
}
?>
