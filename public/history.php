<?php
//
// Description
// -----------
// This method will return the list of actions that were applied to an element of an tax rate. 
// This method is typically used by the UI to display a list of changes that have occured 
// on an element through time. This information can be used to revert elements to a previous value.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:			The ID of the business to get the details for.
// rate_id:				The ID of the tax rate to get the history for.
// field:				The field to get the history for. 
//
// Returns
// -------
// <history>
// <action user_id="2" date="May 12, 2012 10:54 PM" value="Invoice Name" age="2 months" user_display_name="Andrew" />
// ...
// </history>
//
function ciniki_taxes_history($ciniki) {
	//
	// Find all the required and optional arguments
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
	$rc = ciniki_core_prepareArgs($ciniki, 'no', array(
		'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
		'object'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Object'), 
		'object_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'ID'), 
		'field'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Field'), 
		'field_value'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Field Value'), 
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$args = $rc['args'];
	
	//
	// Check access to business_id as owner, or sys admin
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'taxes', 'private', 'checkAccess');
	$rc = ciniki_taxes_checkAccess($ciniki, $args['business_id'], 'ciniki.taxes.history');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	if( $args['object'] == 'ciniki.taxes.rate' ) {
		if( $args['field'] == 'type_id' ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbGetModuleHistoryLinkedToggle');
			return ciniki_core_dbGetModuleHistoryLinkedToggle($ciniki, 'ciniki.taxes', 'ciniki_tax_history',
				$args['business_id'], 'ciniki_tax_type_rates', 
				'rate_id', $args['object_id'], 'type_id', $args['field_value']);
		}
		if( $args['field'] == 'start_date' || $args['field'] == 'end_date' ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbGetModuleHistoryReformat');
			return ciniki_core_dbGetModuleHistoryReformat($ciniki, 'ciniki.taxes', 'ciniki_tax_history', $args['business_id'], 'ciniki_tax_rates', $args['object_id'], $args['field'],'date');
		}

		ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbGetModuleHistory');
		return ciniki_core_dbGetModuleHistory($ciniki, 'ciniki.taxes', 'ciniki_tax_history', $args['business_id'], 'ciniki_tax_rates', $args['object_id'], $args['field']);
	}

	elseif( $args['object'] == 'ciniki.taxes.type' ) {
		if( $args['field'] == 'rate_id' ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbGetModuleHistoryLinkedToggle');
			return ciniki_core_dbGetModuleHistoryLinkedToggle($ciniki, 'ciniki.taxes', 'ciniki_tax_history',
				$args['business_id'], 'ciniki_tax_type_rates', 
				'type_id', $args['object_id'], 'rate_id', $args['field_value']);
		}

		ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbGetModuleHistory');
		return ciniki_core_dbGetModuleHistory($ciniki, 'ciniki.taxes', 'ciniki_tax_history', $args['business_id'], 'ciniki_tax_types', $args['object_id'], $args['field']);
	}

	else {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1380', 'msg'=>'Invalid history object'));
	}
}
?>
