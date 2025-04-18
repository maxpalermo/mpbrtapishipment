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
        return new self(
            $arr['code'] ?? 0,
            $arr['severity'] ?? '',
            $arr['codeDesc'] ?? '',
            $arr['message'] ?? ''
        );
    }
}
