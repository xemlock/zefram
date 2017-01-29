<?php

class Zefram_Stdlib_TestAsset_InvokableObject
{
    public function __invoke()
    {
        return 'foo';
    }
}
