<?php
//
// Description
// -----------
// This function will return the settings that should be sent to the UI.
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_taxes_hooks_uiSettings($ciniki, $business_id, $args) {

    $rsp = array('stat'=>'ok', 'settings'=>array(), 'settings_menu_items'=>array());

    //
    // Get the list of tax types, both active and inactive.  This is used
    // but other modules, and inactive are required incase it's an old setting.
    //
    $strsql = "SELECT ciniki_tax_types.id, "
        . "ciniki_tax_types.name, "
        . "IF((ciniki_tax_types.flags&0x01)=1, 'inactive', 'active') AS active, "
        . "IFNULL(ciniki_tax_rates.name,'') AS rates "
        . "FROM ciniki_tax_types "
        . "LEFT JOIN ciniki_tax_type_rates ON (ciniki_tax_types.id = ciniki_tax_type_rates.type_id "
            . "AND ciniki_tax_type_rates.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
            . ") "
        . "LEFT JOIN ciniki_tax_rates ON (ciniki_tax_type_rates.rate_id = ciniki_tax_rates.id "
            . "AND ciniki_tax_rates.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
            . "AND ciniki_tax_rates.start_date < UTC_TIMESTAMP "
            . "AND (ciniki_tax_rates.end_date = '0000-00-00 00:00:00' "
                . "OR ciniki_tax_rates.end_date > UTC_TIMESTAMP()) "
            . ") "
        . "WHERE ciniki_tax_types.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
        . "AND (ciniki_tax_types.flags&0x01) = 0 "
        . "ORDER BY ciniki_tax_types.name "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
    $rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.taxes', array(
        array('container'=>'types', 'fname'=>'id', 'name'=>'type',
            'fields'=>array('id', 'name', 'active', 'rates'),
            'lists'=>array('rates')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['types']) ) {
        $rsp['settings']['types'] = $rc['types'];
    } else {
        $rsp['settings']['types'] = array();
    }

    //
    // Get the list of tax locations if that is enabled
    //
    if( ($args['modules']['ciniki.taxes']['flags']&0x01) > 0 ) {
        $strsql = "SELECT ciniki_tax_locations.id, "
            . "ciniki_tax_locations.code, "
            . "ciniki_tax_locations.name, "
            . "ciniki_tax_locations.country_code, "
            . "ciniki_tax_locations.start_postal_zip, "
            . "ciniki_tax_locations.end_postal_zip, "
            . "ciniki_tax_rates.name AS rates "
            . "FROM ciniki_tax_locations "
            . "LEFT JOIN ciniki_tax_rates ON ("
                . "ciniki_tax_locations.id = ciniki_tax_rates.location_id "
                . "AND ciniki_tax_rates.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
                . ") "
            . "WHERE ciniki_tax_locations.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
            . "";
        $rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.taxes', array(
            array('container'=>'locations', 'fname'=>'id', 'name'=>'location',
                'fields'=>array('id', 'code', 'name', 'country_code', 'start_postal_zip', 'end_postal_zip', 'rates'),
                'lists'=>array('rates')),
            ));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['locations']) ) {
            $rsp['settings']['locations'] = $rc['locations'];
        } else {
            $rsp['settings']['locations'] = array();
        }
    }

    if( isset($ciniki['business']['modules']['ciniki.taxes']) 
        && (isset($args['permissions']['owners'])
            || isset($args['permissions']['resellers'])
            || ($ciniki['session']['user']['perms']&0x01) == 0x01
            )
        ) {
        $rsp['settings_menu_items'][] = array('priority'=>3000, 'label'=>'Taxes', 'edit'=>array('app'=>'ciniki.taxes.settings'));
    }

    return $rsp;
}
?>
