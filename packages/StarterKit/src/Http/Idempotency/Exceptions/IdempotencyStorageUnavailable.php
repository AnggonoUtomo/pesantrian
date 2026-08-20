<?php

declare(strict_types=1);

namespace StarterKit\Http\Idempotency\Exceptions;

use RuntimeException;

final class IdempotencyStorageUnavailable extends RuntimeException {}
