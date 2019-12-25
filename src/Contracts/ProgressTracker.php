<?php

namespace Translate\StorageManager\Contracts;

interface ProgressTracker
{
    public function beforeBatch(array &$response): void;

    public function afterBatch(array &$response): void;
}
