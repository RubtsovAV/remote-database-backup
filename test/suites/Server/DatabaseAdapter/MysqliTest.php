<?php
/**
 * @license see LICENSE
 */
namespace RubtsovAV\RemoteDatabaseBackup\Test\Server\DatabaseAdapter;

use RubtsovAV\RemoteDatabaseBackup\Server\DatabaseAdapter\Mysqli;

/**
 * @covers RubtsovAV\RemoteDatabaseBackup\Server\DatabaseAdapter\Mysqli
 */
class MysqliTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->exporter = new Mysqli([
            'host' => getenv('DB_HOST'),
            'port' => getenv('DB_PORT'),
            'username' => getenv('DB_USERNAME'),
            'password' => getenv('DB_PASSWORD'),
            'db_name' => getenv('DB_DATABASE'),
            'skip-dump-date' => true,
        ]);
    }

    private function getResourceFileContent($filename)
    {
        $baseDir = 'test/resources/database/mysql/';
        return file_get_contents($baseDir . $filename);
    }


    public function testGetTablesMetadata()
    {
        $expect = [
            [
                'name' => 'table000',
            ],
            [
                'name' => 'table001',
            ],
            [
                'name' => 'table002',
            ],
            [
                'name' => 'table027',
            ],
            [
                'name' => 'table200',
            ],
            [
                'name' => 'table201',
            ],
            [
                'name' => 'tablebig001',
            ],
        ];
        $result = $this->exporter->getTablesMetadata();

        $this->assertEquals($expect, $result);
    }

    /**
     * @dataProvider exportTableProvider
     */
    public function testExportTable($tablename, $resourceName)
    {
        $expect = $this->getResourceFileContent($resourceName);
        ob_start();
        $this->exporter->exportTable($tablename);
        $result = ob_get_clean();

        $this->assertTrue($expect == $result, "Table $tablename is not equals");
    }

    public function exportTableProvider()
    {
        return [
            ['table000', 'sql/table000.sql'],
            ['table001', 'sql/table001.sql'],
            ['table002', 'sql/table002.sql'],
            ['table027', 'sql/table027.sql'],
            ['table200', 'sql/table200.sql'],
            ['table201', 'sql/table201.sql'],
            ['tablebig001', 'sql/tablebig001.sql'],
        ];
    }

    public function testExportViews()
    {
        $this->expectOutputString($this->getResourceFileContent('sql/views.sql'));
        $this->exporter->exportViews();
    }

    public function testExportTriggers()
    {
        $this->expectOutputString($this->getResourceFileContent('sql/triggers.sql'));
        $this->exporter->exportTriggers();
    }

    public function testExportRoutines()
    {
        $this->expectOutputString($this->getResourceFileContent('sql/procedures.sql'));
        $this->exporter->exportRoutines();
    }

    public function testExportCreateDatabase()
    {
        $this->expectOutputString($this->getResourceFileContent('sql/create_database.sql'));
        $this->exporter->exportCreateDatabase();
    }

    public function testExportHeader()
    {
        $this->expectOutputString($this->getResourceFileContent('sql/header.sql'));
        $this->exporter->exportHeader();
    }

    public function testExportFooter()
    {
        $this->expectOutputString($this->getResourceFileContent('sql/footer.sql'));
        $this->exporter->exportFooter();
    }

    public function testExportOfNonExistentTable()
    {
        $this->expectException(\mysqli_sql_exception::class);
        $this->expectExceptionCode(1146);
        $this->expectExceptionMessage("Table 'rest_database_exporter.non-existent' doesn't exist");

        $this->exporter->exportTable('non-existent');
    }
}
