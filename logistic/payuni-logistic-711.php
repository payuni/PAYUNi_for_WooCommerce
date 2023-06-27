<?php

class PAYUNi_Logistic_711 extends PAYUNi_Logistic
{
    public function __construct($instance_id = 0)
    {
        $this->id                 = 'PAYUNi_Logistic_711';
        $this->method_title       = __('統一金流 7-ELEVEN 常溫', 'woocommerce');
        $this->method_description = '';
        parent::__construct($instance_id);
    }
}