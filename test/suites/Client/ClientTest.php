<?php
/**
 * @license see LICENSE
 */
namespace RubtsovAV\RemoteDatabaseBackup\Test\Client;

use GuzzleHttp\Client as HttpClient;
use RubtsovAV\RemoteDatabaseBackup\Client\Client;
use RubtsovAV\RemoteDatabaseBackup\Client\Exception\InvalidResponseException;

/**
 * @covers RubtsovAV\RemoteDatabaseBackup\Client\Client
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

    private function getResourceFilePath($filename)
    {
        $baseDir = 'test/resources/database/mysql/';
        return $baseDir . $filename;
    }

    private function getResourceFileContent($filename)
    {
        
        return file_get_contents($this->getResourceFilePath($filename));
    }


    public function testOfMutationMethods()
    {
        $this->assertEquals(getenv('SERVER_URI'), $this->client->getUri());
        $this->assertEquals(
            [
                'host' => getenv('DB_HOST'),
                'port' => getenv('DB_PORT'),
                'username' => getenv('DB_USERNAME'),
                'password' => getenv('DB_PASSWORD'),
                'db_name' => getenv('DB_DATABASE'),
                'skip-dump-date' => true,
            ],
            $this->client->getDbParams()
        );

        $this->assertInstanceOf(HttpClient::class, $this->client->getHttpClient());

        $this->client->setAdapterName('non-existent');
        $this->assertEquals('non-existent', $this->client->getAdapterName());
    }

    public function testExport()
    {
        $expect = shell_exec($this->getResourceFilePath('bin/join.sh'));

        $this->client->export($this->output, [
            'add-drop-database' => true,
        ]);
        rewind($this->output);
        $result = stream_get_contents($this->output);

        $this->assertTrue($expect == $result, 'Export result is not valid');
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

        $this->assertTrue($expect == $result, 'Views is not equals');
    }

    public function testExportTriggers()
    {
        $expect = $this->getResourceFileContent('sql/triggers.sql');
        $this->client->exportTriggers($this->output);
        rewind($this->output);
        $result = stream_get_contents($this->output);

        $this->assertTrue($expect == $result, 'Triggers is not equals');
    }

    public function testExportRoutines()
    {
        $expect = $this->getResourceFileContent('sql/procedures.sql');
        $this->client->exportRoutines($this->output);
        rewind($this->output);
        $result = stream_get_contents($this->output);

        $this->assertTrue($expect == $result, 'Routines is not equals');
    }

    public function testExportCreateDatabase()
    {
        $expect = $this->getResourceFileContent('sql/create_database.sql');
        $this->client->exportCreateDatabase($this->output);
        rewind($this->output);
        $result = stream_get_contents($this->output);

        $this->assertTrue($expect == $result, 'Create database is not equals');
    }

    public function testExportHeader()
    {
        $expect = $this->getResourceFileContent('sql/header.sql');
        $this->client->exportHeader($this->output);
        rewind($this->output);
        $result = stream_get_contents($this->output);

        $this->assertTrue($expect == $result, 'Header is not equals');
    }

    public function testExportFooter()
    {
        $expect = $this->getResourceFileContent('sql/footer.sql');
        $this->client->exportFooter($this->output);
        rewind($this->output);
        $result = stream_get_contents($this->output);

        $this->assertTrue($expect == $result, 'Footer is not equals');
    }

    public function testExportOfNonExistentTable()
    {
        $this->expectException(InvalidResponseException::class);
        $this->expectExceptionMessage('The response status code is 417');

        $this->client->exportTable($this->output, 'non-existent');
    }

    public function testExportOfNonExistentAdapter()
    {
        $this->expectException(InvalidResponseException::class);
        $this->expectExceptionMessage('The response status code is 417');

        $this->client->setAdapterName('non-existent');
        $this->client->exportHeader($this->output);
    }

    public function testExportWithHtmlResponse()
    {
        $this->expectException(InvalidResponseException::class);
        $this->expectExceptionMessage('The response have undefined Content-Type: text/html');

        $uri = preg_replace('#/[^/]+$#', '/test.html', getenv('SERVER_URI'));
        $client = new Client(
            $uri,
            [
                'host' => getenv('DB_HOST'),
                'port' => getenv('DB_PORT'),
                'username' => getenv('DB_USERNAME'),
                'password' => getenv('DB_PASSWORD'),
                'db_name' => getenv('DB_DATABASE'),
                'skip-dump-date' => true,
            ]
        );
        $client->exportHeader($this->output);
    }

    public function testExportWithTxtResponse()
    {
        $this->expectException(InvalidResponseException::class);
        $this->expectExceptionMessage('The response don\'t have SUCCESS_RESPONSE_MARK');

        $uri = preg_replace('#/[^/]+$#', '/test.txt', getenv('SERVER_URI'));
        $client = new Client(
            $uri,
            [
                'host' => getenv('DB_HOST'),
                'port' => getenv('DB_PORT'),
                'username' => getenv('DB_USERNAME'),
                'password' => getenv('DB_PASSWORD'),
                'db_name' => getenv('DB_DATABASE'),
                'skip-dump-date' => true,
            ]
        );
        $client->exportHeader($this->output);
    }

    public function testExportWithSmallTxtResponse()
    {
        $this->expectException(InvalidResponseException::class);
        $this->expectExceptionMessage('The response don\'t have SUCCESS_RESPONSE_MARK');

        $uri = preg_replace('#/[^/]+$#', '/small.txt', getenv('SERVER_URI'));
        $client = new Client(
            $uri,
            [
                'host' => getenv('DB_HOST'),
                'port' => getenv('DB_PORT'),
                'username' => getenv('DB_USERNAME'),
                'password' => getenv('DB_PASSWORD'),
                'db_name' => getenv('DB_DATABASE'),
                'skip-dump-date' => true,
            ]
        );
        $client->exportHeader($this->output);
    }
}
