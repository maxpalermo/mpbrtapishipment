<?php
namespace MpSoft\MpBrtApiShipment\Api;

class Account
{
    public $userID;
    public $password;

    public function __construct($userID, $password)
    {
        $this->userID = $userID;
        $this->password = $password;
    }

    public static function fromArray($arr)
    {
        return new self($arr['userID'] ?? '', $arr['password'] ?? '');
    }

    public function toArray()
    {
        return [
            'userID' => $this->userID,
            'password' => $this->password,
        ];
    }
}
