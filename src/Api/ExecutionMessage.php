<?php

namespace MpSoft\MpBrtApiShipment\Api;

class ExecutionMessage
{
    public $code;
    public $severity;
    public $codeDesc;
    public $message;

    public function __construct($code, $severity, $codeDesc, $message)
    {
        $this->code = $code;
        $this->severity = $severity;
        $this->codeDesc = $codeDesc;
        $this->message = $message;
    }

    public static function fromArray($arr)
    {
        if (isset($arr['response']['deleteResponse']['executionMessage'])) {
            $arr = $arr['response']['deleteResponse']['executionMessage'];
        }

        return new self(
            $arr['code'] ?? -990,
            $arr['severity'] ?? 'ERROR',
            $arr['codeDesc'] ?? 'ExecutionMessage non trovato',
            $arr['message'] ?? 'Dati inviati non validi'
        );
    }

    public function toMsgError()
    {
        return implode('<br>', [
            "Codice: {$this->code}",
            "Gravità: {$this->severity}",
            "Descrizione: {$this->codeDesc}",
            "Messaggio: {$this->message}",
        ]);
    }

    public function hasError()
    {
        return $this->code < 0;
    }

    public function hasWarning()
    {
        return $this->code > 0;
    }

    public function isOk()
    {
        return 0 === $this->code;
    }
}
