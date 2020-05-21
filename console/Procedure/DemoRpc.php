<?php
namespace Console\Procedure;

class DemoRpc
{
    public function test(int $value1, int $value2)
    {
        if ($value1 <= $value2) {
            return $value2;
        } else {
            $value1;
        }
    }
}
