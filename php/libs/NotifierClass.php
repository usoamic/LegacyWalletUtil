<?php

trait NotifierClass
{
    public
        $error,
        $response;

    /*
     * Protected
     */
    protected function failure($err = 2, $text = '') {
        $this->error = ((is_int($err)) ? get_string($err) : $err).(!is_empty($text) ? $text : '');
        return false;
    }

    protected function success($err = 2) {
        $this->response = ((is_int($err)) ? get_string($err) : $err);
        return true;
    }

    protected function setError($error)
    {
        $this->error = $error;
    }

    protected function setResponse($response)
    {
        $this->response = $response;
    }

    /*
     * Public
     */
    public function clearError() {
        $this->error = '';
    }

    public function clearResponse() {
        $this->response = '';
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function getError()
    {
        return $this->error;
    }
}

?>