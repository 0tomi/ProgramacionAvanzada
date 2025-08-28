<?php

use Core\Authenticator;

// log out the session

(new Authenticator)->logout();

header('location: /');

exit();