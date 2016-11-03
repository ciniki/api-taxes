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
    // Check if any modules are currently using this tax type
    //
    foreach($modules as $module => $m) {
        list($pkg, $mod) = explode('.', $module);
        $rc = ciniki_core_loadMethod($ciniki, $pkg, $mod, 'taxes', 'checkObjectUsed');
        if( $rc['stat'] == 'ok' ) {
            $fn = $rc['function_call'];
            $rc = $fn($ciniki, $modules, $args['business_id'], 'ciniki.taxes.location', $args['location_id']);
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.taxes.12', 'msg'=>'Unable to check if type is still be used', 'err'=>$rc['err']));
            }
            if( $rc['used'] != 'no' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.taxes.13', 'msg'=>"Tax location is still in use. " . $rc['msg']));
            }
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
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.taxes.14', 'msg'=>'There are still tax types using this tax location, it cannot be deleted.'));
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
