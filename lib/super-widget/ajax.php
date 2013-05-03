<?php
/**
 * This file processes AJAX call from the super widget
 * @package IT_Exchange
 * @since 0.4.0
*/

// Die if called directly
if ( ! function_exists( 'add_action' ) ) {
	turtles_all_the_way_down();
	die();
}

$state = empty( $_GET['state'] ) ? false : esc_attr( $_GET['state'] );

if ( $state ) {
	it_exchange_get_template_part( 'super-widget', $state );
	die();
}
die('bad state');





/**
 * Just for fun
 *
 * @since 0.4.0
*/
function turtles_all_the_way_down() {
?>
<pre>
         .-""""-.\
         |"   (a \
         \--'    |
          ;,___.;.
       _ / `"""`\#'.
      | `\"==    \##\
      \   )     /`;##;
       ;-'   .-'  |##|
       |"== (  _.'|##|
       |     ``   /##/
        \"==     .##'
         ',__.--;#;`
         /  /   |\(
         \  \   (
         /  /    \
        (__(____.'
<br />
George says "You can't do that!"
</pre>
<?php
}
