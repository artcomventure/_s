<?php

/**
 * Extract data from HTML.
 *
 * @param int|string $html
 * @param string $query xPath Syntax
 *
 * @return string
 */
function get_data_from_html( int|string $html, string $query ): string {
	if ( is_numeric( $html) ) {
		if ( $post = get_post( $html ) ) {
			$html = $post->post_content;
		}
		else $html = '';
	}

	if ( $html ) {
		$dom = new domDocument;
		@$dom->loadHTML( $html );

		$xpath = new DOMXPath( $dom );
		if ( $node = $xpath->query("//$query/*")->item(0 ) ) {
			$html = $dom->saveHTML( $node );
		}
	}

	return $html;
}