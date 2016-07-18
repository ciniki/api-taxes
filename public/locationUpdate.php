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
function ciniki_taxes_locationUpdate(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        'location_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Location'), 
        'name'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Name'),
        'code'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Code'),
        'country_code'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Country Code'),
        'start_postal_zip'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Start Range Postal/Zip Code'),
        'end_postal_zip'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'End Range Postal/Zip Code'),
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
    $rc = ciniki_taxes_checkAccess($ciniki, $args['business_id'], 'ciniki.taxes.locationUpdate'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }
    $modules = $rc['modules'];

    //
    // Update the tax location
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');
    $rc = ciniki_core_objectUpdate($ciniki, $args['business_id'], 'ciniki.taxes.location', 
        $args['location_id'], $args, 0x07);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    return array('stat'=>'ok');
}
?>
