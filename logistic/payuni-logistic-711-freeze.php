<?php

class PAYUNi_Logistic_711_Freeze extends PAYUNi_Logistic
{
    public function __construct($instance_id = 0)
    {
        $this->id                 = 'PAYUNi_Logistic_711_Freeze';
        $this->method_title       = __('統一金流 7-ELEVEN 冷凍', 'woocommerce');
        $this->method_description = '';
        parent::__construct($instance_id);
    }
}