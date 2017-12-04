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
function ciniki_taxes_hooks_lookupTaxRatesProvince($ciniki, $tnid, $args) {

    //
    // Check country is passed
    //
    if( !isset($args['country']) || $args['country'] == '' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.taxes.4', 'msg'=>'You must specify a country.'));
    }
    if( !isset($args['province']) || $args['province'] == '' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.taxes.5', 'msg'=>'You must specify province.'));
    }

    //
    // Load the list of country codes
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'countryCodes');
    $rc = ciniki_core_countryCodes($ciniki);
    $country_codes = $rc['countries'];

    //
    // Check for the country code
    //
    if( isset($country_codes[$args['country']]) ) {
        $country_code = $args['country'];
    } else {
        $country_code = array_search($args['country'], $country_codes);
        if( $country_code === FALSE ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.taxes.6', 'msg'=>'You must specify a valid country.'));
        }
    }

    //
    // Find the applicable taxes
    //
    $strsql = "SELECT ciniki_tax_rates.id, "
        . "ciniki_tax_rates.name, "
        . "ciniki_tax_rates.location_id, "
        . "ciniki_tax_rates.item_percentage, "
        . "ciniki_tax_rates.item_amount, "
        . "ciniki_tax_rates.invoice_amount, "
        . "ciniki_tax_rates.flags "
        . "FROM ciniki_tax_locations, ciniki_tax_rates "
        . "WHERE ciniki_tax_locations.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "AND ciniki_tax_locations.country_code = '" . ciniki_core_dbQuote($ciniki, $country_code) . "' "
        . "AND ciniki_tax_locations.code = '" . ciniki_core_dbQuote($ciniki, $args['province']) . "' "
        . "AND ciniki_tax_locations.id = ciniki_tax_rates.location_id "
        . "AND ciniki_tax_rates.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
    $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.taxes', array(
        array('container'=>'rates', 'fname'=>'id', 
            'fields'=>array('id', 'name', 'location_id', 'item_percentage', 'item_amount', 'invoice_amount', 'flags')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['rates']) ) {
        return array('stat'=>'ok', 'rates'=>$rc['rates']);
    }

    return array('stat'=>'ok', 'rates'=>array());
}
?>
