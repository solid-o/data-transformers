<?php

declare(strict_types=1);

namespace Solido\DataTransformers\Exception;

use RuntimeException;
use Throwable;

class TransformationFailedException extends RuntimeException
{
    private ?string $invalidMessage;
    private array $invalidMessageParameters;

    public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null, ?string $invalidMessage = null, array $invalidMessageParameters = [])
    {
        parent::__construct($message, $code, $previous);

        $this->setInvalidMessage($invalidMessage, $invalidMessageParameters);
    }

    /**
     * Sets the message that will be shown to the user.
     *
     * @param string|null $invalidMessage           The message or message key
     * @param array       $invalidMessageParameters Data to be passed into the translator
     */
    public function setInvalidMessage(?string $invalidMessage = null, array $invalidMessageParameters = []): void
    {
        $this->invalidMessage = $invalidMessage;
        $this->invalidMessageParameters = $invalidMessageParameters;
    }

    public function getInvalidMessage(): ?string
    {
        return $this->invalidMessage;
    }

    public function getInvalidMessageParameters(): array
    {
        return $this->invalidMessageParameters;
    }
}
