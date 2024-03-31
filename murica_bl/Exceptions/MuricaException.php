<?php

namespace murica_bl\Exceptions;

use Exception;

class MuricaException extends Exception {
    public function getTraceMessages(): string {
        if ($this->getPrevious() instanceof MuricaException) {
            return $this->message . ': ' . $this->getPrevious()->getTraceMessages();
        }
        if ($this->getPrevious()) {
            return $this->message . ': ' . $this->getPrevious()->getMessage();
        }

        return $this->message;
    }
}