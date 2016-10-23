<?php

namespace RubtsovAV\RemoteDatabaseBackup\Server\DatabaseAdapter;

interface AdapterInterface
{
    public function getDatabaseMetadata();
    public function getTablesMetadata();
    public function exportHeader();
    public function exportFooter();
    public function exportCreateDatabase();
    public function exportTable($tableName);
    public function exportViews();
    public function exportTriggers();
    public function exportRoutines();
}
