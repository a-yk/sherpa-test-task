<?php

namespace App\Message;

class ImportMessage
{
    public function __construct(
        private int   $id,
    ) {}

    public function getId(): int
    {
        return $this->id;
    }
}
