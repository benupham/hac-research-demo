<?php
return array(
	'title'       => __( 'Key findings', 'hac-research-blocks' ),
	'description' => __( 'A shaded box of three to five findings.', 'hac-research-blocks' ),
	'categories'  => array( 'hac-research' ),
	'keywords'    => array( 'findings', 'summary', 'takeaways' ),
	'content'     => '<!-- wp:group {"className":"hac-findings","layout":{"type":"constrained"}} -->
<div class="wp-block-group hac-findings"><!-- wp:heading -->
<h2 class="wp-block-heading">Key findings</h2>
<!-- /wp:heading -->
<!-- wp:list -->
<ul class="wp-block-list"><!-- wp:list-item -->
<li><strong>[Number]</strong> [what it means]</li>
<!-- /wp:list-item -->
<!-- wp:list-item -->
<li><strong>[Number]</strong> [what it means]</li>
<!-- /wp:list-item -->
<!-- wp:list-item -->
<li><strong>[Number]</strong> [what it means]</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list --></div>
<!-- /wp:group -->',
);
