<?php
/**
 * @license see LICENSE
 */
namespace RubtsovAV\RestDatabaseExporter\Test\Client;

use RubtsovAV\RestDatabaseExporter\Client\Client;

/**
 * @covers RubtsovAV\RestDatabaseExporter\Client\Client
 */
class ClientTest extends \PHPUnit_Framework_TestCase
{
    private $client;
    private $output;

    public function setUp()
    {
        $this->client = new Client(
            getenv('SERVER_URI'),
            [
                'host' => getenv('DB_HOST'),
                'port' => getenv('DB_PORT'),
                'username' => getenv('DB_USERNAME'),
                'password' => getenv('DB_PASSWORD'),
                'db_name' => getenv('DB_DATABASE'),
                'skip-dump-date' => true,
            ]
        );

        $this->output = fopen('php://temp', 'r+');
    }

    public function tearDown()
    {
        fclose($this->output);
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
        $result = $this->client->getTablesMetadata();

        $this->assertEquals($expect, $result);
    }

    /**
     * @dataProvider exportTableProvider
     */
    public function testExportTable($tablename, $resourceName)
    {
        $expect = $this->getResourceFileContent($resourceName);
        $this->client->exportTable($this->output, $tablename);
        rewind($this->output);
        $result = stream_get_contents($this->output);

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
        $expect = $this->getResourceFileContent('sql/views.sql');
        $this->client->exportViews($this->output);
        rewind($this->output);
        $result = stream_get_contents($this->output);

        $this->assertTrue($expect == $result, "Views is not equals");
    }

    public function testExportTriggers()
    {
        $expect = $this->getResourceFileContent('sql/triggers.sql');
        $this->client->exportTriggers($this->output);
        rewind($this->output);
        $result = stream_get_contents($this->output);

        $this->assertTrue($expect == $result, "Triggers is not equals");
    }

    public function testExportRoutines()
    {
        $expect = $this->getResourceFileContent('sql/procedures.sql');
        $this->client->exportRoutines($this->output);
        rewind($this->output);
        $result = stream_get_contents($this->output);

        $this->assertTrue($expect == $result, "Routines is not equals");
    }

    public function testExportCreateDatabase()
    {
        $expect = $this->getResourceFileContent('sql/create_database.sql');
        $this->client->exportCreateDatabase($this->output);
        rewind($this->output);
        $result = stream_get_contents($this->output);

        $this->assertTrue($expect == $result, "Create database is not equals");
    }

    public function testExportHeader()
    {
        $expect = $this->getResourceFileContent('sql/header.sql');
        $this->client->exportHeader($this->output);
        rewind($this->output);
        $result = stream_get_contents($this->output);

        $this->assertTrue($expect == $result, "Header is not equals");
    }

    public function testExportFooter()
    {
        $expect = $this->getResourceFileContent('sql/footer.sql');
        $this->client->exportFooter($this->output);
        rewind($this->output);
        $result = stream_get_contents($this->output);

        $this->assertTrue($expect == $result, "Footer is not equals");
    }
}
