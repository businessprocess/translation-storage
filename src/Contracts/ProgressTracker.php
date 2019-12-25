<?php

namespace Translate\StorageManager\Contracts;

interface ProgressTracker
{
    public function beforeStart(): void;

    public function afterFinish(): void;

    public function beforeBatch(int $page): void;

    public function afterBatch(array $response): void;
}
