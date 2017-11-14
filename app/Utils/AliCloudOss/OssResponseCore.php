<?php
namespace App\Utils\AliCloudOss;

/**
 * Container for all response-related methods.
 */
class OssResponseCore
{
    /**
     * Stores the HTTP header information.
     */
    public $header;

    /**
     * Stores the SimpleXML response.
     */
    public $body;

    /**
     * Stores the HTTP response code.
     */
    public $status;

    public function setHeader($header)
    {
        $this->header = $header;

        return $this;
    }

    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    public function setStatus($status = null)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Did we receive the status code we expected?
     *
     * @param integer|array $codes (Optional) The status code(s) to expect. Pass an <php:integer> for a single
     *                             acceptable value, or an <php:array> of integers for multiple acceptable values.
     *
     * @return boolean Whether we received the expected status code or not.
     */
    public function isOK($codes = [200, 201, 204, 206])
    {
        if (is_array($codes)) {
            return in_array($this->status, $codes);
        }

        return $this->status === $codes;
    }
}
