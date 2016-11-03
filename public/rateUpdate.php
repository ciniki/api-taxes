<?php
//
// Description
// ===========
// This method will update an existing tax for a business.  The tax amounts 
// (item_percentage, item_amount, invoice_amount) can only be changed if 
// they are not currently being referenced by any invoices.  
//
// Arguments
// ---------
// 
// Returns
// -------
// <rsp stat='ok' />
//
function ciniki_taxes_rateUpdate(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        'rate_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tax Rate'), 
        'name'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Name'),
        'location_id'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Location'),
        'item_percentage'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Item Percentage'),
        'item_amount'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Item Amount'),
        'invoice_amount'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Invoice Amount'),
        'flags'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Flags'),
        'start_date'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'datetimetoutc', 'name'=>'Start Date'),
        'end_date'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'datetimetoutc', 'name'=>'End Date'),
        'type_ids'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'idlist', 'name'=>'Tax Types'),
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
    $rc = ciniki_taxes_checkAccess($ciniki, $args['business_id'], 'ciniki.taxes.rateUpdate'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }
    $modules = $rc['modules'];

    //
    // Check if this tax has been used yet
    //
    $num_invoices = 0;
    if( isset($modules['ciniki.sapos']) ) {
        $strsql = "SELECT 'invoices', COUNT(*) "
            . "FROM ciniki_sapos_invoice_taxes "
            . "WHERE taxrate_id = '" . ciniki_core_dbQuote($ciniki, $args['rate_id']) . "' "
            . "AND business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbCount');
        $rc = ciniki_core_dbCount($ciniki, $strsql, 'ciniki.sapos', 'num');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['num']['invoices']) ) {
            // Add the number of invoices in this module
            $num_invoices += $rc['num']['invoices'];
        }
    }

    //
    // It doesn't matter if there are tax types using this tax rate, it can be changed
    // if there are no invoices yet.
    //

    //
    // The tax values for item_percentage, item_amount or invoice_amount can only be changed
    // if there are no invoices that use them.  Once a tax is used, it can no longer be changed,
    // a new tax needs to be created.
    //
    if( (isset($args['item_percentage']) || isset($args['item_amount']) || isset($args['invoice_amount']))
        && $num_invoices > 0 ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.taxes.21', 'msg'=>'Unable to update tax, there are invoices using this tax.  Please create a new tax'));
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
    $rc = ciniki_core_objectUpdate($ciniki, $args['business_id'], 'ciniki.taxes.rate', 
        $args['rate_id'], $args, 0x04);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Update the tax types
    //
    if( isset($args['type_ids']) ) {
        //
        // Get the existing type ids for this tax rate
        //
        $strsql = "SELECT id, type_id "
            . "FROM ciniki_tax_type_rates "
            . "WHERE ciniki_tax_type_rates.rate_id = '" . ciniki_core_dbQuote($ciniki, $args['rate_id']) . "' "
            . "AND ciniki_tax_type_rates.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQueryList2');
        $rc = ciniki_core_dbQueryList2($ciniki, $strsql, 'ciniki.taxes', 'types');
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.taxes.22', 'msg'=>'Unable to find tax types for tax rate'));
        }
        $tax_types = $rc['types'];

        //
        // Remote type ids no longer used
        //
        foreach($tax_types as $trid => $type_id) {
            // Check for any types that were previously assigned that do not exist in the new type list
            if( !in_array($type_id, $args['type_ids']) ) {
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
        if( is_array($args['type_ids']) ) {
            foreach($args['type_ids'] as $tid => $type_id) {
                // Check for types that do not already exist in type list
                if( !in_array($type_id, $tax_types) ) {
                    $rc = ciniki_core_objectAdd($ciniki, $args['business_id'], 'ciniki.taxes.type_rate', 
                        array('type_id'=>$type_id, 'rate_id'=>$args['rate_id']), 0x04);
                    if( $rc['stat'] != 'ok' ) {
                        ciniki_core_dbTransactionRollback($ciniki, 'ciniki.taxes');
                        return $rc;
                    }
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
