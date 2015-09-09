<?php

namespace PWE\Core;

interface PWECMDJob
{

    public function __construct(PWECore $PWE);

    public function run();
}

