<?php






class TestNamespace_Path_TestClass extends Example_ClassName1
{
    public function __construct()
    {
        $this->rootClass = new RootClass();
        $this->class1 = new Example_ClassName1();
        $this->class2 = new Example_ClassName2();
        $this->string = '';
        $this->integer = 0;
        $this->null = null;
        $this->bool = false;
    }

    /**
     * @param ClassName1 $class1
     */
    public function setClass1(Example_ClassName1 $class1, $array = array())
    {
        $this->class1 = $class1;
    }

    /**
     * @param AsClassName2 $class2
     */
    public function setClass2(Example_ClassName2 $class2, $array = [])
    {
        $this->class2 = $class2;
    }

    /**
     * @param Exception $exception
     */
    public function setException(Example_Exception $exception, $null = null)
    {
        $this->exception = $exception;
    }

    /**
     * @param \Exception $exception
     */
    public function setRootException(Exception $exception, $int = 10)
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
        Example_ClassName1::check();
        self::check();
        static::check();
        parent::check();
    }

    public function checkIf()
    {
        if (Example_ClassName1::check() == true) {
            return Example_ClassName1::getInstance();
        } elseif (TestNamespace_Path_ClassName2::check() == true) {
            return TestNamespace_Path_ClassName2::getInstance();
        }
    }

    public function checkSwitch()
    {
        switch (Example_ClassName1::check()) {
            case Example_ClassName1::FLAG1:
                return Example_ClassName1::getInstance();

            case Example_ClassName1::FLAG2:
            case Example_ClassName1::FLAG3:
                return Example_ClassName2::getInstance();

            default:
                throw new Example_Exception("Error Processing Request", 1);
                break;
        }
    }

    public function checkWhile()
    {
        while ($value = Example_ClassName1::next()) {
            Example_ClassName2::add($value);
        }
    }

    public function checkFor()
    {
        for ($i = Example_ClassName1::min(); $i < Example_ClassName1::max(); $i++) {
            Example_ClassName2::add($value);
        }

        for (; $i < Example_ClassName2::max();) {
            static::add($value);
        }

        for ($i = Example_ClassName1::min(), $k = Example_ClassName2::getK(); $i < Example_ClassName1::max() && Example_ClassName2::hasK(); $i++) {
            Example_ClassName2::add($value);
        }
    }

    public function checkFunctionNamespace()
    {
        check();
    }
}