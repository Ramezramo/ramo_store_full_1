<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

if (DB::getDriverName() !== 'pgsql') {
    fwrite(STDERR, "This maintenance script supports PostgreSQL only.\n");
    exit(1);
}

$apply = in_array('--apply', $argv, true);
$connection = DB::connection();
$pdo = $connection->getPdo();
$schema = $connection->getConfig('schema') ?: 'public';

$columns = DB::select(
    <<<'SQL'
SELECT table_schema, table_name, column_name,
       pg_get_serial_sequence(format('%I.%I', table_schema, table_name), column_name) AS sequence_name
FROM information_schema.columns
WHERE table_schema = ?
  AND column_default LIKE 'nextval(%'
ORDER BY table_name, column_name
SQL,
    [$schema]
);

if (empty($columns)) {
    echo "No PostgreSQL serial sequences found in schema {$schema}.\n";
    exit(0);
}

printf("%s PostgreSQL sequence health check (%s)\n", $apply ? 'Applying' : 'Dry-run', $schema);
$needsRepair = 0;

foreach ($columns as $column) {
    $quotedTable = sprintf('%s.%s', $connection->getQueryGrammar()->wrap($column->table_schema), $connection->getQueryGrammar()->wrap($column->table_name));
    $quotedColumn = $connection->getQueryGrammar()->wrap($column->column_name);
    $max = (int) $connection->scalar("SELECT COALESCE(MAX({$quotedColumn}), 0) FROM {$quotedTable}");
    // sequence_name is supplied by PostgreSQL's pg_get_serial_sequence(), not user input.
    $current = $connection->selectOne("SELECT last_value FROM {$column->sequence_name}");

    $currentValue = $current ? (int) $current->last_value : 0;
    $needsSync = $max > $currentValue;
    $needsRepair += $needsSync ? 1 : 0;

    printf(
        "%-38s max=%-8d sequence=%-8d %s\n",
        $column->table_name . '.' . $column->column_name,
        $max,
        $currentValue,
        $needsSync ? 'DRIFT' : 'OK'
    );

    if ($apply && $needsSync) {
        $connection->select(
            'SELECT setval(?::regclass, ?, true)',
            [$column->sequence_name, $max]
        );
    }
}

if (! $apply && $needsRepair > 0) {
    printf("%d sequence(s) require synchronization. Run with --apply after reviewing this report.\n", $needsRepair);
    exit(2);
}

if ($apply) {
    printf("Synchronized %d drifted PostgreSQL sequence(s).\n", $needsRepair);
}
