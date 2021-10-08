<?php

use PHPUnit\Framework\TestCase;

/**
 * Test clipboard repository
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class EventTest extends TestCase
{
    //protected $backupGlobals = false;

    protected function setUp() : void
    {
        $dic = new ILIAS\DI\Container();
        $GLOBALS['DIC'] = $dic;

        define("ILIAS_LOG_ENABLED", false);

        parent::setUp();

        $db_mock = $this->createMock(ilDBInterface::class);
        $db_mock->method("fetchAssoc")
            ->will(
                $this->onConsecutiveCalls(
                    [
                     "component" => "Services/EventHandling",
                     "id" => "MyTestComponent"
                 ],
                    null
                )
            );

        $pa_mock = $this->createMock(ilPluginAdmin::class);
        $pa_mock->method("getActivePluginsForSlot")
            ->will(
                $this->onConsecutiveCalls(
                    []
                )
            );

        $this->setGlobalVariable(
            "ilDB",
            $db_mock
        );
        $this->setGlobalVariable(
            "ilPluginAdmin",
            $pa_mock
        );
        $this->setGlobalVariable(
            "ilSetting",
            $this->createMock(ilSetting::class)
        );
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    protected function setGlobalVariable(string $name, $value) : void
    {
        global $DIC;

        $GLOBALS[$name] = $value;

        unset($DIC[$name]);
        $DIC[$name] = static function (\ILIAS\DI\Container $c) use ($value) {
            return $value;
        };
    }

    protected function tearDown() : void
    {
    }

    protected function getHandler() : ilAppEventHandler
    {
        return new ilAppEventHandler();
    }

    /**
     * Test event
     */
    public function testEvent()
    {
        $handler = $this->getHandler();

        $this->expectException(ilEventHandlingTestException::class);

        $handler->raise(
            "MyTestComponent",
            "MyEvent",
            [
                "par1" => "val1",
                "par2" => "val2"
            ]
        );
    }
}
