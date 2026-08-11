<?php
/**
 * Não há área pública. Atalhos antigos caem no login.
 * Quem precisa ver dados entra com perfil de consulta (somente leitura).
 */
require_once __DIR__ . '/bootstrap.php';
redirect('/login.php');
