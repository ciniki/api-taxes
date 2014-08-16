<?php
//
// Description
// -----------
// This function will return the taxes applicable for a certain date for a business.
//
// Arguments
// ---------
// ciniki:
//
// Returns
// -------
// <rsp stat='ok' />
//
function ciniki_taxes_ratesForDate($ciniki, $business_id, $tax_date, $location_id) {
	
	//
	// Get the taxes for the business, based on the tax_date supplied
	//
	$strsql = "SELECT ciniki_tax_rates.id, "
		. "ciniki_tax_rates.name, "
		. "ciniki_tax_rates.item_percentage, "
		. "ciniki_tax_rates.item_amount, "
		. "ciniki_tax_rates.location_id, "
		. "ciniki_tax_rates.invoice_amount, "
		. "ciniki_tax_type_rates.type_id "
		. "FROM ciniki_tax_rates "
		. "LEFT JOIN ciniki_tax_type_rates ON (ciniki_tax_rates.id = ciniki_tax_type_rates.rate_id "
			. "AND ciniki_tax_type_rates.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
			. ") "
		. "WHERE ciniki_tax_rates.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
		. "AND ciniki_tax_rates.location_id = '" . ciniki_core_dbQuote($ciniki, $location_id) . "' "
		. "AND ciniki_tax_rates.start_date <= '" . ciniki_core_dbQuote($ciniki, $tax_date) . "' "
		. "AND (ciniki_tax_rates.end_date = '0000-00-00 00:00:00' " // No end date specified
			. "OR ciniki_tax_rates.end_date >= '" . ciniki_core_dbQuote($ciniki, $tax_date) . "') "
		. "";
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
	$rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.sapos', array(
		array('container'=>'rates', 'fname'=>'id',
			'fields'=>array('id', 'name', 'item_percentage', 'item_amount', 'location_id', 'invoice_amount')),
		array('container'=>'types', 'fname'=>'type_id',
			'fields'=>array('id')),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( !isset($rc['rates']) ) {
		return array('stat'=>'ok', 'rates'=>array());
	}
	$rates = $rc['rates'];

	//
	// FIXME: Check for any blank out dates, or tax holidays
	//

	return array('stat'=>'ok', 'rates'=>$rates);
}
?>
