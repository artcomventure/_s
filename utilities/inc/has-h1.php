<?php

// check for `<h1>` tag
function hasH1( $content ) {
	$hasH1 = false;

	// check for plain tag
	if ( is_string( $content ) ) {
		$hasH1 = preg_match( '/<h1 /', $content );
		// get blocks
		$content = parse_blocks( $content );
	}

	// check for block
	if ( !$hasH1 && $content ) foreach ( $content as $block ) {
		if ( !$hasH1 = hasH1( $block['innerBlocks'] ) ) {
			$hasH1 = $block['blockName'] === 'core/post-title' && ($block['attrs']['level'] ?? 2) === 1
			         || $block['blockName'] === 'core/heading' && ($block['attrs']['level'] ?? 2) === 1;
		}

		// already found one
		if ( $hasH1 ) break;
	}

	return $hasH1;
}