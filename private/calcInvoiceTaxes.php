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
    // If locations is enabled, then decide if taxes should be calculated yet or not
    // If tax locations are not enabled, then all tax_location_ids will be zero.
    //
    $tax_location_id = 0; // Default to zero, or if no tax locations are being used
    if( isset($ciniki['business']['modules']['ciniki.taxes']['flags']) 
        && ($ciniki['business']['modules']['ciniki.taxes']['flags']&0x01) > 0 ) {
        //
        // Check if customer is specified, and if they have a location
        //
        if( isset($invoice['tax_location_id']) && $invoice['tax_location_id'] > 0 ) {
            $tax_location_id = $invoice['tax_location_id'];
        } else {
            //
            // If we don't have customer or shipping address then can't calculate taxes yet
            //
            return array('stat'=>'ok', 'taxes'=>array());
        }
    }

    //
    // Get the taxes for a business, that are for the time period the invoice is in.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'taxes', 'private', 'ratesForDate');
    $rc = ciniki_taxes_ratesForDate($ciniki, $business_id, $invoice['date'], $tax_location_id);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['rates']) ) {
        return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1076', 'msg'=>'Unable to load taxes'));
    }
    $business_taxes = $rc['rates'];     // Taxes in array by id

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
            if( isset($tax['types']) && is_array($tax['types']) 
                && array_key_exists($item['taxtype_id'], $tax['types']) 
                && $tax['location_id'] == $tax_location_id  // Double check tax_location_id
                ) {
                if( $tax['item_percentage'] > 0 ) {
                    $item_amount = bcmul($item['amount'], bcdiv($tax['item_percentage'], 100, 6), 4);
                    $business_taxes[$tid]['calculated_items_amount'] = 
                        bcadd($business_taxes[$tid]['calculated_items_amount'], $item_amount, 4);
                }
                if( $tax['item_amount'] > 0 ) {
                    $business_taxes[$tid]['calculated_items_amount'] = 
                        bcadd($business_taxes[$tid]['calculated_items_amount'], 
                            bcmul($item['quantity'], $tax['item_amount'], 4), 4);
                }
                if( $tax['invoice_amount'] > 0 ) {
                    $business_taxes[$tid]['calculated_invoice_amount'] = 
                        bcadd($business_taxes[$tid]['calculated_invoice_amount'], $tax['invoice_amount'], 4);
                }
            }
        }
    }

    return array('stat'=>'ok', 'taxes'=>$business_taxes);
}
?>
