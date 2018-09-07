<?php
use PHPUnit\Framework\TestCase;

class memoryTest extends TestCase
{
    /**
     * @var \Zaek\Engine\Main
     */
    protected $_app;

    protected function setUp()
    {
        $_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__ . '/../../../../');
        $_SERVER['SERVER_PORT'] = 80;
        $_SERVER['SERVER_NAME'] = 'localhost';

        $this->_app = new \Zaek\Engine\Main;

    }

    public function testMemoryFilterOrder()
    {
        /**
         * @var Zaek\Data\Memory\Connector $connector
         */
        $connector = $this->_app->data();
        $connector->insert('users', [
            'id' => 1,
            'name' => 'admin',
        ]);

        $result = $connector->select('users', ['name' => 'root']);
        $this->assertEquals($result->fetch(), false);

        $result = $connector->select('users', ['name' => 'admin']);
        $this->assertEquals($result->fetch(), [1,'admin']);

        $connector->insert('users', [
            'id' => 2,
            'name' => 'root',
        ]);

        $result = $connector->select('users', [], [], ['id' => 'DESC']);
        $this->assertEquals($result->fetch(), [2,'root']);

        $result = $connector->select('users', [], [], ['id' => 'ASC']);
        $this->assertEquals($result->fetch(), [1,'admin']);
    }

    public function testMemory()
    {
        /**
         * @var Zaek\Data\Memory\Connector $connector
         */
        $connector = $this->_app->data();
        $connector->delete('users');

        $connector->insert('users', [
            'id' => 1,
            'name' => 'admin',
        ]);

        $result = $connector->select('users');

        $this->assertEquals(
            $result->fetch(),
            [1, 'admin']
        );
        $this->assertEquals(
            $result->fetch(),
            false
        );
        $this->assertEquals($connector->select('users')->getLength(), 1);

        $this->_app->data()->delete('users', [
            'name' => 'admin',
            'x' => 2,
        ]);

        $this->assertEquals($connector->select('users')->getLength(), 0);
    }

    public function testUpdate()
    {
        /**
         * @var Zaek\Data\Memory\Connector $connector
         */
        $connector = $this->_app->data();
        $connector->delete('users');

        $connector->insert('users', [
            'id' => 1,
            'name' => 'admin',
        ]);

        $connector->update('users', [
            'name' => 'user',
        ]);
        $this->assertEquals(
            $connector->select('users')->fetch(),
            [1,'user']
        );
    }

}