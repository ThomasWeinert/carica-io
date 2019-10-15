<?php

namespace Carica\Io\Deferred {

  interface PromiseLike {

    /**
     * Default state - not yet finalized
     * @var string
     */
    public const STATE_PENDING = 'pending';
    /**
     * Final state, object was resolved, the action was successful
     * @var string
     */
    public const STATE_RESOLVED = 'resolved';

    /**
     * Final state, object was rejected, the action failed
     *
     * @var string
     */
    public const STATE_REJECTED = 'rejected';

    public function always(callable $callback): self;

    public function done(callable $callback): self;

    public function fail(callable $callback): self;

    public function progress(callable $callback): self;

    public function state(): string;

    public function then(
      callable $doneFilter = NULL,
      callable $failFilter = NULL,
      callable $progressFilter = NULL
    ): self;
  }
}
