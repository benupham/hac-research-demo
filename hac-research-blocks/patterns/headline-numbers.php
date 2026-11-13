<?php
$cell = '<!-- wp:group {"layout":{"type":"constrained"}} -->
<div class="wp-block-group"><!-- wp:paragraph {"className":"hac-stat-num"} -->
<p class="hac-stat-num">00%</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph {"className":"hac-stat-label"} -->
<p class="hac-stat-label">[What the number measures]</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->';
return array(
	'title'       => __( 'Headline numbers', 'hac-research-blocks' ),
	'description' => __( 'A row of large statistics with short labels.', 'hac-research-blocks' ),
	'categories'  => array( 'hac-research' ),
	'keywords'    => array( 'stats', 'numbers', 'statistics' ),
	'content'     => '<!-- wp:group {"className":"hac-stats","layout":{"type":"grid","minimumColumnWidth":"11rem"}} -->
<div class="wp-block-group hac-stats">' . $cell . $cell . $cell . '</div>
<!-- /wp:group -->',
);
