<?php
namespace DBWorker\Service\DB\Pgsql;

use DBWorker\Service\DB\Expr;

if (!extension_loaded('pdo_pgsql')) {
    throw new \Exception('Require "pdo_pgsql" extension!');
}

class Adapter extends \DBWorker\Service\DB\Adapter
{
    protected $identifier_symbol = '"';

    public function lastID(string $table = null, string $column = null)
    {
        $sql = ($table && $column)
             ? sprintf("SELECT CURRVAL('%s')", $this->sequenceName($table, $column))
             : 'SELECT LASTVAL()';

        return $this->execute($sql)->getCol();
    }

    public function nextID(string $table, string $column)
    {
        $sql = sprintf("SELECT NEXTVAL('%s')", $this->sequenceName($table, $column));

        return $this->execute($sql)->getCol();
    }

    public function getTables(): array
    {
        $select = $this->select('information_schema.tables')
                       ->setColumns('table_schema', 'table_name')
                       ->whereNotIn('table_schema', ['pg_catalog', 'information_schema']);

        $tables = [];
        foreach ($select->iterator() as $row) {
            $tables[] = sprintf('%s.%s', $row['table_schema'], $row['table_name']);
        }

        return $tables;
    }

    public function parseTableName(string $table): array
    {
        $table = str_replace('"', '', $table);
        $pos = strpos($table, '.');

        if ($pos) {
            list($schema, $table) = explode('.', $table, 2);

            return [$schema, $table];
        }

        return ['public', $table];
    }

    protected function sequenceName(string $table, string $column): Expr
    {
        list($schema, $table) = $this->parseTableName($table);

        $sequence = sprintf('%s_%s_seq', $table, $column);
        if ($schema) {
            $sequence = $schema . '.' . $sequence;
        }

        return $this->quoteIdentifier($sequence);
    }
}
