<?php

namespace App\Models;

class Quote extends Quotation
{
    // Production cleanup: this alias model inherits quotation storage rules explicitly.
    protected $hidden = [
        'approval_token',
        'pdf_token',
    ];
}
