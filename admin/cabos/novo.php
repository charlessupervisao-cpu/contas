<?php

declare(strict_types=1);

require_once dirname(dirname(__DIR__)) . '/bootstrap.php';

Auth::requireLogin('cabos');
redirect('/admin/cabos.php#form-cabo');
