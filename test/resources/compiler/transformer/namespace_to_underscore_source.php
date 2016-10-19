<?php

namespace TestNamespace\Path;

use Example\ClassName1;
use Example\ClassName2 as AsClassName2;
use Example\Exception;

class TestClass extends ClassName1
{
    public function __construct()
    {
        $this->rootClass = new \RootClass();
        $this->class1 = new ClassName1();
        $this->class2 = new AsClassName2();
        $this->string = '';
        $this->integer = 0;
        $this->null = null;
        $this->bool = false;
    }

    /**
     * @param ClassName1 $class1
     */
    public function setClass1(ClassName1 $class1, $array = array())
    {
        $this->class1 = $class1;
    }

    /**
     * @param AsClassName2 $class2
     */
    public function setClass2(AsClassName2 $class2, $array = [])
    {
        $this->class2 = $class2;
    }

    /**
     * @param Exception $exception
     */
    public function setException(Exception $exception, $null = null)
    {
        $this->exception = $exception;
    }

    /**
     * @param \Exception $exception
     */
    public function setRootException(\Exception $exception, $int = 10)
    {
        $this->exception = $exception;
    }

    /**
     * @param string $string
     */
    public function setString($string = 'string')
    {
        $this->string = $string;
    }

    /**
     * @param int $integer
     */
    public function setInteger($integer = 20)
    {
        $this->integer = $integer;
    }

    /**
     * @param int $integer
     */
    public function setNull($null = null)
    {
        $this->null = $null;
    }

    /**
     * @param bool $bool
     */
    public function setBool($bool = true)
    {
        $this->bool = $bool;
    }

    public function checkStatic()
    {
        ClassName1::check();
        self::check();
        static::check();
        parent::check();
    }

    public function checkIf()
    {
        if (ClassName1::check() == true) {
            return ClassName1::getInstance();
        } elseif (ClassName2::check() == true) {
            return ClassName2::getInstance();
        }
    }

    public function checkSwitch()
    {
        switch (ClassName1::check()) {
            case ClassName1::FLAG1:
                return ClassName1::getInstance();

            case ClassName1::FLAG2:
            case ClassName1::FLAG3:
                return AsClassName2::getInstance();

            default:
                throw new Exception("Error Processing Request", 1);
                break;
        }
    }

    public function checkWhile()
    {
        while ($value = ClassName1::next()) {
            AsClassName2::add($value);
        }
    }

    public function checkFor()
    {
        for ($i = ClassName1::min(); $i < ClassName1::max(); $i++) {
            AsClassName2::add($value);
        }

        for (; $i < AsClassName2::max();) {
            static::add($value);
        }

        for ($i = ClassName1::min(), $k = AsClassName2::getK(); $i < ClassName1::max() && AsClassName2::hasK(); $i++) {
            AsClassName2::add($value);
        }
    }

    public function checkFunctionNamespace()
    {
        \check();
    }
}