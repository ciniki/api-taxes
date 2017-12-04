<?php
//
// Description
// ===========
// This method returns the list of tax types both current and past for a tenant.
//
// Arguments
// ---------
// 
// Returns
// -------
// <rsp stat='ok' id='34' />
//
function ciniki_taxes_typeList(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
        'locations'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Locations'), 
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
    $rc = ciniki_taxes_checkAccess($ciniki, $args['tnid'], 'ciniki.taxes.typeList'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }
    $modules = $rc['modules'];

    $rsp = array('stat'=>'ok', 'active'=>array(), 'inactive'=>array());

    //
    // Get the list of active tax types, with current tax rates
    //
    $strsql = "SELECT ciniki_tax_types.id, "
        . "ciniki_tax_types.name, "
        . "ciniki_tax_types.flags, "
        . "ciniki_tax_type_rates.rate_id AS rate_id, "
        . "ciniki_tax_rates.name AS rate_name "
        . "";
    if( ($modules['ciniki.taxes']['flags']&0x01) > 0 ) {
        $strsql .= ", IFNULL(ciniki_tax_locations.name, '') AS rate_location ";
    } else {
        $strsql .= ", '' AS rate_location ";
    }
    $strsql .= "FROM ciniki_tax_types "
        . "LEFT JOIN ciniki_tax_type_rates ON (ciniki_tax_types.id = ciniki_tax_type_rates.type_id "
            . "AND ciniki_tax_type_rates.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . ") "
        . "LEFT JOIN ciniki_tax_rates ON (ciniki_tax_type_rates.rate_id = ciniki_tax_rates.id "
            . "AND ciniki_tax_rates.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "AND ciniki_tax_rates.start_date < UTC_TIMESTAMP "
            . "AND (ciniki_tax_rates.end_date = '0000-00-00 00:00:00' "
                . "OR ciniki_tax_rates.end_date > UTC_TIMESTAMP()) "
            . ") ";
    if( ($modules['ciniki.taxes']['flags']&0x01) > 0 ) {
        $strsql .= "LEFT JOIN ciniki_tax_locations ON ("
            . "ciniki_tax_rates.location_id = ciniki_tax_locations.id "
            . "AND ciniki_tax_locations.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . ") ";
    }
    $strsql .= "WHERE ciniki_tax_types.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND (ciniki_tax_types.flags&0x01) = 0 "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
    $rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.taxes', array(
        array('container'=>'types', 'fname'=>'id', 'name'=>'type',
            'fields'=>array('id', 'name', 'flags')),
        array('container'=>'rates', 'fname'=>'rate_id', 'name'=>'rate',
            'fields'=>array('id'=>'rate_id', 'name'=>'rate_name', 'location'=>'rate_location')),
//          'fields'=>array('id', 'name', 'flags', 'rate_ids', 'rates'),
//          'idlists'=>array('rate_ids'), 'lists'=>array('rates')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['types']) ) {
        $rsp['active'] = $rc['types'];
//      foreach($rsp['active'] as $tid => $type) {
//          $rsp['active'][$tid]['type']['rate_text'] = '';
//          foreach($type['type']['rates'] as $rid => $rate) {
//              $rsp['active'][$tid]['type']['rate_text'] .= ($rsp['active'][$tid]['type']['rate_text']!=''?'<br/>':'');
//              if( ($modules['ciniki.taxes']['flags']&0x01) > 0 ) {
//                  $rsp['active'][$tid]['type']['rate_text'] .= $rate['rate']['location'] . ' - ';
//              }
//          }
//      }
    }

    //
    // Get the list of inactive tax types
    //
    $strsql = "SELECT ciniki_tax_types.id, "
        . "ciniki_tax_types.name, "
        . "ciniki_tax_types.flags, "
        . "ciniki_tax_type_rates.rate_id AS rate_ids, "
        . "ciniki_tax_rates.name AS rates "
        . "";
    if( ($modules['ciniki.taxes']['flags']&0x01) > 0 ) {
        $strsql .= ", IFNULL(ciniki_tax_locations.name, '') AS rate_location ";
    } else {
        $strsql .= ", '' AS rate_location ";
    }
    $strsql .= "FROM ciniki_tax_types "
        . "LEFT JOIN ciniki_tax_type_rates ON (ciniki_tax_types.id = ciniki_tax_type_rates.type_id "
            . "AND ciniki_tax_type_rates.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . ") "
        . "LEFT JOIN ciniki_tax_rates ON (ciniki_tax_type_rates.rate_id = ciniki_tax_rates.id "
            . "AND ciniki_tax_rates.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "AND ciniki_tax_rates.start_date < UTC_TIMESTAMP "
            . "AND (ciniki_tax_rates.end_date = '0000-00-00 00:00:00' "
                . "OR ciniki_tax_rates.end_date > UTC_TIMESTAMP()) "
            . ") ";
    if( ($modules['ciniki.taxes']['flags']&0x01) > 0 ) {
        $strsql .= "LEFT JOIN ciniki_tax_locations ON ("
            . "ciniki_tax_rates.location_id = ciniki_tax_locations.id "
            . "AND ciniki_tax_locations.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . ") ";
    }
    $strsql .= "WHERE ciniki_tax_types.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND (ciniki_tax_types.flags&0x01) = 1 "
        . "";
    $rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.taxes', array(
        array('container'=>'types', 'fname'=>'id', 'name'=>'type',
            'fields'=>array('id', 'name', 'flags')),
        array('container'=>'rates', 'fname'=>'rate_id', 'name'=>'rate',
            'fields'=>array('id'=>'rate_id', 'name'=>'rate_name', 'location'=>'rate_location')),
//          'fields'=>array('id', 'name', 'flags', 'rate_ids', 'rates'),
//          'idlists'=>array('rate_ids'), 'lists'=>array('rates')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['types']) ) {
        $rsp['inactive'] = $rc['types'];
    }

    //
    // If locations specified, get the list of known locations
    //
    if( isset($args['locations']) && $args['locations'] == 'yes' ) {
        $strsql = "SELECT id, code, name "
            . "FROM ciniki_tax_locations "
            . "WHERE ciniki_tax_locations.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
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
