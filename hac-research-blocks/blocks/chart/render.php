<?php
/**
 * Server render for the hac/chart block.
 *
 * @var array $attributes
 */

if ( ! function_exists( 'hac_chart_parse' ) ) {
	/**
	 * Parse pasted spreadsheet text (tabs or commas) into headers and rows.
	 */
	function hac_chart_parse( $text ) {
		$lines = preg_split( '/\r\n|\r|\n/', trim( (string) $text ) );
		$lines = array_values( array_filter( $lines, 'strlen' ) );
		if ( count( $lines ) < 2 ) {
			return array( array(), array() );
		}
		$delim   = ( false !== strpos( $lines[0], "\t" ) ) ? "\t" : ',';
		$headers = array_map( 'trim', str_getcsv( $lines[0], $delim, '"', '\\' ) );
		$rows    = array();
		foreach ( array_slice( $lines, 1 ) as $line ) {
			$cells = array_map( 'trim', str_getcsv( $line, $delim, '"', '\\' ) );
			$label = array_shift( $cells );
			$vals  = array();
			foreach ( $cells as $cell ) {
				$num    = preg_replace( '/[^0-9.\-]/', '', $cell );
				$vals[] = ( '' === $num ) ? null : (float) $num;
			}
			$rows[] = array( 'label' => $label, 'values' => $vals );
		}
		return array( $headers, $rows );
	}

	function hac_chart_fmt( $v, $unit ) {
		if ( null === $v ) {
			return '—';
		}
		$dec = ( floor( $v ) == $v ) ? 0 : 1;
		return number_format_i18n( $v, $dec ) . $unit;
	}

	function hac_chart_nice_max( $max ) {
		if ( $max <= 0 ) {
			return array( 1, 0.2 );
		}
		$raw  = $max / 5;
		$p    = pow( 10, floor( log10( $raw ) ) );
		$f    = $raw / $p;
		$step = ( $f <= 1 ? 1 : ( $f <= 2 ? 2 : ( $f <= 5 ? 5 : 10 ) ) ) * $p;
		return array( ceil( $max / $step ) * $step, $step );
	}
}

list( $headers, $rows ) = hac_chart_parse( $attributes['data'] ?? '' );
$type   = $attributes['chartType'] ?? 'bar';
$unit   = $attributes['unit'] ?? '';
$title  = $attributes['title'] ?? '';
$desc   = $attributes['description'] ?? '';
$source = $attributes['source'] ?? '';
$uid    = wp_unique_id( 'hac-chart-' );

if ( empty( $rows ) ) {
	echo '<p>' . esc_html__( 'Paste a table to draw the chart.', 'hac-research-blocks' ) . '</p>';
	return;
}

$series_names = array_slice( $headers, 1 );
$n_series     = max( 1, count( $series_names ) );

