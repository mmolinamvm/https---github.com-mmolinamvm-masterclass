<?php
// generar_hash.php
header('Content-Type: text/plain; charset=utf-8');

echo "Copia aquests codis SQL i executa'ls directament a la teva base de dades:\n\n";

$hash_alumne = password_hash('alumne123', PASSWORD_BCRYPT);
echo "UPDATE usuaris SET password_hash = '{$hash_alumne}' WHERE email = 'alumne@masterclass.com';\n\n";

$hash_profe = password_hash('profe123', PASSWORD_BCRYPT);
echo "UPDATE usuaris SET password_hash = '{$hash_profe}' WHERE email = 'profe@masterclass.com';\n";