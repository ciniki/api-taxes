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
function ciniki_taxes_calcInvoiceTaxes($ciniki, $tnid, $invoice) {

    if( !isset($invoice['items']) ) {
        return array('stat'=>'ok', 'taxes'=>array());
    }

    //
    // If locations is enabled, then decide if taxes should be calculated yet or not
    // If tax locations are not enabled, then all tax_location_ids will be zero.
    //
    $tax_location_id = 0; // Default to zero, or if no tax locations are being used
    if( isset($ciniki['tenant']['modules']['ciniki.taxes']['flags']) 
        && ($ciniki['tenant']['modules']['ciniki.taxes']['flags']&0x01) > 0 ) {
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
    // Get the taxes for a tenant, that are for the time period the invoice is in.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'taxes', 'private', 'ratesForDate');
    $rc = ciniki_taxes_ratesForDate($ciniki, $tnid, $invoice['date'], $tax_location_id);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['rates']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.taxes.7', 'msg'=>'Unable to load taxes'));
    }
    $tenant_taxes = $rc['rates'];     // Taxes in array by id

    // 
    // Set the calculated amount to zero
    //
    foreach($tenant_taxes as $tid => $tax) {
        $tenant_taxes[$tid]['used'] = 'no';
        $tenant_taxes[$tid]['calculated_items_amount'] = 0;
        $tenant_taxes[$tid]['calculated_invoice_amount'] = 0;
    }
    $preorder_taxes = $tenant_taxes;

    //
    // Go through the invoice items and calculate the taxes
    //
    $invoice['items'][] = array(
        'id'=>0,
        'amount' => (isset($invoice['shipping_amount']) ? $invoice['shipping_amount'] : 0),
        'preorder_amount' => (isset($invoice['preorder_shipping_amount']) ? $invoice['preorder_shipping_amount'] : 0),
        'taxtype_id' => (isset($invoice['taxtype_id']) ? $invoice['taxtype_id'] : 0),
        );
    foreach($invoice['items'] as $iid => $item) {
        foreach($tenant_taxes as $tid => $tax) {
            //
            // Check if the tax should be applied
            //
            if( isset($tax['types']) && is_array($tax['types']) 
                && array_key_exists($item['taxtype_id'], $tax['types']) 
                && $tax['location_id'] == $tax_location_id  // Double check tax_location_id
                ) {
                $tenant_taxes[$tid]['used'] = 'yes';
                // 
                // Calculate if tax is specified as included
                //
                if( ($tax['flags']&0x01) == 0x01 ) {
                    if( $tax['item_percentage'] > 0 ) {
                        //
                        // For 13% tax, tax_amount = item_amount * (13/113)
                        //
                        $item_amount = bcmul($item['amount'], bcdiv($tax['item_percentage'], 100 + $tax['item_percentage'], 4), 4);
                        $tenant_taxes[$tid]['calculated_items_amount'] = 
                            bcadd($tenant_taxes[$tid]['calculated_items_amount'], $item_amount, 4);
                    }
                } 
                //
                // Calculate taxes extra
                //
                else {
                    if( $tax['item_percentage'] > 0 ) {
                        $item_amount = bcmul($item['amount'], bcdiv($tax['item_percentage'], 100, 6), 4);
                        $tenant_taxes[$tid]['calculated_items_amount'] = 
                            bcadd($tenant_taxes[$tid]['calculated_items_amount'], $item_amount, 4);
                    }
                    if( $tax['item_amount'] > 0 ) {
                        $tenant_taxes[$tid]['calculated_items_amount'] = 
                            bcadd($tenant_taxes[$tid]['calculated_items_amount'], 
                                bcmul($item['quantity'], $tax['item_amount'], 4), 4);
                    }
                    if( $tax['invoice_amount'] > 0 ) {
                        $tenant_taxes[$tid]['calculated_invoice_amount'] = 
                            bcadd($tenant_taxes[$tid]['calculated_invoice_amount'], $tax['invoice_amount'], 4);
                    }
                }
            }
        }
        foreach($preorder_taxes as $tid => $tax) {
            //
            // Check if the tax should be applied
            //
            if( isset($tax['types']) && is_array($tax['types']) 
                && array_key_exists($item['taxtype_id'], $tax['types']) 
                && $tax['location_id'] == $tax_location_id  // Double check tax_location_id
                ) {
                $preorder_taxes[$tid]['used'] = 'yes';
                // 
                // Calculate if tax is specified as included
                //
                if( ($tax['flags']&0x01) == 0x01 ) {
                    if( $tax['item_percentage'] > 0 ) {
                        //
                        // For 13% tax, tax_amount = item_amount * (13/113)
                        //
                        $item_amount = bcmul($item['preorder_amount'], bcdiv($tax['item_percentage'], 100 + $tax['item_percentage'], 4), 4);
                        $preorder_taxes[$tid]['calculated_items_amount'] = 
                            bcadd($preorder_taxes[$tid]['calculated_items_amount'], $item_amount, 4);
                    }
                } 
                //
                // Calculate taxes extra
                //
                else {
                    if( $tax['item_percentage'] > 0 ) {
                        $item_amount = bcmul($item['preorder_amount'], bcdiv($tax['item_percentage'], 100, 6), 4);
                        $preorder_taxes[$tid]['calculated_items_amount'] = 
                            bcadd($preorder_taxes[$tid]['calculated_items_amount'], $item_amount, 4);
                    }
                    if( $tax['item_amount'] > 0 ) {
                        $preorder_taxes[$tid]['calculated_items_amount'] = 
                            bcadd($preorder_taxes[$tid]['calculated_items_amount'], 
                                bcmul($item['quantity'], $tax['item_amount'], 4), 4);
                    }
                    if( $tax['invoice_amount'] > 0 ) {
                        $preorder_taxes[$tid]['calculated_invoice_amount'] = 
                            bcadd($preorder_taxes[$tid]['calculated_invoice_amount'], $tax['invoice_amount'], 4);
                    }
                }
            }
        }
    }

    //
    // Remove unused taxes
    //
    foreach($tenant_taxes as $tid => $tax) {
        if( $tenant_taxes[$tid]['used'] == 'no' ) {
            unset($tenant_taxes[$tid]);
        }
    }
    foreach($preorder_taxes as $tid => $tax) {
        if( $preorder_taxes[$tid]['used'] == 'no' ) {
            unset($preorder_taxes[$tid]);
        }
    }

    return array('stat'=>'ok', 'taxes'=>$tenant_taxes, 'preorder_taxes'=>$preorder_taxes);
}
?>
