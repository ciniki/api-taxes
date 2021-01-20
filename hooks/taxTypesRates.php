<?php
//
// Description
// -----------
// This function is available for other modules to pull a list of tax types to be displayed,
// and used when setting tax types for the other modules.
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_taxes_hooks_taxTypesRates($ciniki, $tnid, $args) {
    //
    // Get the list of tax types, both active and inactive.  This is used
    // but other modules, and inactive are required incase it's an old setting.
    //
    $strsql = "SELECT types.id, "
        . "types.name, "
        . "rates.id AS rate_id, "
        . "rates.name AS rate_name, "
        . "rates.item_percentage AS item_percentage "
        . "FROM ciniki_tax_types AS types "
        . "LEFT JOIN ciniki_tax_type_rates ON ("
            . "types.id = ciniki_tax_type_rates.type_id "
            . "AND ciniki_tax_type_rates.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . ") "
        . "LEFT JOIN ciniki_tax_rates AS rates ON ("
            . "ciniki_tax_type_rates.rate_id = rates.id "
            . "AND rates.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . "AND rates.start_date < UTC_TIMESTAMP "
            . "AND (rates.end_date = '0000-00-00 00:00:00' "
                . "OR rates.end_date > UTC_TIMESTAMP()) "
            . ") "
        . "WHERE types.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "AND (types.flags&0x01) = 0 "
        . "ORDER BY types.name "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
    $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.taxes', array(
        array('container'=>'types', 'fname'=>'id', 'fields'=>array('id', 'name')),
        array('container'=>'rates', 'fname'=>'rate_id', 'fields'=>array('id'=>'rate_id', 'name'=>'rate_name', 'item_percentage')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $types = isset($rc['types']) ? $rc['types'] : array();

    return array('stat'=>'ok', 'types'=>$types);
}
?>
