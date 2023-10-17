<?php

class PAYUNi_Logistic_Tcat extends PAYUNi_Logistic
{
    public function __construct($instance_id = 0)
    {
        $this->id                 = 'PAYUNi_Logistic_Tcat';
        $this->method_title       = __('統一金流 黑貓宅配 常溫', 'woocommerce');
        $this->method_description = '';
        parent::__construct($instance_id);
    }
}