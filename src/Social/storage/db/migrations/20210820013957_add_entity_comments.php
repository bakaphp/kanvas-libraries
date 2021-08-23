<?php

use Phinx\Db\Adapter\MysqlAdapter;

class AddEntityComments extends Phinx\Migration\AbstractMigration
{
    public function change()
    {
        $this->execute("ALTER DATABASE COLLATE='utf8mb4_unicode_520_ci';");

        $this->table('entity_comments', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_general_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('entity_id', 'char', [
                'null' => false,
                'default' => '',
                'limit' => 50,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'id',
            ])
            ->addColumn('entity_namespace', 'string', [
                'null' => false,
                'default' => '',
                'limit' => 255,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'entity_id',
            ])
            ->addColumn('apps_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'entity_namespace',
            ])
            ->addColumn('companies_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'apps_id',
            ])
            ->addColumn('users_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'companies_id',
            ])
            ->addColumn('message', 'text', [
                'null' => false,
                'limit' => MysqlAdapter::TEXT_LONG,
                'collation' => 'utf8mb4_general_ci',
                'encoding' => 'utf8mb4',
                'after' => 'users_id',
            ])
            ->addColumn('reactions_count', 'integer', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'message',
            ])
            ->addColumn('parent_id', 'integer', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'reactions_count',
            ])
            ->addColumn('created_at', 'datetime', [
                'null' => false,
                'after' => 'parent_id',
            ])
            ->addColumn('updated_at', 'datetime', [
                'null' => true,
                'default' => null,
                'after' => 'created_at',
            ])
            ->addColumn('is_deleted', 'integer', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'updated_at',
            ])
            ->addIndex(['apps_id'], [
                'name' => 'apps_id',
                'unique' => false,
            ])
            ->addIndex(['companies_id'], [
                'name' => 'companies_id',
                'unique' => false,
            ])
            ->addIndex(['users_id'], [
                'name' => 'users_id',
                'unique' => false,
            ])
            ->addIndex(['parent_id'], [
                'name' => 'parent_id',
                'unique' => false,
            ])
            ->addIndex(['created_at'], [
                'name' => 'created_at',
                'unique' => false,
            ])
            ->addIndex(['id', 'apps_id', 'is_deleted'], [
                'name' => 'id_apps_id_is_deleted',
                'unique' => false,
            ])
            ->addIndex(['is_deleted'], [
                'name' => 'is_deleted',
                'unique' => false,
            ])
            ->addIndex(['entity_id'], [
                'name' => 'message_id',
                'unique' => false,
            ])
            ->addIndex(['entity_namespace'], [
                'name' => 'entity_namespace',
                'unique' => false,
            ])
            ->create();
    }
}
