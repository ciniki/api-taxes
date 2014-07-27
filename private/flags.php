<?php
//
// Description
// -----------
// The module flags
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_taxes_flags($ciniki, $modules) {
	$flags = array(
		array('flag'=>array('bit'=>'1', 'name'=>'Locations')),
		array('flag'=>array('bit'=>'2', 'name'=>'Location Code')),
		);

	return array('stat'=>'ok', 'flags'=>$flags);
}
?>
