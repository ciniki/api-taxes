<?php
//
// Description
// ===========
// This method will add a new tax rate for a tenant.  It will need to be assigned
// to tax types before it can be utilized anywhere.
//
// Arguments
// ---------
// 
// Returns
// -------
// <rsp stat='ok' id='34' />
//
function ciniki_taxes_rateAdd(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
        'name'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Name'),
        'location_id'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'0', 'name'=>'Location'),
        'item_percentage'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Item Percentage'),
        'item_amount'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Item Amount'),
        'invoice_amount'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Invoice Amount'),
        'flags'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'0', 'name'=>'Flags'),
        'start_date'=>array('required'=>'yes', 'blank'=>'yes', 'type'=>'datetimetoutc', 'name'=>'Start Date'),
        'end_date'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'type'=>'datetimetoutc', 'name'=>'End Date'),
        'type_ids'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'idlist', 'name'=>'Tax Types'),
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
    $rc = ciniki_taxes_checkAccess($ciniki, $args['tnid'], 'ciniki.taxes.rateAdd'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }

    if( $args['item_percentage'] == '' && $args['item_amount'] == '' && $args['invoice_amount'] == '' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.taxes.16', 'msg'=>'You must specify a item percentage, item amount or invoice amount.'));
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
    // Add the tax rate
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
    $rc = ciniki_core_objectAdd($ciniki, $args['tnid'], 'ciniki.taxes.rate', $args, 0x04);
    if( $rc['stat'] != 'ok' ) {
        ciniki_core_dbTransactionRollback($ciniki, 'ciniki.taxes');
        return $rc;
    }
    $rate_id = $rc['id'];

    //
    // Attach the tax rate to the specified tax types
    //
    if( isset($args['type_ids']) ) {
        foreach($args['type_ids'] as $tid) {
            $rc = ciniki_core_objectAdd($ciniki, $args['tnid'], 'ciniki.taxes.type_rate', 
                array('type_id'=>$tid, 'rate_id'=>$rate_id), 0x04);
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

    return array('stat'=>'ok', 'id'=>$rate_id);
}
?>
