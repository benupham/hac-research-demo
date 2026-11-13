<?php
$findings = include __DIR__ . '/key-findings.php';
$numbers  = include __DIR__ . '/headline-numbers.php';
return array(
	'title'       => __( 'Research brief (full template)', 'hac-research-blocks' ),
	'description' => __( 'Everything a brief needs: summary, key findings, headline numbers, a chart, sections, authors and notes.', 'hac-research-blocks' ),
	'categories'  => array( 'hac-research' ),
	'keywords'    => array( 'brief', 'report', 'research', 'template' ),
	'postTypes'   => array( 'post' ),
	'blockTypes'  => array( 'core/post-content' ),
	'content'     => '<!-- wp:paragraph {"className":"hac-kicker"} -->
<p class="hac-kicker">Rural Research Brief</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph {"className":"hac-meta"} -->
<p class="hac-meta"><strong>Published</strong> [Month day, year] · <strong>By</strong> [Authors]</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph -->
<p>[Summary: two or three sentences on what this brief found and why it matters.]</p>
<!-- /wp:paragraph -->
' . $findings['content'] . '
' . $numbers['content'] . '
<!-- wp:heading -->
<h2 class="wp-block-heading">[First section]</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>[Text]</p>
<!-- /wp:paragraph -->
<!-- wp:hac/chart {"title":"[Chart title]","data":"Category\tValue\nFirst\t10\nSecond\t20\nThird\t15","description":"","source":"Source: Housing Assistance Council tabulations of [data source]."} /-->
<!-- wp:heading -->
<h2 class="wp-block-heading">About the authors</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>[Author bio]</p>
<!-- /wp:paragraph -->
<!-- wp:heading -->
<h2 class="wp-block-heading">Notes</h2>
<!-- /wp:heading -->
<!-- wp:list {"ordered":true,"className":"hac-notes"} -->
<ol class="wp-block-list hac-notes"><!-- wp:list-item -->
<li>[Note]</li>
<!-- /wp:list-item --></ol>
<!-- /wp:list -->',
);
