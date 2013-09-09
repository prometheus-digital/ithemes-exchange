<div class="it-exchange-visual-cc">
	<div class="it-exchange-visual-cc-line-1 it-exchange-visual-cc-holder it-exchange-columns-wrapper">
		<div class="it-exchange-cc-holder-first-name it-exchange-column">
			<div class="it-exchange-column-inner">
				<?php it_exchange( 'purchase-dialog', 'cc-first-name', array( 'format' => 'field', 'placeholder' => __( 'First name on card', 'LION' ) ) ) ?>
			</div>
		</div>
		<div class="it-exchange-cc-holder-last-name it-exchange-column">
			<div class="it-exchange-column-inner">
				<?php it_exchange( 'purchase-dialog', 'cc-last-name', array( 'format' => 'field', 'placeholder' => __( 'Last name on card', 'LION' ) ) ) ?>
			</div>
		</div>
	</div>
	<div class="it-exchange-visual-cc-line-2 it-exchange-visual-cc-number">
		<div class="it-exchange-cc-number-inner">
			<?php it_exchange( 'purchase-dialog', 'cc-number', array( 'format' => 'field', 'placeholder' => __( 'Card Number', 'LION' ) ) ); ?>
		</div>
	</div>
	<div class="it-exchange-visual-cc-line-3 it-exchange-visual-cc-data it-exchange-columns-wrapper">
		<div class="it-exchange-visual-cc-expiration it-exchange-column">
			<div class="it-exchange-column-inner">
				<?php it_exchange( 'purchase-dialog', 'cc-expiration-month', array( 'format' => 'field', 'placeholder' => __( 'MM', 'LION' ) ) ); ?>
				<?php it_exchange( 'purchase-dialog', 'cc-expiration-year', array( 'format' => 'field', 'placeholder' => __( 'YY', 'LION' ) ) ); ?>
			</div>
		</div>
		<div class="it-exchange-visual-cc-code it-exchange-column">
			<div class="it-exchange-column-inner">
				<?php it_exchange( 'purchase-dialog', 'cc-code', array( 'format' => 'field', 'placeholder' => __( 'CVC' ) ) ); ?>
			</div>
		</div>
	</div>
</div>
