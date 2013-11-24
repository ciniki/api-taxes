<?php
//
// Description
// -----------
// This function will calculate the taxes for a list of items, and return
// the list of taxes to be applied.
//
// This function can be adapted in the future to outsource tax calculations to avalara
// or another service.
//
// Arguments
// ---------
//
// Returns
// -------
// <rsp stat='ok' />
//
function ciniki_taxes_calcInvoiceTaxes($ciniki, $business_id, $invoice) {

	if( !isset($invoice['items']) ) {
		return array('stat'=>'ok', 'taxes'=>array());
	}

	//
	// Get the taxes for a business, that are for the time period the invoice is in.
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'taxes', 'private', 'ratesForDate');
	$rc = ciniki_taxes_ratesForDate($ciniki, $business_id, $invoice['invoice_date']);
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( !isset($rc['rates']) ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1076', 'msg'=>'Unable to load taxes'));
	}
	$business_taxes = $rc['rates'];		// Taxes in array by id

	// 
	// Set the calculated amount to zero
	//
	foreach($business_taxes as $tid => $tax) {
		$business_taxes[$tid]['calculated_items_amount'] = 0;
		$business_taxes[$tid]['calculated_invoice_amount'] = 0;
	}

	//
	// Go through the invoice items and calculate the taxes
	//
	foreach($invoice['items'] as $iid => $item) {
		foreach($business_taxes as $tid => $tax) {
			//
			// Check if the tax should be applied
			//
			if( in_array($item['taxtype_id'], $tax['type_ids']) ) {
				if( $tax['item_percentage'] > 0 ) {
					$business_taxes[$tid]['calculated_items_amount'] += 
						($item['quantity'] * $item['unit_amount'])*($tax['item_percentage']/100);
				}
				if( $tax['item_amount'] > 0 ) {
					$business_taxes[$tid]['calculated_items_amount'] += $item['quantity']*$tax['item_amount'];
				}
				if( $tax['invoice_amount'] > 0 ) {
					$business_taxes[$tid]['calculated_invoice_amount'] = $tax['invoice_amount'];
				}
			}
		}
	}

	return array('stat'=>'ok', 'taxes'=>$business_taxes);
}
?>
