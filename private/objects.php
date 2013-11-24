<?php
//
// Description
// -----------
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_taxes_objects($ciniki) {
	$objects = array();
	$objects['type'] = array(
		'name'=>'Tax Type',
		'sync'=>'yes',
		'table'=>'ciniki_tax_types',
		'fields'=>array(
			'name'=>array(),
			'flags'=>array(),
			),
		'history_table'=>'ciniki_tax_history',
		);
	$objects['rate'] = array(
		'name'=>'Tax Rate',
		'sync'=>'yes',
		'table'=>'ciniki_tax_rates',
		'fields'=>array(
			'name'=>array(),
			'item_percentage'=>array(),
			'item_amount'=>array(),
			'invoice_amount'=>array(),
			'flags'=>array(),
			'start_date'=>array(),
			'end_date'=>array(),
			),
		'history_table'=>'ciniki_tax_history',
		);
	$objects['type_rate'] = array(
		'name'=>'Type Rate',
		'sync'=>'yes',
		'table'=>'ciniki_tax_type_rates',
		'fields'=>array(
			'type_id'=>array('ref'=>'ciniki.taxes.type'),
			'rate_id'=>array('ref'=>'ciniki.taxes.rate'),
			),
		'history_table'=>'ciniki_tax_history',
		);
	$objects['setting'] = array(
		'type'=>'settings',
		'name'=>'Tax Settings',
		'table'=>'ciniki_tax_settings',
		'history_table'=>'ciniki_tax_history',
		);
	return array('stat'=>'ok', 'objects'=>$objects);
}
?>
