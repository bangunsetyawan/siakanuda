<?php
$db = new SQLite3(__DIR__ . '/../siakanuda.db');
$sql = file_get_contents(__DIR__ . '/../database/update_schema_v3.sql');
$db->exec($sql);
echo "Schema updated successfully!\n";
