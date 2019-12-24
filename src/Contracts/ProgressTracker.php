<?php

namespace Translate\StorageManager\Contracts;

interface ProgressTracker
{
    public function beforeBatch(array &$batch): void;

    public function afterBatch(array &$batch): void;

    public function beforeItem(array &$item): void;

    public function afterItem(array &$item): void;
}
