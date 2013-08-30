<div class="it-exchange-visual-cc">
	<div class="it-exchange-visual-cc-line-1">
		<?php it_exchange( 'purchase-dialog', 'cc-first-name', array( 'format' => 'field', 'placeholder' => __( 'First name on card', 'LION' ) ) ) ?>&nbsp;
		<?php it_exchange( 'purchase-dialog', 'cc-last-name', array( 'format' => 'field', 'placeholder' => __( 'Last name on card', 'LION' ) ) ) ?><br />
		<?php it_exchange( 'purchase-dialog', 'cc-number', array( 'format' => 'field', 'placeholder' => __( 'Card Number', 'LION' ) ) ); ?>
	</div>

	<div class="it-exchange-visual-cc-line-2">
		<div class="it-exchange-visual-cc-expiration">
			<?php it_exchange( 'purchase-dialog', 'cc-expiration-month', array( 'format' => 'field', 'placeholder' => __( 'MM', 'LION' ) ) ); ?>
			<?php it_exchange( 'purchase-dialog', 'cc-expiration-year', array( 'format' => 'field', 'placeholder' => __( 'YY', 'LION' ) ) ); ?>
		</div>
		<div class="it-exchange-visual-cc-code">
			<?php it_exchange( 'purchase-dialog', 'cc-code', array( 'format' => 'field', 'placeholder' => __( 'CVC' ) ) ); ?>
		</div>
	</div>
</div>
