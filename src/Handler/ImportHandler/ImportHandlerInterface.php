<?php

namespace App\Handler\ImportHandler;

use App\Entity\Import;

interface ImportHandlerInterface {
    public function __invoke(Import $import): void;
}
