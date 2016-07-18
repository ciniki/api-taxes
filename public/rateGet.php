<?php
//
// Description
// ===========
// This method will return the details for a tax rate.
//
// Arguments
// ---------
// 
// Returns
// -------
// <rsp stat='ok' id='34' />
//
function ciniki_taxes_rateGet(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        'rate_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tax Rate'), 
        'locations'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Locations'), 
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
    $rc = ciniki_taxes_checkAccess($ciniki, $args['business_id'], 'ciniki.taxes.rateGet'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }
    $modules = $rc['modules'];

//  ciniki_core_loadMethod($ciniki, 'ciniki', 'businesses', 'private', 'timezoneOffset');
//  $utc_offset = ciniki_businesses_timezoneOffset($ciniki);
    ciniki_core_loadMethod($ciniki, 'ciniki', 'businesses', 'private', 'intlSettings');
    $rc = ciniki_businesses_intlSettings($ciniki, $args['business_id']);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $intl_timezone = $rc['settings']['intl-default-timezone'];
    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
    $date_format = ciniki_users_dateFormat($ciniki, 'php');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'datetimeFormat');
    $datetime_format = ciniki_users_datetimeFormat($ciniki, 'php');

    //
    // Get the details about a tax
    //
    $strsql = "SELECT ciniki_tax_rates.id, ciniki_tax_rates.name, "
        . "ciniki_tax_rates.location_id, "
        . "ciniki_tax_rates.item_percentage, ciniki_tax_rates.item_amount, ciniki_tax_rates.invoice_amount, "
        . "ciniki_tax_rates.flags, "
        . "ciniki_tax_rates.start_date, "
        . "ciniki_tax_rates.end_date, "
        . "ciniki_tax_type_rates.type_id AS type_ids "
        . "FROM ciniki_tax_rates "
        . "LEFT JOIN ciniki_tax_type_rates ON (ciniki_tax_rates.id = ciniki_tax_type_rates.rate_id "
            . "AND ciniki_tax_type_rates.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
            . ") "
        . "WHERE ciniki_tax_rates.id = '" . ciniki_core_dbQuote($ciniki, $args['rate_id']) . "' "
        . "AND ciniki_tax_rates.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
    $rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.taxes', array(
        array('container'=>'rates', 'fname'=>'id', 'name'=>'rate',
            'fields'=>array('id', 'name', 'location_id', 'item_percentage', 'item_amount', 'invoice_amount',
                'flags', 'start_date', 'end_date', 'type_ids'),
            'utctotz'=>array('start_date'=>array('timezone'=>$intl_timezone, 'format'=>$datetime_format),
                'end_date'=>array('timezone'=>$intl_timezone, 'format'=>$datetime_format)),
            'idlists'=>array('type_ids')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['rates']) || !isset($rc['rates'][0]['rate']) ) {
        return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1394', 'msg'=>'Unable to find the tax rate'));
    }
    $rsp = array('stat'=>'ok', 'rate'=>$rc['rates'][0]['rate']);

    //
    // Get the available locations
    //
    if( ($modules['ciniki.taxes']['flags']&0x02) > 0 
        && isset($args['locations']) && $args['locations'] == 'yes' 
        ) {
        $strsql = "SELECT id, code, name "
            . "FROM ciniki_tax_locations "
            . "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
            . "ORDER BY code, name "
            . "";
        $rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.taxes', array(
            array('container'=>'locations', 'fname'=>'id', 'name'=>'location',
                'fields'=>array('id', 'code', 'name')),
            ));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['locations']) ) {
            $rsp['locations'] = $rc['locations'];
        } else {
            $rsp['locations'] = array();
        }
    }

    return $rsp;
}
?>
