<?php
//
// Description
// ===========
// This method will add a new tax type for a tenant.  It will need to be assigned
// to tax types before it can be utilized anywhere.
//
// Arguments
// ---------
// 
// Returns
// -------
// <rsp stat='ok' id='34' />
//
function ciniki_taxes_typeAdd(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
        'name'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Name'),
        'flags'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'0', 'name'=>'Flags'),
        'rate_ids'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'idlist', 'name'=>'Tax Rates'),
        )); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    $args = $rc['args'];

    //  
    // Make sure this module is activated, and
    // check permission to run this function for this tenant
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'taxes', 'private', 'checkAccess');
    $rc = ciniki_taxes_checkAccess($ciniki, $args['tnid'], 'ciniki.taxes.typeAdd'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }
    
    //
    // Check if the type name already exists
    //
    $strsql = "SELECT id "
        . "FROM ciniki_tax_types "
        . "WHERE ciniki_tax_types.name = '" . ciniki_core_dbQuote($ciniki, $args['name']) . "' "
        . "AND ciniki_tax_types.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.taxes', 'type');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['rows']) && count($rc['rows']) > 0 ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.taxes.23', 'msg'=>'You already have a tax type with this name, please choose another'));
    }

    //
    // Start the transaction
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionStart');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionRollback');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionCommit');
    $rc = ciniki_core_dbTransactionStart($ciniki, 'ciniki.taxes');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Add the tax type
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
    $rc = ciniki_core_objectAdd($ciniki, $args['tnid'], 'ciniki.taxes.type', $args, 0x04);
    if( $rc['stat'] != 'ok' ) {
        ciniki_core_dbTransactionRollback($ciniki, 'ciniki.taxes');
        return $rc;
    }
    $type_id = $rc['id'];

    //
    // Attach the tax rate to the specified tax types
    //
    if( isset($args['rate_ids']) ) {
        foreach($args['rate_ids'] as $rid) {
            $rc = ciniki_core_objectAdd($ciniki, $args['tnid'], 'ciniki.taxes.type_rate', 
                array('type_id'=>$type_id, 'rate_id'=>$rid), 0x04);
            if( $rc['stat'] != 'ok' ) {
                ciniki_core_dbTransactionRollback($ciniki, 'ciniki.taxes');
                return $rc;
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
    // Update the last_change date in the tenant modules
    // Ignore the result, as we don't want to stop user updates if this fails.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'updateModuleChangeDate');
    ciniki_tenants_updateModuleChangeDate($ciniki, $args['tnid'], 'ciniki', 'taxes');

    return array('stat'=>'ok', 'id'=>$type_id);
}
?>
