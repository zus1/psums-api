<?php


class Sign
{
    public function getModel() {
        return Factory::getModel(Factory::MODEL_SIGN);
    }

    public function addSign(array $payload, string $streamId) {
        $sign = $this->getModel()->select(array("sign_key"), array("stream_id" => $streamId));
        if(!$sign) {
            throw new Exception("Sign failed");
        }
        $payload["sign"] = $sign[0]["sign_key"];

        return $payload;
    }
}