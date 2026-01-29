<?php
/**
 * Test soubor - kontrola přímého přístupu k PHP
 */

echo "✅ Test úspěšný!\n\n";
echo "Absolutní cesta: " . __DIR__ . "\n";
echo "ROOT_PATH by měl být: " . dirname(__DIR__) . "\n";
echo "Aktuální čas: " . date('Y-m-d H:i:s') . "\n";
