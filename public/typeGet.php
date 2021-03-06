<?php
//
// Description
// ===========
// This method will return the details for a tax type.
//
// Arguments
// ---------
// 
// Returns
// -------
// <rsp stat='ok' id='34' />
//
function ciniki_taxes_typeGet(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
        'type_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tax Type'), 
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
    $rc = ciniki_taxes_checkAccess($ciniki, $args['tnid'], 'ciniki.taxes.typeGet'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }

    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'timezoneOffset');
    $utc_offset = ciniki_tenants_timezoneOffset($ciniki);
    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'datetimeFormat');
    $datetime_format = ciniki_users_datetimeFormat($ciniki);

    //
    // Get the details about a tax
    //
    $strsql = "SELECT ciniki_tax_types.id, "
        . "ciniki_tax_types.name, "
        . "ciniki_tax_types.flags, "
        . "ciniki_tax_type_rates.rate_id AS rate_ids "
        . "FROM ciniki_tax_types "
        . "LEFT JOIN ciniki_tax_type_rates ON (ciniki_tax_types.id = ciniki_tax_type_rates.type_id "
            . "AND ciniki_tax_type_rates.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . ") "
        . "WHERE ciniki_tax_types.id = '" . ciniki_core_dbQuote($ciniki, $args['type_id']) . "' "
        . "AND ciniki_tax_types.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
    $rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.taxes', array(
        array('container'=>'types', 'fname'=>'id', 'name'=>'type',
            'fields'=>array('id', 'name', 'flags', 'rate_ids'),
            'idlists'=>array('rate_ids')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['types']) || !isset($rc['types'][0]['type']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.taxes.28', 'msg'=>'Unable to find the tax type'));
    }
    $type = $rc['types'][0]['type'];

    return array('stat'=>'ok', 'type'=>$type);
}
?>
