<?php
// logout_home.php: Logs out any user and redirects to home
session_start();
session_destroy();
header('Location: index.php');
exit();

<div style="margin: 1em 0; text-align: center;">
	<button onclick="window.history.back()" class="btn">&larr; Back</button>
</div>
