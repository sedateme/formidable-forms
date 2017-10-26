<div class="frm_actions_dropdown dropdown">
	<a href="#" id="frm-navbarDrop" class="frm-dropdown-toggle frm-dots" data-toggle="dropdown" title="<?php esc_attr_e( 'Show options', 'formidable' ) ?>">&middot; &middot; &middot;</a>
	<ul class="frm-dropdown-menu frm-on-top" role="menu" aria-labelledby="frm-navbarDrop">
		<?php foreach ( $links as $link ) { ?>
		<li>
			<a href="<?php echo esc_url( $link['url'] ); ?>" tabindex="-1" <?php
				if ( isset( $link['data'] ) ) {
					foreach ( $link['data'] as $data => $value ) {
						echo 'data-' . esc_attr( $data ) . '="' . esc_attr( $value ) . '" ';
					}
				}
				?> >
				<span class="<?php echo esc_attr( $link['icon'] ) ?>"></span>
				<span class="frm_link_label"><?php echo esc_html( $link['label'] ) ?></span>
			</a>
		</li>
		<?php } ?>
	</ul>
</div>