<?php
//
// Description
// ===========
// This method returns the list of tax locations both current and past for a tenant.
//
// Arguments
// ---------
// 
// Returns
// -------
// <rsp stat='ok' id='34' />
//
function ciniki_taxes_locationList(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
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
    $rc = ciniki_taxes_checkAccess($ciniki, $args['tnid'], 'ciniki.taxes.locationList'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }

//  ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'timezoneOffset');
//  $utc_offset = ciniki_tenants_timezoneOffset($ciniki);
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'intlSettings');
    $rc = ciniki_tenants_intlSettings($ciniki, $args['tnid']);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $intl_timezone = $rc['settings']['intl-default-timezone'];
    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
    $date_format = ciniki_users_dateFormat($ciniki, 'php');

    //
    // Get the list of future taxes
    //
    $strsql = "SELECT ciniki_tax_locations.id, "
        . "ciniki_tax_locations.name, "
        . "ciniki_tax_locations.code, "
        . "ciniki_tax_locations.country_code, "
        . "ciniki_tax_locations.start_postal_zip, "
        . "ciniki_tax_locations.end_postal_zip "
        . "FROM ciniki_tax_locations "
        . "WHERE ciniki_tax_locations.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "ORDER BY ciniki_tax_locations.code, ciniki_tax_locations.name "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
    $rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.taxes', array(
        array('container'=>'locations', 'fname'=>'id', 'name'=>'location',
            'fields'=>array('id', 'name', 'code', 'country_code', 'start_postal_zip', 'end_postal_zip')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['locations']) ) {
        $locations = $rc['locations'];
        foreach($locations as $lid => $location) {
            $locations[$lid]['location']['constraints'] = $location['location']['start_postal_zip'];
            if( $location['location']['end_postal_zip'] != '' 
                && $location['location']['start_postal_zip'] != $location['location']['end_postal_zip'] ) {
                $locations[$lid]['location']['constraints'] .= ' - ' . $location['location']['end_postal_zip'];
            }
        }
    } else {
        $locations = array();
    }

    return array('stat'=>'ok', 'locations'=>$locations);
}
?>
