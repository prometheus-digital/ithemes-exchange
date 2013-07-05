<?php

$data = "\u062f--&#1583;
\u0625--&#1573;
\u0192--&#402;
\u09f3--&#2547;
\u0628--&#1576;
\u00a5--&#165;
\u20a1--&#8353;
\u010d--&#269;
\u062c--&#1580;
\u00a3--&#163;
\u20ac--&#8364;
\u20b5--&#8373;
\u20aa--&#8362;
\u2089--&#8329;
\u0639--&#1593;
\u0441--&#1089;
\u043e--&#1086;
\u043c--&#1084;
\u17db--&#6107;
\u20ae--&#8366;
\u20a6--&#8358;
\u20b1--&#8369;
\u0142--&#322;
\u20b2--&#8370;
\u0e3f--&#3647;
\u20ab--&#8363;";

$file_data = file_get_contents( 'misc.php' );

foreach ( explode( "\n", $data ) as $line ) {
	list( $unicode, $entity ) = explode( '--', $line );
	
	$file_data = preg_replace( "/\\$unicode;?/", $entity, $file_data );
}

file_put_contents( 'misc.php', $file_data );