$wrapper = get_block_wrapper_attributes( array( 'class' => 'hac-chart hac-chart--' . esc_attr( $type ) ) );
?>
<figure <?php echo $wrapper; // phpcs:ignore ?> aria-labelledby="<?php echo esc_attr( $uid ); ?>-t">
	<div class="hac-chart__head">
		<?php if ( $title ) : ?>
			<h3 class="hac-chart__title" id="<?php echo esc_attr( $uid ); ?>-t"><?php echo esc_html( $title ); ?></h3>
		<?php endif; ?>
		<?php if ( 'stacked' !== $type && $n_series > 1 ) : ?>
			<div class="hac-chart__series" role="group" aria-label="<?php esc_attr_e( 'Measure', 'hac-research-blocks' ); ?>">
				<?php foreach ( $series_names as $i => $name ) : ?>
					<button type="button" data-series="<?php echo (int) $i; ?>" aria-pressed="<?php echo 0 === $i ? 'true' : 'false'; ?>"><?php echo esc_html( $name ); ?></button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
	<p class="screen-reader-text" id="<?php echo esc_attr( $uid ); ?>-d"><?php echo esc_html( $desc ); ?></p>
	<?php
	if ( 'stacked' === $type ) :
		// 100% stacked horizontal bars: first series is highlighted, the rest is the remainder.
		$w     = 680;
		$row_h = 52;
		$h     = count( $rows ) * $row_h + 40;
		$x0    = 210;
		$bw    = $w - $x0 - 10;
		?>
		<div class="hac-chart__plot" data-series="0">
			<svg viewBox="0 0 <?php echo (int) $w; ?> <?php echo (int) $h; ?>" role="img" aria-labelledby="<?php echo esc_attr( $uid ); ?>-t <?php echo esc_attr( $uid ); ?>-d">
				<?php foreach ( $rows as $r => $row ) :
					$v  = (float) ( $row['values'][0] ?? 0 );
					$y  = 8 + $r * $row_h;
					$sw = max( 0, min( 1, $v / 100 ) ) * $bw;
					?>
					<text x="0" y="<?php echo $y + 24; ?>" class="hac-chart__lab"><?php echo esc_html( $row['label'] ); ?></text>
					<rect x="<?php echo $x0; ?>" y="<?php echo $y; ?>" width="<?php echo round( $sw, 1 ); ?>" height="36" class="hac-chart__bar"><title><?php echo esc_html( $row['label'] . ': ' . hac_chart_fmt( $v, $unit ) . ' ' . ( $series_names[0] ?? '' ) ); ?></title></rect>
					<rect x="<?php echo round( $x0 + $sw, 1 ); ?>" y="<?php echo $y; ?>" width="<?php echo round( $bw - $sw, 1 ); ?>" height="36" class="hac-chart__rest"></rect>
					<text x="<?php echo round( $x0 + $sw + 8, 1 ); ?>" y="<?php echo $y + 24; ?>" class="hac-chart__val"><?php echo esc_html( hac_chart_fmt( $v, $unit ) . ' ' . strtolower( $series_names[0] ?? '' ) ); ?></text>
				<?php endforeach; ?>
			</svg>
		</div>
		<?php
	else :
		foreach ( range( 0, $n_series - 1 ) as $s ) :
			$data = $rows;
			if ( ! empty( $attributes['sort'] ) ) {
				usort( $data, function ( $a, $b ) use ( $s ) {
					return ( $b['values'][ $s ] ?? -INF ) <=> ( $a['values'][ $s ] ?? -INF );
				} );
			}
			$vals = array_map( function ( $r ) use ( $s ) { return $r['values'][ $s ] ?? 0; }, $data );
			list( $max, $step ) = hac_chart_nice_max( max( $vals ) );
			$n = count( $data );
			?>
			<div class="hac-chart__plot" data-series="<?php echo (int) $s; ?>" <?php echo $s ? 'hidden' : ''; ?>>
			<?php
			if ( 'hbar' === $type ) :
				$w = 680; $row_h = 34; $h = $n * $row_h + 10; $x0 = 170; $bw = $w - $x0 - 90;
				?>
				<svg viewBox="0 0 <?php echo $w; ?> <?php echo $h; ?>" role="img" aria-labelledby="<?php echo esc_attr( $uid ); ?>-t <?php echo esc_attr( $uid ); ?>-d">
					<?php foreach ( $data as $i => $row ) :
						$v = $row['values'][ $s ] ?? null; $y = 4 + $i * $row_h; $bwv = $v ? ( $v / $max ) * $bw : 0; ?>
						<text x="0" y="<?php echo $y + 20; ?>" class="hac-chart__lab"><?php echo esc_html( $row['label'] ); ?></text>
						<rect x="<?php echo $x0; ?>" y="<?php echo $y; ?>" width="<?php echo round( $bwv, 1 ); ?>" height="26" class="hac-chart__bar" tabindex="0"><title><?php echo esc_html( $row['label'] . ': ' . hac_chart_fmt( $v, $unit ) ); ?></title></rect>
						<text x="<?php echo round( $x0 + $bwv + 6, 1 ); ?>" y="<?php echo $y + 19; ?>" class="hac-chart__val"><?php echo esc_html( hac_chart_fmt( $v, $unit ) ); ?></text>
					<?php endforeach; ?>
				</svg>
				<?php
			else :
				$w = 900; $h = 340; $m = array( 't' => 14, 'r' => 10, 'b' => 56, 'l' => 58 );
				$iw = $w - $m['l'] - $m['r']; $ih = $h - $m['t'] - $m['b']; $bwid = $iw / max( 1, $n );
				$short  = max( array_map( function ( $r ) { return strlen( (string) $r['label'] ); }, $data ) ) <= 4;
				$every  = ( $n <= 14 || ( $short && $n <= 60 ) ) ? 1 : (int) ceil( $n / 12 );
				$rotate = $n > 14 && 1 === $every;
				?>
				<svg viewBox="0 0 <?php echo $w; ?> <?php echo $h; ?>" role="img" aria-labelledby="<?php echo esc_attr( $uid ); ?>-t <?php echo esc_attr( $uid ); ?>-d">
					<?php for ( $t = 0; $t <= $max + 1e-9; $t += $step ) : $y = $m['t'] + $ih - ( $t / $max ) * $ih; ?>
						<line x1="<?php echo $m['l']; ?>" x2="<?php echo $w - $m['r']; ?>" y1="<?php echo round( $y, 1 ); ?>" y2="<?php echo round( $y, 1 ); ?>" class="hac-chart__grid"></line>
						<text x="<?php echo $m['l'] - 8; ?>" y="<?php echo round( $y + 4, 1 ); ?>" text-anchor="end" class="hac-chart__tick"><?php echo esc_html( hac_chart_fmt( $t, $unit ) ); ?></text>
					<?php endfor; ?>
					<?php foreach ( $data as $i => $row ) :
						$v = $row['values'][ $s ] ?? null; $bh = $v ? ( $v / $max ) * $ih : 0;
						$x = $m['l'] + $i * $bwid; $lx = $x + $bwid / 2; $ly = $h - $m['b'] + 16; ?>
						<rect x="<?php echo round( $x + $bwid * 0.15, 1 ); ?>" y="<?php echo round( $m['t'] + $ih - $bh, 1 ); ?>" width="<?php echo round( max( 1, $bwid * 0.7 ), 1 ); ?>" height="<?php echo round( $bh, 1 ); ?>" class="hac-chart__bar" tabindex="0"><title><?php echo esc_html( $row['label'] . ': ' . hac_chart_fmt( $v, $unit ) ); ?></title></rect>
						<?php if ( 0 === $i % $every ) : ?>
							<text x="<?php echo round( $lx, 1 ); ?>" y="<?php echo $ly; ?>" class="hac-chart__tick" text-anchor="<?php echo $rotate ? 'end' : 'middle'; ?>" <?php echo $rotate ? 'transform="rotate(-60 ' . round( $lx, 1 ) . ' ' . $ly . ')"' : ''; ?>><?php echo esc_html( $row['label'] ); ?></text>
						<?php endif; ?>
					<?php endforeach; ?>
					<line x1="<?php echo $m['l']; ?>" x2="<?php echo $w - $m['r']; ?>" y1="<?php echo $m['t'] + $ih; ?>" y2="<?php echo $m['t'] + $ih; ?>" class="hac-chart__axis"></line>
				</svg>
			<?php endif; ?>
			</div>
			<?php
		endforeach;
	endif;
	?>
	<?php if ( $source ) : ?>
		<figcaption class="hac-chart__source"><?php echo esc_html( $source ); ?></figcaption>
	<?php endif; ?>
	<details class="hac-chart__data">
		<summary><?php esc_html_e( 'Show the data', 'hac-research-blocks' ); ?></summary>
		<div class="hac-chart__tablewrap">
			<table>
				<?php if ( $title ) : ?><caption><?php echo esc_html( $title ); ?></caption><?php endif; ?>
				<thead><tr>
					<?php foreach ( $headers as $hcell ) : ?><th scope="col"><?php echo esc_html( $hcell ); ?></th><?php endforeach; ?>
				</tr></thead>
				<tbody>
					<?php foreach ( $rows as $row ) : ?>
						<tr><th scope="row"><?php echo esc_html( $row['label'] ); ?></th>
						<?php foreach ( $row['values'] as $v ) : ?><td><?php echo esc_html( hac_chart_fmt( $v, $unit ) ); ?></td><?php endforeach; ?>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</details>
</figure>
