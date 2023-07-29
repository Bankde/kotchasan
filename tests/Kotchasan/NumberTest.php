<?php

namespace Kotchasan;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2022-09-28 at 11:44:12.
 */
class NumberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Number
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new Number();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {

    }

    /**
     * Generated from @assert (100) [==] "100".
     *
     * @covers Kotchasan\Number::format
     */
    public function testFormat()
    {

        $this->assertEquals(
            "100",
            \Kotchasan\Number::format(100)
        );
    }

    /**
     * Generated from @assert (100.1) [==] "100.1".
     *
     * @covers Kotchasan\Number::format
     */
    public function testFormat2()
    {

        $this->assertEquals(
            "100.1",
            \Kotchasan\Number::format(100.1)
        );
    }

    /**
     * Generated from @assert (1000.12) [==] "1,000.12".
     *
     * @covers Kotchasan\Number::format
     */
    public function testFormat3()
    {

        $this->assertEquals(
            "1,000.12",
            \Kotchasan\Number::format(1000.12)
        );
    }

    /**
     * Generated from @assert (1000.1555) [==] "1,000.1555".
     *
     * @covers Kotchasan\Number::format
     */
    public function testFormat4()
    {

        $this->assertEquals(
            "1,000.1555",
            \Kotchasan\Number::format(1000.1555)
        );
    }

    /**
     * Generated from @assert (1, 2) [==] 0.5.
     *
     * @covers Kotchasan\Number::division
     */
    public function testDivision()
    {

        $this->assertEquals(
            0.5,
            \Kotchasan\Number::division(1, 2)
        );
    }

    /**
     * Generated from @assert (1, 0) [==] 0.
     *
     * @covers Kotchasan\Number::division
     */
    public function testDivision2()
    {

        $this->assertEquals(
            0,
            \Kotchasan\Number::division(1, 0)
        );
    }

    /**
     * Generated from @assert ('G%04d', 1) [==] "G0001".
     *
     * @covers Kotchasan\Number::printf
     */
    public function testPrintf()
    {

        $this->assertEquals(
            "G0001",
            \Kotchasan\Number::printf('G%04d', 1)
        );
    }

    /**
     * Generated from @assert ('G-%s-%04d', 1, 'PREFIX') [==] "G-PREFIX-0001".
     *
     * @covers Kotchasan\Number::printf
     */
    public function testPrintf2()
    {

        $this->assertEquals(
            "G-PREFIX-0001",
            \Kotchasan\Number::printf('G-%s-%04d', 1, 'PREFIX')
        );
    }
}